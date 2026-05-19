<?php
session_start();
require __DIR__ . "/db.php";
require __DIR__ . "/ui.php";

$errors = [];
$countries = country_options();
$nickname = '';
$country = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname'] ?? '');
    $country = trim($_POST['country'] ?? '');
    $password = $_POST['password'] ?? '';
    $passwordRepeat = $_POST['password_repeat'] ?? '';

    if ($nickname === '') $errors[] = "Введите никнейм.";
    if (!is_valid_country($country)) $errors[] = "Выберите страну из списка.";
    if (strlen($password) < 4) $errors[] = "Пароль должен содержать минимум 4 символа.";
    if ($password !== $passwordRepeat) $errors[] = "Пароли не совпадают.";

    if (!$errors) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO users (nickname, country, rating, role, password_hash)
                VALUES (?, ?, 0, 'player', ?)
                RETURNING *
            ");
            $stmt->execute([$nickname, $country, password_hash($password, PASSWORD_DEFAULT)]);
            $_SESSION['user'] = $stmt->fetch();
            header("Location: dashboard.php");
            exit;
        } catch (Throwable $e) {
            $errors[] = "Пользователь с таким никнеймом уже существует.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация игрока</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="min-height:100vh;">
<div class="card p-4 shadow-sm" style="width:420px;">
    <h4 class="mb-3">Регистрация игрока</h4>

    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <label class="form-label">Никнейм</label>
        <input class="form-control mb-3" type="text" name="nickname" required>

        <label class="form-label">Страна</label>
        <select class="form-select mb-3" name="country" required>
            <option value="">Выберите страну</option>
            <?php foreach ($countries as $countryOption): ?>
                <option value="<?= htmlspecialchars($countryOption) ?>" <?= $country === $countryOption ? 'selected' : '' ?>>
                    <?= htmlspecialchars($countryOption) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label class="form-label">Пароль</label>
        <input class="form-control mb-3" type="password" name="password" required>

        <label class="form-label">Повторите пароль</label>
        <input class="form-control mb-3" type="password" name="password_repeat" required>

        <button class="btn btn-primary w-100">Зарегистрироваться</button>
        <a class="btn btn-link w-100 mt-2" href="login.php">Уже есть аккаунт</a>
    </form>
</div>
</body>
</html>
