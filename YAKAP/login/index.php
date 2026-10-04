<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| CHECK EXISTING LOGIN
|--------------------------------------------------------------------------
*/

if (!empty($_SESSION['user_id'])) {

    header('Location: /YAKAP/dashboard/index.php');
    exit;

}


$error = '';


/*
|--------------------------------------------------------------------------
| LOGIN PROCESS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';


    $stmt = $pdo->prepare("
        SELECT
            id,
            username,
            password_hash,
            full_name,
            role
        FROM users
        WHERE username = ?
          AND status = 'Active'
        LIMIT 1
    ");

    $stmt->execute([$username]);

    $user = $stmt->fetch();


    if (
        $user &&
        password_verify(
            $password,
            $user['password_hash']
        )
    ) {

        session_regenerate_id(true);

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']     = $user['role'];


        header(
            'Location: /YAKAP/dashboard/index.php'
        );

        exit;

    }


    $error = 'Invalid username or password.';

}

?>

<!doctype html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        YAKAP Login
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- YAKAP CSS -->

    <link
        href="/YAKAP/assets/css/style.css"
        rel="stylesheet"
    >


    <style>

        /*
        |--------------------------------------------------------------------------
        | YAKAP LOGIN LOGO
        |--------------------------------------------------------------------------
        */

        .login-logo {

            display: block;

            width: 150px;

            max-width: 100%;

            height: auto;

            margin: 0 auto 15px auto;

        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN CARD
        |--------------------------------------------------------------------------
        */

        .login-card {

            width: min(420px, 92%);

            background: #ffffff;

            padding: 30px;

            border-radius: 18px;

            box-shadow: 0 8px 35px #00000012;

        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN LOGO AREA
        |--------------------------------------------------------------------------
        */

        .login-logo-container {

            text-align: center;

            margin-bottom: 15px;

        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN TITLE
        |--------------------------------------------------------------------------
        */

        .login-title {

            font-weight: 600;

            color: #123b35;

        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN BUTTON
        |--------------------------------------------------------------------------
        */

        .login-button {

            background: #1f7a5a;

            border-color: #1f7a5a;

        }

        .login-button:hover {

            background: #185f47;

            border-color: #185f47;

        }

    </style>

</head>


<body class="login-page">


<div class="login-card">


    <!-- =====================================================
         YAKAP LOGO
    ====================================================== -->

    <div class="login-logo-container">

        <img
            src="/YAKAP/assets/images/yakap-logo.png"
            class="login-logo"
            alt="PhilHealth YAKAP - Para Malayo sa Sakit"
        >

    </div>


    <!-- =====================================================
         TITLE
    ====================================================== -->

       <p class="text-center text-muted mb-4">

        PhilHealth Medicine / Services Monitoring

    </p>


    <!-- =====================================================
         ERROR MESSAGE
    ====================================================== -->

    <?php if ($error): ?>

        <div class="alert alert-danger">

            <?= htmlspecialchars($error) ?>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         LOGIN FORM
    ====================================================== -->

    <form
        method="post"
        autocomplete="off"
    >


        <!-- USERNAME -->

        <label class="form-label">

            Username

        </label>

        <input
            type="text"
            class="form-control mb-3"
            name="username"
            required
            autofocus
        >


        <!-- PASSWORD -->

        <label class="form-label">

            Password

        </label>

        <input
            type="password"
            class="form-control mb-4"
            name="password"
            required
        >


        <!-- SIGN IN -->

        <button
            type="submit"
            class="btn login-button text-white w-100"
        >

            Sign In

        </button>


    </form>


    <!-- VERSION -->

    <p class="text-muted text-center mt-3 mb-0">

        YAKAP Version 1.1

    </p>


</div>


</body>

</html>