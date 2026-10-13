<?php
require_once __DIR__ . '/_bootstrap.php';
$statuses = ['Pending', 'Confirmed', 'Shipped', 'Delivered', 'Cancelled'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $order_id = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $status = $_POST['status'] ?? '';
    if ($order_id && in_array($status, $statuses, true)) {
        $stmt = $pdo->prepare('UPDATE orders SET status = ? WHERE id = ?');
        $stmt->execute([$status, $order_id]);
        admin_flash('success', 'Order status updated.');
    }
    header('Location: orders.php');
    exit;
}
$orders = $pdo->query('SELECT * FROM orders ORDER BY created_at DESC, id DESC')->fetchAll();
admin_page_start('Orders');
?>
<div class="admin-heading"><div><p class="eyebrow">Fulfilment</p><h1>Orders</h1></div></div>
<div class="admin-panel table-wrap"><table class="admin-table"><thead><tr><th>Order</th><th>Customer</th><th>Destination</th><th>Total</th><th>Status</th><th>Placed</th></tr></thead><tbody><?php foreach ($orders as $order): ?><tr><td><strong>#<?php echo (int)$order['id']; ?></strong><small><?php echo htmlspecialchars($order['payment_method']); ?></small></td><td><strong><?php echo htmlspecialchars($order['name']); ?></strong><small><?php echo htmlspecialchars($order['phone']); ?></small></td><td><?php echo htmlspecialchars($order['city']); ?><small><?php echo htmlspecialchars($order['address']); ?></small></td><td>$<?php echo number_format($order['total'], 2); ?></td><td><form method="post" class="status-form"><?php echo csrf_field(); ?><input type="hidden" name="order_id" value="<?php echo $order['id']; ?>"><select name="status" onchange="this.form.submit()"><?php foreach ($statuses as $status): ?><option value="<?php echo $status; ?>" <?php echo $order['status'] === $status ? 'selected' : ''; ?>><?php echo $status; ?></option><?php endforeach; ?></select></form></td><td><?php echo htmlspecialchars(date('M j, Y', strtotime($order['created_at']))); ?></td></tr><?php endforeach; ?></tbody></table><?php if (!$orders): ?><p class="empty-state">No orders yet.</p><?php endif; ?></div>
<?php admin_page_end(); ?>