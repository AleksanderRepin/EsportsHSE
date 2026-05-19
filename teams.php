<?php
require __DIR__ . "/db.php";
include __DIR__ . "/header.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_admin();
    $name = trim($_POST['name'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);

    if ($name === '') $errors[] = "Введите название команды.";
    if (!is_valid_country($country)) $errors[] = "Выберите страну из списка.";
    if ($rating < 0 || $rating > 10000) $errors[] = "Рейтинг должен быть от 0 до 10000.";

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO team (name, country, rating) VALUES (?, ?, ?)");
        $stmt->execute([$name, $country, $rating]);
        header('Location: teams.php');
        exit;
    }
}

$countries = country_options();
$editTeamId = (int)($_GET['edit'] ?? 0);
$selectedTeamId = (int)($_GET['team_id'] ?? 0);
$editTeam = null;
$selectedTeam = null;
$selectedTeamMembers = [];

if ($editTeamId) {
    $stmt = $pdo->prepare("SELECT id, name, country, rating FROM team WHERE id = ?");
    $stmt->execute([$editTeamId]);
    $editTeam = $stmt->fetch();
}

if ($selectedTeamId) {
    $stmt = $pdo->prepare("SELECT id, name, country, rating FROM team WHERE id = ?");
    $stmt->execute([$selectedTeamId]);
    $selectedTeam = $stmt->fetch();

    if ($selectedTeam) {
        $stmt = $pdo->prepare("
            SELECT tp.id, u.nickname, u.country, u.rating, COALESCE(pr.name, tp.role) AS role_name, tp.joined_at
            FROM teamplayer tp
            JOIN users u ON u.id = tp.user_id
            LEFT JOIN playerrole pr ON pr.id = tp.role_id
            WHERE tp.team_id = ?
            ORDER BY u.nickname
        ");
        $stmt->execute([$selectedTeamId]);
        $selectedTeamMembers = $stmt->fetchAll();
    }
}
$filters = [
    'q' => trim($_GET['q'] ?? ''),
    'country' => is_valid_country(trim($_GET['country'] ?? '')) ? trim($_GET['country'] ?? '') : '',
    'rating_min' => trim($_GET['rating_min'] ?? ''),
    'rating_max' => trim($_GET['rating_max'] ?? ''),
    'sort' => $_GET['sort'] ?? 'rating_desc',
];
$where = [];
$params = [];
if ($filters['q'] !== '') {
    $where[] = "t.name ILIKE ?";
    $params[] = '%' . $filters['q'] . '%';
}
if ($filters['country'] !== '') {
    $where[] = "t.country = ?";
    $params[] = $filters['country'];
}
if ($filters['rating_min'] !== '' && is_numeric($filters['rating_min'])) {
    $where[] = "t.rating >= ?";
    $params[] = (int)$filters['rating_min'];
}
if ($filters['rating_max'] !== '' && is_numeric($filters['rating_max'])) {
    $where[] = "t.rating <= ?";
    $params[] = (int)$filters['rating_max'];
}
$sorts = [
    'rating_desc' => 't.rating DESC, t.name ASC',
    'rating_asc' => 't.rating ASC, t.name ASC',
    'name_asc' => 't.name ASC',
    'country_asc' => 't.country ASC, t.name ASC',
    'players_desc' => 'players_count DESC, t.name ASC',
];
$orderBy = $sorts[$filters['sort']] ?? $sorts['rating_desc'];
$sql = "
    SELECT t.*, COUNT(tp.id) AS players_count
    FROM team t
    LEFT JOIN teamplayer tp ON tp.team_id = t.id
";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " GROUP BY t.id ORDER BY $orderBy";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$teams = $stmt->fetchAll();
?>

<div class="page-card">
    <h2 class="page-title">Команды</h2>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_GET['delete_error'])): ?>
        <div class="alert alert-warning">Команду нельзя удалить: она участвует в матчах или других связанных данных.</div>
    <?php endif; ?>

    <div class="border rounded p-3 mb-4 bg-light">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Поиск команды</label>
                <input type="text" name="q" class="form-control" value="<?= htmlspecialchars($filters['q']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label">Страна</label>
                <select name="country" class="form-select">
                    <option value="">Все страны</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?= htmlspecialchars($country) ?>" <?= $filters['country'] === $country ? 'selected' : '' ?>>
                            <?= htmlspecialchars($country) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Рейтинг от</label>
                <input type="number" name="rating_min" class="form-control" value="<?= htmlspecialchars($filters['rating_min']) ?>" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">До</label>
                <input type="number" name="rating_max" class="form-control" value="<?= htmlspecialchars($filters['rating_max']) ?>" min="0">
            </div>
            <div class="col-md-2">
                <label class="form-label">Сортировка</label>
                <select name="sort" class="form-select">
                    <option value="rating_desc" <?= $filters['sort'] === 'rating_desc' ? 'selected' : '' ?>>Рейтинг: высокий</option>
                    <option value="rating_asc" <?= $filters['sort'] === 'rating_asc' ? 'selected' : '' ?>>Рейтинг: низкий</option>
                    <option value="name_asc" <?= $filters['sort'] === 'name_asc' ? 'selected' : '' ?>>Название</option>
                    <option value="country_asc" <?= $filters['sort'] === 'country_asc' ? 'selected' : '' ?>>Страна</option>
                    <option value="players_desc" <?= $filters['sort'] === 'players_desc' ? 'selected' : '' ?>>Игроков больше</option>
                </select>
            </div>
            <div class="col-md-1 d-grid gap-2">
                <button class="btn btn-primary">Найти</button>
                <a href="teams.php" class="btn btn-outline-secondary">Сброс</a>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">Найдено команд: <?= count($teams) ?></div>
        <?php if ($isAdmin): ?>
            <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addTeamForm">
                Добавить команду
            </button>
        <?php endif; ?>
    </div>

    <?php if ($isAdmin): ?>
    <div class="collapse mb-4" id="addTeamForm">
        <form method="post" class="row g-3 border rounded p-3">
            <div class="col-md-4">
                <label class="form-label">Название</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Страна</label>
                <select name="country" class="form-select" required>
                    <option value="">Выберите страну</option>
                    <?php foreach ($countries as $country): ?>
                        <option value="<?= htmlspecialchars($country) ?>"><?= htmlspecialchars($country) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Рейтинг</label>
                <input type="number" name="rating" class="form-control" value="0" min="0" max="10000" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary w-100">Сохранить</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <table class="table table-hover table-bordered">
        <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Страна</th>
            <th>Рейтинг</th>
            <th>Игроков</th>
            <?php if ($isAdmin): ?><th>Действия</th><?php endif; ?>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($teams as $team): ?>
            <tr>
                <td><?= $team['id'] ?></td>
                <td>
                    <a class="fw-semibold" href="teams.php?team_id=<?= (int)$team['id'] ?>">
                        <?= htmlspecialchars($team['name']) ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($team['country']) ?></td>
                <td><?= $team['rating'] ?></td>
                <td><?= $team['players_count'] ?></td>
                <?php if ($isAdmin): ?>
                <td>
                    <div class="d-flex gap-2">
                        <a class="btn btn-sm btn-outline-primary" href="teams.php?edit=<?= (int)$team['id'] ?>">Редактировать</a>
                        <form method="post" action="team_delete.php" onsubmit="return confirm('Удалить команду?');">
                            <input type="hidden" name="id" value="<?= (int)$team['id'] ?>">
                            <button class="btn btn-sm btn-outline-danger">Удалить</button>
                        </form>
                    </div>
                </td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if ($isAdmin && $editTeam): ?>
