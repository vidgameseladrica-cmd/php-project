<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';


/*
|--------------------------------------------------------------------------
| ADMIN ACCESS ONLY
|--------------------------------------------------------------------------
*/

if (($_SESSION['role'] ?? '') !== 'Admin') {

    http_response_code(403);

    exit('Access denied.');

}


/*
|--------------------------------------------------------------------------
| POST ONLY
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

$id = (int)($_POST['id'] ?? 0);

$fullName = trim(
    $_POST['full_name'] ?? ''
);

$role = trim(
    $_POST['role'] ?? ''
);

$username = trim(
    $_POST['username'] ?? ''
);

$status = trim(
    $_POST['status'] ?? ''
);

$password =
    $_POST['password'] ?? '';

$confirmPassword =
    $_POST['confirm_password'] ?? '';


/*
|--------------------------------------------------------------------------
| VALIDATE USER ID
|--------------------------------------------------------------------------
*/

if ($id <= 0) {

    exit('Invalid user ID.');

}


/*
|--------------------------------------------------------------------------
| VALIDATE REQUIRED FIELDS
|--------------------------------------------------------------------------
*/

if (
    $fullName === '' ||
    $username === ''
) {

    header(
        'Location: edit.php?id=' . $id .
        '&error=' .
        urlencode('Please fill in all required fields.')
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| VALIDATE ROLE
|--------------------------------------------------------------------------
*/

$allowedRoles = [

    'Admin',

    'Pharmacist',

    'Encoder'

];


if (!in_array($role, $allowedRoles, true)) {

    header(
        'Location: edit.php?id=' . $id .
        '&error=' .
        urlencode('Invalid position.')
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| VALIDATE STATUS
|--------------------------------------------------------------------------
*/

$allowedStatus = [

    'Active',

    'Inactive'

];


if (!in_array($status, $allowedStatus, true)) {

    header(
        'Location: edit.php?id=' . $id .
        '&error=' .
        urlencode('Invalid status.')
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| CHECK DUPLICATE USERNAME
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT id

    FROM users

    WHERE username = ?
    AND id != ?

    LIMIT 1
");

$stmt->execute([

    $username,

    $id

]);


if ($stmt->fetch()) {

    header(
        'Location: edit.php?id=' . $id .
        '&error=' .
        urlencode('Username already exists.')
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| PASSWORD VALIDATION
|--------------------------------------------------------------------------
*/

if ($password !== '') {

    if (strlen($password) < 6) {

        header(
            'Location: edit.php?id=' . $id .
            '&error=' .
            urlencode(
                'Password must be at least 6 characters.'
            )
        );

        exit;

    }


    if ($password !== $confirmPassword) {

        header(
            'Location: edit.php?id=' . $id .
            '&error=' .
            urlencode(
                'Password and Confirm Password do not match.'
            )
        );

        exit;

    }

}


/*
|--------------------------------------------------------------------------
| UPDATE USER
|--------------------------------------------------------------------------
*/

try {


    /*
    |----------------------------------------------------------
    | UPDATE WITH PASSWORD
    |----------------------------------------------------------
    */

    if ($password !== '') {


        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );


        $stmt = $pdo->prepare("
            UPDATE users

            SET

                full_name = ?,

                username = ?,

                role = ?,

                status = ?,

                password_hash = ?

            WHERE id = ?
        ");


        $stmt->execute([

            $fullName,

            $username,

            $role,

            $status,

            $passwordHash,

            $id

        ]);


    } else {


        /*
        |------------------------------------------------------
        | UPDATE WITHOUT CHANGING PASSWORD
        |------------------------------------------------------
        */

        $stmt = $pdo->prepare("
            UPDATE users

            SET

                full_name = ?,

                username = ?,

                role = ?,

                status = ?

            WHERE id = ?
        ");


        $stmt->execute([

            $fullName,

            $username,

            $role,

            $status,

            $id

        ]);

    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE CURRENT SESSION
    |--------------------------------------------------------------------------
    */

    if (
        isset($_SESSION['user_id']) &&
        (int)$_SESSION['user_id'] === $id
    ) {

        $_SESSION['username'] =
            $username;

        $_SESSION['full_name'] =
            $fullName;

        $_SESSION['role'] =
            $role;

    }


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    header(
        'Location: index.php?success=' .
        urlencode(
            'User account updated successfully.'
        )
    );

    exit;


} catch (PDOException $e) {


    header(
        'Location: edit.php?id=' . $id .
        '&error=' .
        urlencode(
            'Unable to update user account.'
        )
    );

    exit;

}