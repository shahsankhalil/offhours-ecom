<?php
// OffHour Watch Store - Checkout Page (checkout.php)
require_once 'config/security.php';
ensure_session();

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

// If cart is empty, redirect back to cart page
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

include_once 'includes/header.php';
?>

<section class="container">
    <h1 class="page-title">Checkout</h1>

    <div class="checkout-layout">
        <!-- Left: Delivery Information Form Card -->
        <div class="form-card">
            <h2 style="font-family: var(--font-display); font-size: 20px; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                Delivery Details
            </h2>
            
            <form id="checkoutForm" action="place_order.php" method="POST">
                <?php echo csrf_field(); ?>
                <div class="form-group-grid">
                    <div class="form-group form-group-full">
                        <label for="name">Full Name <span style="color: red;">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" placeholder="OffHour Customer" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address <span style="color: red;">*</span></label>
                        <input type="email" id="email" name="email" class="form-control" placeholder="customer@offhour.store" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number <span style="color: red;">*</span></label>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="+92 300 0000000" required>
                    </div>

                    <div class="form-group form-group-full">
                        <label for="address">Delivery Address <span style="color: red;">*</span></label>
                        <input type="text" id="address" name="address" class="form-control" placeholder="House 12, Main Boulevard" required>
                    </div>

                    <div class="form-group">
                        <label for="city">City <span style="color: red;">*</span></label>
                        <input type="text" id="city" name="city" class="form-control" placeholder="Lahore" required>
                    </div>

                    <div class="form-group">
                        <label for="postal_code">Postal / ZIP Code <span style="color: red;">*</span></label>
                        <input type="text" id="postal_code" name="postal_code" class="form-control" placeholder="54000" required>
                    </div>
                </div>

                <div style="margin-top: 24px; margin-bottom: 24px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px;">Payment Method</label>
                    <div class="payment-method-box">
                        <input type="radio" id="cod" name="payment_method" value="Cash on Delivery" checked style="pointer-events: none; accent-color: var(--primary-color);">
                        <div style="flex: 1;">
                            <div style="font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                                Cash on Delivery (COD)
                                <span class="cod-badge">PRE-SELECTED</span>
                            </div>
                            <span style="font-size: 12px; color: var(--text-muted);">Pay securely with cash upon physical parcel inspection at your front door.</span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 14px; align-items: center; margin-top: 30px;">
                    <button type="submit" class="btn btn-primary btn-place-order" style="padding: 14px 28px; font-size: 15px;">
                        Place Order (COD)
                    </button>
                    <a href="cart.php" class="btn btn-outline" style="padding: 14px 20px;">
                        Return to Cart
                    </a>
                </div>
            </form>
        </div>

        <!-- Right: Real-time static checkout order review -->
        <aside>
            <div class="cart-summary-card" style="position: sticky; top: 100px;">
                <h3 style="margin-bottom: 16px;">Review Your Items</h3>
                
                <div style="max-height: 280px; overflow-y: auto; margin-bottom: 20px; padding-right: 4px; border-bottom: 1px solid var(--border-color); padding-bottom: 16px;">
                    <?php foreach ($cart as $item): ?>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div style="display: flex; gap: 12px; align-items: center; max-width: 70%;">
                                <div class="checkout-item-image">
                                    <?php if (!empty($item['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    <?php else: ?>
                                        <span>⌚</span>
                                    <?php endif; ?>
                                </div>
                                <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <div style="font-weight: 700; font-size: 13px; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($item['name']); ?>
                                    </div>
                                    <span style="font-size: 11px; color: var(--text-muted);">Qty: <?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?></span>
                                </div>
                            </div>
                            <span style="font-weight: 700; font-size: 13px; color: var(--text-dark);">
                                $<?php echo number_format($item['price'] * $item['quantity'], 2); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="summary-row">
                    <span class="text-muted">Subtotal</span>
                    <span style="font-weight: 600;">$<?php echo number_format($subtotal, 2); ?></span>
                </div>
                <div class="summary-row">
                    <span class="text-muted">Courier Shipping</span>
                    <span style="color: var(--success-color); font-weight: 700;">FREE</span>
                </div>
                <div class="summary-row" style="margin-bottom: 0;">
                    <span class="text-muted">Tax / VAT</span>
                    <span style="color: var(--text-muted); font-size: 12px; font-style: italic;">No extra tax</span>
                </div>

                <div class="summary-row total" style="margin-bottom: 0;">
                    <span>Total Cost</span>
                    <span style="color: var(--primary-color); font-size: 22px;">$<?php echo number_format($subtotal, 2); ?></span>
                </div>
            </div>
        </aside>
    </div>
</section>

<?php
include_once 'includes/footer.php';
?>
