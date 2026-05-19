<?php
session_start();
require __DIR__ . "/db.php";

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nickname = trim($_POST['nickname'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE nickname = ?");
    $stmt->execute([$nickname]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = $user;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Неверный логин или пароль.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход в панель турниров</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex justify-content-center align-items-center" style="min-height:100vh;">
<div class="card p-4 shadow-sm" style="width:360px;">
    <h4 class="mb-3">Вход в систему</h4>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post">
        <label class="form-label">Логин</label>
        <input class="form-control mb-3" type="text" name="nickname" value="admin" required>

        <label class="form-label">Пароль</label>
        <input class="form-control mb-3" type="password" name="password" required>

        <button class="btn btn-primary w-100">Войти</button>
        <a class="btn btn-link w-100 mt-2" href="register.php">Зарегистрироваться как игрок</a>
    </form>
</div>
</body>
</html>
