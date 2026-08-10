/**
 * محرّك صوت «المستشار الذكي»: رسالة صوتية مسجّلة + مكالمة حيّة.
 *
 * الصوت يعمل على WebSocket لا يستطيع Laravel تمريره، فهذا هو الاتصال المباشر
 * الوحيد بالخدمة. عنوان القناة يأتي من الباك إند (/ai/config) ولا يُكتب هنا.
 *
 * البروتوكول: نرسل {type:'record_utterance'} للرسالة المسجّلة، أو {type:'start_live'}
 * ثم إطارات PCM ثنائية للمكالمة. ويعود النص عبر transcript_interim
 * و transcript_final و assistant_text، والصوت إطارات PCM بين audio_start و audio_end.
 */
(function initAiChatVoice() {
  const LIVE_READY_TIMEOUT = 15000;
  const PROCESSING_TIMEOUT = 60000;
  const MIN_RECORDING_BYTES = 800;

  function toBase64(buffer) {
    const bytes = new Uint8Array(buffer);
    const chunkSize = 0x8000;
    let binary = '';
    for (let i = 0; i < bytes.length; i += chunkSize) {
      binary += String.fromCharCode.apply(null, bytes.subarray(i, i + chunkSize));
    }
    return btoa(binary);
  }

  function toPcm16(float32) {
    const out = new Int16Array(float32.length);
    for (let i = 0; i < float32.length; i++) {
      const s = Math.max(-1, Math.min(1, float32[i]));
      out[i] = s < 0 ? s * 0x8000 : s * 0x7fff;
    }
    return out;
  }

  function noop() {}

  /**
   * الخدمة قد تُمرّر نص خطأ المزوّد الخارجي كما هو (مفاتيح، حصص، روابط توثيق).
   * لا نعرضه للمستخدم أبداً: نستنتج منه سبباً مفهوماً ونترك الأصل في الكونسول.
   */
  function friendlyError(raw) {
    const text = String(raw == null ? '' : raw);
    if (text && window.console && console.debug) console.debug('[ai-voice]', text);

    if (/api[\s_-]?key|unauthor|authenticat|forbidden|\b40[13]\b/i.test(text)) {
      return 'خدمة الصوت غير متاحة حالياً. يمكنك متابعة الاستفسار بالمحادثة النصية.';
    }
    if (/quota|credit|balance|limit|\b429\b/i.test(text)) {
      return 'تم استهلاك حصة الخدمة الصوتية حالياً. يرجى المحاولة لاحقاً.';
    }
    if (/timeout|timed out/i.test(text)) {
      return 'انتهت مهلة الاستجابة الصوتية. يرجى المحاولة مرة أخرى.';
    }
    return 'تعذر إكمال الطلب الصوتي حالياً. يرجى المحاولة مرة أخرى.';
  }

  function create(options) {
    const socketUrl = options.socketUrl;
    const ttsRate = options.ttsSampleRate || 24000;
    const liveRate = options.liveSampleRate || 16000;
    // إطارات ~80ms تجعل الوصل بين الحزم بلا فراغات ولا نقرات.
    const flushSamples = Math.floor(ttsRate * 0.08);

    const on = {
      userText: options.onUserText || noop,
      advisorText: options.onAdvisorText || noop,
      system: options.onSystem || noop,
      error: options.onError || noop,
      state: options.onState || noop,
      level: options.onLevel || noop,
      recording: options.onRecording || noop,
      call: options.onCall || noop,
      timer: options.onTimer || noop,
    };

    let ws = null;
    let stream = null;
    let audioCtx = null;
    let sourceNode = null;
    let analyser = null;
    let processor = null;
    let levelRaf = null;

    let recorder = null;
    let recording = false;
    let recordPaused = false;
    let discardOnStop = false;
    let recordStartedAt = null;
    let recordPausedTotal = 0;
    let recordPausedAt = null;
    let recordTimerId = null;
    let processingTimer = null;

    let callActive = false;
    let callConnecting = false;
    let callCancelled = false;
    let callStartedAt = null;
    let callTimerId = null;
    let muted = false;
    let micPaused = false;
    let micResumeTimer = null;

    let playCtx = null;
    let playHead = 0;
    let pcmOddByte = null;
    let pcmQueue = [];
    let pcmQueued = 0;
    let playbackChain = Promise.resolve();
    let ttsPlaying = false;

    /* ── تشغيل صوت المستشار ─────────────────────────────────────────────── */

    async function ensurePlayCtx() {
      if (!playCtx) {
        playCtx = new (window.AudioContext || window.webkitAudioContext)({ sampleRate: ttsRate });
        playHead = playCtx.currentTime;
      }
      if (playCtx.state === 'suspended') await playCtx.resume();
      return playCtx;
    }

    function resetPlayback(ctx) {
      pcmOddByte = null;
      pcmQueue = [];
      pcmQueued = 0;
      playbackChain = Promise.resolve();
      playHead = ctx ? ctx.currentTime + 0.08 : 0;
    }

    function stopPlayback() {
      ttsPlaying = false;
      if (micResumeTimer) {
        clearTimeout(micResumeTimer);
        micResumeTimer = null;
      }
      resetPlayback(null);
      if (playCtx) {
        playCtx.close().catch(noop);
        playCtx = null;
      }
    }

    function isSpeakerBusy() {
      return !!(playCtx && playHead > playCtx.currentTime + 0.05);
    }

    function takeSamples(count) {
      const out = new Float32Array(count);
      let offset = 0;
      while (offset < count && pcmQueue.length) {
        const head = pcmQueue[0];
        const need = count - offset;
        if (head.length <= need) {
          out.set(head, offset);
          offset += head.length;
          pcmQueue.shift();
        } else {
          out.set(head.subarray(0, need), offset);
          pcmQueue[0] = head.subarray(need);
          offset += need;
        }
      }
      pcmQueued -= offset;
      return out;
    }

    async function scheduleChunk(samples) {
      if (!samples.length) return;
      const ctx = await ensurePlayCtx();
      const buffer = ctx.createBuffer(1, samples.length, ttsRate);
      buffer.copyToChannel(samples, 0);
      const source = ctx.createBufferSource();
      source.buffer = buffer;
      source.connect(ctx.destination);
      // لا نُدخل صمتاً بين الحزم؛ نتقدّم فقط عند نقص فعلي في المخزون.
      if (playHead < ctx.currentTime + 0.01) playHead = ctx.currentTime + 0.04;
      source.start(playHead);
      playHead += buffer.duration;
    }

    function flushPcm(force) {
      while (pcmQueued >= flushSamples || (force && pcmQueued > 0)) {
        const count = pcmQueued >= flushSamples ? flushSamples : pcmQueued;
        if (count <= 0) break;
        if (!force && pcmQueued < flushSamples) break;
        const chunk = takeSamples(count);
        playbackChain = playbackChain.then(() => scheduleChunk(chunk)).catch(noop);
      }
    }

    function queuePcm(arrayBuffer) {
      let bytes = new Uint8Array(arrayBuffer);
      if (pcmOddByte !== null) {
        const merged = new Uint8Array(1 + bytes.length);
        merged[0] = pcmOddByte;
        merged.set(bytes, 1);
        bytes = merged;
        pcmOddByte = null;
      }
      if (bytes.length % 2 === 1) {
        pcmOddByte = bytes[bytes.length - 1];
        bytes = bytes.subarray(0, bytes.length - 1);
      }
      if (bytes.length < 2) return;

      // DataView يتجنّب مشكلة المحاذاة عندما يكون byteOffset فردياً.
      const view = new DataView(bytes.buffer, bytes.byteOffset, bytes.byteLength);
      const samples = new Float32Array(bytes.byteLength / 2);
      for (let i = 0; i < samples.length; i++) samples[i] = view.getInt16(i * 2, true) / 32768;
      pcmQueue.push(samples);
      pcmQueued += samples.length;
      flushPcm(false);
    }

    function waitForDrain() {
      return ensurePlayCtx().then((ctx) => new Promise((resolve) => {
        function poll() {
          playbackChain.then(() => {
            if (ctx.currentTime >= playHead - 0.08) resolve();
            else setTimeout(poll, 40);
          });
        }
        poll();
      }));
    }

    /* ── الميكروفون ─────────────────────────────────────────────────────── */

    async function ensureMic() {
      if (stream) return;
      if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
        throw new Error('المتصفح لا يدعم التسجيل الصوتي.');
      }
      try {
        stream = await navigator.mediaDevices.getUserMedia({
          audio: { echoCancellation: true, noiseSuppression: true, autoGainControl: true },
        });
      } catch (err) {
        throw new Error(
          err && (err.name === 'NotAllowedError' || err.name === 'SecurityError')
            ? 'لم يُسمح باستخدام الميكروفون. اسمح به من إعدادات المتصفح.'
            : 'تعذر الوصول إلى الميكروفون.'
        );
      }
      audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      sourceNode = audioCtx.createMediaStreamSource(stream);
      analyser = audioCtx.createAnalyser();
      analyser.fftSize = 256;
      sourceNode.connect(analyser);
      if (audioCtx.state === 'suspended') await audioCtx.resume();
    }

    function releaseMic() {
      stopLevelMeter();
      if (processor) {
        processor.disconnect();
        processor = null;
      }
      if (stream) {
        stream.getTracks().forEach((track) => track.stop());
        stream = null;
      }
      sourceNode = null;
      analyser = null;
      if (audioCtx) {
        audioCtx.close().catch(noop);
        audioCtx = null;
      }
    }

    function setMicEnabled(enabled) {
      if (stream) stream.getAudioTracks().forEach((track) => { track.enabled = enabled; });
    }

    function applyMicTransmit() {
      setMicEnabled(callActive && !muted && !micPaused);
    }

    function startLevelMeter() {
      if (!analyser || levelRaf) return;
      const data = new Uint8Array(analyser.frequencyBinCount);
      const tick = () => {
        if (!analyser) return;
        analyser.getByteFrequencyData(data);
        let sum = 0;
        for (let i = 0; i < data.length; i++) sum += data[i];
        on.level(Math.min(1, (sum / data.length) / 90));
        levelRaf = requestAnimationFrame(tick);
      };
      levelRaf = requestAnimationFrame(tick);
    }

    function stopLevelMeter() {
      if (levelRaf) {
        cancelAnimationFrame(levelRaf);
        levelRaf = null;
      }
      on.level(0);
    }

    /* ── القناة ─────────────────────────────────────────────────────────── */

    function connect() {
      return new Promise((resolve, reject) => {
        if (!socketUrl) {
          reject(new Error('قناة الصوت غير مضبوطة.'));
          return;
        }
        let socket;
        try {
          socket = new WebSocket(socketUrl);
        } catch (_) {
          reject(new Error('تعذر الاتصال بالمستشار الصوتي.'));
          return;
        }
        socket.binaryType = 'arraybuffer';
        socket.addEventListener('open', () => resolve(socket));
        socket.addEventListener('error', () => reject(new Error('تعذر الاتصال بالمستشار الصوتي.')));
        socket.addEventListener('close', () => { if (ws === socket) ws = null; });
        socket.addEventListener('message', handleMessage);
      });
    }

    function send(payload) {
      if (ws && ws.readyState === WebSocket.OPEN) {
        ws.send(JSON.stringify(payload));
        return true;
      }
      return false;
    }

    function clearProcessingTimer() {
      if (processingTimer) {
        clearTimeout(processingTimer);
        processingTimer = null;
      }
    }

    function handleMessage(event) {
      if (event.data instanceof ArrayBuffer) {
        queuePcm(event.data);
        return;
      }
      let msg;
      try { msg = JSON.parse(event.data); } catch (_) { return; }

      if (msg.type === 'ready' || msg.type === 'live_ready') {
        if (callConnecting) on.state('connecting', 'جارٍ الاتصال...');
        else if (callActive) on.state('listening', 'تحدّث الآن');
        return;
      }

      if (msg.type === 'transcript_interim') {
        if (!micPaused) on.userText(msg.text, true);
        return;
      }

      if (msg.type === 'transcript_final') {
        on.userText(msg.text, false);
        if (callActive) pauseMic();
        return;
      }

      if (msg.type === 'assistant_text') {
        clearProcessingTimer();
        on.advisorText(msg.text);
        if (callActive) pauseMic();
        return;
      }

      if (msg.type === 'audio_start') {
        clearProcessingTimer();
        ttsPlaying = true;
        // نُفرغ الطابور فوراً كي لا يمحو إعادةُ ضبط متأخرة إطاراتٍ وصلت مبكراً.
        resetPlayback(null);
        ensurePlayCtx().then((ctx) => {
          if (playHead <= 0) playHead = ctx.currentTime + 0.08;
        });
        if (callActive) pauseMic();
        else on.state('speaking', 'المستشار يتحدّث...');
        return;
      }

      if (msg.type === 'audio_end') {
        flushPcm(true);
        if (callActive) resumeMicAfterPlayback();
        else {
          on.state('speaking', 'المستشار يتحدّث...');
          waitForDrain().then(() => {
            ttsPlaying = false;
            on.state('idle', '');
          }).catch(() => { ttsPlaying = false; });
        }
        return;
      }

      if (msg.type === 'error') {
        ttsPlaying = false;
        clearProcessingTimer();
        stopLevelMeter();
        on.error(friendlyError(msg.message));
        if (callActive) resumeMicAfterPlayback(200);
      }
    }

    /* ── إدارة الميكروفون أثناء المكالمة ────────────────────────────────── */

    function pauseMic() {
      micPaused = true;
      if (micResumeTimer) {
        clearTimeout(micResumeTimer);
        micResumeTimer = null;
      }
      applyMicTransmit();
      if (callActive && !muted) on.state('speaking', 'المستشار يرد — لا تتحدّث الآن');
      emitCall();
    }

    /** لا نُعيد فتح الميكروفون قبل أن ينتهي صوت المستشار فعلياً من السمّاعة. */
    function resumeMicAfterPlayback(extraDelay) {
      const delay = typeof extraDelay === 'number' ? extraDelay : 700;
      if (micResumeTimer) clearTimeout(micResumeTimer);
      playbackChain
        .then(() => waitForDrain())
        .then(() => new Promise((resolve) => { micResumeTimer = setTimeout(resolve, delay); }))
        .then(() => {
          micResumeTimer = null;
          ttsPlaying = false;
          if (!callActive) return;
          micPaused = false;
          applyMicTransmit();
          on.state('listening', muted ? 'الميكروفون مكتوم' : 'تحدّث الآن');
          emitCall();
          send({ type: 'playback_done' });
        })
        .catch(() => { ttsPlaying = false; });
    }

    /* ── الرسالة الصوتية المسجّلة ───────────────────────────────────────── */

    function recordedMs() {
      if (!recordStartedAt) return 0;
      const pausedNow = recordPausedAt ? Date.now() - recordPausedAt : 0;
      return Date.now() - recordStartedAt - recordPausedTotal - pausedNow;
    }

    function emitRecording() {
      on.recording({ active: recording, paused: recordPaused, ms: recordedMs() });
    }

    function stopRecordTimer() {
      if (recordTimerId) {
        clearInterval(recordTimerId);
        recordTimerId = null;
      }
      recordStartedAt = null;
      recordPausedTotal = 0;
      recordPausedAt = null;
    }

    async function startRecording() {
      if (recording || callActive || callConnecting) return;
      if (ttsPlaying || isSpeakerBusy()) {
        on.state('speaking', 'انتظر حتى ينتهي المستشار من الرد');
        return;
      }
      if (!window.MediaRecorder) {
        on.error('المتصفح لا يدعم التسجيل الصوتي.');
        return;
      }

      try {
        stopPlayback();
        on.state('connecting', 'جارٍ الاتصال...');
        if (!ws || ws.readyState !== WebSocket.OPEN) ws = await connect();
        await ensureMic();

        const mimetype = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
          ? 'audio/webm;codecs=opus'
          : 'audio/webm';
        recorder = new MediaRecorder(stream, { mimeType: mimetype });
        const chunks = [];
        discardOnStop = false;
        recordPaused = false;
        recordPausedTotal = 0;
        recordPausedAt = null;

        recorder.ondataavailable = (e) => { if (e.data.size) chunks.push(e.data); };
        recorder.onstop = async () => {
          const discard = discardOnStop;
          discardOnStop = false;
          recording = false;
          recordPaused = false;
          stopLevelMeter();
          stopRecordTimer();
          emitRecording();

          if (discard) {
            on.state('idle', '');
            return;
          }
          if (!chunks.length) {
            on.error('لم يُسجَّل صوت — حاول مجدداً.');
            return;
          }

          on.state('processing', 'جارٍ تحليل صوتك...');
          processingTimer = setTimeout(() => {
            clearProcessingTimer();
            on.error('تعذر تحليل الصوت حالياً.');
          }, PROCESSING_TIMEOUT);

          try {
            const blob = new Blob(chunks, { type: mimetype });
            if (blob.size < MIN_RECORDING_BYTES) throw new Error('التسجيل قصير جداً — حاول مجدداً.');
            const base64 = toBase64(await blob.arrayBuffer());
            if (!send({ type: 'record_utterance', audio_base64: base64, mimetype })) {
              throw new Error('انقطع الاتصال — حاول مجدداً.');
            }
          } catch (err) {
            clearProcessingTimer();
            on.error(err.message);
          }
        };

        recorder.start(250);
        recording = true;
        recordStartedAt = Date.now();
        recordTimerId = setInterval(emitRecording, 250);
        emitRecording();
        on.state('recording', 'جارٍ التسجيل...');
        startLevelMeter();
      } catch (err) {
        on.error(err.message || 'تعذر بدء التسجيل.');
      }
    }

    function stopRecorder() {
      if (!recorder || recorder.state === 'inactive') return;
      try { if (recorder.state === 'paused') recorder.resume(); } catch (_) {}
      recorder.stop();
    }

    function sendRecording() {
      if (!recording) return;
      discardOnStop = false;
      stopRecorder();
    }

    function discardRecording() {
      if (!recording) return;
      discardOnStop = true;
      stopRecorder();
    }

    function togglePauseRecording() {
      if (!recording || !recorder) return;
      try {
        if (recorder.state === 'recording') {
          recorder.pause();
          recordPausedAt = Date.now();
          recordPaused = true;
          on.state('recording', 'التسجيل متوقف مؤقتاً');
        } else if (recorder.state === 'paused') {
          if (recordPausedAt) {
            recordPausedTotal += Date.now() - recordPausedAt;
            recordPausedAt = null;
          }
          recorder.resume();
          recordPaused = false;
          on.state('recording', 'جارٍ التسجيل...');
        }
        emitRecording();
      } catch (err) {
        on.error('تعذر إيقاف التسجيل أو متابعته.');
      }
    }

    /* ── المكالمة الحيّة ────────────────────────────────────────────────── */

    function callMs() {
      return callStartedAt ? Date.now() - callStartedAt : 0;
    }

    function emitCall() {
      on.call({
        active: callActive,
        connecting: callConnecting,
        muted,
        advisorSpeaking: micPaused,
        ms: callMs(),
      });
    }

    async function startCall() {
      if (callActive || callConnecting) return;
      if (recording) {
        on.error('أنهِ التسجيل الصوتي قبل بدء المكالمة.');
        return;
      }
      if (ttsPlaying || isSpeakerBusy()) {
        on.state('speaking', 'انتظر حتى ينتهي المستشار من الرد');
        return;
      }

      callCancelled = false;
      callConnecting = true;
      emitCall();
      on.state('connecting', 'جارٍ الاتصال...');

      try {
        if (!ws || ws.readyState !== WebSocket.OPEN) ws = await connect();
        if (callCancelled) return;
        await ensureMic();
        if (callCancelled) return;

        await new Promise((resolve, reject) => {
          const timeout = setTimeout(() => {
            ws.removeEventListener('message', onReady);
            reject(new Error('انتهت مهلة بدء المكالمة.'));
          }, LIVE_READY_TIMEOUT);

          function onReady(event) {
            if (event.data instanceof ArrayBuffer) return;
            let msg;
            try { msg = JSON.parse(event.data); } catch (_) { return; }
            if (msg.type === 'live_ready') {
              clearTimeout(timeout);
              ws.removeEventListener('message', onReady);
              resolve();
            } else if (msg.type === 'error') {
              clearTimeout(timeout);
              ws.removeEventListener('message', onReady);
              reject(new Error(friendlyError(msg.message)));
            }
          }

          ws.addEventListener('message', onReady);
          if (!send({ type: 'start_live' })) {
            clearTimeout(timeout);
            ws.removeEventListener('message', onReady);
            reject(new Error('انقطع الاتصال — حاول مجدداً.'));
          }
        });
        if (callCancelled) return;

        processor = audioCtx.createScriptProcessor(4096, 1, 1);
        const silent = audioCtx.createGain();
        silent.gain.value = 0;
        const inputRate = audioCtx.sampleRate;
        callActive = true;

        processor.onaudioprocess = (e) => {
          if (!callActive || micPaused || muted) return;
          if (!ws || ws.readyState !== WebSocket.OPEN) return;
          const input = e.inputBuffer.getChannelData(0);
          const ratio = inputRate / liveRate;
          const outLength = Math.floor(input.length / ratio);
          const downsampled = new Float32Array(outLength);
          for (let i = 0; i < outLength; i++) downsampled[i] = input[Math.floor(i * ratio)];
          ws.send(toPcm16(downsampled).buffer);
        };
        sourceNode.connect(processor);
        processor.connect(silent);
        silent.connect(audioCtx.destination);

        callConnecting = false;
        muted = false;
        micPaused = false;
        callStartedAt = Date.now();
        callTimerId = setInterval(emitCall, 500);
        applyMicTransmit();
        emitCall();
        on.state('listening', 'تحدّث الآن');
        startLevelMeter();
      } catch (err) {
        if (callCancelled) return;
        callActive = false;
        callConnecting = false;
        muted = false;
        emitCall();
        on.error(err.message || 'تعذر بدء المكالمة.');
      }
    }

    function endCall(silentNotice) {
      if (!callActive && !callConnecting) return;

      const wasActive = callActive;
      const duration = callMs();

      callCancelled = true;
      callActive = false;
      callConnecting = false;
      micPaused = false;
      muted = false;
      if (callTimerId) {
        clearInterval(callTimerId);
        callTimerId = null;
      }
      callStartedAt = null;

      // إنهاء المكالمة يقطع المستشار في منتصف كلامه، كالهاتف تماماً.
      stopPlayback();
      setMicEnabled(true);
      clearProcessingTimer();
      releaseMic();

      if (ws && ws.readyState === WebSocket.OPEN) {
        send({ type: 'stop_live' });
        try { ws.close(); } catch (_) {}
      }
      ws = null;

      emitCall();
      if (!silentNotice) {
        on.system(wasActive ? `انتهت المكالمة · المدة ${formatDuration(duration)}` : 'تم إلغاء الاتصال.');
      }
      on.state('idle', '');
    }

    function toggleMute() {
      if (!callActive) return;
      muted = !muted;
      applyMicTransmit();
      if (muted) {
        // الكتم يوقف تدفّق الصوت الذي يحتاجه التعرّف لإنهاء الجملة،
        // فنطلب من الخدمة إنهاء ما هو قيد المعالجة كي يصل الرد.
        if (!micPaused && !ttsPlaying) send({ type: 'finalize_utterance' });
        on.state('listening', 'الميكروفون مكتوم');
      } else {
        on.state('listening', micPaused ? 'المستشار يرد — لا تتحدّث الآن' : 'تحدّث الآن');
      }
      emitCall();
    }

    function formatDuration(ms) {
      const total = Math.max(0, Math.floor(ms / 1000));
      return `${Math.floor(total / 60)}:${String(total % 60).padStart(2, '0')}`;
    }

    function destroy() {
      if (recording) discardRecording();
      endCall(true);
      stopPlayback();
      releaseMic();
      if (ws) {
        try { ws.close(); } catch (_) {}
        ws = null;
      }
    }

    return {
      startRecording,
      sendRecording,
      discardRecording,
      togglePauseRecording,
      startCall,
      endCall,
      toggleMute,
      destroy,
      formatDuration,
      isRecording: () => recording,
      isCallBusy: () => callActive || callConnecting,
      isBusy: () => recording || callActive || callConnecting || ttsPlaying,
    };
  }

  window.AiChatVoice = { create };
})();
