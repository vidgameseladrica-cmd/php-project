<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/../config/database.php';

if (!empty($_SESSION['user_id'])) {
    header('Location: /YAKAP/dashboard/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, username, password_hash, full_name, role FROM users WHERE username = ? AND status = 'Active' LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        header('Location: /YAKAP/dashboard/index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>YAKAP Login</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="/YAKAP/assets/css/style.css" rel="stylesheet">
</head>
<body class="login-page">
<div class="login-card">
    <div class="text-center mb-3">
        <img src="/YAKAP/assets/images/yakap-logo.png" class="login-logo" alt="PhilHealth YAKAP - Para Malayo sa Sakit">
    </div>
    
    <p class="text-center text-muted mb-4">PhilHealth Medicine and Services Monitoring</p>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="off">
        <label class="form-label">Username</label>
        <input class="form-control mb-3" name="username" required autofocus>

        <label class="form-label">Password</label>
        <input class="form-control mb-4" type="password" name="password" required>

        <button class="btn btn-primary w-100">Sign In</button>
    </form>

    <p class="text-muted text-center mt-3 mb-0">YAKAP Version 1.0</p>
</div>
</body>
</html>
