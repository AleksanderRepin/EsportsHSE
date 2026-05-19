<?php
require __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
require_admin();
include __DIR__ . "/header.php";

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$errors = [];

$stmt = $pdo->prepare("SELECT id, name, team_size FROM gametype WHERE id = ?");
$stmt->execute([$id]);
$type = $stmt->fetch();
if (!$type) {
    die("Дисциплина не найдена.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $teamSize = (int)($_POST['team_size'] ?? 0);

    if ($name === '') $errors[] = "Введите название.";
    if ($teamSize < 1) $errors[] = "Размер команды должен быть больше 0.";

    if (!$errors) {
        $stmt = $pdo->prepare("UPDATE gametype SET name = ?, team_size = ? WHERE id = ?");
        $stmt->execute([$name, $teamSize, $id]);
        header("Location: game_types.php?type_id=$id");
        exit;
    }

    $type = ['id' => $id, 'name' => $name, 'team_size' => $teamSize];
}
?>

<div class="page-card">
    <h2 class="page-title">Редактировать дисциплину</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="col-md-6">
            <label class="form-label">Название</label>
            <input class="form-control" name="name" value="<?= htmlspecialchars($type['name']) ?>" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Размер команды</label>
            <input class="form-control" type="number" name="team_size" min="1" value="<?= (int)$type['team_size'] ?>" required>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Сохранить</button>
            <a class="btn btn-outline-secondary" href="game_types.php?type_id=<?= $id ?>">Отмена</a>
        </div>
    </form>
</div>

<?php include __DIR__ . "/footer.php"; ?>
