<?php
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";
require_admin();

$id = (int)($_POST['id'] ?? 0);
if ($id) {
    try {
        $stmt = $pdo->prepare("DELETE FROM team WHERE id = ?");
        $stmt->execute([$id]);
    } catch (Throwable $e) {
        header("Location: teams.php?delete_error=1");
        exit;
    }
}

header("Location: teams.php");
exit;
