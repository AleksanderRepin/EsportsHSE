<?php
require __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
require_admin();
include __DIR__ . "/header.php";

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['form'] === 'game_type') {
        $name = trim($_POST['name'] ?? '');
        $teamSize = (int)($_POST['team_size'] ?? 0);

        if ($name !== '' && $teamSize > 0) {
            $stmt = $pdo->prepare("INSERT INTO gametype (name, team_size) VALUES (?, ?) ON CONFLICT (name) DO NOTHING");
            $stmt->execute([$name, $teamSize]);
            $message = "Игровая дисциплина сохранена.";
        }
    }

    if ($_POST['form'] === 'map') {
        $gameTypeId = (int)($_POST['game_type_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($gameTypeId > 0 && $name !== '') {
            $stmt = $pdo->prepare("INSERT INTO gamemap (game_type_id, name) VALUES (?, ?) ON CONFLICT (game_type_id, name) DO NOTHING");
            $stmt->execute([$gameTypeId, $name]);
            $message = "Карта сохранена.";
            header("Location: game_types.php?type_id=$gameTypeId");
            exit;
        }
    }
}

$types = $pdo->query("SELECT * FROM gametype ORDER BY name")->fetchAll();
$maps = $pdo->query("
    SELECT gm.id, gm.game_type_id, gm.name, gt.name AS game_name
    FROM gamemap gm
    JOIN gametype gt ON gt.id = gm.game_type_id
    ORDER BY gt.name, gm.name
")->fetchAll();
$mapsByType = [];
foreach ($maps as $map) {
    $mapsByType[(int)$map['game_type_id']][] = $map;
}
$activeTypeId = (int)($_GET['type_id'] ?? ($types[0]['id'] ?? 0));
if ($types && !in_array($activeTypeId, array_map('intval', array_column($types, 'id')), true)) {
    $activeTypeId = (int)$types[0]['id'];
}
?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="page-card">
            <h2 class="page-title">Игровые дисциплины</h2>

            <?php if ($message): ?>
                <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if (isset($_GET['delete_error'])): ?>
                <div class="alert alert-warning">Дисциплину нельзя удалить: она используется в турнирах.</div>
            <?php endif; ?>

            <button class="btn btn-outline-primary mb-3" type="button" data-bs-toggle="collapse" data-bs-target="#addGameTypeForm">
                Добавить дисциплину
            </button>

            <div class="collapse" id="addGameTypeForm">
            <form method="post" class="row g-3 mb-4 border rounded p-3">
                <input type="hidden" name="form" value="game_type">
                <div class="col-md-7">
                    <label class="form-label">Название</label>
                    <input class="form-control" name="name" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Размер команды</label>
                    <input class="form-control" name="team_size" type="number" min="1" required>
                </div>
                <div class="col-12">
                    <button class="btn btn-primary">Добавить дисциплину</button>
                </div>
            </form>
            </div>

            <table class="table table-bordered">
                <thead class="table-light">
                <tr><th>ID</th><th>Название</th><th>Размер</th><th>Действия</th></tr>
                </thead>
                <tbody>
                <?php foreach ($types as $type): ?>
                    <tr>
                        <td><?= $type['id'] ?></td>
                        <td><?= htmlspecialchars($type['name']) ?></td>
                        <td><?= $type['team_size'] ?></td>
                        <td>
                            <div class="d-flex gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="game_type_edit.php?id=<?= (int)$type['id'] ?>">Редактировать</a>
                                <form method="post" action="game_type_delete.php" onsubmit="return confirm('Удалить дисциплину?');">
                                    <input type="hidden" name="id" value="<?= (int)$type['id'] ?>">
                                    <button class="btn btn-sm btn-outline-danger">Удалить</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="page-card">
            <h2 class="page-title">Карты</h2>

            <?php if (!$types): ?>
                <div class="text-muted">Сначала добавьте игровую дисциплину.</div>
            <?php else: ?>
                <ul class="nav nav-tabs mb-4" role="tablist">
                    <?php foreach ($types as $type): ?>
                        <?php $typeId = (int)$type['id']; ?>
                        <li class="nav-item" role="presentation">
                            <a
                                class="nav-link <?= $activeTypeId === $typeId ? 'active' : '' ?>"
                                href="game_types.php?type_id=<?= $typeId ?>"
                            >
                                <?= htmlspecialchars($type['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php foreach ($types as $type): ?>
                    <?php
                    $typeId = (int)$type['id'];
                    $typeMaps = $mapsByType[$typeId] ?? [];
                    ?>
                    <?php if ($activeTypeId === $typeId): ?>
                        <form method="post" class="row g-3 mb-4">
                            <input type="hidden" name="form" value="map">
                            <input type="hidden" name="game_type_id" value="<?= $typeId ?>">
                            <div class="col-md-8">
                                <label class="form-label">Новая карта для <?= htmlspecialchars($type['name']) ?></label>
                                <input class="form-control" name="name" required>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button class="btn btn-primary w-100">Добавить карту</button>
                            </div>
                        </form>

                        <?php if (!$typeMaps): ?>
                            <div class="text-muted">Для этой дисциплины карты пока не добавлены.</div>
                        <?php else: ?>
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                <tr><th>Карта</th><th>Действия</th></tr>
                                </thead>
                                <tbody>
                                <?php foreach ($typeMaps as $map): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($map['name']) ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a class="btn btn-sm btn-outline-primary" href="map_edit.php?id=<?= (int)$map['id'] ?>">Редактировать</a>
                                                <form method="post" action="map_delete.php" onsubmit="return confirm('Удалить карту?');">
                                                    <input type="hidden" name="id" value="<?= (int)$map['id'] ?>">
                                                    <input type="hidden" name="type_id" value="<?= $typeId ?>">
                                                    <button class="btn btn-sm btn-outline-danger">Удалить</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . "/footer.php"; ?>
