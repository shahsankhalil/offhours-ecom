<?php
// OffHour Watch Store - Order Confirmation Page (order_success.php)
require_once 'config/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$order_id = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);
$order = null;
$items = [];
$mail_status = null;

// A. First try to load order details from Database via PDO
if ($order_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch();

        if ($order) {
            $stmt_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
            $stmt_items->execute([$order_id]);
            $items = $stmt_items->fetchAll();
        }
    } catch (PDOException $e) {
        // Silently catch and use session fallback if database is unavailable
    }
}

// B. If database returned nothing, fall back to temporary session cache (ideal for initial local setups)
if (!$order && isset($_SESSION['last_order'])) {
    $order = $_SESSION['last_order']['order'];
    $items = $_SESSION['last_order']['items'];
    $mail_status = $_SESSION['last_order']['status'];
}

// Include Header styling
include_once 'includes/header.php';
?>

<section class="container">
    <?php if ($order): ?>
        <div class="success-card">
            <!-- Polished Success Checkmark Icon -->
            <div class="success-icon-badge">✓</div>
            
            <h2>Thank You for Your Order!</h2>
            <p>Your timepiece booking has been confirmed. Our team is handpacking your watch for reliable delivery.</p>
            
            <!-- Dynamic Receipt Details Box in Sky Blue & Off-White -->
            <div class="order-meta-box">
                <div style="border-bottom: 2px solid var(--border-color); padding-bottom: 12px; margin-bottom: 16px;">
                    <h3 style="font-family: var(--font-display); font-size: 16px; font-weight: 700; color: var(--primary-dark); text-transform: uppercase;">
                        Order Receipt Details
                    </h3>
                </div>

                <div class="order-meta-row">
                    <span class="meta-label">Order ID Reference</span>
                    <span class="meta-value" style="color: var(--primary-color);">#<?php echo htmlspecialchars($order['id'] ?? $order_id); ?></span>
                </div>
                <div class="order-meta-row">
                    <span class="meta-label">Customer Name</span>
                    <span class="meta-value"><?php echo htmlspecialchars($order['name']); ?></span>
                </div>
                <div class="order-meta-row">
                    <span class="meta-label">Contact Phone</span>
                    <span class="meta-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                </div>
                <div class="order-meta-row" style="flex-direction: column; align-items: flex-start; gap: 4px;">
                    <span class="meta-label">Delivery Destination</span>
                    <span style="font-size: 13px; font-weight: 500; font-style: italic; color: var(--text-dark);">
                        <?php echo htmlspecialchars($order['address']); ?>, <?php echo htmlspecialchars($order['city']); ?>, <?php echo htmlspecialchars($order['postal_code']); ?>
                    </span>
                </div>
                <div class="order-meta-row">
                    <span class="meta-label">Payment Terms</span>
                    <span class="meta-value" style="color: var(--success-color);">Cash on Delivery (COD)</span>
                </div>
                
                <!-- Detailed Purchased Items table -->
                <div style="margin-top: 20px;">
                    <h4 style="font-size: 13px; margin-bottom: 8px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">Purchased watches</h4>
                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--border-color); text-align: left;">
                                <th style="padding: 6px 0; color: var(--text-muted); font-weight: 600;">Watch Model</th>
                                <th style="padding: 6px 0; text-align: center; color: var(--text-muted); font-weight: 600;">Qty</th>
                                <th style="padding: 6px 0; text-align: right; color: var(--text-muted); font-weight: 600;">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr style="border-bottom: 1px dashed var(--border-color);">
                                    <td style="padding: 8px 0; font-weight: 700;">⌚ <?php echo htmlspecialchars($item['watch_name'] ?? $item['name'] ?? 'Premium Watch'); ?></td>
                                    <td style="padding: 8px 0; text-align: center; font-weight: 500;"><?php echo $item['quantity']; ?></td>
                                    <td style="padding: 8px 0; text-align: right; font-weight: 700;">$<?php echo number_format($item['price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr>
                                <td colspan="2" style="padding: 12px 0 0; font-weight: 700; font-size: 15px;">Total Amount (COD Pin)</td>
                                <td style="padding: 12px 0 0; text-align: right; font-weight: 700; font-size: 16px; color: var(--primary-color);">
                                    $<?php echo number_format($order['total'], 2); ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Email confirmation notification -->
            <div style="background-color: var(--primary-light); color: var(--primary-dark); padding: 16px; border-radius: 8px; margin-bottom: 30px; border: 1px solid rgba(2, 132, 199, 0.15); font-size: 13px; text-align: left; line-height: 1.5;">

            <div style="display: flex; gap: 14px; justify-content: center;">
                <a href="index.php" class="btn btn-primary" style="padding: 12px 24px;">
                    Return to Collection
                </a>
                
                <?php if (file_exists('mail/emails_sent.html')): ?>
                    <a href="mail/emails_sent.html" target="_blank" class="btn btn-outline" style="padding: 12px 20px;">
                        Open Email Draft &rarr;
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Safe State if direct path load -->
        <div class="empty-cart-state" style="border: 1px dashed var(--border-color); background-color: #ffffff; border-radius: 12px;">
            <div style="font-size: 48px; margin-bottom: 12px;">🔍</div>
            <h2>Order Details Not Found</h2>
            <p>We couldn't track down this specific order's files. Visit our collection page to place a new order.</p>
            <a href="index.php" class="btn btn-primary">
                Return to Shop
            </a>
        </div>
    <?php endif; ?>
</section>

<?php
include_once 'includes/footer.php';
?>
