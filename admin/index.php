<?php

declare(strict_types=1);

/**
 * Admin panel controller: authentication, lead actions and rendering.
 * Data access goes through LeadRepository; presentation lives in view.php.
 */

use SlavaAds\Admin\AdminAuth;
use SlavaAds\Admin\LeadCsvExporter;
use SlavaAds\Database\Connection;
use SlavaAds\Lead\LeadRepository;
use SlavaAds\Lead\LeadStatus;

/** @var \SlavaAds\Config\Env $env */
$env = require __DIR__ . '/../api/bootstrap.php';
require __DIR__ . '/view.php';

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

$auth = AdminAuth::fromEnv($env);

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: ./');
    exit;
}

$loginError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if ($auth->verify($username, $password)) {
        session_regenerate_id(true);
        $_SESSION['auth'] = true;
        header('Location: ./');
        exit;
    }
    $loginError = 'Неверный логин или пароль';
}

if (empty($_SESSION['auth'])) {
    admin_render_login($loginError);
    exit;
}

$repository = new LeadRepository(Connection::fromEnv($env));
$statusFilter = LeadStatus::tryFromValue($_GET['status'] ?? null);

// Update a lead's status.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_status'], $_POST['lead_id'])) {
    $newStatus = LeadStatus::tryFromValue((string) $_POST['set_status']);
    if ($newStatus !== null) {
        $repository->updateStatus((int) $_POST['lead_id'], $newStatus);
    }
    header('Location: ./' . ($statusFilter !== null ? '?status=' . $statusFilter->value : ''));
    exit;
}

// CSV export.
if (isset($_GET['export'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="leads-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    (new LeadCsvExporter())->write($out, $repository->all());
    fclose($out);
    exit;
}

$stats = $repository->stats((new DateTimeImmutable('now'))->modify('-7 days'));
$leads = $repository->recent(500, $statusFilter);

admin_render_dashboard($leads, $stats, $statusFilter?->value ?? '');
