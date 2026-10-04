<?php

declare(strict_types=1);

$pageTitle = 'Edit User Account';

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';


/*
|--------------------------------------------------------------------------
| ADMIN ACCESS ONLY
|--------------------------------------------------------------------------
*/

if (($_SESSION['role'] ?? '') !== 'Admin') {

    http_response_code(403);

    exit('Access denied. Administrator only.');

}


/*
|--------------------------------------------------------------------------
| GET USER ID
|--------------------------------------------------------------------------
*/

$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {

    exit('Invalid user ID.');

}


/*
|--------------------------------------------------------------------------
| GET USER RECORD
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        full_name,
        username,
        role,
        status,
        created_at

    FROM users

    WHERE id = ?

    LIMIT 1
");

$stmt->execute([$id]);

$user = $stmt->fetch();


if (!$user) {

    exit('User account not found.');

}

?>


<div class="card-panel">


    <!-- PAGE HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h5 class="mb-1">
                Edit User Account
            </h5>

            <small class="text-muted">
                Update user account information
            </small>

        </div>


        <a
            href="index.php"
            class="btn btn-secondary">

            ← Back

        </a>

    </div>


    <!-- EDIT FORM -->

    <form
        method="post"
        action="update.php"
        autocomplete="off">


        <!-- USER ID -->

        <input
            type="hidden"
            name="id"
            value="<?= (int)$user['id'] ?>"
        >


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
                    value="<?= htmlspecialchars(
                        $user['full_name']
                    ) ?>"
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


                    <option
                        value="Admin"
                        <?= $user['role'] === 'Admin'
                            ? 'selected'
                            : '' ?>>

                        Administrator

                    </option>


                    <option
                        value="Pharmacist"
                        <?= $user['role'] === 'Pharmacist'
                            ? 'selected'
                            : '' ?>>

                        Pharmacist

                    </option>


                    <option
                        value="Encoder"
                        <?= $user['role'] === 'Encoder'
                            ? 'selected'
                            : '' ?>>

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
                    value="<?= htmlspecialchars(
                        $user['username']
                    ) ?>"
                    maxlength="50"
                    required>

            </div>


            <!-- STATUS -->

            <div class="col-md-6">

                <label class="form-label">

                    Status

                </label>


                <select
                    name="status"
                    class="form-select">


                    <option
                        value="Active"
                        <?= $user['status'] === 'Active'
                            ? 'selected'
                            : '' ?>>

                        Active

                    </option>


                    <option
                        value="Inactive"
                        <?= $user['status'] === 'Inactive'
                            ? 'selected'
                            : '' ?>>

                        Inactive

                    </option>


                </select>

            </div>


            <!-- NEW PASSWORD -->

            <div class="col-md-6">

                <label class="form-label">

                    New Password

                </label>


                <input
                    type="password"
                    name="password"
                    class="form-control"
                    minlength="6"
                    autocomplete="new-password">


                <small class="text-muted">

                    Leave blank if you do not want to change the password.

                </small>

            </div>


            <!-- CONFIRM PASSWORD -->

            <div class="col-md-6">

                <label class="form-label">

                    Confirm New Password

                </label>


                <input
                    type="password"
                    name="confirm_password"
                    class="form-control"
                    minlength="6"
                    autocomplete="new-password">


                <small class="text-muted">

                    Required only when changing password.

                </small>

            </div>


        </div>


        <!-- BUTTONS -->

        <div class="mt-4 d-flex gap-2">


            <button
                type="submit"
                class="btn btn-primary">

                Update User

            </button>


            <a
                href="index.php"
                class="btn btn-secondary">

                Cancel

            </a>


        </div>


    </form>

</div>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>