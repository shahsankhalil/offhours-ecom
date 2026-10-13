<?php
// OffHour Watch Store - Place Order Backend (place_order.php)
require_once 'config/db.php';
require_once 'config/security.php';

ensure_session();

// Redirect back if table is accessed without standard post request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php");
    exit;
}
verify_csrf();

$cart = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

// 1. Sanitize & Validate Form Values safely
$name = isset($_POST['name']) ? trim(strip_tags($_POST['name'])) : '';
$email = isset($_POST['email']) ? filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) : '';
$phone = isset($_POST['phone']) ? trim(strip_tags($_POST['phone'])) : '';
$address = isset($_POST['address']) ? trim(strip_tags($_POST['address'])) : '';
$city = isset($_POST['city']) ? trim(strip_tags($_POST['city'])) : '';
$postal_code = isset($_POST['postal_code']) ? trim(strip_tags($_POST['postal_code'])) : '';
$payment_method = 'Cash on Delivery'; // Fixed value

if (empty($name) || empty($email) || empty($phone) || empty($address) || empty($city) || empty($postal_code)) {
    die("Error: Please supply all mandatory contact and delivery fields in correct format to finalize checkout.");
}

// Calculate grand total cost
$order_total = 0;
foreach ($cart as $item) {
    $order_total += $item['price'] * $item['quantity'];
}

// 2. Perform Database Transactions
try {
    // If PDO exists but tables don't, check and explain
    $pdo->beginTransaction();

    // A. Insert into Orders Table
    $sql_order = "INSERT INTO orders (name, email, phone, address, city, postal_code, payment_method, total, status, created_at) 
                  VALUES (:name, :email, :phone, :address, :city, :postal_code, :pm, :total, 'Pending', NOW())";
    
    $stmt_order = $pdo->prepare($sql_order);
    $stmt_order->execute([
        ':name' => $name,
        ':email' => $email,
        ':phone' => $phone,
        ':address' => $address,
        ':city' => $city,
        ':postal_code' => $postal_code,
        ':pm' => $payment_method,
        ':total' => $order_total
    ]);
    
    $new_order_id = $pdo->lastInsertId();

    // B. Refresh each cart line from the database before storing the order.
    $sql_item = "INSERT INTO order_items (order_id, watch_id, watch_name, quantity, price) 
                 VALUES (:order_id, :watch_id, :watch_name, :quantity, :price)";
    $stmt_item = $pdo->prepare($sql_item);

    $stmt_watch = $pdo->prepare("SELECT id, name, price, stock, image FROM watches WHERE id = ? FOR UPDATE");
    $stmt_stock = $pdo->prepare("UPDATE watches SET stock = stock - ? WHERE id = ?");
    $verified_total = 0;

    foreach ($cart as $item) {
        $stmt_watch->execute([$item['id']]);
        $watch = $stmt_watch->fetch();
        if (!$watch || (int)$watch['stock'] < (int)$item['quantity']) {
            throw new RuntimeException('One or more selected watches are no longer available in the requested quantity.');
        }

        $quantity = (int)$item['quantity'];
        $verified_total += (float)$watch['price'] * $quantity;
        // Insert item record
        $stmt_item->execute([
            ':order_id' => $new_order_id,
            ':watch_id' => $watch['id'],
            ':watch_name' => $watch['name'],
            ':quantity' => $quantity,
            ':price' => $watch['price']
        ]);

        // Deduct inventory
        $stmt_stock->execute([$quantity, $watch['id']]);
    }

    $pdo->prepare("UPDATE orders SET total = ? WHERE id = ?")->execute([$verified_total, $new_order_id]);

    $pdo->commit();

    // 3. Fetch newly inserted order details to ensure data integrity
    $stmt_fetch_order = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt_fetch_order->execute([$new_order_id]);
    $inserted_order = $stmt_fetch_order->fetch();

    $stmt_fetch_items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt_fetch_items->execute([$new_order_id]);
    $inserted_items = $stmt_fetch_items->fetchAll();

    // Save temporary receipt fields inside Session for the success page.
    $_SESSION['last_order'] = [
        'order' => $inserted_order,
        'items' => $inserted_items,
        'status' => 'Order saved successfully.'
    ];

    // Clear user shopping cart session
    $_SESSION['cart'] = [];

    // Redirect to Order success page
    header("Location: order_success.php?order_id=" . $new_order_id);
    exit;

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    // Display elegant debugger info panel to helper users if they have database configuration mishaps in XAMPP
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Order Setup Error - OffHour</title>
        <!-- Google Fonts: Inter -->
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
        <style>
            body { font-family: 'Inter', sans-serif; background-color: #f8fafc; color: #0f172a; padding: 40px 20px; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
            .error-card { background: white; max-width: 600px; padding: 30px; border-radius: 12px; border: 1px solid #fee2e2; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05); }
            h1 { font-family: 'Space Grotesk', sans-serif; color: #dc2626; font-size: 22px; margin-top: 0; margin-bottom: 12px; }
            p { font-size: 14px; line-height: 1.5; color: #475569; margin-bottom: 20px; }
            code, pre { font-family: monospace; background-color: #f1f5f9; padding: 4px 8px; border-radius: 4px; font-size: 13px; color: #0284c7; }
            pre { padding: 12px; display: block; overflow-x: auto; font-size: 12px; line-height: 1.4; border-left: 4px solid #0284c7; }
            .steps { list-style: none; padding: 0; margin-bottom: 24px; font-size: 14px; }
            .steps li { margin-bottom: 8px; padding-left: 24px; position: relative; }
            .steps li::before { content: "✓"; color: #0284c7; font-weight: bold; position: absolute; left: 0; }
            .btn { display: inline-block; padding: 10xp 20px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px; background-color: #0284c7; color: white; border: none; cursor: pointer; padding: 10px 18px; }
            .btn:hover { background-color: #0369a1; }
        </style>
    </head>
    <body>
        <div class="error-card">
            <h1>Database Setup Required</h1>
            <p>We encountered a database exception during checkout. This is typically due to missing tables inside MySQL. Let's fix this in your XAMPP installation:</p>
            
            <ul class="steps">
                <li>Make sure inside XAMPP Control Panel, <strong>Apache</strong> and <strong>MySQL</strong> are started.</li>
                <li>Visit <a href="http://localhost/phpmyadmin" target="_blank" style="color: #0284c7; font-weight: 600;">http://localhost/phpmyadmin</a> in your web browser.</li>
                <li>Create a brand new database named <code>offhour_db</code> in phpMyAdmin.</li>
                <li>Click <strong>Import</strong> tab, click choose file, choose the file <code>offhour/db_setup.sql</code>, and click <strong>Go / Import</strong>.</li>
            </ul>

            <p><strong>Technical Exception Details:</strong></p>
            <pre><?php echo htmlspecialchars($e->getMessage()); ?></pre>

            <div style="margin-top: 30px;">
                <a href="checkout.php" class="btn">Retrying Checkout</a>
                <a href="index.php" style="margin-left: 14px; color: #64748b; font-size: 14px; font-weight: 600; text-decoration: none;">Return Home</a>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
