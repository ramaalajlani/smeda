<?php
/**
 * Legacy login fragment — redirects to unified login page.
 * @deprecated Use front/login.php
 */
require_once dirname(__DIR__) . '/layout/paths.php';
header('Location: ' . front_url('login.php'), true, 302);
exit;
