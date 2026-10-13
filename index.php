<?php
// OffHour Watch Store - Home & Collection Page (index.php)
require_once 'config/db.php';
require_once 'config/security.php';

ensure_session();

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Handle Add to Cart action
$success_message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    verify_csrf();
    $watch_id = filter_input(INPUT_POST, 'watch_id', FILTER_VALIDATE_INT);
    
    if ($watch_id) {
        // Fetch watch details from database to ensure price and name are authentic
        $stmt = $pdo->prepare("SELECT * FROM watches WHERE id = ?");
        $stmt->execute([$watch_id]);
        $watch = $stmt->fetch();
        
        if ($watch) {
            // Check if watch already is in the cart
            if (isset($_SESSION['cart'][$watch_id])) {
                $_SESSION['cart'][$watch_id]['quantity']++;
            } else {
                $_SESSION['cart'][$watch_id] = [
                    'id' => $watch['id'],
                    'name' => $watch['name'],
                    'price' => (float)$watch['price'],
                    'image' => $watch['image'],
                    'quantity' => 1
                ];
            }
            $success_message = "<strong>" . htmlspecialchars($watch['name']) . "</strong> successfully added to your cart!";
        }
    }
}

// Fetch all available watches from the database
try {
    $stmt = $pdo->query("SELECT * FROM watches ORDER BY id ASC");
    $watches = $stmt->fetchAll();
} catch (PDOException $e) {
    $watches = [];
    $error_error = "Could not fetch watches: " . $e->getMessage();
}

// Include Header
include_once 'includes/header.php';
?>

<section class="container">
    <!-- Hero Banner with elegant text styling -->
    <div class="hero">
        <h1>Precision Engineered Watches</h1>
        <p>Expertly crafted minimalist timepieces in distinct sky blue and white. Modern aesthetic paired with unparalleled durability.</p>
    </div>

    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
            <a href="cart.php" style="float: right; color: var(--primary-dark); font-weight: 700; text-decoration: underline;">View Cart</a>
        </div>
    <?php endif; ?>

    <div class="collection-header" style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
        <div>
            <h2 style="font-family: var(--font-display); font-size: 24px; font-weight: 700;">Featured Collection</h2>
            <p style="color: var(--text-muted); font-size: 14px;">Select from our premium lineup below.</p>
        </div>
        <div style="font-size: 13px; color: var(--text-muted); font-weight: 500;">
            Showing <?php echo count($watches); ?> timepieces
        </div>
    </div>

    <!-- Active Grid representing all items -->
    <div class="watch-grid">
        <?php if (!empty($watches)): ?>
            <?php foreach ($watches as $watch): ?>
                <div class="watch-card" id="watch-<?php echo $watch['id']; ?>">
                    <div class="watch-img-container">
                        <!-- Try to show physical image. If file doesn't exist, use the polished CSS placeholder rendering -->
                        <?php $watch_image = trim((string)($watch['image'] ?? '')); ?>
                        <?php $is_remote_image = preg_match('/^https?:\/\//i', $watch_image) === 1; ?>
                        <?php if ($watch_image !== '' && ($is_remote_image || file_exists($watch_image))): ?>
                            <img src="<?php echo htmlspecialchars($watch_image); ?>" alt="<?php echo htmlspecialchars($watch['name']); ?>" class="watch-img">
                        <?php else: ?>
                            <div class="watch-placeholder-img">
                                <div class="placeholder-icon">⌚</div>
                                <div class="placeholder-text"><?php echo htmlspecialchars($watch['category']); ?></div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="watch-badge">
                            <?php echo htmlspecialchars($watch['category']); ?>
                        </div>
                    </div>
                    
                    <div class="watch-info">
                        <h3 class="watch-name"><?php echo htmlspecialchars($watch['name']); ?></h3>
                        <p class="watch-desc"><?php echo htmlspecialchars($watch['description']); ?></p>
                        
                        <div class="watch-footer">
                            <span class="watch-price">$<?php echo number_format($watch['price'], 2); ?></span>
                            
                            <?php if ($watch['stock'] > 0): ?>
                                <form action="index.php#watch-<?php echo $watch['id']; ?>" method="POST" style="margin: 0;">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="watch_id" value="<?php echo $watch['id']; ?>">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary">
                                        Add to Cart
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary" disabled style="cursor: not-allowed; opacity: 0.6;">
                                    Sold Out
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; background: #ffffff; border-radius: 8px; border: 1px dashed var(--border-color);">
                <p style="color: var(--text-muted);">No watches found. Please make sure to import the <code>db_setup.sql</code> file into your phpMyAdmin <code>offhour_db</code> database.</p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
// Include Footer
include_once 'includes/footer.php';
?>
