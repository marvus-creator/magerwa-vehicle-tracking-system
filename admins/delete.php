<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admins/index.php');
}

$id = (int) ($_POST['id'] ?? 0);

if ($id === (int) $_SESSION['admin_id']) {
    set_flash('warning', 'You cannot delete your own account while logged in.');
    redirect('admins/index.php');
}

$total = (int) db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
if ($total <= 1) {
    set_flash('warning', 'At least one staff account must remain.');
    redirect('admins/index.php');
}

$stmt = db()->prepare('DELETE FROM admins WHERE id = ?');
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    set_flash('success', 'Staff member deleted successfully.');
} else {
    set_flash('danger', 'Staff member not found.');
}
redirect('admins/index.php');
