<?php

declare(strict_types=1);

session_start();

require_once __DIR__ . '/../config/database.php';


// =========================================================
// CHECK LOGIN
// =========================================================

if (empty($_SESSION['user_id'])) {

    header('Location: /YAKAP/auth/login.php');

    exit;

}


// =========================================================
// ONLY POST REQUEST
// =========================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    header('Location: /YAKAP/patients/index.php');

    exit;

}


// =========================================================
// GET FORM DATA
// =========================================================

$patientId = (int)($_POST['patient_id'] ?? 0);

$recordDate = trim(
    $_POST['record_date'] ?? ''
);

$physician = trim(
    $_POST['physician'] ?? ''
);

$diagnosis = trim(
    $_POST['diagnosis'] ?? ''
);

$notes = trim(
    $_POST['notes'] ?? ''
);

$createdBy = (int)$_SESSION['user_id'];


// =========================================================
// VALIDATION
// =========================================================

if (
    $patientId <= 0 ||
    $recordDate === '' ||
    $diagnosis === ''
) {

    exit(
        'Please complete all required fields.'
    );

}


// =========================================================
// CHECK PATIENT
// =========================================================

$stmt = $pdo->prepare("
    SELECT id
    FROM patients
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $patientId
]);

$patient = $stmt->fetch();


if (!$patient) {

    exit(
        'Patient not found.'
    );

}


// =========================================================
// SAVE MEDICAL RECORD
// =========================================================

$stmt = $pdo->prepare("
    INSERT INTO medical_records
    (
        patient_id,
        record_date,
        diagnosis,
        notes,
        physician,
        created_by
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
    )
");


$stmt->execute([

    $patientId,

    $recordDate,

    $diagnosis,

    $notes,

    $physician,

    $createdBy

]);


// =========================================================
// RETURN TO PATIENT PROFILE
// =========================================================

header(
    'Location: /YAKAP/patients/view.php?id='
    . $patientId
);

exit;