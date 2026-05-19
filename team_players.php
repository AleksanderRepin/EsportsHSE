<?php
require __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
require_admin();
include __DIR__ . "/header.php";

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $teamId = (int)($_POST['team_id'] ?? 0);
    $userId = (int)($_POST['user_id'] ?? 0);
    $roleId = (int)($_POST['role_id'] ?? 0);

    $roleStmt = $pdo->prepare("SELECT name FROM playerrole WHERE id = ?");
    $roleStmt->execute([$roleId]);
    $roleName = $roleStmt->fetchColumn();

    if (!$teamId || !$userId || !$roleId || !$roleName) {
        $errors[] = "Выберите команду, игрока и игровую роль.";
    }

    if (!$errors) {
        $stmt = $pdo->prepare("
            INSERT INTO teamplayer (user_id, team_id, role, role_id, joined_at)
            VALUES (?, ?, ?, ?, NOW())
            ON CONFLICT (user_id, team_id) DO NOTHING
        ");
        $stmt->execute([$userId, $teamId, $roleName, $roleId]);
        header('Location: team_players.php');
        exit;
    }
}

$teams = $pdo->query("SELECT id, name FROM team ORDER BY name")->fetchAll();
$players = $pdo->query("SELECT id, nickname FROM users WHERE role = 'player' ORDER BY nickname")->fetchAll();
$roles = $pdo->query("SELECT id, name FROM playerrole ORDER BY name")->fetchAll();
$filters = [
    'team_id' => (int)($_GET['team_id'] ?? 0),
    'role_id' => (int)($_GET['role_id'] ?? 0),
    'q' => trim($_GET['q'] ?? ''),
];
$where = [];
$params = [];
if ($filters['team_id']) {
    $where[] = "t.id = ?";
    $params[] = $filters['team_id'];
}
if ($filters['role_id']) {
    $where[] = "pr.id = ?";
    $params[] = $filters['role_id'];
}
if ($filters['q'] !== '') {
    $where[] = "u.nickname ILIKE ?";
    $params[] = '%' . $filters['q'] . '%';
}
$sql = "
    SELECT tp.id, t.name AS team_name, u.nickname, COALESCE(pr.name, tp.role) AS role_name, tp.joined_at
    FROM teamplayer tp
    JOIN team t ON t.id = tp.team_id
    JOIN users u ON u.id = tp.user_id
    LEFT JOIN playerrole pr ON pr.id = tp.role_id
";
if ($where) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= " ORDER BY t.name, u.nickname";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();
?>

<div class="page-card">
    <h2 class="page-title">Составы команд</h2>

    <?php if ($errors): ?>
        <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $errors)) ?></div>
    <?php endif; ?>

    <div class="border rounded p-3 mb-4 bg-light">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Команда</label>
                <select name="team_id" class="form-select">
                    <option value="0">Все команды</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?= $team['id'] ?>" <?= $filters['team_id'] === (int)$team['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($team['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Игровая роль</label>
                <select name="role_id" class="form-select">
                    <option value="0">Все роли</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role['id'] ?>" <?= $filters['role_id'] === (int)$role['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Игрок</label>
                <input class="form-control" name="q" value="<?= htmlspecialchars($filters['q']) ?>">
            </div>
            <div class="col-md-2 d-grid gap-2">
                <button class="btn btn-primary">Найти</button>
                <a href="team_players.php" class="btn btn-outline-secondary">Сброс</a>
            </div>
        </form>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="text-muted">Найдено записей: <?= count($members) ?></div>
        <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#addMemberForm">
            Добавить игрока в состав
        </button>
    </div>

    <div class="collapse mb-4" id="addMemberForm">
    <form method="post" class="row g-3 border rounded p-3">
        <div class="col-md-4">
            <label class="form-label">Команда</label>
            <select name="team_id" class="form-select" required>
                <?php foreach ($teams as $team): ?>
                    <option value="<?= $team['id'] ?>"><?= htmlspecialchars($team['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Игрок</label>
            <select name="user_id" class="form-select" required>
                <?php foreach ($players as $player): ?>
                    <option value="<?= $player['id'] ?>"><?= htmlspecialchars($player['nickname']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Игровая роль</label>
            <select name="role_id" class="form-select" required>
                <?php foreach ($roles as $role): ?>
                    <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-primary w-100">Добавить</button>
        </div>
    </form>
    </div>

    <table class="table table-hover table-bordered">
        <thead class="table-light">
        <tr>
            <th>Команда</th>
            <th>Игрок</th>
            <th>Игровая роль</th>
            <th>Дата вступления</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($members as $member): ?>
            <tr>
                <td><?= htmlspecialchars($member['team_name']) ?></td>
                <td><?= htmlspecialchars($member['nickname']) ?></td>
                <td><?= htmlspecialchars($member['role_name']) ?></td>
                <td><?= $member['joined_at'] ?></td>
                <td>
                    <form method="post" action="team_player_delete.php" onsubmit="return confirm('Удалить игрока из общего состава?');">
                        <input type="hidden" name="id" value="<?= (int)$member['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger">Удалить</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include __DIR__ . "/footer.php"; ?>
