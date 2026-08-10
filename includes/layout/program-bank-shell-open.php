<?php
/**
 * فتح قوقعة بنك البرامج — نفس سايدبار الداشبورد + توبار وهمبرغر للموبايل.
 */
$basePath = isset($basePath) ? $basePath : '../../';
require_once __DIR__ . '/shell-preference.php';

if (empty($dashboardRole)) {
    $dashboardRole = authority_shell_role() ?: 'training_manager';
}
if (empty($sidebarTitle)) {
    $sidebarTitle = 'SMEDA — بنك الحقائب';
}

include __DIR__ . '/dashboard-shell-open.php';
