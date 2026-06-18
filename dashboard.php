<?php
require_once __DIR__ . '/includes/functions.php';
require_login();

$staffCount = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$clientCount = (int) db()->query('SELECT COUNT(*) FROM clients')->fetchColumn();
$vehicleCount = (int) db()->query('SELECT COUNT(*) FROM vehicles')->fetchColumn();
$linkedCount = (int) db()->query('SELECT COUNT(*) FROM vehicles WHERE client_id IS NOT NULL')->fetchColumn();
$unlinkedCount = $vehicleCount - $linkedCount;

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Dashboard</h2>
        <p class="text-muted mb-0">Overview of clients and tracked vehicles</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg col-md-4 col-sm-6">
        <a href="<?= url('admins/index.php') ?>" class="text-decoration-none">
            <div class="card stat-card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="stat-icon bg-secondary-subtle text-secondary"><i class="bi bi-person-badge-fill"></i></div>
                    <h3 class="fw-bold mb-0 text-dark"><?= $staffCount ?></h3>
                    <p class="text-muted mb-0">Staff Members</p>
                </div>
            </div>
        </a>
    </div>
    <div class="col-lg col-md-4 col-sm-6">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-people-fill"></i></div>
                <h3 class="fw-bold mb-0"><?= $clientCount ?></h3>
                <p class="text-muted mb-0">Registered Clients</p>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-4 col-sm-6">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-car-front-fill"></i></div>
                <h3 class="fw-bold mb-0"><?= $vehicleCount ?></h3>
                <p class="text-muted mb-0">Registered Vehicles</p>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-4 col-sm-6">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-link-45deg"></i></div>
                <h3 class="fw-bold mb-0"><?= $linkedCount ?></h3>
                <p class="text-muted mb-0">Linked Vehicles</p>
            </div>
        </div>
    </div>
    <div class="col-lg col-md-4 col-sm-6">
        <div class="card stat-card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-exclamation-circle-fill"></i></div>
                <h3 class="fw-bold mb-0"><?= $unlinkedCount ?></h3>
                <p class="text-muted mb-0">Unlinked Vehicles</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-md-4">
        <a href="<?= url('clients/create.php') ?>" class="text-decoration-none">
            <div class="card action-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-person-plus-fill fs-2 text-primary me-3"></i>
                    <div><span class="fw-semibold d-block text-dark">Register Client</span><small class="text-muted">Add a new client record</small></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('vehicles/create.php') ?>" class="text-decoration-none">
            <div class="card action-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-plus-square-fill fs-2 text-success me-3"></i>
                    <div><span class="fw-semibold d-block text-dark">Register Vehicle</span><small class="text-muted">Add a new vehicle record</small></div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4">
        <a href="<?= url('vehicles/link.php') ?>" class="text-decoration-none">
            <div class="card action-card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-link-45deg fs-2 text-info me-3"></i>
                    <div><span class="fw-semibold d-block text-dark">Link Vehicle</span><small class="text-muted">Assign client and plate number</small></div>
                </div>
            </div>
        </a>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
