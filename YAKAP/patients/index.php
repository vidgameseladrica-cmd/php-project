<?php

$pageTitle = 'Patient Records';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';


/*
|--------------------------------------------------------------------------
| SEARCH PATIENT
|--------------------------------------------------------------------------
*/

$search = trim($_GET['q'] ?? '');


/*
|--------------------------------------------------------------------------
| PATIENT QUERY
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        *,
        TIMESTAMPDIFF(
            YEAR,
            birthdate,
            CURDATE()
        ) AS age

    FROM patients
";

$params = [];


/*
|--------------------------------------------------------------------------
| SEARCH FILTER
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $sql .= "
        WHERE
            patient_code LIKE ?

            OR philhealth_id LIKE ?

            OR CONCAT(
                last_name,
                ', ',
                first_name,
                ' ',
                COALESCE(middle_name, '')
            ) LIKE ?
    ";

    $keyword = '%' . $search . '%';

    $params = [
        $keyword,
        $keyword,
        $keyword
    ];
}


/*
|--------------------------------------------------------------------------
| ORDER
|--------------------------------------------------------------------------
*/

$sql .= "
    ORDER BY id DESC
    LIMIT 200
";


/*
|--------------------------------------------------------------------------
| EXECUTE QUERY
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare($sql);

$stmt->execute($params);

$patients = $stmt->fetchAll();

?>

<!-- =========================================================
     PATIENT RECORDS
========================================================= -->

<div class="card-panel">


    <!-- =====================================================
         HEADER
    ======================================================

    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>

            <h5 class="mb-1">
                Medical Patient Records
            </h5>

            <small class="text-muted">
                PhilHealth YAKAP Patient Monitoring
            </small>

        </div> -->


        <a
            class="btn btn-primary"
            href="add.php">

            + Add Patient

        </a>

    </div>


    <!-- =====================================================
         SEARCH FORM
    ====================================================== -->

    <form
        method="get"
        class="row g-2 mb-4"
    >


        <!-- SEARCH -->

        <div class="col-md-8">

            <input
                type="text"
                name="q"
                value="<?= htmlspecialchars($search) ?>"
                class="form-control"
                placeholder="Search Patient ID, PhilHealth ID or Patient Name"
            >

        </div>


        <!-- SEARCH BUTTON -->

        <div class="col-auto">

            <button
                type="submit"
                class="btn btn-outline-primary">

                🔍 Search

            </button>

        </div>


        <!-- CLEAR BUTTON -->

        <?php if ($search !== ''): ?>

            <div class="col-auto">

                <a
                    href="index.php"
                    class="btn btn-outline-secondary">

                    Clear

                </a>

            </div>

        <?php endif; ?>


    </form>


    <!-- =====================================================
         SEARCH RESULT
    ====================================================== -->

    <?php if ($search !== ''): ?>

        <div class="mb-3">

            <small class="text-muted">

                Search result for:

                <strong>
                    <?= htmlspecialchars($search) ?>
                </strong>

                — <?= count($patients) ?> record(s)

            </small>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         PATIENT TABLE
    ====================================================== -->

    <div class="table-responsive">

        <table class="table table-hover align-middle">


            <!-- TABLE HEADER -->

            <thead>

                <tr>

                    <th>
                        Patient ID
                    </th>

                    <th>
                        PhilHealth ID
                    </th>

                    <th>
                        Patient Name
                    </th>

                    <th>
                        Age
                    </th>

                    <th>
                        Sex
                    </th>

                    <th>
                        Status
                    </th>

                    <th class="text-center">
                        Action
                    </th>

                </tr>

            </thead>


            <!-- TABLE BODY -->

            <tbody>


            <?php if (empty($patients)): ?>


                <tr>

                    <td
                        colspan="7"
                        class="text-center text-muted py-4">

                        No patient records found.

                    </td>

                </tr>


            <?php else: ?>


                <?php foreach ($patients as $patient): ?>


                    <?php

                    /*
                    |--------------------------------------------------------------------------
                    | PATIENT NAME
                    |--------------------------------------------------------------------------
                    */

                    $patientName =
                        $patient['last_name'] . ', ' .
                        $patient['first_name'] . ' ' .
                        ($patient['middle_name'] ?? '');


                    /*
                    |--------------------------------------------------------------------------
                    | STATUS
                    |--------------------------------------------------------------------------
                    */

                    if ($patient['status'] === 'Active') {

                        $statusClass = 'success';

                    } else {

                        $statusClass = 'secondary';

                    }

                    ?>


                    <tr>


                        <!-- PATIENT ID -->

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $patient['patient_code']
                                ) ?>

                            </strong>

                        </td>


                        <!-- PHILHEALTH ID -->

                        <td>

                            <?= htmlspecialchars(
                                $patient['philhealth_id']
                            ) ?>

                        </td>


                        <!-- NAME -->

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $patientName
                                ) ?>

                            </strong>

                        </td>


                        <!-- AGE -->

                        <td>

                            <?= htmlspecialchars(
                                (string)$patient['age']
                            ) ?>

                            yrs.

                        </td>


                        <!-- SEX -->

                        <td>

                            <?= htmlspecialchars(
                                $patient['sex']
                            ) ?>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <span
                                class="badge text-bg-<?= $statusClass ?>">

                                <?= htmlspecialchars(
                                    $patient['status']
                                ) ?>

                            </span>

                        </td>


                        <!-- ACTION -->

                        <td class="text-center">

                            <div class="d-flex justify-content-center gap-1">


                                <!-- VIEW -->

                                <a
                                    class="btn btn-sm btn-outline-primary"
                                    href="view.php?id=<?= $patient['id'] ?>">

                                    View

                                </a>


                                <!-- EDIT 

                                <a
                                    class="btn btn-sm btn-outline-secondary"
                                    href="edit.php?id=<?= $patient['id'] ?>">

                                    Edit

                                </a> -->


                                <!-- MEDICINE HISTORY -->

                                <a
                                    class="btn btn-sm btn-outline-success"
                                    href="Patientmedicine.php?id=<?= $patient['id'] ?>">

                                    💊 Medicine

                                </a>


                            </div>

                        </td>


                    </tr>


                <?php endforeach; ?>


            <?php endif; ?>


            </tbody>


        </table>

    </div>


</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>