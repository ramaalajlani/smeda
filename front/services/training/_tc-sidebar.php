<?php
/**
 * سايدبار — مركز أو مدرب (يُضبط تلقائياً من الدور).
 */
$tcActive = $tcActive ?? '';
$basePath = $basePath ?? '../../';
$tcRoot = $basePath . 'services/training/';

$tcKitScreens = ['kit', 'kit-materials', 'kit-programs', 'kit-courses', 'kit-create', 'kit-form', 'kit-create-form', 'kit-trainers'];
$tcTrainerScreens = ['trainer', 'trainer-form', 'trainer-kits', 'trainer-courses', 'trainer-create'];
$tcCourseScreens = ['course', 'groups', 'group', 'trainees', 'modules', 'attendance', 'certificates', 'report', 'course-create', 'course-edit', 'scores'];

$rawId = isset($_GET['id']) && ctype_digit((string)$_GET['id']) ? (int)$_GET['id'] : null;
$tcCourseId = isset($_GET['course']) && ctype_digit((string)$_GET['course']) ? (int)$_GET['course'] : null;
$tcGroupId = isset($_GET['group']) && ctype_digit((string)$_GET['group']) ? (int)$_GET['group'] : null;
$tcKitId = isset($_GET['kit']) && ctype_digit((string)$_GET['kit']) ? (int)$_GET['kit'] : null;
$tcTrainerId = isset($_GET['trainer']) && ctype_digit((string)$_GET['trainer']) ? (int)$_GET['trainer'] : null;

if (!$tcCourseId && in_array($tcActive, ['course', 'report', 'course-edit'], true) && $rawId) {
    $tcCourseId = $rawId;
}
if (!$tcKitId && in_array($tcActive, $tcKitScreens, true) && $rawId) {
    $tcKitId = $rawId;
}
if (!$tcTrainerId && in_array($tcActive, $tcTrainerScreens, true) && $rawId) {
    $tcTrainerId = $rawId;
}

$tcMainActive = $tcActive;
if (in_array($tcActive, $tcCourseScreens, true)) {
    $tcMainActive = 'courses';
} elseif (in_array($tcActive, $tcKitScreens, true)) {
    $tcMainActive = 'kits';
} elseif (in_array($tcActive, $tcTrainerScreens, true)) {
    $tcMainActive = 'trainers';
} elseif (in_array($tcActive, ['trainee-form', 'trainee-create', 'trainees-list', 'trainee'], true)) {
    $tcMainActive = 'trainees-list';
} elseif ($tcActive === 'profile') {
    $tcMainActive = 'profile';
}

$tcGq = $tcGroupId ? ('&group=' . $tcGroupId) : '';

