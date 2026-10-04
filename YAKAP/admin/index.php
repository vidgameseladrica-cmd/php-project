<?php

declare(strict_types=1);

session_start();

$pageTitle = 'User Accounts';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';


/*
|--------------------------------------------------------------------------
| CHECK ADMIN ACCESS
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION['user_id']) ||
    ($_SESSION['role'] ?? '') !== 'Admin'
) {

    http_response_code(403);

    exit('Access denied. Administrator only.');

}


/*
|--------------------------------------------------------------------------
| GET USER ACCOUNTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
    SELECT
        id,
        full_name,
        username,
        role,
        status,
        created_at

    FROM users

    ORDER BY id DESC
");

$users = $stmt->fetchAll();


/*
|--------------------------------------------------------------------------
| MESSAGE
|--------------------------------------------------------------------------
*/

$success = $_GET['success'] ?? '';

$error = $_GET['error'] ?? '';

?>

<div class="card-panel">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h5 class="mb-1">
                User Accounts
            </h5>

            <small class="text-muted">
                Manage YAKAP system users
            </small>

        </div>


        <button
            type="button"
            class="btn btn-primary"
            data-bs-toggle="modal"
            data-bs-target="#userModal">

            + Add User Account

        </button>

    </div>


    <!-- =====================================================
         SUCCESS MESSAGE
    ====================================================== -->

   <?php if ($success !== ''): ?>

    <div class="alert alert-success alert-dismissible fade show">

        <?= htmlspecialchars(
            $success === '1'
                ? 'User account successfully created.'
                : $success
        ) ?>

        <button
            type="button"
            class="btn-close"
            data-bs-dismiss="alert">
        </button>

    </div>

<?php endif; ?>


    <!-- =====================================================
         ERROR MESSAGE
    ====================================================== -->

    <?php if ($error !== ''): ?>

        <div
            class="alert alert-danger alert-dismissible fade show">

            <?= htmlspecialchars($error) ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        </div>

    <?php endif; ?>


    <!-- =====================================================
         USER TABLE
    ====================================================== -->

    <div class="table-responsive">

        <table class="table table-hover align-middle">

            <thead>
    <tr>
        <th>#</th>
        <th>Full Name</th>
        <th>Position</th>
        <th>Username</th>
        <th>Status</th>
        <th>Date Created</th>
        <th>Action</th>
    </tr>
</thead>

            <tbody>

            <?php if (empty($users)): ?>

                <tr>

                    <td
                        colspan="6"
                        class="text-center text-muted py-4">

                        No user accounts found.

                    </td>

                </tr>

            <?php else: ?>


                <?php $counter = 1; ?>


                <?php foreach ($users as $user): ?>

    <tr>

        <td>
            <?= $counter++ ?>
        </td>


        <td>

            <strong>
                <?= htmlspecialchars($user['full_name']) ?>
            </strong>

        </td>


        <td>

            <?php

            $roleClass = match ($user['role']) {

                'Admin' => 'danger',
                'Pharmacist' => 'primary',
                'Encoder' => 'success',
                default => 'secondary'

            };

            ?>

            <span class="badge text-bg-<?= $roleClass ?>">

                <?= htmlspecialchars($user['role']) ?>

            </span>

        </td>


        <td>

            <?= htmlspecialchars($user['username']) ?>

        </td>


        <td>

            <?php

            $statusClass =
                $user['status'] === 'Active'
                    ? 'success'
                    : 'secondary';

            ?>

            <span class="badge text-bg-<?= $statusClass ?>">

                <?= htmlspecialchars($user['status']) ?>

            </span>

        </td>


        <td>

            <?= date(
                'M d, Y h:i A',
                strtotime($user['created_at'])
            ) ?>

        </td>


        <!-- EDIT BUTTON -->

        <td>

            <a
                href="edit.php?id=<?= (int)$user['id'] ?>"
                class="btn btn-sm btn-outline-primary">

                Edit

            </a>

        </td>

    </tr>

<?php endforeach; ?>


            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- =========================================================
     ADD USER MODAL
========================================================= -->

<div
    class="modal fade"
    id="userModal"
    tabindex="-1"
    aria-hidden="true">


    <div class="modal-dialog modal-lg">

        <div class="modal-content">


            <form
                method="post"
                action="save.php"
                autocomplete="off">


                <!-- MODAL HEADER -->

                <div class="modal-header">

                    <h5 class="modal-title">

                        Add User Account

                    </h5>


                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>


                <!-- MODAL BODY -->

                <div class="modal-body">

                    <div class="row g-3">


                        <!-- FULL NAME -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Full Name *
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                placeholder="e.g. Juan Dela Cruz"
                                maxlength="150"
                                required>

                        </div>


                        <!-- POSITION -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Position *
                            </label>

                            <select
                                name="role"
                                class="form-select"
                                required>

                                <option value="">
                                    Select Position
                                </option>

                                <option value="Admin">
                                    Administrator
                                </option>

                                <option value="Pharmacist">
                                    Pharmacist
                                </option>

                                <option value="Encoder">
                                    Encoder
                                </option>

                            </select>

                        </div>


                        <!-- USERNAME -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Username *
                            </label>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                placeholder="Enter username"
                                maxlength="50"
                                autocomplete="off"
                                required>

                        </div>


                        <!-- PASSWORD -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Password *
                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                placeholder="Enter password"
                                minlength="6"
                                autocomplete="new-password"
                                required>

                        </div>


                        <!-- CONFIRM PASSWORD -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Confirm Password *
                            </label>

                            <input
                                type="password"
                                name="confirm_password"
                                class="form-control"
                                placeholder="Confirm password"
                                minlength="6"
                                autocomplete="new-password"
                                required>

                        </div>


                    </div>

                </div>


                <!-- MODAL FOOTER -->

                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>


                    <button
                        type="submit"
                        class="btn btn-primary">

                        Save User

                    </button>

                </div>


            </form>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>