<div class="modal fade" id="editTeamModal" tabindex="-1" aria-labelledby="editTeamModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form method="post" action="team_edit.php">
                <input type="hidden" name="id" value="<?= (int)$editTeam['id'] ?>">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTeamModalLabel">Редактировать команду</h5>
                    <a class="btn-close" href="teams.php" aria-label="Закрыть"></a>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label">Название</label>
                            <input class="form-control" name="name" value="<?= htmlspecialchars($editTeam['name']) ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Страна</label>
                            <select class="form-select" name="country" required>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?= htmlspecialchars($country) ?>" <?= $editTeam['country'] === $country ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($country) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Рейтинг</label>
                            <input class="form-control" type="number" name="rating" min="0" max="10000" value="<?= (int)$editTeam['rating'] ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-outline-secondary" href="teams.php">Отмена</a>
                    <button class="btn btn-primary">Сохранить</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('editTeamModal')).show();
});
</script>
<?php endif; ?>

<?php if ($selectedTeam): ?>
<div class="modal fade" id="teamMembersModal" tabindex="-1" aria-labelledby="teamMembersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="teamMembersModalLabel">Состав: <?= htmlspecialchars($selectedTeam['name']) ?></h5>
                    <div class="text-muted small"><?= count($selectedTeamMembers) ?> игроков в общем составе команды</div>
                </div>
                <a class="btn-close" href="teams.php" aria-label="Закрыть"></a>
            </div>
            <div class="modal-body">
                <?php if (!$selectedTeamMembers): ?>
                    <div class="text-muted">В общем составе команды пока нет игроков.</div>
                <?php else: ?>
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                        <tr><th>Игрок</th><th>Роль</th><th>Страна</th><th>Рейтинг</th><th>Дата</th><?php if ($isAdmin): ?><th>Действия</th><?php endif; ?></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($selectedTeamMembers as $member): ?>
                            <tr>
                                <td><?= htmlspecialchars($member['nickname']) ?></td>
                                <td><?= htmlspecialchars($member['role_name']) ?></td>
                                <td><?= htmlspecialchars($member['country']) ?></td>
                                <td><?= (int)$member['rating'] ?></td>
                                <td><?= htmlspecialchars($member['joined_at']) ?></td>
                                <?php if ($isAdmin): ?>
                                <td>
                                    <form method="post" action="team_player_delete.php" onsubmit="return confirm('Удалить игрока из общего состава?');">
                                        <input type="hidden" name="id" value="<?= (int)$member['id'] ?>">
                                        <input type="hidden" name="return_to" value="teams">
                                        <input type="hidden" name="team_id" value="<?= (int)$selectedTeam['id'] ?>">
                                        <button class="btn btn-sm btn-outline-danger">Удалить</button>
                                    </form>
                                </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <a class="btn btn-outline-secondary" href="teams.php">Закрыть</a>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    new bootstrap.Modal(document.getElementById('teamMembersModal')).show();
});
</script>
<?php endif; ?>

<?php include __DIR__ . "/footer.php"; ?>