if (!function_exists('tc_nav')) {
    function tc_nav($active, $slug, $href, $icon, $label, $attrs = '') {
        $cls = $active === $slug ? 'active' : '';
        echo "<a class=\"$cls\" href=\"" . htmlspecialchars($href, ENT_QUOTES) . "\" $attrs><i class=\"bi $icon\"></i>" . htmlspecialchars($label) . "</a>";
    }
}
?>
<aside class="tc-sidebar" id="tcSidebar">
  <div class="tc-sb-brand">
    <div class="av" id="sbAv">م</div>
    <div class="nm" id="sbName">مركز تدريبي</div>
    <div class="rl" id="sbRole">تطبيق المركز التدريبي</div>
  </div>
  <nav class="tc-sb-nav grow">
    <!-- مركز -->
    <div data-ws="center">
      <?php tc_nav($tcMainActive, 'courses', $tcRoot.'center-app.php', 'bi-mortarboard', 'الدورات'); ?>
      <?php tc_nav($tcMainActive, 'kits', $tcRoot.'center-kits.php', 'bi-briefcase', 'الحقائب التدريبية'); ?>
      <?php tc_nav($tcMainActive, 'trainers', $tcRoot.'center-trainers.php', 'bi-person-workspace', 'المدربون'); ?>
      <?php tc_nav($tcMainActive, 'trainees-list', $tcRoot.'center-trainees-list.php', 'bi-people', 'المتدربون'); ?>
    </div>
    <!-- مدرب -->
    <div data-ws="trainer" hidden>
      <?php tc_nav($tcMainActive, 'courses', $tcRoot.'trainer-app.php', 'bi-mortarboard', 'دوراتي'); ?>
    </div>

    <?php if ($tcCourseId): ?>
      <div class="tc-sb-label">هذه الدورة</div>
      <?php
        tc_nav($tcActive, 'course',       $tcRoot."center-course.php?id={$tcCourseId}",          'bi-grid',           'لوحة الدورة');
        tc_nav($tcActive, 'groups',       $tcRoot."center-groups.php?course={$tcCourseId}",       'bi-collection',     'الصفوف');
        tc_nav($tcActive, 'trainees',     $tcRoot."center-trainees.php?course={$tcCourseId}",     'bi-people-fill',    'متدربو الدورة');
        tc_nav(in_array($tcActive, ['modules','scores'], true) ? 'modules' : $tcActive, 'modules',
          $tcRoot."center-modules.php?course={$tcCourseId}{$tcGq}", 'bi-journal-text', $tcGroupId ? 'درجات الصف' : 'المواد والدرجات');
        tc_nav($tcActive, 'attendance',   $tcRoot."center-attendance.php?course={$tcCourseId}{$tcGq}", 'bi-calendar-check', $tcGroupId ? 'حضور الصف' : 'الحضور');
        tc_nav($tcActive, 'certificates', $tcRoot."center-certificates.php?course={$tcCourseId}", 'bi-patch-check',    'الشهادات');
        tc_nav($tcActive, 'report',       $tcRoot."center-course-report.php?id={$tcCourseId}",    'bi-file-earmark',   'تقرير الدورة');
      ?>
    <?php endif; ?>

    <?php if ($tcCourseId && $tcGroupId): ?>
      <div class="tc-sb-label">هذا الصف</div>
      <?php
        tc_nav($tcActive, 'group', $tcRoot."center-group.php?course={$tcCourseId}&group={$tcGroupId}", 'bi-people', 'متدربو الصف');
        echo '<a href="' . htmlspecialchars($tcRoot."center-modules.php?course={$tcCourseId}", ENT_QUOTES) . '"><i class="bi bi-arrows-fullscreen"></i>كل الدورة (بدون صف)</a>';
      ?>
    <?php endif; ?>

    <div data-ws="center">
    <?php if ($tcKitId): ?>
      <div class="tc-sb-label">هذه الحقيبة</div>
      <?php
        tc_nav($tcActive, 'kit',           $tcRoot."center-kit.php?id={$tcKitId}", 'bi-grid', 'إدارة الحقيبة');
        tc_nav($tcActive, 'kit-materials', $tcRoot."center-kit-materials.php?kit={$tcKitId}", 'bi-collection', 'مواد الحقيبة');
        tc_nav($tcActive, 'kit-trainers',  $tcRoot."center-kit-trainers.php?kit={$tcKitId}", 'bi-person-workspace', 'مدربو الحقيبة');
        tc_nav($tcActive, 'kit-form',      $tcRoot."center-kit-form.php?id={$tcKitId}", 'bi-pencil-square', 'تعديل الحقيبة');
        tc_nav($tcActive, 'kit-programs',  $tcRoot."center-kit-programs.php?kit={$tcKitId}", 'bi-journal-bookmark', 'البرامج');
        tc_nav($tcActive, 'kit-courses',   $tcRoot."center-kit-courses.php?kit={$tcKitId}", 'bi-collection', 'دورات الحقيبة');
        tc_nav($tcActive, 'kit-create',    $tcRoot."center-kit-create-course.php?kit={$tcKitId}", 'bi-plus-circle', 'إنشاء دورة');
      ?>
    <?php endif; ?>

    <?php if ($tcTrainerId): ?>
      <div class="tc-sb-label">هذا المدرب</div>
      <?php
        tc_nav($tcActive, 'trainer', $tcRoot."center-trainer.php?id={$tcTrainerId}", 'bi-person-badge', 'إدارة المدرب');
        tc_nav($tcActive, 'trainer-form', $tcRoot."center-trainer-form.php?id={$tcTrainerId}", 'bi-pencil-square', 'تعديل المدرب');
        tc_nav($tcActive, 'trainer-kits', $tcRoot."center-trainer-kits.php?trainer={$tcTrainerId}", 'bi-briefcase', 'حقائب المدرب');
        tc_nav($tcActive, 'trainer-courses', $tcRoot."center-trainer-courses.php?trainer={$tcTrainerId}", 'bi-collection', 'دورات المدرب');
      ?>
    <?php endif; ?>
    </div>
  </nav>
  <div class="tc-sb-nav tc-sb-foot">
    <a class="<?php echo $tcMainActive === 'profile' ? 'active' : ''; ?>" href="<?php echo $tcRoot; ?>center-profile.php"><i class="bi bi-info-circle"></i> معلومات المستخدم</a>
    <button type="button" onclick="tcLogout()"><i class="bi bi-box-arrow-right"></i> تسجيل الخروج</button>
  </div>
