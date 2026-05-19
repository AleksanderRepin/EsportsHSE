<?php
require __DIR__ . "/db.php";
require __DIR__ . "/auth.php";
require_admin();

$id = (int)($_POST['id'] ?? 0);
$returnTo = $_POST['return_to'] ?? '';
$teamId = (int)($_POST['team_id'] ?? 0);
if ($id) {
    $stmt = $pdo->prepare("DELETE FROM teamplayer WHERE id = ?");
    $stmt->execute([$id]);
}

if ($returnTo === 'teams' && $teamId) {
    header("Location: teams.php?team_id=" . $teamId);
    exit;
}

header("Location: team_players.php");
exit;
