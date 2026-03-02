<?php
require_once '../includes/session.php';
requireAdmin(); // or remove if not using admin auth
require_once '../includes/db.php';

$id     = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;

if (!$id || !in_array($status, ['approved', 'rejected'])) {
    header('Location: leaves.php');
    exit;
}

$stmt = $db->prepare(""
    UPDATE leaves
    SET status = ?
    WHERE id = ? AND status = 'pending'
");
$stmt->execute([$status, $id]);

header('Location: leaves.php');
exit;