</aside>
<div class="tc-overlay" id="tcOverlay" onclick="tcToggleSidebar(false)"></div>
<script>
function tcIsMobileNav(){ return window.matchMedia('(max-width:899.98px)').matches; }
function tcToggleSidebar(open){
  const s=document.getElementById('tcSidebar'), o=document.getElementById('tcOverlay');
  if(!s) return;
  if(!tcIsMobileNav()){
    s.classList.remove('open');
    if(o) o.classList.remove('open');
    document.body.classList.remove('tc-nav-open');
    return;
  }
  const willOpen = (open===undefined) ? !s.classList.contains('open') : !!open;
  s.classList.toggle('open', willOpen);
  if(o) o.classList.toggle('open', willOpen);
  document.body.classList.toggle('tc-nav-open', willOpen);
}
function tcLogout(){ try{ if(window.AppAuth&&window.AppAuth.logout){window.AppAuth.logout();return;} }catch(e){} try{localStorage.clear();}catch(e){} location.href='<?php echo $basePath; ?>login.php'; }
function tcApplyWorkspaceNav(){
  try{
    const isTrainer = window.AppAuth && AppAuth.isTrainerWorkspaceUser && AppAuth.isTrainerWorkspaceUser();
    document.querySelectorAll('[data-ws="center"]').forEach(el => { el.hidden = !!isTrainer; });
    document.querySelectorAll('[data-ws="trainer"]').forEach(el => { el.hidden = !isTrainer; });
    document.querySelectorAll('[data-center-only]').forEach(el => { el.hidden = !!isTrainer; });
    const roleEl=document.getElementById('sbRole');
    if(roleEl) roleEl.textContent = isTrainer ? 'تطبيق المدرب' : 'تطبيق المركز التدريبي';
  }catch(e){}
}
document.addEventListener('DOMContentLoaded', ()=>{
  document.body.classList.add('tc-app');
  try{
    const u=window.AppAuth&&window.AppAuth.getUser&&window.AppAuth.getUser();
    if(u&&u.name){
      document.getElementById('sbName').textContent=u.name;
      document.getElementById('sbAv').textContent=(u.name.trim()[0]||'م');
    }
  }catch(e){}
  tcApplyWorkspaceNav();
  // بعد جلب /me قد يتغيّر الكاش
  setTimeout(tcApplyWorkspaceNav, 400);
  const side=document.getElementById('tcSidebar');
  if(side){
    side.querySelectorAll('a').forEach(a=>{
      a.addEventListener('click', (e)=>{
        if(window.TC && TC.isDirty && TC.isDirty()){
          if(!confirm('لديك تعديلات غير محفوظة. مغادرة الصفحة؟')){ e.preventDefault(); return; }
          TC.clearDirty();
        }
        if(tcIsMobileNav()) tcToggleSidebar(false);
      });
    });
  }
  window.addEventListener('resize', ()=>{ if(!tcIsMobileNav()) tcToggleSidebar(false); });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') tcToggleSidebar(false); });
});
</script>
