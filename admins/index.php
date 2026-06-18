<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

$total = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
$meta = paginate($total, PER_PAGE, current_page());

$stmt = db()->prepare('SELECT id, names, email, phone, national_id, created_at FROM admins ORDER BY id DESC LIMIT :limit OFFSET :offset');
$stmt->bindValue(':limit', PER_PAGE, PDO::PARAM_INT);
$stmt->bindValue(':offset', $meta['offset'], PDO::PARAM_INT);
$stmt->execute();
$admins = $stmt->fetchAll();

$pageTitle = 'Staff';
require_once __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold mb-0">Staff</h2>
        <p class="text-muted mb-0"><?= $total ?> registered admin(s)</p>
    </div>
    <a href="<?= url('admins/create.php') ?>" class="btn btn-primary"><i class="bi bi-person-plus-fill me-1"></i>Add Staff</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Names</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>National ID</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$admins): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No staff registered yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($admins as $i => $a): ?>
                        <tr>
                            <td><?= $meta['offset'] + $i + 1 ?></td>
                            <td class="fw-semibold">
                                <?= e($a['names']) ?>
                                <?php if ((int) $a['id'] === (int) $_SESSION['admin_id']): ?>
                                    <span class="badge text-bg-info ms-1">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($a['email']) ?></td>
                            <td><?= e($a['phone']) ?></td>
                            <td><?= e($a['national_id']) ?></td>
                            <td class="text-end">
                                <a href="<?= url('admins/edit.php?id=' . $a['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil-square"></i></a>
                                <?php if ((int) $a['id'] !== (int) $_SESSION['admin_id']): ?>
                                <form action="<?= url('admins/delete.php') ?>" method="post" class="d-inline" onsubmit="return confirm('Delete staff member <?= e($a['names']) ?>? This cannot be undone.');">
                                    <input type="hidden" name="id" value="<?= e($a['id']) ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php require __DIR__ . '/../includes/pagination.php'; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
