<?php
require __DIR__ . "/db.php";
include __DIR__ . "/header.php";

$stats = [
    'Турниры' => $pdo->query("SELECT COUNT(*) FROM tournament")->fetchColumn(),
    'Команды' => $pdo->query("SELECT COUNT(*) FROM team")->fetchColumn(),
    'Игроки' => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'player'")->fetchColumn(),
    'Матчи' => $pdo->query("SELECT COUNT(*) FROM match")->fetchColumn(),
];

$tournaments = $pdo->query("
    SELECT t.id, t.name, t.status, g.name AS game_name, COUNT(tt.id) AS teams_count
    FROM tournament t
    JOIN gametype g ON g.id = t.game_type_id
    LEFT JOIN tournamentteam tt ON tt.tournament_id = t.id
    GROUP BY t.id, g.name
    ORDER BY t.start_date DESC
")->fetchAll();
?>

<div class="page-card mb-4">
    <h2 class="page-title">Панель управления</h2>
    <div class="row g-3">
        <?php foreach ($stats as $label => $value): ?>
            <div class="col-md-3">
                <div class="border rounded p-3 text-center">
                    <div class="fs-3 fw-bold"><?= $value ?></div>
                    <div class="text-muted"><?= htmlspecialchars($label) ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="page-card">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="page-title mb-0">Турниры</h3>
        <?php if ($isAdmin): ?>
            <a href="create_tournament.php" class="btn btn-primary">Создать турнир</a>
        <?php endif; ?>
    </div>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
        <tr>
            <th>Турнир</th>
            <th>Игра</th>
            <th>Статус</th>
            <th>Команд</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tournaments as $t): ?>
            <?php $ready = $t['teams_count'] >= 2 && (((int)$t['teams_count'] & ((int)$t['teams_count'] - 1)) === 0); ?>
            <tr>
                <td><?= htmlspecialchars($t['name']) ?></td>
                <td><?= htmlspecialchars($t['game_name']) ?></td>
                <td><span class="badge <?= status_badge_class($t['status']) ?> status-badge"><?= htmlspecialchars(status_label($t['status'])) ?></span></td>
                <td><?= $t['teams_count'] ?></td>
                <td><a class="btn btn-sm btn-outline-primary" href="tournament.php?id=<?= $t['id'] ?>">Открыть</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . "/footer.php"; ?>
