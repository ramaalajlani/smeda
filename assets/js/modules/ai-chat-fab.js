/**
 * «المستشار الذكي» — زر عائم + نافذة داخل نفس الصفحة.
 *
 * المحادثة النصية وتصنيف ISIC4 وسجل المحادثات تمر كلها عبر Laravel.
 * الصوت والمكالمة وحدهما يعملان على WebSocket لا يستطيع PHP تمريره، فيتولاهما
 * ai-chat-voice.js بعنوان قناة يأتي من الباك إند عبر /ai/config.
 *
 * session_id تديره الخدمة على السيرفر، فلا ترسله الواجهة.
 */
(function initAiChatFab() {
  const ROOT_ID = 'aiChatFabRoot';
  const STYLE_ID = 'ai-chat-fab-style';
  const MAX_LEN = 5000;
  const MAX_DESC_LEN = 2000;
  const WAVE_BARS = 30;
  const GENERIC_ERROR = 'تعذر الحصول على رد حالياً.';

  const SUGGESTIONS = [
    'ما هي الخدمات المتاحة؟',
    'كيف أستفيد من خدمات المنصة؟',
    'لدي استفسار عن مشروع',
  ];

  // صفحات فيها زر AI عائم آخر (اقتراح التصنيف) — نزيح زرنا جانبياً لتفادي التراكب.
  const OFFSET_PAGES = /need-create|need-view/i;

    let busy = false;
  let voice = null;
  let capabilities = null;
  let departmentId = null;
  let interimRow = null;
  let waveValues = new Array(WAVE_BARS).fill(0);

  function ensureStyles() {
    if (document.getElementById(STYLE_ID)) return;
    const style = document.createElement('style');
    style.id = STYLE_ID;
    style.textContent = `
      /* ثيم مطابق حرفياً لـ ai.smedc-sy.tech */
      #${ROOT_ID}{
        --aic-primary:#17947B;
        --aic-dark:#0F5F4F;
        --aic-accent:#06AA89;
        --aic-soft:rgba(234,248,244,.14);
        --aic-text:#eef0f4;
        --aic-muted:#8b93a3;
        --aic-bad:#e0605d;
        --aic-bg:#0b0d12;
        --aic-surface:#14171f;
        --aic-elev:#1b1f29;
        --aic-line:#262b36;
        --aic-grad:linear-gradient(135deg,#17947B 0%,#06AA89 100%);
        --aic-card:0 2px 10px rgba(0,0,0,.18),0 12px 28px rgba(0,0,0,.22);
        --aic-fab:60px;
        --aic-box-w:min(400px,calc(100vw - 28px));
        --aic-box-h:min(620px,calc(100dvh - 110px));
        --aic-pad-x:16px;
        position:fixed;left:26px;right:auto;
        bottom:calc(26px + env(safe-area-inset-bottom,0px));
        z-index:1085;font-family:inherit;text-align:start;
        color:var(--aic-text);line-height:1.6
      }
      #${ROOT_ID}.aic-offset{left:92px;right:auto}
      #${ROOT_ID} *{box-sizing:border-box}
      #${ROOT_ID} button{font-family:inherit}
      #${ROOT_ID} :focus-visible{outline:2px solid var(--aic-primary);outline-offset:2px}

      /* ── الزر العائم ── */
      #${ROOT_ID} .aic-fab{
        position:relative;width:var(--aic-fab);height:var(--aic-fab);padding:0;
        border:0;border-radius:50%;background:var(--aic-grad);color:#fff;
        box-shadow:0 10px 28px rgba(23,148,123,.45);
        display:flex;align-items:center;justify-content:center;font-size:1.45rem;
        cursor:pointer;transition:transform .2s ease,box-shadow .2s ease
      }
      #${ROOT_ID} .aic-fab:hover{
        transform:translateY(-2px) scale(1.04);
        box-shadow:0 14px 34px rgba(23,148,123,.55)
      }
      #${ROOT_ID} .aic-fab:active{transform:translateY(0) scale(.98)}
      #${ROOT_ID} .aic-fab .aic-ico-open{display:grid;place-items:center;line-height:1;color:#fff}
      #${ROOT_ID} .aic-ico-close{display:none}
      #${ROOT_ID}.aic-open .aic-ico-open{display:none}
      #${ROOT_ID}.aic-open .aic-ico-close{display:grid;place-items:center;line-height:1;color:#fff}
      #${ROOT_ID} .aic-fab i{color:#fff}
      #${ROOT_ID} .aic-tip{
        position:absolute;left:calc(100% + 12px);right:auto;top:50%;translate:0 -50%;
        background:rgba(21,26,34,.96);color:#fff;font-size:.78rem;font-weight:700;
        padding:7px 12px;border-radius:11px;white-space:nowrap;pointer-events:none;
        opacity:0;transition:opacity .2s ease;border:1px solid var(--aic-line);
        box-shadow:0 8px 22px rgba(0,0,0,.35)
      }
      #${ROOT_ID} .aic-fab:hover .aic-tip,#${ROOT_ID} .aic-fab:focus-visible .aic-tip{opacity:1}
      #${ROOT_ID}.aic-open .aic-tip{display:none}

      /* ── النافذة: chat box عائم (وليس واجهة كاملة) ── */
      #${ROOT_ID} .aic-panel{
        position:absolute;left:0;right:auto;
        bottom:calc(var(--aic-fab) + 14px);
        width:var(--aic-box-w);height:var(--aic-box-h);max-height:var(--aic-box-h);
        display:none;flex-direction:column;overflow:hidden;
        background:var(--aic-surface);
        border:1px solid var(--aic-line);border-radius:20px;
        box-shadow:0 18px 48px rgba(0,0,0,.45)
      }
      #${ROOT_ID}.aic-open .aic-panel{display:flex;animation:aicIn .28s cubic-bezier(.22,1,.36,1)}
      #${ROOT_ID}.aic-open .aic-fab{display:flex}
      #${ROOT_ID}.aic-min .aic-panel{display:none}
      #${ROOT_ID}.aic-min .aic-tabs,
      #${ROOT_ID}.aic-min .aic-view,
      #${ROOT_ID}.aic-min .aic-view.active{display:none}
      @keyframes aicIn{from{opacity:0;transform:translateY(10px) scale(.985)}to{opacity:1;transform:none}}
      body.aic-chat-lock{overflow:auto}

      /* ── الترويسة ── */
      #${ROOT_ID} .aic-head{
        flex:0 0 auto;display:flex;align-items:center;gap:10px;
        padding:14px var(--aic-pad-x);
        background:rgba(21,26,34,.92);border-bottom:1px solid var(--aic-line)
      }
      #${ROOT_ID} .aic-avatar{
        flex:0 0 auto;width:36px;height:36px;border-radius:50%;
        background:var(--aic-grad);color:#fff;display:grid;place-items:center;
        font-size:1.05rem;box-shadow:0 0 0 3px rgba(234,248,244,.16)
      }
      #${ROOT_ID} .aic-id{flex:1 1 auto;min-width:0;display:flex;align-items:center;gap:11px}
      #${ROOT_ID} .aic-id-txt{min-width:0;display:flex;flex-direction:column;line-height:1.35}
      #${ROOT_ID} .aic-title{font-size:.95rem;font-weight:800;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
      #${ROOT_ID} .aic-sub{display:none}
      #${ROOT_ID} .aic-tools{flex:0 0 auto;display:flex;align-items:center;gap:2px}
      #${ROOT_ID} .aic-tool{
        width:34px;height:34px;border:0;border-radius:10px;background:transparent;
        color:var(--aic-muted);font-size:.95rem;line-height:1;cursor:pointer;
        display:grid;place-items:center;transition:background .2s ease,color .2s ease
      }
      #${ROOT_ID} .aic-tool:hover:not(:disabled){background:rgba(255,255,255,.06);color:#fff}
      #${ROOT_ID} .aic-tool:disabled{opacity:.35;cursor:not-allowed}
      #${ROOT_ID} .aic-tool[hidden]{display:none}
      #${ROOT_ID} .aic-tool-call{color:var(--aic-primary)}
      #${ROOT_ID} .aic-menu-wrap{position:relative}
      #${ROOT_ID} .aic-menu{
        position:absolute;inset-inline-start:0;top:calc(100% + 8px);min-width:178px;
        background:var(--aic-elev);border:1px solid var(--aic-line);border-radius:14px;
        box-shadow:0 14px 32px rgba(0,0,0,.45);padding:6px;display:none;z-index:4
      }
      #${ROOT_ID} .aic-menu.open{display:block}
      #${ROOT_ID} .aic-menu-item{
        width:100%;display:flex;align-items:center;gap:9px;padding:10px 11px;
        border:0;border-radius:10px;background:transparent;color:var(--aic-text);
        font-size:.83rem;font-weight:700;cursor:pointer;text-align:start
      }
      #${ROOT_ID} .aic-menu-item:hover{background:rgba(255,255,255,.06)}
      #${ROOT_ID} .aic-menu-item i{color:var(--aic-primary)}

      /* ── التبويبات ── */
      #${ROOT_ID} .aic-tabs{
        flex:0 0 auto;display:flex;gap:0;padding:0 var(--aic-pad-x);
        background:transparent;border-bottom:1px solid var(--aic-line)
      }
      #${ROOT_ID} .aic-tabs[hidden]{display:none}
      #${ROOT_ID} .aic-tab{
        display:inline-flex;align-items:center;gap:7px;border:0;background:transparent;
        color:var(--aic-muted);font-size:.82rem;font-weight:700;cursor:pointer;
        padding:12px 14px;border-radius:0;border-bottom:2px solid transparent;
        transition:color .2s ease,border-color .2s ease
      }
      #${ROOT_ID} .aic-tab:hover{color:#d7deea}
      #${ROOT_ID} .aic-tab.active{color:#fff;border-bottom-color:var(--aic-primary)}
      #${ROOT_ID} .aic-tab[data-aic-tab="isic4"] i{color:#F5C542!important}
      #${ROOT_ID} .aic-view{flex:1 1 auto;min-height:0;display:none;flex-direction:column}
      #${ROOT_ID} .aic-view.active{display:flex}

      /* ── المحادثة ── */
      #${ROOT_ID} .aic-body{
        flex:1 1 auto;overflow-y:auto;overscroll-behavior:contain;
        padding:18px var(--aic-pad-x);display:flex;flex-direction:column;gap:13px;
        background:transparent
      }
      #${ROOT_ID} .aic-body::-webkit-scrollbar{width:5px}
      #${ROOT_ID} .aic-body::-webkit-scrollbar-button{display:none}
      #${ROOT_ID} .aic-body::-webkit-scrollbar-track{background:transparent}
      #${ROOT_ID} .aic-body::-webkit-scrollbar-thumb{background:rgba(255,255,255,.14);border-radius:99px}

      /* ── حالة الترحيب ── */
      #${ROOT_ID} .aic-welcome{
        margin:auto;padding:24px 18px;text-align:center;max-width:460px
      }
      #${ROOT_ID} .aic-welcome-ava,
      #${ROOT_ID} .aic-welcome-title,
      #${ROOT_ID} .aic-chips{display:none}
      #${ROOT_ID} .aic-welcome-text{
        font-size:.9rem;color:var(--aic-muted);font-weight:500;line-height:1.85;margin:0
      }

      /* ── الرسائل ── */
      #${ROOT_ID} .aic-row{display:flex;align-items:flex-start;gap:9px;max-width:100%}
      #${ROOT_ID} .aic-row.user{justify-content:flex-end}
      #${ROOT_ID} .aic-ava{
        flex:0 0 auto;width:28px;height:28px;border-radius:50%;margin-top:3px;
        background:var(--aic-grad);color:#fff;display:grid;place-items:center;
        font-size:.78rem;box-shadow:0 0 0 2px rgba(234,248,244,.14)
      }
      #${ROOT_ID} .aic-stack{
        display:flex;flex-direction:column;align-items:flex-start;
        min-width:0;max-width:calc(88% - 37px)
      }
      #${ROOT_ID} .aic-row.user .aic-stack{max-width:88%;align-items:flex-end}
      #${ROOT_ID} .aic-msg{
        padding:11px 14px;border-radius:16px;font-size:.865rem;line-height:1.8;
        overflow-wrap:anywhere
      }
      #${ROOT_ID} .aic-msg.bot{
        background:var(--aic-elev);color:var(--aic-text);
        border:1px solid var(--aic-line);border-start-start-radius:6px
      }
      #${ROOT_ID} .aic-msg.user{
        background:var(--aic-primary);color:#fff;font-weight:600;
        border-end-end-radius:6px;white-space:pre-wrap;
        box-shadow:0 6px 18px rgba(23,148,123,.28)
      }
      #${ROOT_ID} .aic-row.interim .aic-msg{opacity:.72;font-style:italic}
      #${ROOT_ID} .aic-time{
        font-size:.68rem;color:var(--aic-muted);margin-top:5px;padding:0 6px;
        font-variant-numeric:tabular-nums;direction:ltr
      }
      #${ROOT_ID} .aic-continue{
        margin-top:7px;border:0;border-radius:12px;background:var(--aic-soft);
        color:var(--aic-accent);font-size:.78rem;font-weight:800;padding:8px 14px;
        cursor:pointer;display:inline-flex;align-items:center;gap:7px;
        transition:background .2s ease
      }
      #${ROOT_ID} .aic-continue:hover:not(:disabled){background:rgba(234,248,244,.18)}
      #${ROOT_ID} .aic-continue:disabled{opacity:.6;cursor:not-allowed}
      #${ROOT_ID} .aic-note{
        align-self:center;background:rgba(255,255,255,.05);color:var(--aic-muted);
        font-size:.74rem;font-weight:700;padding:6px 13px;border-radius:99px;text-align:center
      }

      #${ROOT_ID} .aic-p{margin:0 0 9px;white-space:pre-line}
      #${ROOT_ID} .aic-h{font-weight:800;margin:0 0 7px}
      #${ROOT_ID} .aic-list{
        margin:0 0 9px;padding-inline-start:19px;
        display:flex;flex-direction:column;gap:5px
      }
      #${ROOT_ID} .aic-list li::marker{color:var(--aic-primary)}
      #${ROOT_ID} .aic-msg.bot strong{font-weight:800}
      #${ROOT_ID} .aic-msg.bot > :last-child{margin-bottom:0}

      /* ── الخطأ ── */
      #${ROOT_ID} .aic-err{
        align-self:flex-start;max-width:90%;background:rgba(255,107,122,.08);
        border:1px solid rgba(255,107,122,.22);border-radius:16px;
        border-start-start-radius:6px;padding:13px 15px;font-size:.845rem;color:#ff8f9a;
        display:flex;flex-direction:column;align-items:flex-start;gap:10px
      }
      #${ROOT_ID} .aic-err-head{display:flex;align-items:center;gap:8px;font-weight:700}
      #${ROOT_ID} .aic-err-head i{font-size:1rem}
      #${ROOT_ID} .aic-retry{
        border:0;background:rgba(255,107,122,.16);color:#ff8f9a;border-radius:12px;
        padding:8px 14px;font-size:.78rem;font-weight:800;cursor:pointer;
        display:inline-flex;align-items:center;gap:7px;transition:background .2s ease
      }
      #${ROOT_ID} .aic-retry:hover{background:rgba(255,107,122,.24)}

      /* ── مؤشر الكتابة ── */
      #${ROOT_ID} .aic-typing{
        display:flex;align-items:center;gap:9px;padding:12px 15px;
        background:var(--aic-elev);border:1px solid var(--aic-line);
        border-radius:16px;border-start-start-radius:6px;
        font-size:.79rem;color:var(--aic-muted);font-weight:600
      }
      #${ROOT_ID} .aic-dots{display:flex;gap:4px}
      #${ROOT_ID} .aic-dots i{
        width:6px;height:6px;border-radius:50%;background:var(--aic-primary);opacity:.35;
        animation:aicDot 1.1s infinite ease-in-out
      }
      #${ROOT_ID} .aic-dots i:nth-child(2){animation-delay:.16s}
      #${ROOT_ID} .aic-dots i:nth-child(3){animation-delay:.32s}
      @keyframes aicDot{0%,60%,100%{opacity:.3;transform:translateY(0)}30%{opacity:1;transform:translateY(-3px)}}

      /* ── شريط حالة الصوت ── */
      #${ROOT_ID} .aic-vstatus{
        flex:0 0 auto;display:none;align-items:center;gap:8px;
        margin:0 var(--aic-pad-x) 4px;
        padding:7px 13px;border-radius:99px;background:var(--aic-elev);
        border:1px solid var(--aic-line);font-size:.76rem;font-weight:700;color:var(--aic-muted)
      }
      #${ROOT_ID} .aic-vstatus.show{display:flex}
      #${ROOT_ID} .aic-vdot{
        width:8px;height:8px;border-radius:50%;background:var(--aic-primary);flex:0 0 auto;
        box-shadow:0 0 10px rgba(6,170,137,.5);animation:aicPulse 1s ease infinite
      }
      #${ROOT_ID} .aic-vstatus[data-state="error"] .aic-vdot{background:var(--aic-bad);animation:none;box-shadow:none}
      #${ROOT_ID} .aic-vstatus[data-state="recording"] .aic-vdot{background:#ff5c5c;box-shadow:0 0 10px rgba(255,92,92,.45)}
      @keyframes aicPulse{0%,100%{opacity:1}50%{opacity:.35}}

      /* ── صندوق الكتابة ── */
      #${ROOT_ID} .aic-foot{
        flex:0 0 auto;display:flex;flex-direction:column;gap:8px;
        padding:12px var(--aic-pad-x) 14px;
        background:transparent;border-top:0;
        padding-bottom:max(14px,env(safe-area-inset-bottom,0px))
      }
      #${ROOT_ID} .aic-foot[hidden]{display:none}
      #${ROOT_ID} .aic-inputwrap{
        flex:0 0 auto;width:100%;min-width:0;display:flex;align-items:flex-end;
        background:var(--aic-elev);border:1px solid var(--aic-line);
        border-radius:999px;padding:4px 6px 4px 8px;
        transition:border-color .2s ease,box-shadow .2s ease
      }
      #${ROOT_ID} .aic-inputwrap:focus-within{
        border-color:rgba(6,170,137,.45);
        box-shadow:0 0 0 3px rgba(234,248,244,.12)
      }
      #${ROOT_ID} .aic-input{
        flex:1 1 auto;width:100%;resize:none;border:0;background:transparent;
        min-height:40px;max-height:104px;padding:9px 12px;overflow-y:auto;
        font-size:.86rem;line-height:1.6;font-family:inherit;color:var(--aic-text);
        scrollbar-width:thin
      }
      #${ROOT_ID} .aic-input:focus{outline:0}
      #${ROOT_ID} .aic-input::placeholder{color:#6d7588}
      #${ROOT_ID} .aic-input::-webkit-scrollbar{width:4px}
      #${ROOT_ID} .aic-input::-webkit-scrollbar-button{display:none;width:0;height:0}
      #${ROOT_ID} .aic-input::-webkit-scrollbar-track{background:transparent}
      #${ROOT_ID} .aic-input::-webkit-scrollbar-thumb{background:rgba(255,255,255,.16);border-radius:99px}
      #${ROOT_ID} .aic-act{
        flex:0 0 auto;width:40px;height:40px;border:0;border-radius:50%;
        background:transparent;color:var(--aic-muted);cursor:pointer;
        display:grid;place-items:center;line-height:0;
        transition:transform .2s ease,background .2s ease,color .2s ease,opacity .2s ease
      }
      #${ROOT_ID} .aic-act i{font-size:1.05rem;line-height:1}
      #${ROOT_ID} .aic-mic:hover:not(:disabled){color:var(--aic-primary);background:rgba(234,248,244,.1)}
      #${ROOT_ID} .aic-send{
        background:var(--aic-grad);color:#fff;
        box-shadow:0 4px 14px rgba(23,148,123,.35)
      }
      #${ROOT_ID} .aic-send i{transform:scaleX(-1);color:#fff}
      #${ROOT_ID} .aic-send:hover:not(:disabled){transform:translateY(-1px);box-shadow:0 8px 18px rgba(23,148,123,.42)}
      #${ROOT_ID} .aic-send:disabled{opacity:.45;cursor:not-allowed;box-shadow:none}
      #${ROOT_ID} .aic-act[hidden]{display:none}
      #${ROOT_ID} .aic-voice-hint{
        text-align:center;font-size:.72rem;font-weight:600;color:var(--aic-muted);
        letter-spacing:.2px;opacity:.9
      }
      #${ROOT_ID} .aic-voice-hint[hidden]{display:none}

      /* ── شريط التسجيل ── */
      #${ROOT_ID} .aic-rec{
        flex:0 0 auto;display:none;align-items:center;gap:9px;
        padding:12px var(--aic-pad-x);
        background:transparent;border-top:1px solid var(--aic-line);
        padding-bottom:max(14px,env(safe-area-inset-bottom,0px))
      }
      #${ROOT_ID} .aic-rec.show{display:flex}
      #${ROOT_ID} .aic-rec-mid{
        flex:1 1 auto;min-width:0;display:flex;align-items:center;gap:9px;
        background:var(--aic-elev);border:1px solid var(--aic-line);
        border-radius:999px;padding:8px 13px
      }
      #${ROOT_ID} .aic-rec-timer{
        font-size:.78rem;font-weight:800;color:#ff6b7a;direction:ltr;
        font-variant-numeric:tabular-nums;flex:0 0 auto
      }
      #${ROOT_ID} .aic-wave{flex:1 1 auto;min-width:0;display:flex;align-items:center;gap:2px;height:26px}
      #${ROOT_ID} .aic-wave span{
        flex:1 1 auto;min-width:2px;height:100%;border-radius:99px;
        background:linear-gradient(180deg,#7EE0BF,#4FD1A5);opacity:.9;transform:scaleY(.12);
        transform-origin:center;transition:transform .08s linear
      }
      #${ROOT_ID} .aic-rec-btn{
        flex:0 0 auto;width:40px;height:40px;border:0;border-radius:50%;cursor:pointer;
        display:grid;place-items:center;line-height:0;font-size:.9rem;
        background:var(--aic-elev);color:var(--aic-muted);border:1px solid var(--aic-line);
        transition:background .2s ease,color .2s ease
      }
      #${ROOT_ID} .aic-rec-btn:hover{background:#222a3a;color:#fff}
      #${ROOT_ID} .aic-rec-btn.danger:hover{background:rgba(255,107,122,.14);color:#ff8f9a}
      #${ROOT_ID} .aic-rec .aic-act{
        background:var(--aic-grad);color:#0b1f1a;box-shadow:0 4px 14px rgba(23,148,123,.35)
      }
      #${ROOT_ID} .aic-rec .aic-act i{color:#0b1f1a}

      /* ── شاشة المكالمة ── */
      #${ROOT_ID} .aic-call{
        position:absolute;inset:0;z-index:5;display:none;flex-direction:column;
        align-items:center;justify-content:center;gap:6px;padding:26px;
        background:linear-gradient(168deg,#1a2e2a 0%,#10201c 55%,#0b1614 100%);color:#fff
      }
      #${ROOT_ID} .aic-call.show{display:flex;animation:aicIn .2s ease-out}
      #${ROOT_ID} .aic-call-ava{
        width:118px;height:118px;border-radius:50%;display:grid;place-items:center;
        font-size:2.9rem;background:var(--aic-grad);margin-bottom:6px;position:relative;
        overflow:visible;
        box-shadow:0 0 0 8px rgba(234,248,244,.12),0 0 42px rgba(6,170,137,.4),0 12px 30px rgba(0,0,0,.35);
        animation:aicOrb 2.6s ease-in-out infinite
      }
      #${ROOT_ID} .aic-call-ava::before{
        content:'';position:absolute;inset:-16px;border-radius:50%;z-index:-1;
        background:radial-gradient(circle at 32% 30%,rgba(234,248,244,.45),rgba(23,148,123,.2) 55%,transparent 72%);
        animation:aicHalo 2.6s ease-in-out infinite
      }
      #${ROOT_ID} .aic-call-ava::after{
        content:'';position:absolute;inset:-10px;border-radius:50%;
        border:2px solid rgba(126,224,191,.28);
        transform:scale(calc(1 + (var(--aic-level,0) * .12)));
        transition:transform .1s linear
      }
      @keyframes aicOrb{0%,100%{transform:scale(1)}50%{transform:scale(1.06)}}
      @keyframes aicHalo{0%,100%{opacity:.55;transform:scale(1)}50%{opacity:.9;transform:scale(1.14)}}
      #${ROOT_ID} .aic-call-title{font-size:1.05rem;font-weight:800}
      #${ROOT_ID} .aic-call-sub{font-size:.8rem;opacity:.8;text-align:center;min-height:1.6em}
      #${ROOT_ID} .aic-call-timer{
        font-size:1.5rem;font-weight:800;direction:ltr;
        font-variant-numeric:tabular-nums;margin:6px 0 18px;letter-spacing:.5px
      }
      #${ROOT_ID} .aic-call-acts{display:flex;align-items:center;gap:14px}
      #${ROOT_ID} .aic-call-btn{
        width:60px;height:60px;border:0;border-radius:50%;cursor:pointer;
        display:grid;place-items:center;font-size:1.15rem;line-height:0;
        background:linear-gradient(135deg,#63D2B2,#2FA98C);color:#fff;
        transition:background .2s ease,transform .2s ease
      }
      #${ROOT_ID} .aic-call-btn:hover{transform:translateY(-2px)}
      #${ROOT_ID} .aic-call-btn.is-muted{background:#fff;color:#1E7C66}
      #${ROOT_ID} .aic-call-btn.hangup{background:linear-gradient(135deg,#ff6b6b,#e0245e)}
      #${ROOT_ID} .aic-call-btn.hangup:hover{filter:brightness(1.05)}
      #${ROOT_ID} .aic-call-btn.hangup i{transform:rotate(135deg)}

      /* ── تصنيف ISIC4 ── */
      #${ROOT_ID} .aic-isic4{
        flex:1 1 auto;min-height:0;overflow-y:auto;overscroll-behavior:contain;
        padding:16px var(--aic-pad-x);display:flex;flex-direction:column;gap:11px
      }
      #${ROOT_ID} .aic-isic4::-webkit-scrollbar{width:5px}
      #${ROOT_ID} .aic-isic4::-webkit-scrollbar-button{display:none}
      #${ROOT_ID} .aic-isic4::-webkit-scrollbar-thumb{background:rgba(255,255,255,.14);border-radius:99px}
      #${ROOT_ID} .aic-label{font-size:.83rem;font-weight:800;color:#fff}
      #${ROOT_ID} .aic-hint{font-size:.75rem;color:var(--aic-muted);margin-top:-6px}
      #${ROOT_ID} .aic-desc{
        width:100%;resize:none;border:1px solid var(--aic-line);border-radius:16px;
        background:var(--aic-elev);padding:13px 15px;min-height:96px;font-size:.85rem;line-height:1.7;
        font-family:inherit;color:var(--aic-text)
      }
      #${ROOT_ID} .aic-desc:focus{outline:0;border-color:rgba(6,170,137,.45);box-shadow:0 0 0 3px rgba(234,248,244,.12)}
      #${ROOT_ID} .aic-desc::placeholder{color:#6d7588}
      #${ROOT_ID} .aic-classify{
        align-self:flex-start;border:0;border-radius:14px;background:var(--aic-grad);
        color:#0b1f1a;font-size:.82rem;font-weight:800;padding:12px 20px;min-height:44px;
        cursor:pointer;display:inline-flex;align-items:center;gap:8px;
        box-shadow:0 6px 16px rgba(23,148,123,.28);transition:transform .2s ease
      }
      #${ROOT_ID} .aic-classify:hover:not(:disabled){transform:translateY(-2px)}
      #${ROOT_ID} .aic-classify:disabled{opacity:.45;box-shadow:none;cursor:not-allowed}
      #${ROOT_ID} .aic-card{
        background:var(--aic-elev);border:1px solid var(--aic-line);border-radius:16px;padding:14px 15px
      }
      #${ROOT_ID} .aic-card h4{
        margin:0 0 9px;font-size:.79rem;font-weight:800;color:var(--aic-muted);
        text-transform:none;letter-spacing:0
      }
      #${ROOT_ID} .aic-codeline{display:flex;align-items:center;flex-wrap:wrap;gap:9px}
      #${ROOT_ID} .aic-code{
        display:inline-block;background:var(--aic-soft);color:var(--aic-accent);
        border-radius:10px;padding:5px 11px;font-size:.83rem;font-weight:800;
        direction:ltr;font-variant-numeric:tabular-nums;overflow-wrap:anywhere
      }
      #${ROOT_ID} .aic-codelabel{font-size:.85rem;font-weight:800;overflow-wrap:anywhere;color:#fff}
      #${ROOT_ID} .aic-reason{margin:9px 0 0;font-size:.82rem;line-height:1.75;color:var(--aic-text)}
      #${ROOT_ID} .aic-alt{
        padding:10px 0;border-top:1px solid var(--aic-line);font-size:.81rem;line-height:1.7
      }
      #${ROOT_ID} .aic-alt:first-of-type{border-top:0;padding-top:0}

      /* ── سجل المحادثات ── */
      #${ROOT_ID} .aic-hist{
        position:absolute;inset:0;z-index:4;display:none;flex-direction:column;
        background:linear-gradient(180deg,#141922 0%,#0E1218 100%)
      }
      #${ROOT_ID} .aic-hist.show{display:flex;animation:aicIn .2s ease-out}
      #${ROOT_ID} .aic-hist-head{
        flex:0 0 auto;display:flex;align-items:center;gap:10px;
        padding:14px var(--aic-pad-x);
        background:rgba(21,26,34,.92);border-bottom:1px solid var(--aic-line)
      }
      #${ROOT_ID} .aic-hist-title{flex:1 1 auto;font-size:.92rem;font-weight:800;color:#fff}
      #${ROOT_ID} .aic-hist-list{
        flex:1 1 auto;overflow-y:auto;padding:14px var(--aic-pad-x);
        display:flex;flex-direction:column;gap:9px
      }
      #${ROOT_ID} .aic-hist-list::-webkit-scrollbar{width:5px}
      #${ROOT_ID} .aic-hist-list::-webkit-scrollbar-button{display:none}
      #${ROOT_ID} .aic-hist-list::-webkit-scrollbar-thumb{background:rgba(255,255,255,.14);border-radius:99px}
      #${ROOT_ID} .aic-hist-item{
        width:100%;display:flex;align-items:center;gap:11px;border:1px solid var(--aic-line);
        background:var(--aic-elev);border-radius:14px;padding:12px 14px;cursor:pointer;text-align:start;
        transition:transform .2s ease,border-color .2s ease
      }
      #${ROOT_ID} .aic-hist-item:hover{transform:translateY(-2px);border-color:rgba(6,170,137,.35)}
      #${ROOT_ID} .aic-hist-ico{
        flex:0 0 auto;width:34px;height:34px;border-radius:12px;display:grid;place-items:center;
        background:var(--aic-soft);color:var(--aic-primary);font-size:.9rem
      }
      #${ROOT_ID} .aic-hist-txt{flex:1 1 auto;min-width:0;display:flex;flex-direction:column;gap:2px}
      #${ROOT_ID} .aic-hist-when{font-size:.82rem;font-weight:700;color:#fff}
      #${ROOT_ID} .aic-hist-count{font-size:.73rem;color:var(--aic-muted)}
      #${ROOT_ID} .aic-empty{
        margin:auto;text-align:center;font-size:.83rem;color:var(--aic-muted);padding:20px
      }

      /* ── صندوق التأكيد ── */
      #${ROOT_ID} .aic-confirm{
        position:absolute;inset:0;z-index:6;display:none;align-items:center;justify-content:center;
        padding:20px;background:rgba(4,8,14,.62);backdrop-filter:blur(3px)
      }
      #${ROOT_ID} .aic-confirm.open{display:flex;animation:aicIn .18s ease-out}
      #${ROOT_ID} .aic-confirm-box{
        background:var(--aic-elev);border:1px solid var(--aic-line);
        border-radius:18px;padding:20px;width:100%;max-width:440px;
        box-shadow:0 20px 44px rgba(0,0,0,.45)
      }
      #${ROOT_ID} .aic-confirm-text{font-size:.87rem;font-weight:700;margin:0 0 16px;color:#fff}
      #${ROOT_ID} .aic-confirm-acts{display:flex;gap:9px}
      #${ROOT_ID} .aic-confirm-acts button{
        flex:1;border-radius:14px;padding:11px;font-size:.81rem;font-weight:800;
        cursor:pointer;min-height:44px
      }
      #${ROOT_ID} .aic-btn-ghost{border:1px solid var(--aic-line);background:transparent;color:var(--aic-muted)}
      #${ROOT_ID} .aic-btn-ghost:hover{background:rgba(255,255,255,.05);color:#fff}
      #${ROOT_ID} .aic-btn-solid{border:0;background:var(--aic-grad);color:#0b1f1a}

      /* ── موبايل: chat box بعرض الشاشة تقريباً ── */
      @media (max-width:575.98px){
        #${ROOT_ID},#${ROOT_ID}.aic-offset{
          --aic-fab:56px;--aic-pad-x:14px;
          --aic-box-w:calc(100vw - 20px);
          --aic-box-h:min(72dvh,calc(100dvh - 96px));
          left:10px;right:auto;bottom:calc(14px + env(safe-area-inset-bottom,0px))
        }
        #${ROOT_ID} .aic-fab{font-size:1.35rem}
        #${ROOT_ID} .aic-tip{display:none}
        #${ROOT_ID} .aic-panel{border-radius:18px}
        #${ROOT_ID} .aic-call-ava{width:96px;height:96px;font-size:2.4rem}
      }

      @media (prefers-reduced-motion:reduce){
        #${ROOT_ID} *{animation:none!important;transition:none!important}
        #${ROOT_ID} .aic-fab:hover,#${ROOT_ID} .aic-act:hover,#${ROOT_ID} .aic-hist-item:hover{transform:none}
      }
    `;
    document.head.appendChild(style);
  }

  /* ────────── عناصر آمنة (نص فقط، بلا innerHTML لمحتوى الخدمة) ────────── */

  function el(tag, className, text) {
    const node = document.createElement(tag);
    if (className) node.className = className;
    if (text != null) node.textContent = String(text);
    return node;
  }

  function icon(name) {
    const i = document.createElement('i');
    i.className = `bi bi-${name}`;
    i.setAttribute('aria-hidden', 'true');
    return i;
  }

  function avatar() {
    const span = el('span', 'aic-ava');
    span.setAttribute('aria-hidden', 'true');
    span.appendChild(icon('robot'));
    return span;
  }

  function nowLabel() {
    const d = new Date();
    return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
  }

  function scrollToEnd(body) {
    body.scrollTop = body.scrollHeight;
  }

  /**
   * تنسيق مبسّط لنص الخدمة: عريض، قوائم، عناوين.
   * كل النصوص تُدرج كـ textContent، فلا يمرّ أي HTML من الخدمة.
   */
  const BOLD_RE = /\*\*(.+?)\*\*/g;
  const HEADING_RE = /^#{1,6}\s+(.*)$/;
  const BULLET_RE = /^\s*[*\-•]\s+(.*)$/;
  const ORDERED_RE = /^\s*\d+[.)]\s+(.*)$/;

  function appendInline(parent, text) {
    let cursor = 0;
    let match;
    BOLD_RE.lastIndex = 0;
    while ((match = BOLD_RE.exec(text)) !== null) {
      if (match.index > cursor) {
        parent.appendChild(document.createTextNode(text.slice(cursor, match.index)));
      }
      parent.appendChild(el('strong', null, match[1]));
      cursor = BOLD_RE.lastIndex;
    }
    if (cursor < text.length) {
      parent.appendChild(document.createTextNode(text.slice(cursor)));
    }
  }

  function renderRichText(container, raw) {
    const lines = String(raw).replace(/\r\n?/g, '\n').split('\n');
    let list = null;
    let paragraph = null;

    lines.forEach((line) => {
      const trimmed = line.trim();
      if (!trimmed) {
        list = null;
        paragraph = null;
        return;
      }

      const heading = HEADING_RE.exec(trimmed);
      if (heading) {
        list = null;
        paragraph = null;
        const node = el('div', 'aic-h');
        appendInline(node, heading[1]);
        container.appendChild(node);
        return;
      }

      const bullet = BULLET_RE.exec(line);
      const ordered = bullet ? null : ORDERED_RE.exec(line);
      if (bullet || ordered) {
        const tag = ordered ? 'ol' : 'ul';
        if (!list || list.tagName.toLowerCase() !== tag) {
          list = el(tag, 'aic-list');
          container.appendChild(list);
        }
        paragraph = null;
        const item = el('li');
        appendInline(item, (bullet ? bullet[1] : ordered[1]).trim());
        list.appendChild(item);
        return;
      }

      list = null;
      if (paragraph) {
        paragraph.appendChild(document.createTextNode('\n'));
      } else {
        paragraph = el('p', 'aic-p');
        container.appendChild(paragraph);
      }
      appendInline(paragraph, trimmed);
    });

    if (!container.childNodes.length) {
      container.appendChild(el('p', 'aic-p', String(raw)));
    }
  }

  /** صف رسالة: أفاتار للمستشار + فقاعة نص آمن + وقت. */
  function pushMessage(body, text, kind) {
    const row = el('div', `aic-row ${kind}`);
    if (kind === 'bot') row.appendChild(avatar());

    const stack = el('div', 'aic-stack');
    const bubble = el('div', `aic-msg ${kind}`);
    if (kind === 'bot') renderRichText(bubble, text);
    else bubble.textContent = String(text);
    stack.appendChild(bubble);
    stack.appendChild(el('time', 'aic-time', nowLabel()));
    row.appendChild(stack);

    body.appendChild(row);
    scrollToEnd(body);
    return row;
  }

  function pushNote(body, text) {
    body.appendChild(el('div', 'aic-note', text));
    scrollToEnd(body);
  }

  function renderWelcome(root) {
    const body = root.querySelector('.aic-body');
    const box = el('div', 'aic-welcome');
    box.appendChild(el(
      'div',
      'aic-welcome-text',
      'اطرح سؤالك لتبدأ المحادثة مع المستشار الذكي.'
    ));
    body.appendChild(box);
  }

  function dropChips(root) {
    const chips = root.querySelector('.aic-chips');
    if (chips) chips.remove();
  }

  function showTyping(body) {
    const row = el('div', 'aic-row bot');
    row.appendChild(avatar());

    const node = el('div', 'aic-typing');
    node.setAttribute('aria-label', 'المستشار يكتب');
    node.appendChild(el('span', null, 'المستشار يكتب'));
    const dots = el('div', 'aic-dots');
    dots.appendChild(el('i'));
    dots.appendChild(el('i'));
    dots.appendChild(el('i'));
    node.appendChild(dots);

    row.appendChild(node);
    body.appendChild(row);
    scrollToEnd(body);
    return row;
  }

  function showError(root, text, retryText) {
    const body = root.querySelector('.aic-body');
    const box = el('div', 'aic-err');
    box.setAttribute('role', 'alert');

    const head = el('div', 'aic-err-head');
    head.appendChild(icon('exclamation-circle'));
    head.appendChild(el('span', null, text));
    box.appendChild(head);

    if (retryText) {
      const retry = el('button', 'aic-retry');
      retry.type = 'button';
      retry.appendChild(icon('arrow-clockwise'));
      retry.appendChild(el('span', null, 'إعادة المحاولة'));
      retry.addEventListener('click', () => {
        box.remove();
        requestReply(root, retryText);
      });
      box.appendChild(retry);
    }

    body.appendChild(box);
    scrollToEnd(body);
  }

  /* ────────── المحادثة النصية (عبر Laravel) ────────── */

  function autoGrow(input) {
    input.style.height = 'auto';
    input.style.height = `${Math.min(input.scrollHeight, 104)}px`;
  }

  function errorMessage(err, fallback) {
    if (err && err.status === 429) {
      return 'أرسلت رسائل كثيرة خلال وقت قصير. انتظر قليلاً ثم حاول مجدداً.';
    }
    if (err && (err.status === 422 || err.status === 403)) {
      return (err.data && (err.data.message || firstValidationError(err.data))) || (fallback || GENERIC_ERROR);
    }
    return fallback || GENERIC_ERROR;
  }

  function firstValidationError(data) {
    const errors = data && data.errors;
    if (!errors) return null;
    const first = Object.keys(errors)[0];
    return first && Array.isArray(errors[first]) ? errors[first][0] : null;
  }

  function syncSendState(root) {
    const input = root.querySelector('.aic-input');
    const hasText = input.value.trim() !== '';
    const send = root.querySelector('.aic-send');
    const mic = root.querySelector('.aic-mic');
    const micAvailable = !!(mic && capabilities && capabilities.canRecord);

    // نبدّل بين الميكروفون والإرسال حسب وجود نص، كتطبيقات المحادثة المعتادة.
    send.hidden = micAvailable && !hasText;
    if (mic) mic.hidden = !micAvailable || hasText;

    send.disabled = busy || !hasText;
    if (mic) mic.disabled = busy;
  }

  function setBusy(root, value) {
    busy = value;
    root.querySelector('[data-aic-menu]').disabled = value;
    syncSendState(root);
  }

  /**
   * الخدمة تقتطع الردود الطويلة وتعلّمها بـ truncated، فنعرض زر «تابع»
   * يطلب البقية ويعيد بناء الفقاعة بالنص الكامل.
   */
  function attachContinue(root, row) {
    const stack = row.querySelector('.aic-stack');
    if (!stack || stack.querySelector('.aic-continue')) return;

    const button = el('button', 'aic-continue');
    button.type = 'button';
    button.appendChild(icon('arrow-down-circle'));
    button.appendChild(el('span', null, 'تابع'));
    button.addEventListener('click', () => continueReply(root, row, button));
    stack.insertBefore(button, stack.querySelector('.aic-time'));
  }

  async function continueReply(root, row, button) {
    if (busy) return;
    const body = root.querySelector('.aic-body');
    const bubble = row.querySelector('.aic-msg');
    setBusy(root, true);
    button.disabled = true;
    button.querySelector('span').textContent = 'جارٍ الإكمال...';

    try {
      const res = await window.APP_API.post(window.APP_ROUTES.aiChatContinue(), {
        department_id: departmentId || undefined,
      });
      const full = (res && res.full_reply)
        || `${bubble.dataset.raw || bubble.textContent}${(res && res.reply) || ''}`;

      bubble.replaceChildren();
      bubble.dataset.raw = full;
      renderRichText(bubble, full);

      button.remove();
      if (res && res.truncated) attachContinue(root, row);
    } catch (err) {
      button.disabled = false;
      button.querySelector('span').textContent = 'تابع';
      showError(root, errorMessage(err), null);
    } finally {
      setBusy(root, false);
      scrollToEnd(body);
    }
  }

  /** ينفّذ الطلب ويعرض الرد — يُستخدم للإرسال العادي ولإعادة المحاولة. */
  async function requestReply(root, text) {
    if (busy) return;
    const body = root.querySelector('.aic-body');
    setBusy(root, true);
    const typing = showTyping(body);

    try {
      const payload = { message: text };
      if (departmentId) payload.department_id = departmentId;
      const res = await window.APP_API.post(window.APP_ROUTES.aiChat(), payload);
      typing.remove();
      const reply = res && (res.reply || (res.data && res.data.reply));
      if (reply) {
        const row = pushMessage(body, reply, 'bot');
        row.querySelector('.aic-msg').dataset.raw = reply;
        if (res.truncated) attachContinue(root, row);
      } else {
        showError(root, GENERIC_ERROR, text);
      }
    } catch (err) {
      typing.remove();
      showError(root, errorMessage(err), text);
    } finally {
      setBusy(root, false);
      scrollToEnd(body);
    }
  }

  function sendText(root, text) {
    const clean = String(text || '').trim();
    if (!clean || busy) return;

    dropChips(root);
    pushMessage(root.querySelector('.aic-body'), clean, 'user');
    requestReply(root, clean);
  }

  function submitFromInput(root) {
    const input = root.querySelector('.aic-input');
    const text = input.value.trim();
    if (!text || busy) return;

    input.value = '';
    autoGrow(input);
    syncSendState(root);
    sendText(root, text);
  }

  function hasMessages(root) {
    return !!root.querySelector('.aic-row, .aic-err, .aic-note');
  }

  async function resetChat(root) {
    if (busy) return;
    const body = root.querySelector('.aic-body');
    setBusy(root, true);
    try {
      await window.APP_API.post(window.APP_ROUTES.aiChatReset(), {});
      body.replaceChildren();
      renderWelcome(root);
    } catch (err) {
      showError(root, errorMessage(err), null);
    } finally {
      setBusy(root, false);
      root.querySelector('.aic-input').focus();
    }
  }

  /* ────────── تصنيف ISIC4 (عبر Laravel) ────────── */

  /** كود التصنيف كرقاقة، ومسمّى النشاط بجانبه. */
  function appendCode(parent, match) {
    if (match.code) parent.appendChild(el('span', 'aic-code', match.code));
    const label = match.label || (match.code ? null : match.raw);
    if (label) parent.appendChild(el('span', 'aic-codelabel', label));
  }

  function renderClassification(target, result) {
    target.replaceChildren();

    if (result.best_match && (result.best_match.code || result.best_match.label)) {
      const card = el('div', 'aic-card');
      card.appendChild(el('h4', null, 'الكود المقترح'));
      const line = el('div', 'aic-codeline');
      appendCode(line, result.best_match);
      card.appendChild(line);
      if (result.best_match.reason) {
        card.appendChild(el('p', 'aic-reason', result.best_match.reason));
      }
      target.appendChild(card);
    } else if (result.clarifying_question) {
      const card = el('div', 'aic-card');
      card.appendChild(el('h4', null, 'نحتاج توضيحاً إضافياً'));
      card.appendChild(el('p', 'aic-reason', result.clarifying_question));
      target.appendChild(card);
    }

    if (Array.isArray(result.alternatives) && result.alternatives.length) {
      const card = el('div', 'aic-card');
      card.appendChild(el('h4', null, 'خيارات بديلة'));
      result.alternatives.forEach((alternative) => {
        const row = el('div', 'aic-alt');
        const line = el('div', 'aic-codeline');
        appendCode(line, alternative);
        row.appendChild(line);
        if (alternative.reason) {
          row.appendChild(el('p', 'aic-reason', alternative.reason));
        }
        card.appendChild(row);
      });
      target.appendChild(card);
    }

    if (!target.childNodes.length) {
      target.appendChild(el('div', 'aic-empty', 'لم نتمكن من تحديد تصنيف مناسب. جرّب وصفاً أوضح.'));
    }
  }

  async function classifyActivity(root) {
    const input = root.querySelector('.aic-desc');
    const button = root.querySelector('.aic-classify');
    const result = root.querySelector('[data-aic-isic4-result]');
    const description = input.value.trim();
    if (!description || button.disabled) return;

    button.disabled = true;
    const label = button.querySelector('span');
    const original = label.textContent;
    label.textContent = 'جارٍ التصنيف...';
    result.replaceChildren(el('div', 'aic-empty', 'جارٍ البحث والتصنيف...'));

    try {
      const res = await window.APP_API.post(window.APP_ROUTES.aiIsic4Classify(), { description });
      renderClassification(result, res || {});
    } catch (err) {
      const box = el('div', 'aic-err');
      box.setAttribute('role', 'alert');
      const head = el('div', 'aic-err-head');
      head.appendChild(icon('exclamation-circle'));
      head.appendChild(el('span', null, errorMessage(err, 'تعذر تصنيف النشاط حالياً.')));
      box.appendChild(head);
      result.replaceChildren(box);
    } finally {
      button.disabled = false;
      label.textContent = original;
    }
  }

  /* ────────── سجل المحادثات (عبر Laravel) ────────── */

  function formatWhen(value) {
    if (!value) return 'محادثة سابقة';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return 'محادثة سابقة';
    try {
      return date.toLocaleString('ar', { dateStyle: 'medium', timeStyle: 'short' });
    } catch (_) {
      return date.toISOString().slice(0, 16).replace('T', ' ');
    }
  }

  async function loadHistory(root) {
    const list = root.querySelector('[data-aic-hist-list]');
    list.replaceChildren(el('div', 'aic-empty', 'جارٍ التحميل...'));

    try {
      const res = await window.APP_API.get(window.APP_ROUTES.aiChatHistory());
      const items = (res && res.items) || [];
      if (!items.length) {
        list.replaceChildren(el('div', 'aic-empty', 'لا توجد محادثات سابقة بعد.'));
        return;
      }

      list.replaceChildren();
      items.forEach((item) => {
        const button = el('button', 'aic-hist-item');
        button.type = 'button';

        const ico = el('span', 'aic-hist-ico');
        ico.setAttribute('aria-hidden', 'true');
        ico.appendChild(icon('chat-left-text'));
        button.appendChild(ico);

        const text = el('div', 'aic-hist-txt');
        text.appendChild(el('span', 'aic-hist-when', formatWhen(item.updated_at)));
        text.appendChild(el('span', 'aic-hist-count',
          item.messages_count ? `${item.messages_count} رسالة` : 'محادثة محفوظة'));
        button.appendChild(text);
        button.appendChild(icon('chevron-left'));

        button.addEventListener('click', () => openHistorySession(root, item.session_id));
        list.appendChild(button);
      });
    } catch (err) {
      const box = el('div', 'aic-empty', errorMessage(err, 'تعذر تحميل سجل المحادثات.'));
      box.setAttribute('role', 'alert');
      list.replaceChildren(box);
    }
  }

  async function openHistorySession(root, sessionId) {
    const list = root.querySelector('[data-aic-hist-list]');
    list.replaceChildren(el('div', 'aic-empty', 'جارٍ استعادة المحادثة...'));

    try {
      const res = await window.APP_API.get(window.APP_ROUTES.aiChatHistoryMessages(sessionId));
      await window.APP_API.post(window.APP_ROUTES.aiChatHistoryResume(sessionId), {});

      const messages = (res && res.messages) || [];
      const body = root.querySelector('.aic-body');
      body.replaceChildren();

      if (!messages.length) {
        renderWelcome(root);
      } else {
        messages.forEach((message) => {
          pushMessage(body, message.content, message.role === 'user' ? 'user' : 'bot');
        });
      }

      switchTab(root, 'chat');
      closeHistory(root);
      scrollToEnd(body);
    } catch (err) {
      const box = el('div', 'aic-empty', errorMessage(err, 'تعذر استعادة المحادثة.'));
      box.setAttribute('role', 'alert');
      list.replaceChildren(box);
    }
  }

  function openHistory(root) {
    if (voice && voice.isCallBusy()) return;
    closeMenu(root);
    root.querySelector('.aic-hist').classList.add('show');
    loadHistory(root);
  }

  function closeHistory(root) {
    root.querySelector('.aic-hist').classList.remove('show');
  }

  /* ────────── الصوت والمكالمة (WebSocket مباشر) ────────── */

  function setVoiceStatus(root, state, label) {
    const bar = root.querySelector('.aic-vstatus');
    const text = root.querySelector('[data-aic-vstatus-text]');
    if (!label) {
      bar.classList.remove('show');
      return;
    }
    bar.dataset.state = state || 'idle';
    text.textContent = label;
    bar.classList.add('show');
  }

  function renderWave(root, level) {
    waveValues.push(level);
    waveValues.shift();
    const bars = root.querySelectorAll('.aic-wave span');
    bars.forEach((bar, index) => {
      const value = waveValues[index] || 0;
      bar.style.transform = `scaleY(${(0.12 + value * 0.88).toFixed(3)})`;
    });
    root.querySelector('.aic-call').style.setProperty('--aic-level', level.toFixed(3));
  }

  function ensureVoice(root) {
    if (voice) return voice;
    if (!capabilities || !capabilities.socketUrl || !window.AiChatVoice) return null;

    const body = root.querySelector('.aic-body');

    voice = window.AiChatVoice.create({
      socketUrl: capabilities.socketUrl,
      ttsSampleRate: capabilities.ttsSampleRate,
      liveSampleRate: capabilities.liveSampleRate,

      onUserText: (text, interim) => {
        dropChips(root);
        if (interim) {
          if (!interimRow) {
            interimRow = pushMessage(body, text, 'user');
            interimRow.classList.add('interim');
          } else {
            interimRow.querySelector('.aic-msg').textContent = text;
            scrollToEnd(body);
          }
          return;
        }
        if (interimRow) {
          interimRow.classList.remove('interim');
          interimRow.querySelector('.aic-msg').textContent = text;
          interimRow = null;
          scrollToEnd(body);
        } else {
          pushMessage(body, text, 'user');
        }
      },

      onAdvisorText: (text) => {
        dropChips(root);
        pushMessage(body, text, 'bot');
      },

      onSystem: (text) => pushNote(body, text),

      onError: (message) => {
        interimRow = null;
        setVoiceStatus(root, 'error', message);
        showError(root, message, null);
      },

      onState: (state, label) => setVoiceStatus(root, state, label),

      onLevel: (level) => renderWave(root, level),

      onRecording: (info) => {
        const dock = root.querySelector('.aic-rec');
        const foot = root.querySelector('.aic-foot');
        dock.classList.toggle('show', info.active);
        foot.hidden = info.active;
        root.querySelector('[data-aic-rec-timer]').textContent =
          voice ? voice.formatDuration(info.ms) : '0:00';
        const pause = root.querySelector('[data-aic-rec-pause]');
        pause.replaceChildren(icon(info.paused ? 'play-fill' : 'pause-fill'));
        pause.setAttribute('aria-label', info.paused ? 'متابعة التسجيل' : 'إيقاف مؤقت');
      },

      onCall: (info) => {
        const screen = root.querySelector('.aic-call');
        screen.classList.toggle('show', info.active || info.connecting);
        root.querySelector('[data-aic-call-timer]').textContent = info.connecting
          ? '…'
          : (voice ? voice.formatDuration(info.ms) : '0:00');
        root.querySelector('[data-aic-call-title]').textContent = info.connecting
          ? 'جارٍ الاتصال...'
          : (info.muted ? 'الميكروفون مكتوم' : 'مكالمة جارية');
        root.querySelector('[data-aic-call-sub]').textContent = info.connecting
          ? 'يتم تجهيز المكالمة الصوتية'
          : (info.muted
            ? 'صوتك مكتوم — لن يسمعك المستشار'
            : (info.advisorSpeaking ? 'المستشار يتحدّث الآن — يُرجى الاستماع' : 'تحدّث بحرية مع المستشار'));

        const mute = root.querySelector('[data-aic-mute]');
        mute.classList.toggle('is-muted', !!info.muted);
        mute.setAttribute('aria-pressed', info.muted ? 'true' : 'false');
        mute.replaceChildren(icon(info.muted ? 'mic-mute-fill' : 'mic-fill'));
        mute.setAttribute('aria-label', info.muted ? 'إلغاء كتم الميكروفون' : 'كتم الميكروفون');

        // زر المكالمة في الترويسة يختفي أثناء المكالمة؛ الإنهاء من شاشة المكالمة.
        const busyCall = info.active || info.connecting;
        const callBtn = root.querySelector('[data-aic-call]');
        if (callBtn) callBtn.hidden = !capabilities.canCall || busyCall;
        root.querySelector('[data-aic-history]').disabled = busyCall;
        // التصغير أثناء المكالمة يطوي شاشتها، فنمنعه ونفتح النافذة إن كانت مصغّرة.
        root.querySelector('[data-aic-min]').disabled = busyCall;
        if (busyCall && root.classList.contains('aic-min')) {
          root.classList.remove('aic-min');
          setPageLock(root);
        }
      },
    });

    return voice;
  }

  async function loadCapabilities() {
    if (capabilities) return capabilities;

    try {
      const res = await window.APP_API.get(window.APP_ROUTES.aiConfig());
      const features = (res && res.features) || {};
      const config = (res && res.voice) || {};
      const modes = config.stt_modes || [];
      const socketUrl = (res && res.voice_socket_url) || null;
      const canUseMic = !!(socketUrl && window.AiChatVoice
        && navigator.mediaDevices && navigator.mediaDevices.getUserMedia);

      capabilities = {
        socketUrl,
        canRecord: canUseMic && features.voiceChat !== false && modes.indexOf('record') !== -1,
        canCall: canUseMic && features.voiceCall !== false && modes.indexOf('live') !== -1,
        canClassify: features.isic4 !== false,
        // نعرض تلميح الصوت إذا كانت الخدمة تدعمه، حتى قبل منح إذن الميكروفون.
        voiceHint: !!(socketUrl && (
          (features.voiceChat !== false && modes.indexOf('record') !== -1)
          || (features.voiceCall !== false && modes.indexOf('live') !== -1)
        )),
        ttsSampleRate: config.tts_sample_rate,
        liveSampleRate: config.live_sample_rate,
      };
      if (typeof res.department_id === 'string' && res.department_id.trim()) {
        departmentId = res.department_id.trim();
      }
    } catch (_) {
      // تعذّر جلب الإعدادات لا يعطّل المحادثة النصية؛ نُخفي المزايا الإضافية فقط.
      capabilities = { socketUrl: null, canRecord: false, canCall: false, canClassify: false, voiceHint: false };
    }

    return capabilities;
  }

  function applyCapabilities(root) {
    root.querySelector('[data-aic-call]').hidden = !capabilities.canCall;
    root.querySelector('[data-aic-tab="isic4"]').hidden = !capabilities.canClassify;
    root.querySelector('.aic-tabs').hidden = !capabilities.canClassify;
    const hint = root.querySelector('[data-aic-voice-hint]');
    if (hint) hint.hidden = !capabilities.voiceHint;
    syncSendState(root);
  }

  /* ────────── التبويبات والنافذة ────────── */

  function switchTab(root, name) {
    root.querySelectorAll('.aic-tab').forEach((tab) => {
      const active = tab.dataset.aicTab === name;
      tab.classList.toggle('active', active);
      tab.setAttribute('aria-selected', active ? 'true' : 'false');
    });
    root.querySelectorAll('.aic-view').forEach((view) => {
      view.classList.toggle('active', view.dataset.aicView === name);
    });
  }

  function closeMenu(root) {
    root.querySelector('.aic-menu').classList.remove('open');
    root.querySelector('[data-aic-menu]').setAttribute('aria-expanded', 'false');
  }

  /** الـ chat box لا يقفل تمرير الصفحة. */
  function setPageLock(root) {
    document.body.classList.remove('aic-chat-lock');
    const open = root.classList.contains('aic-open') && !root.classList.contains('aic-min');
    root.querySelector('.aic-panel').setAttribute('aria-modal', open ? 'true' : 'false');
  }

  function openPanel(root) {
    root.classList.add('aic-open');
    root.classList.remove('aic-min');
    setPageLock(root);
    root.querySelector('.aic-fab').setAttribute('aria-label', 'إغلاق المستشار الذكي');
    scrollToEnd(root.querySelector('.aic-body'));
    root.querySelector('.aic-input').focus();

    loadCapabilities().then(() => applyCapabilities(root));
  }

  function closePanel(root) {
    if (voice && voice.isCallBusy()) {
      // إغلاق النافذة أثناء مكالمة يجب أن يُنهيها لا أن يتركها تعمل في الخفاء.
      voice.endCall();
    }
    if (voice && voice.isRecording()) voice.discardRecording();

    closeMenu(root);
    closeHistory(root);
    root.querySelector('.aic-confirm').classList.remove('open');
    root.classList.remove('aic-open', 'aic-min');
    setPageLock(root);
    const fab = root.querySelector('.aic-fab');
    fab.setAttribute('aria-label', 'المستشار الذكي');
    fab.focus();
  }

  /* ────────── التركيب ────────── */

  function waveMarkup() {
    return new Array(WAVE_BARS).fill('<span></span>').join('');
  }

  function panelMarkup() {
    return `
      <section class="aic-panel" role="dialog" aria-modal="false" aria-label="المستشار الذكي">
        <header class="aic-head">
          <div class="aic-id">
            <span class="aic-avatar"><i class="bi bi-robot" aria-hidden="true"></i></span>
            <span class="aic-id-txt">
              <span class="aic-title">المستشار الذكي</span>
              <span class="aic-sub">مساعدك للاستفسارات</span>
            </span>
          </div>
          <div class="aic-tools">
            <button type="button" class="aic-tool" data-aic-history aria-label="سجل المحادثات"><i class="bi bi-clock-history" aria-hidden="true"></i></button>
            <button type="button" class="aic-tool aic-tool-call" data-aic-call aria-label="مكالمة صوتية" hidden><i class="bi bi-telephone" aria-hidden="true"></i></button>
            <span class="aic-menu-wrap">
              <button type="button" class="aic-tool" data-aic-menu aria-label="خيارات" aria-haspopup="true" aria-expanded="false"><i class="bi bi-three-dots-vertical" aria-hidden="true"></i></button>
              <span class="aic-menu" role="menu">
                <button type="button" class="aic-menu-item" data-aic-new role="menuitem"><i class="bi bi-plus-circle" aria-hidden="true"></i>محادثة جديدة</button>
                <button type="button" class="aic-menu-item" data-aic-min role="menuitem"><i class="bi bi-dash-lg" aria-hidden="true"></i>تصغير</button>
              </span>
            </span>
            <button type="button" class="aic-tool" data-aic-close aria-label="إغلاق"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
          </div>
        </header>

        <nav class="aic-tabs" role="tablist" hidden>
          <button type="button" class="aic-tab active" data-aic-tab="chat" role="tab" aria-selected="true"><i class="bi bi-chat-dots" aria-hidden="true"></i>محادثة</button>
          <button type="button" class="aic-tab" data-aic-tab="isic4" role="tab" aria-selected="false" hidden><i class="bi bi-tag" aria-hidden="true"></i>تصنيف ISIC4</button>
        </nav>

        <div class="aic-view active" data-aic-view="chat">
          <div class="aic-body" role="log" aria-live="polite"></div>

          <div class="aic-vstatus" data-state="idle" role="status">
            <span class="aic-vdot" aria-hidden="true"></span>
            <span data-aic-vstatus-text></span>
          </div>

          <form class="aic-foot">
            <div class="aic-inputwrap">
              <textarea class="aic-input" rows="1" maxlength="${MAX_LEN}" placeholder="اكتب رسالة..." aria-label="اكتب رسالة"></textarea>
              <button type="button" class="aic-act aic-mic" data-aic-mic aria-label="تسجيل رسالة صوتية" hidden><i class="bi bi-mic-fill" aria-hidden="true"></i></button>
              <button type="submit" class="aic-act aic-send" aria-label="إرسال" disabled><i class="bi bi-send-fill" aria-hidden="true"></i></button>
            </div>
            <div class="aic-voice-hint" data-aic-voice-hint hidden>الصوت: تسجيل + مكالمة</div>
          </form>

          <div class="aic-rec">
            <button type="button" class="aic-rec-btn danger" data-aic-rec-delete aria-label="حذف التسجيل"><i class="bi bi-trash3" aria-hidden="true"></i></button>
            <div class="aic-rec-mid">
              <span class="aic-rec-timer" data-aic-rec-timer>0:00</span>
              <div class="aic-wave" aria-hidden="true">${waveMarkup()}</div>
            </div>
            <button type="button" class="aic-rec-btn" data-aic-rec-pause aria-label="إيقاف مؤقت"><i class="bi bi-pause-fill" aria-hidden="true"></i></button>
            <button type="button" class="aic-act" data-aic-rec-send aria-label="إرسال التسجيل"><i class="bi bi-send-fill" aria-hidden="true"></i></button>
          </div>
        </div>

        <div class="aic-view" data-aic-view="isic4">
          <div class="aic-isic4">
            <label class="aic-label" for="aicDesc">صف نشاطك التجاري بلغتك الخاصة</label>
            <textarea class="aic-desc" id="aicDesc" maxlength="${MAX_DESC_LEN}" placeholder="مثال: عندي مزرعة أزرع فيها القمح والشعير"></textarea>
            <button type="button" class="aic-classify"><i class="bi bi-tag-fill" aria-hidden="true"></i><span>تصنيف النشاط</span></button>
            <div data-aic-isic4-result></div>
          </div>
        </div>

        <div class="aic-hist" role="dialog" aria-label="سجل المحادثات">
          <div class="aic-hist-head">
            <span class="aic-hist-title">سجل المحادثات</span>
            <button type="button" class="aic-tool" data-aic-hist-close aria-label="إغلاق السجل"><i class="bi bi-x-lg" aria-hidden="true"></i></button>
          </div>
          <div class="aic-hist-list" data-aic-hist-list></div>
        </div>

        <div class="aic-call" role="dialog" aria-label="مكالمة صوتية">
          <div class="aic-call-ava" aria-hidden="true"><i class="bi bi-robot"></i></div>
          <div class="aic-call-title" data-aic-call-title>جارٍ الاتصال...</div>
          <div class="aic-call-sub" data-aic-call-sub>يتم تجهيز المكالمة الصوتية</div>
          <div class="aic-call-timer" data-aic-call-timer>…</div>
          <div class="aic-call-acts">
            <button type="button" class="aic-call-btn" data-aic-mute aria-label="كتم الميكروفون" aria-pressed="false"><i class="bi bi-mic-fill" aria-hidden="true"></i></button>
            <button type="button" class="aic-call-btn hangup" data-aic-hangup aria-label="إنهاء المكالمة"><i class="bi bi-telephone-fill" aria-hidden="true"></i></button>
          </div>
        </div>

        <div class="aic-confirm" role="alertdialog" aria-label="تأكيد بدء محادثة جديدة">
          <div class="aic-confirm-box">
            <p class="aic-confirm-text">سيتم إنهاء سياق المحادثة الحالية.</p>
            <div class="aic-confirm-acts">
              <button type="button" class="aic-btn-ghost" data-aic-cancel>إلغاء</button>
              <button type="button" class="aic-btn-solid" data-aic-confirm>بدء محادثة جديدة</button>
            </div>
          </div>
        </div>
      </section>
      <button type="button" class="aic-fab" aria-label="المستشار الذكي">
        <i class="bi bi-chat-fill aic-ico-open" aria-hidden="true"></i>
        <i class="bi bi-x-lg aic-ico-close" aria-hidden="true"></i>
        <span class="aic-tip" aria-hidden="true">المستشار الذكي</span>
      </button>
    `;
  }

  function mount() {
    if (document.getElementById(ROOT_ID)) return;
    if (!window.APP_API || !window.APP_ROUTES || !window.APP_ROUTES.aiChat) return;
    if (!window.AppAuth || !window.AppAuth.isLoggedIn || !window.AppAuth.isLoggedIn()) return;

    ensureStyles();

    const root = document.createElement('div');
    root.id = ROOT_ID;
    if (OFFSET_PAGES.test(window.location.pathname || '')) {
      root.classList.add('aic-offset');
    }
    root.innerHTML = panelMarkup();
    document.body.appendChild(root);

    renderWelcome(root);

    const input = root.querySelector('.aic-input');
    const menu = root.querySelector('.aic-menu');
    const menuBtn = root.querySelector('[data-aic-menu]');
    const confirmBox = root.querySelector('.aic-confirm');

    root.querySelector('.aic-fab').addEventListener('click', () => {
      if (root.classList.contains('aic-open')) closePanel(root);
      else openPanel(root);
    });
    root.querySelector('[data-aic-close]').addEventListener('click', () => closePanel(root));
    root.querySelector('[data-aic-min]').addEventListener('click', () => {
      closeMenu(root);
      root.classList.toggle('aic-min');
      setPageLock(root);
      if (!root.classList.contains('aic-min')) scrollToEnd(root.querySelector('.aic-body'));
    });

    root.querySelectorAll('.aic-tab').forEach((tab) => {
      tab.addEventListener('click', () => switchTab(root, tab.dataset.aicTab));
    });

    menuBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = menu.classList.toggle('open');
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    root.querySelector('[data-aic-new]').addEventListener('click', () => {
      closeMenu(root);
      if (hasMessages(root)) {
        confirmBox.classList.add('open');
        root.querySelector('[data-aic-confirm]').focus();
      } else {
        input.focus();
      }
    });

    root.querySelector('[data-aic-cancel]').addEventListener('click', () => {
      confirmBox.classList.remove('open');
      input.focus();
    });

    root.querySelector('[data-aic-confirm]').addEventListener('click', () => {
      confirmBox.classList.remove('open');
      resetChat(root);
    });

    root.querySelector('[data-aic-history]').addEventListener('click', () => openHistory(root));
    root.querySelector('[data-aic-hist-close]').addEventListener('click', () => closeHistory(root));

    root.querySelector('.aic-classify').addEventListener('click', () => classifyActivity(root));

    /* ── الصوت ── */
    root.querySelector('[data-aic-mic]').addEventListener('click', () => {
      const engine = ensureVoice(root);
      if (engine) engine.startRecording();
    });
    root.querySelector('[data-aic-rec-send]').addEventListener('click', () => {
      if (voice) voice.sendRecording();
    });
    root.querySelector('[data-aic-rec-delete]').addEventListener('click', () => {
      if (voice) voice.discardRecording();
    });
    root.querySelector('[data-aic-rec-pause]').addEventListener('click', () => {
      if (voice) voice.togglePauseRecording();
    });
    root.querySelector('[data-aic-call]').addEventListener('click', () => {
      const engine = ensureVoice(root);
      if (engine) engine.startCall();
    });
    root.querySelector('[data-aic-mute]').addEventListener('click', () => {
      if (voice) voice.toggleMute();
    });
    root.querySelector('[data-aic-hangup]').addEventListener('click', () => {
      if (voice) voice.endCall();
    });

    input.addEventListener('input', () => {
      autoGrow(input);
      syncSendState(root);
    });
    input.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        submitFromInput(root);
      }
    });

    root.querySelector('.aic-foot').addEventListener('submit', (e) => {
      e.preventDefault();
      submitFromInput(root);
    });

    document.addEventListener('click', (e) => {
      if (!root.contains(e.target)) closeMenu(root);
    });

    document.addEventListener('keydown', (e) => {
      if (e.key !== 'Escape' || !root.classList.contains('aic-open')) return;
      if (confirmBox.classList.contains('open')) {
        confirmBox.classList.remove('open');
        return;
      }
      if (menu.classList.contains('open')) {
        closeMenu(root);
        return;
      }
      if (voice && voice.isCallBusy()) {
        voice.endCall();
        return;
      }
      if (root.querySelector('.aic-hist').classList.contains('show')) {
        closeHistory(root);
        return;
      }
      closePanel(root);
    });

    // مغادرة الصفحة أثناء مكالمة يجب أن تُغلق الميكروفون والقناة.
    window.addEventListener('pagehide', () => {
      if (voice) voice.destroy();
    });

    // نجلب المزايا مبكراً كي لا تظهر الأزرار متأخرة عند أول فتح.
    const prefetch = () => loadCapabilities().then(() => applyCapabilities(root)).catch(() => {});
    if (window.requestIdleCallback) window.requestIdleCallback(prefetch, { timeout: 2500 });
    else setTimeout(prefetch, 800);

    window.AiChatFab = {
      open: () => openPanel(root),
      close: () => closePanel(root),
      toggle: () => (root.classList.contains('aic-open') ? closePanel(root) : openPanel(root)),
    };
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', mount);
  } else {
    mount();
  }
})();
