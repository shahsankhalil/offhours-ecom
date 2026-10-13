<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';
ensure_session();

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | OffHour</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="admin-login-body">
    <main class="login-card">
        <a class="logo" href="../index.php"><span class="logo-off">Off</span><span class="logo-hour">Hour</span><span class="logo-dot">.</span></a>
        <p class="eyebrow">Store control</p>
        <h1>Admin login</h1>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post">
            <?php echo csrf_field(); ?>
            <div class="form-group"><label for="username">Username</label><input class="form-control" id="username" name="username" required autofocus></div>
            <div class="form-group"><label for="password">Password</label><input class="form-control" id="password" name="password" type="password" required></div>
            <button class="btn btn-primary btn-block" type="submit">Sign in</button>
        </form>
        <p class="login-hint">Initial local account: admin / admin123</p>
    </main>
</body>
</html>