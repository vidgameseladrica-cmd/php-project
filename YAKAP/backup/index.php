<?php
$pageTitle = 'Backup Data Records';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="card-panel">
<h5>Database Backup</h5>
<p class="text-muted">This Version 1 screen is prepared for the database backup module.</p>
<a href="create.php" class="btn btn-primary">Create Backup</a>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
