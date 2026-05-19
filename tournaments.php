<?php
require __DIR__ . "/db.php";
include __DIR__ . "/header.php";

$tournaments = $pdo->query("
    SELECT t.*, g.name AS gametype_name, COUNT(tt.id) AS teams_count
    FROM tournament t
    LEFT JOIN gametype g ON t.game_type_id = g.id
    LEFT JOIN tournamentteam tt ON tt.tournament_id = t.id
    GROUP BY t.id, g.name
    ORDER BY t.start_date DESC, t.id DESC
")->fetchAll();
?>

<div class="page-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="page-title mb-0">Турниры</h2>
        <?php if ($isAdmin): ?>
            <a href="create_tournament.php" class="btn btn-primary">Создать турнир</a>
        <?php endif; ?>
    </div>

    <table class="table table-hover table-bordered">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Дисциплина</th>
            <th>Даты</th>
            <th>Статус</th>
            <th>Команд</th>
            <th>Призовой фонд</th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tournaments as $t): ?>
            <?php $ready = $t['teams_count'] >= 2 && (((int)$t['teams_count'] & ((int)$t['teams_count'] - 1)) === 0); ?>
            <tr>
                <td><?= $t['id'] ?></td>
                <td><?= htmlspecialchars($t['name']) ?></td>
                <td><?= htmlspecialchars($t['gametype_name']) ?></td>
                <td><?= $t['start_date'] ?> - <?= $t['end_date'] ?></td>
                <td><span class="badge <?= status_badge_class($t['status']) ?> status-badge"><?= htmlspecialchars(status_label($t['status'])) ?></span></td>
                <td><?= $t['teams_count'] ?></td>
                <td><?= number_format((float)$t['prize_pool'], 2, '.', ' ') ?></td>
                <td><a href="tournament.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-primary">Открыть</a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . "/footer.php"; ?>
