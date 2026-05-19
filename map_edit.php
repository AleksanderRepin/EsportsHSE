<?php
require __DIR__ . "/db.php";
require_once __DIR__ . "/auth.php";
require_admin();
include __DIR__ . "/header.php";

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$errors = [];

$stmt = $pdo->prepare("SELECT id, game_type_id, name FROM gamemap WHERE id = ?");
$stmt->execute([$id]);
$map = $stmt->fetch();
if (!$map) {
    die("Карта не найдена.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    if ($name === '') $errors[] = "Введите название карты.";

    if (!$errors) {
        $stmt = $pdo->prepare("UPDATE gamemap SET name = ? WHERE id = ?");
        $stmt->execute([$name, $id]);
        header("Location: game_types.php?type_id=" . (int)$map['game_type_id']);
        exit;
    }
    $map['name'] = $name;
}
?>

<div class="page-card">
    <h2 class="page-title">Редактировать карту</h2>
    <?php if ($errors): ?>
        <div class="alert alert-danger"><?= htmlspecialchars(implode(' ', $errors)) ?></div>
    <?php endif; ?>
    <form method="post" class="row g-3">
        <input type="hidden" name="id" value="<?= $id ?>">
        <div class="col-md-6">
            <label class="form-label">Название</label>
            <input class="form-control" name="name" value="<?= htmlspecialchars($map['name']) ?>" required>
        </div>
        <div class="col-12">
            <button class="btn btn-primary">Сохранить</button>
            <a class="btn btn-outline-secondary" href="game_types.php?type_id=<?= (int)$map['game_type_id'] ?>">Отмена</a>
        </div>
    </form>
</div>

<?php include __DIR__ . "/footer.php"; ?>
