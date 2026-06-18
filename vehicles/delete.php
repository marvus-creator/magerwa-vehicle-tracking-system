<?php
require_once __DIR__ . '/../includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('vehicles/index.php');
}

$id = (int) ($_POST['id'] ?? 0);

$stmt = db()->prepare('DELETE FROM vehicles WHERE id = ?');
$stmt->execute([$id]);

if ($stmt->rowCount() > 0) {
    set_flash('success', 'Vehicle deleted successfully.');
} else {
    set_flash('danger', 'Vehicle not found.');
}
redirect('vehicles/index.php');
