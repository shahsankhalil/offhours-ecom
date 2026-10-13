<?php
// OffHour Watch Store - Cart Page (cart.php)
require_once 'config/db.php';
require_once 'config/security.php';
ensure_session();

// Handle cart mutations
$removal_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $watch_id = filter_input(INPUT_POST, 'watch_id', FILTER_VALIDATE_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);

    if ($watch_id && isset($_SESSION['cart'][$watch_id]) && isset($_POST['remove_item'])) {
        $removed_name = $_SESSION['cart'][$watch_id]['name'];
        unset($_SESSION['cart'][$watch_id]);
        $removal_msg = "Removed <strong>" . htmlspecialchars($removed_name) . "</strong> from your cart.";
    } elseif ($watch_id && isset($_SESSION['cart'][$watch_id]) && isset($_POST['update_item'])) {
        if ($quantity === false || $quantity < 1) {
            unset($_SESSION['cart'][$watch_id]);
        } else {
            $watch_stmt = $pdo->prepare("SELECT stock FROM watches WHERE id = ?");
            $watch_stmt->execute([$watch_id]);
            $stock = (int)($watch_stmt->fetchColumn() ?: 0);
            $_SESSION['cart'][$watch_id]['quantity'] = min($quantity, $stock);
            if ($_SESSION['cart'][$watch_id]['quantity'] < 1) {
                unset($_SESSION['cart'][$watch_id]);
            }
        }
    }
}

// Calculate details
$items = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$subtotal = 0;
foreach ($items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

include_once 'includes/header.php';
?>

<section class="container">
    <h1 class="page-title">Shopping Cart</h1>

    <?php if (!empty($removal_msg)): ?>
        <div class="alert alert-success" style="background-color: #fee2e2; color: #991b1b; border-color: #fca5a5;">
            <?php echo $removal_msg; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($items)): ?>
        <div class="cart-layout">
            <!-- Left Panel: Product Listing -->
            <div class="cart-table-card">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Watch Details</th>
                            <th style="text-align: center;">Price</th>
                            <th style="text-align: center;">Qty</th>
                            <th style="text-align: right;">Subtotal</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <?php $row_total = $item['price'] * $item['quantity']; ?>
                            <tr>
                                <td>
                                    <div class="cart-item-watch">
                                        <div class="cart-item-img">
                                            <?php if (!empty($item['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <?php else: ?>
                                                <span>⌚</span>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <div class="cart-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                            <span style="font-size: 12px; color: var(--text-muted);">ID: #<?php echo $item['id']; ?></span>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center; font-weight: 500;">
                                    $<?php echo number_format($item['price'], 2); ?>
                                </td>
                                <td style="text-align: center;">
                                    <form action="cart.php" method="POST" class="quantity-form">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="watch_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" min="1" value="<?php echo $item['quantity']; ?>" class="quantity-input" aria-label="Quantity">
                                        <button type="submit" name="update_item" class="btn btn-outline btn-small">Update</button>
                                    </form>
                                </td>
                                <td style="text-align: right; font-weight: 700; color: var(--primary-dark);">
                                    $<?php echo number_format($row_total, 2); ?>
                                </td>
                                <td style="text-align: right;">
                                    <form action="cart.php" method="POST" style="margin: 0;" onsubmit="return confirm('Remove <?php echo htmlspecialchars($item['name']); ?> from cart?');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="watch_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" name="remove_item" class="btn btn-danger btn-remove-item" style="padding: 6px 12px; font-size: 12px;">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px; display: flex; justify-content: space-between;">
                    <a href="index.php" class="btn btn-outline">
                        &larr; Continue Exploring
                    </a>
                </div>
            </div>

            <!-- Right Panel: Summary card -->
            <aside class="cart-summary-card">
                <h3>Order Summary</h3>
                
                <div class="summary-row">
                    <span class="text-muted">Total Items</span>
                    <span style="font-weight: 600;">
                        <?php 
                        $total_qty = 0;
                        foreach($items as $i) { $total_qty += $i['quantity']; }
                        echo $total_qty;
                        ?>
                    </span>
                </div>
                
                <div class="summary-row">
                    <span class="text-muted">Promo Code</span>
                    <span style="color: var(--text-muted); font-style: italic;">None applied</span>
                </div>

                <div class="summary-row">
                    <span class="text-muted">Delivery</span>
                    <span style="color: var(--success-color); font-weight: 700; font-size: 12px; text-transform: uppercase;">FREE COD SHIPPING</span>
                </div>

                <div class="summary-row total">
                    <span>Total Amount</span>
                    <span style="color: var(--primary-color);">$<?php echo number_format($subtotal, 2); ?></span>
                </div>

                <a href="checkout.php" class="btn btn-primary btn-block" style="padding: 14px; font-size: 15px;">
                    Proceed to Checkout &rarr;
                </a>
                
                <div style="text-align: center; margin-top: 14px; display: flex; align-items: center; justify-content: center; gap: 6px; font-size: 11px; color: var(--text-muted); font-weight: 500; text-transform: uppercase;">
                    <span style="width: 8px; height: 8px; background-color: var(--primary-color); border-radius: 50%;"></span>
                    Cash on Delivery Available
                </div>
            </aside>
        </div>
    <?php else: ?>
        <div class="empty-cart-state">
            <div style="font-size: 48px; margin-bottom: 12px;">🛒</div>
            <h2>Your cart is sitting empty</h2>
            <p>Our off-hour timepieces are waiting for you to discover them.</p>
            <a href="index.php" class="btn btn-primary">
                View Collection
            </a>
        </div>
    <?php endif; ?>
</section>

<?php
include_once 'includes/footer.php';
?>
