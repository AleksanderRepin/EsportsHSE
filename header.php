<?php
require_once __DIR__ . "/auth.php";
require_once __DIR__ . "/ui.php";
require_login();
$isAdmin = is_admin();
$currentPage = basename($_SERVER['SCRIPT_NAME'] ?? '');
$navItems = [
    'dashboard.php' => 'Главная',
    'tournaments.php' => 'Турниры',
    'teams.php' => 'Команды',
    'players.php' => 'Пользователи',
];
if ($isAdmin) {
    $navItems = array_slice($navItems, 0, 1, true)
        + ['game_types.php' => 'Игры и карты']
        + array_slice($navItems, 1, null, true);
}
$activeMap = [
    'create_tournament.php' => 'tournaments.php',
    'tournament.php' => 'tournaments.php',
    'match.php' => 'tournaments.php',
    'matches.php' => 'tournaments.php',
];
$activePage = $activeMap[$currentPage] ?? $currentPage;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель турниров</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f6f7fb; }
        .navbar-brand { font-weight: 700; }
        .page-card {
            background: #fff;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 3px 14px rgba(0,0,0,.05);
        }
        .page-title { font-weight: 700; margin-bottom: 20px; }
        .table td, .table th { vertical-align: middle; }
        .status-badge { font-weight: 600; }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="dashboard.php">Панель турниров</a>
        <div class="navbar-nav me-auto">
            <?php foreach ($navItems as $href => $label): ?>
                <a class="nav-link <?= $activePage === $href ? 'active' : '' ?>" href="<?= $href ?>">
                    <?= htmlspecialchars($label) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <span class="navbar-text text-white-50 me-3">
            <?= htmlspecialchars($_SESSION['user']['nickname'] ?? '') ?>
            <?php if (!$isAdmin): ?>
                <span class="badge text-bg-secondary ms-2">Игрок</span>
            <?php endif; ?>
        </span>
        <a class="btn btn-outline-light btn-sm" href="logout.php">Выйти</a>
    </div>
</nav>
<div class="container my-4">
