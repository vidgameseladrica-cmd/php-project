<?php
$pageTitle = 'Reports';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="row g-3">
<div class="col-md-4"><div class="card-panel"><h5>Patient Report</h5><p class="text-muted">View registered patient records.</p><a class="btn btn-outline-primary" href="/YAKAP/patients/index.php">Open</a></div></div>
<div class="col-md-4"><div class="card-panel"><h5>Inventory Report</h5><p class="text-muted">View medicine stock and alerts.</p><a class="btn btn-outline-primary" href="/YAKAP/medicines/index.php">Open</a></div></div>
<div class="col-md-4"><div class="card-panel"><h5>Purchase Report</h5><p class="text-muted">View medicine purchase history.</p><a class="btn btn-outline-primary" href="/YAKAP/purchases/index.php">Open</a></div></div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
