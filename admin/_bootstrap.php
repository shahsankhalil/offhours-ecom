<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/security.php';

ensure_session();
require_admin();

function admin_page_start(string $title): void
{
    $flash = consume_admin_flash();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo htmlspecialchars($title); ?> | OffHour Admin</title>
        <link rel="stylesheet" href="../css/style.css">
    </head>
    <body class="admin-body">
        <header class="admin-header">
            <div class="admin-container admin-nav">
                <a class="logo" href="dashboard.php"><span class="logo-off">Off</span><span class="logo-hour">Hour</span><span class="logo-dot">.</span> Admin</a>
                <nav class="nav-menu">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="watches.php">Watches</a>
                    <a href="orders.php">Orders</a>
                    <a href="logout.php">Log out</a>
                </nav>
            </div>
        </header>
        <main class="admin-container admin-main">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo htmlspecialchars($flash['type']); ?>"><?php echo htmlspecialchars($flash['message']); ?></div>
            <?php endif; ?>
    <?php
}

function admin_page_end(): void
{
    echo '</main></body></html>';
}
?>