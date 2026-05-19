<?php
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";
require_admin();

$id = (int)($_POST['id'] ?? 0);
if ($id && $id !== (int)($_SESSION['user']['id'] ?? 0)) {
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    } catch (Throwable $e) {
        header("Location: players.php?delete_error=1");
        exit;
    }
}

header("Location: players.php");
exit;
