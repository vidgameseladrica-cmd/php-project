<?php
declare(strict_types=1);
require_once __DIR__ . '/auth.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($pageTitle ?? 'YAKAP') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/YAKAP/assets/css/style.css" rel="stylesheet">
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="login-logo-container">

        <img
            src="/YAKAP/assets/images/yakap-logo.png"
            class="login-logo"
            alt="PhilHealth YAKAP - Para Malayo sa Sakit"
        >

        <nav>
            <a href="/YAKAP/dashboard/index.php">Dashboard</a>
            <a href="/YAKAP/patients/index.php">Medical Patient Records</a>
            <a href="/YAKAP/medicines/index.php">Medicine Inventory</a>
            <a href="/YAKAP/purchases/index.php">Medicine Purchases</a>
            <a href="/YAKAP/reports/index.php">Reports</a>
            <a href="/YAKAP/backup/index.php">Backup Records</a>
			<a href="/YAKAP/medstat/medicine_statistics.php">Medicine Statistics</a>
			<a href="/YAKAP/admin/index.php">System Adminstration</a>
            <a href="/YAKAP/auth/logout.php">Logout</a>
        </nav>
    </aside>
    <main class="content">
        <div class="topbar">
            <div>
                <h1><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
                <small>Medicine and Services Monitoring System</small>
            </div>
            <span class="user-badge"><?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?></span>
        </div>
