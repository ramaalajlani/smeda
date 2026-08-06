<?php
/** سايدبار المتدرب. */
$teActive = $teActive ?? '';
$basePath = $basePath ?? '../../';
$teRoot = $basePath . 'services/training/';
if (!function_exists('te_nav')) {
  function te_nav($active, $slug, $href, $icon, $label) {
    $cls = $active === $slug ? 'active' : '';
    echo "<a class=\"$cls\" href=\"" . htmlspecialchars($href, ENT_QUOTES) . "\"><i class=\"bi $icon\"></i>" . htmlspecialchars($label) . "</a>";
  }
}
?>
<aside class="tc-sidebar" id="tcSidebar">
  <div class="tc-sb-brand">
    <div class="av" id="sbAv">م</div>
    <div class="nm" id="sbName">متدرب</div>
    <div class="rl" id="sbRole">تطبيق المتدرب</div>
  </div>
  <nav class="tc-sb-nav grow">
    <?php te_nav($teActive, 'courses', $teRoot.'trainee-app.php', 'bi-mortarboard', 'دوراتي'); ?>
    <?php te_nav($teActive, 'certificates', $teRoot.'trainee-certificates.php', 'bi-patch-check', 'شهاداتي'); ?>
  </nav>
  <div class="tc-sb-nav tc-sb-foot">
    <a class="<?php echo $teActive === 'profile' ? 'active' : ''; ?>" href="<?php echo $teRoot; ?>trainee-profile.php"><i class="bi bi-info-circle"></i> معلوماتي</a>
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
    s.classList.remove('open'); if(o) o.classList.remove('open'); document.body.classList.remove('tc-nav-open'); return;
  }
  const willOpen = (open===undefined) ? !s.classList.contains('open') : !!open;
  s.classList.toggle('open', willOpen); if(o) o.classList.toggle('open', willOpen); document.body.classList.toggle('tc-nav-open', willOpen);
}
function tcLogout(){ try{ if(window.AppAuth&&window.AppAuth.logout){window.AppAuth.logout();return;} }catch(e){} try{localStorage.clear();}catch(e){} location.href='<?php echo $basePath; ?>login.php'; }
document.addEventListener('DOMContentLoaded', ()=>{
  document.body.classList.add('tc-app');
  try{
    const u=window.AppAuth&&window.AppAuth.getUser&&window.AppAuth.getUser();
    if(u&&u.name){ document.getElementById('sbName').textContent=u.name; document.getElementById('sbAv').textContent=(u.name.trim()[0]||'م'); }
  }catch(e){}
  const side=document.getElementById('tcSidebar');
  if(side){ side.querySelectorAll('a').forEach(a=>a.addEventListener('click', ()=>{ if(tcIsMobileNav()) tcToggleSidebar(false); })); }
  window.addEventListener('resize', ()=>{ if(!tcIsMobileNav()) tcToggleSidebar(false); });
  document.addEventListener('keydown', (e)=>{ if(e.key==='Escape') tcToggleSidebar(false); });
});
</script>
