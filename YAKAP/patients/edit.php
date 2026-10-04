<?php

declare(strict_types=1);

$pageTitle = 'Edit Patient';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';


/*
|--------------------------------------------------------------------------
| GET PATIENT ID
|--------------------------------------------------------------------------
*/

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('Invalid patient ID.');
}


/*
|--------------------------------------------------------------------------
| GET PATIENT RECORD
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        *,
        TIMESTAMPDIFF(
            YEAR,
            birthdate,
            CURDATE()
        ) AS age

    FROM patients

    WHERE id = ?

    LIMIT 1
");

$stmt->execute([$id]);

$patient = $stmt->fetch();


if (!$patient) {
    exit('Patient not found.');
}

?>


<!-- =========================================================
     EDIT PATIENT
========================================================= -->

<div class="card-panel">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h5 class="mb-1">
                Update Patient Information
            </h5>

            <small class="text-muted">
                Edit medical patient record
            </small>
        </div>

        <a
            href="view.php?id=<?= $id ?>"
            class="btn btn-secondary">

            ← Back to Patient

        </a>

    </div>


    <!-- =====================================================
         PATIENT FORM
    ====================================================== -->

    <form
        method="post"
        action="update.php"
        autocomplete="off">


        <!-- DATABASE ID -->

        <input
            type="hidden"
            name="id"
            value="<?= $id ?>"
        >


        <div class="row g-3">


            <!-- =================================================
                 PATIENT ID
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    Patient ID *
                </label>

                <input
                    type="text"
                    name="patient_code"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $patient['patient_code']
                    ) ?>"
                    maxlength="50"
                    required>

            </div>


            <!-- =================================================
                 PHILHEALTH ID
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    PhilHealth ID *
                </label>

                <input
                    type="text"
                    name="philhealth_id"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $patient['philhealth_id']
                    ) ?>"
                    required>

            </div>


            <!-- =================================================
                 STATUS
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    Status
                </label>

                <select
                    name="status"
                    class="form-select">

                    <option
                        value="Active"
                        <?= $patient['status'] === 'Active'
                            ? 'selected'
                            : '' ?>>

                        Active

                    </option>

                    <option
                        value="Inactive"
                        <?= $patient['status'] === 'Inactive'
                            ? 'selected'
                            : '' ?>>

                        Inactive

                    </option>

                </select>

            </div>


            <!-- =================================================
                 FIRST NAME
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    First Name *
                </label>

                <input
                    type="text"
                    name="first_name"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $patient['first_name']
                    ) ?>"
                    required>

            </div>


            <!-- =================================================
                 MIDDLE NAME
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    Middle Name
                </label>

                <input
                    type="text"
                    name="middle_name"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $patient['middle_name'] ?? ''
                    ) ?>">

            </div>


            <!-- =================================================
                 LAST NAME
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    Last Name *
                </label>

                <input
                    type="text"
                    name="last_name"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $patient['last_name']
                    ) ?>"
                    required>

            </div>


            <!-- =================================================
                 BIRTHDATE
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    Birthdate *
                </label>

                <input
                    type="date"
                    name="birthdate"
                    id="birthdate"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $patient['birthdate']
                    ) ?>"
                    required>

            </div>


            <!-- =================================================
                 AGE - AUTO COMPUTED
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    Age
                </label>

                <input
                    type="text"
                    id="age"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        (string)$patient['age']
                    ) ?> years old"
                    readonly>

                <small class="text-muted">
                    Automatically computed from Birthdate
                </small>

            </div>


            <!-- =================================================
                 SEX
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    Sex *
                </label>

                <select
                    name="sex"
                    class="form-select"
                    required>


                    <option value="">
                        Select Sex
                    </option>


                    <?php

                    $sexOptions = [
                        'Male',
                        'Female',
                        'Other'
                    ];

                    ?>


                    <?php foreach ($sexOptions as $sex): ?>

                        <option
                            value="<?= $sex ?>"
                            <?= $patient['sex'] === $sex
                                ? 'selected'
                                : '' ?>>

                            <?= $sex ?>

                        </option>

                    <?php endforeach; ?>


                </select>

            </div>


            <!-- =================================================
                 ADDRESS
            ================================================== -->

            <div class="col-md-8">

                <label class="form-label">
                    Address
                </label>

                <input
                    type="text"
                    name="address"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $patient['address'] ?? ''
                    ) ?>">

            </div>


            <!-- =================================================
                 CONTACT NUMBER
            ================================================== -->

            <div class="col-md-4">

                <label class="form-label">
                    Contact Number
                </label>

                <input
                    type="text"
                    name="contact_number"
                    class="form-control"
                    value="<?= htmlspecialchars(
                        $patient['contact_number'] ?? ''
                    ) ?>">

            </div>


        </div>


        <!-- =====================================================
             BUTTONS
        ====================================================== -->

        <div class="mt-4 d-flex gap-2">


            <button
                type="submit"
                class="btn btn-primary">

                Update Patient

            </button>


        


        </div>


    </form>

</div>


<!-- =========================================================
     AUTO AGE COMPUTATION
========================================================= -->

<script>

function calculateAge() {

    const birthdateInput =
        document.getElementById('birthdate');

    const ageInput =
        document.getElementById('age');


    if (!birthdateInput.value) {

        ageInput.value = '';

        return;

    }


    const birthdate =
        new Date(birthdateInput.value);


    const today =
        new Date();


    let age =
        today.getFullYear() -
        birthdate.getFullYear();


    const monthDifference =
        today.getMonth() -
        birthdate.getMonth();


    /*
    |--------------------------------------------------------------------------
    | Adjust age if birthday has not occurred yet this year
    |--------------------------------------------------------------------------
    */

    if (
        monthDifference < 0 ||
        (
            monthDifference === 0 &&
            today.getDate() < birthdate.getDate()
        )
    ) {

        age--;

    }


    /*
    |--------------------------------------------------------------------------
    | Invalid / Future Birthdate
    |--------------------------------------------------------------------------
    */

    if (
        isNaN(birthdate.getTime()) ||
        age < 0
    ) {

        ageInput.value = '';

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY AGE
    |--------------------------------------------------------------------------
    */

    ageInput.value =
        age + ' years old';

}


/*
|--------------------------------------------------------------------------
| RUN WHEN BIRTHDATE CHANGES
|--------------------------------------------------------------------------
*/

document
    .getElementById('birthdate')
    .addEventListener(
        'change',
        calculateAge
    );


/*
|--------------------------------------------------------------------------
| RUN ON PAGE LOAD
|--------------------------------------------------------------------------
*/

calculateAge();

</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>