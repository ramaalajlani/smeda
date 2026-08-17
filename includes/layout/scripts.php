<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- i18n (after Bootstrap for dropdowns) -->
<script src="<?php echo $basePath ?? ''; ?>assets/js/core/i18n.js?v=1.0"></script>

<!-- ========================= -->
<!-- CORE (🔥 لازم أول شي) -->
<!-- ========================= -->
<script src="<?php echo $basePath ?? ''; ?>assets/js/core/config.js?v=2.2"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/core/routes.js?v=2.4"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/core/api.js?v=2.2"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/core/helpers.js?v=2.3"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/core/ui.js?v=2.3"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/core/auth.js?v=20260810-4"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/core/permissions.js?v=2.1"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/core/access-control.js?v=2.4"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/core/bootstrap-auth.js?v=20260810-2"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/core/notifications.js?v=1.0"></script>

<!-- ========================= -->
<!-- MODULES (بعد الـ core) -->
<!-- ========================= -->
<script src="<?php echo $basePath ?? ''; ?>assets/js/modules/counters.js?v=1.0"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/modules/navbar.js?v=1.0"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/modules/logo-fallback.js?v=1.0"></script>
<?php if (!empty($loadAuthMessaging)): ?>
<script src="<?php echo $basePath ?? ''; ?>assets/js/modules/auth-messaging.js?v=1.0"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/modules/ui-feedback.js?v=1.0"></script>
<?php endif; ?>
<?php if (empty($disableAiChat)): ?>
<script src="<?php echo $basePath ?? ''; ?>assets/js/modules/ai-chat-voice.js?v=1.1"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/modules/ai-chat-fab.js?v=3.7"></script>
<?php endif; ?>
<!-- ========================= -->
<!-- MAIN -->
<!-- ========================= -->
<script src="<?php echo $basePath ?? ''; ?>assets/js/modules/global-motion.js?v=1.0"></script>
<script src="<?php echo $basePath ?? ''; ?>assets/js/main.js?v=1.0"></script>