<?php
require_once __DIR__ . '/_bootstrap.php';
$watch_count = (int)$pdo->query('SELECT COUNT(*) FROM watches')->fetchColumn();
$order_count = (int)$pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pending_count = (int)$pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetchColumn();
$revenue = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE status <> 'Cancelled'")->fetchColumn();
admin_page_start('Dashboard');
?>
<div class="admin-heading"><div><p class="eyebrow">Overview</p><h1>Good evening, <?php echo htmlspecialchars($_SESSION['admin_username']); ?>.</h1></div><a href="watches.php?action=new" class="btn btn-primary">Add watch</a></div>
<div class="admin-stats">
    <div class="stat-card"><span>Watches</span><strong><?php echo $watch_count; ?></strong></div>
    <div class="stat-card"><span>Total orders</span><strong><?php echo $order_count; ?></strong></div>
    <div class="stat-card"><span>Pending</span><strong><?php echo $pending_count; ?></strong></div>
    <div class="stat-card"><span>Revenue</span><strong>$<?php echo number_format($revenue, 2); ?></strong></div>
</div>
<div class="admin-panel"><div class="admin-heading"><h2>Quick actions</h2></div><div class="quick-links"><a class="btn btn-outline" href="watches.php">Manage watches</a><a class="btn btn-outline" href="orders.php">Review orders</a><a class="btn btn-outline" href="../index.php">View storefront</a></div></div>
<?php admin_page_end(); ?>