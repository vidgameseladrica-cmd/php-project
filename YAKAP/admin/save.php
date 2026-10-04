<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';


/*
|--------------------------------------------------------------------------
| ADMIN ACCESS ONLY
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'Admin'
) {

    http_response_code(403);

    exit('Access denied.');

}


/*
|--------------------------------------------------------------------------
| ONLY POST REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: index.php');

    exit;

}


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$fullName = trim(
    $_POST['full_name'] ?? ''
);

$role = trim(
    $_POST['role'] ?? ''
);

$username = trim(
    $_POST['username'] ?? ''
);

$password = $_POST['password'] ?? '';

$confirmPassword =
    $_POST['confirm_password'] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if ($fullName === '') {

    header(
        'Location: index.php?error=' .
        urlencode('Full Name is required.')
    );

    exit;

}


if (
    !in_array(
        $role,
        ['Admin', 'Pharmacist', 'Encoder'],
        true
    )
) {

    header(
        'Location: index.php?error=' .
        urlencode('Invalid position selected.')
    );

    exit;

}


if ($username === '') {

    header(
        'Location: index.php?error=' .
        urlencode('Username is required.')
    );

    exit;

}


if (!preg_match('/^[A-Za-z0-9._-]+$/', $username)) {

    header(
        'Location: index.php?error=' .
        urlencode(
            'Username may only contain letters, numbers, dot, underscore and dash.'
        )
    );

    exit;

}


if (strlen($password) < 6) {

    header(
        'Location: index.php?error=' .
        urlencode(
            'Password must be at least 6 characters.'
        )
    );

    exit;

}


if ($password !== $confirmPassword) {

    header(
        'Location: index.php?error=' .
        urlencode(
            'Password and Confirm Password do not match.'
        )
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| CHECK USERNAME
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id
    FROM users
    WHERE username = ?
    LIMIT 1
");

$stmt->execute([
    $username
]);


if ($stmt->fetch()) {

    header(
        'Location: index.php?error=' .
        urlencode(
            'Username already exists.'
        )
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| HASH PASSWORD
|--------------------------------------------------------------------------
*/

$passwordHash = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| SAVE USER
|--------------------------------------------------------------------------
*/

try {

    $stmt = $pdo->prepare("
        INSERT INTO users
        (
            username,
            password_hash,
            full_name,
            role,
            status
        )

        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            'Active'
        )
    ");


    $stmt->execute([

        $username,

        $passwordHash,

        $fullName,

        $role

    ]);


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    header(
        'Location: index.php?success=1'
    );

    exit;


} catch (PDOException $e) {


    /*
    |--------------------------------------------------------------------------
    | DUPLICATE USERNAME SAFETY
    |--------------------------------------------------------------------------
    */

    if ($e->getCode() === '23000') {

        header(
            'Location: index.php?error=' .
            urlencode(
                'Username already exists.'
            )
        );

        exit;

    }


    /*
    |--------------------------------------------------------------------------
    | OTHER DATABASE ERROR
    |--------------------------------------------------------------------------
    */

    header(
        'Location: index.php?error=' .
        urlencode(
            'Unable to save user account.'
        )
    );

    exit;

}