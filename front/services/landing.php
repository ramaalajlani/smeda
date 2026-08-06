<?php
$basePath = '../';
$activePage = 'services';

require_once __DIR__ . '/../includes/layout/paths.php';
require_once __DIR__ . '/../includes/sections/service-landings-data.php';

$slug = isset($_GET['slug']) ? preg_replace('/[^a-z0-9-]/', '', (string) $_GET['slug']) : '';
$landing = get_service_landing($slug);

if (!$landing) {
    header('Location: ' . front_url('index.php#services'));
    exit;
}

$pageTitle = $landing['page_title'];

include __DIR__ . '/../includes/sections/service-landing-page.php';
