<?php
require_once __DIR__ . '/_bootstrap.php';

$action = $_GET['action'] ?? 'list';
$watch_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$errors = [];
$watch = ['id' => '', 'name' => '', 'description' => '', 'category' => '', 'price' => '', 'stock' => '', 'image' => ''];

if ($action === 'delete' && $watch_id && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $stmt = $pdo->prepare('DELETE FROM watches WHERE id = ?');
    $stmt->execute([$watch_id]);
    admin_flash('success', 'Watch deleted.');
    header('Location: watches.php');
    exit;
}

if ($action === 'edit' && $watch_id) {
    $stmt = $pdo->prepare('SELECT * FROM watches WHERE id = ?');
    $stmt->execute([$watch_id]);
    $watch = $stmt->fetch() ?: $watch;
    if (!$watch['id']) {
        admin_flash('error', 'Watch not found.');
        header('Location: watches.php');
        exit;
    }
}

if (($action === 'new' || $action === 'edit') && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $watch['name'] = trim($_POST['name'] ?? '');
    $watch['description'] = trim($_POST['description'] ?? '');
    $watch['category'] = trim($_POST['category'] ?? '');
    $watch['price'] = (float)($_POST['price'] ?? 0);
    $watch['stock'] = (int)($_POST['stock'] ?? 0);
    $watch['image'] = trim($_POST['image'] ?? '');
    if (!empty($_FILES['image_file']['name'])) {
        $allowed_types = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
        $file_type = mime_content_type($_FILES['image_file']['tmp_name']);
        if ($_FILES['image_file']['error'] !== UPLOAD_ERR_OK || !isset($allowed_types[$file_type]) || $_FILES['image_file']['size'] > 3 * 1024 * 1024) {
            $errors[] = 'Image must be a JPG, PNG, or WEBP file smaller than 3 MB.';
        } else {
            $filename = bin2hex(random_bytes(12)) . '.' . $allowed_types[$file_type];
            $target = __DIR__ . '/uploads/' . $filename;
            if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target)) {
                $watch['image'] = 'admin/uploads/' . $filename;
            } else {
                $errors[] = 'The image could not be saved.';
            }
        }
    }
    if ($watch['name'] === '') $errors[] = 'Name is required.';
    if ($watch['description'] === '') $errors[] = 'Description is required.';
    if ($watch['category'] === '') $errors[] = 'Category is required.';
    if ($watch['price'] <= 0) $errors[] = 'Price must be greater than zero.';
    if ($watch['stock'] < 0) $errors[] = 'Stock cannot be negative.';
    if (!$errors) {
        if ($action === 'edit') {
            $stmt = $pdo->prepare('UPDATE watches SET name = ?, description = ?, category = ?, price = ?, image = ?, stock = ? WHERE id = ?');
            $stmt->execute([$watch['name'], $watch['description'], $watch['category'], $watch['price'], $watch['image'] ?: null, $watch['stock'], $watch['id']]);
            admin_flash('success', 'Watch updated.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO watches (name, description, category, price, image, stock) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$watch['name'], $watch['description'], $watch['category'], $watch['price'], $watch['image'] ?: null, $watch['stock']]);
            admin_flash('success', 'Watch added.');
        }
        header('Location: watches.php');
        exit;
    }
}

admin_page_start($action === 'list' ? 'Watches' : ($action === 'edit' ? 'Edit watch' : 'Add watch'));
if ($action === 'new' || $action === 'edit'):
?>
<div class="admin-heading"><div><p class="eyebrow">Catalogue</p><h1><?php echo $action === 'edit' ? 'Edit watch' : 'Add watch'; ?></h1></div><a href="watches.php" class="btn btn-outline">Back to watches</a></div>
<?php if ($errors): ?><div class="alert alert-error"><?php echo htmlspecialchars(implode(' ', $errors)); ?></div><?php endif; ?>
<form class="admin-panel admin-form" method="post" enctype="multipart/form-data">
    <?php echo csrf_field(); ?>
    <div class="form-group-grid"><div class="form-group"><label for="name">Name</label><input class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($watch['name']); ?>" required></div><div class="form-group"><label for="category">Category</label><input class="form-control" id="category" name="category" value="<?php echo htmlspecialchars($watch['category']); ?>" required></div><div class="form-group form-group-full"><label for="description">Description</label><textarea class="form-control" id="description" name="description" rows="4" required><?php echo htmlspecialchars($watch['description']); ?></textarea></div><div class="form-group"><label for="price">Price</label><input class="form-control" id="price" name="price" type="number" min="0.01" step="0.01" value="<?php echo htmlspecialchars($watch['price']); ?>" required></div><div class="form-group"><label for="stock">Stock</label><input class="form-control" id="stock" name="stock" type="number" min="0" value="<?php echo htmlspecialchars($watch['stock']); ?>" required></div><div class="form-group"><label for="image_file">Upload image</label><input class="form-control" id="image_file" name="image_file" type="file" accept="image/jpeg,image/png,image/webp"></div><div class="form-group"><label for="image">Or image path / URL</label><input class="form-control" id="image" name="image" value="<?php echo htmlspecialchars($watch['image']); ?>" placeholder="images/watch.jpg"></div></div>
    <button class="btn btn-primary" type="submit">Save watch</button>
</form>
<?php else:
    $watches = $pdo->query('SELECT * FROM watches ORDER BY created_at DESC, id DESC')->fetchAll();
?>
<div class="admin-heading"><div><p class="eyebrow">Catalogue</p><h1>Watches</h1></div><a href="watches.php?action=new" class="btn btn-primary">Add watch</a></div>
<div class="admin-panel table-wrap"><table class="admin-table"><thead><tr><th>Watch</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead><tbody><?php foreach ($watches as $item): ?><tr><td><strong><?php echo htmlspecialchars($item['name']); ?></strong><small><?php echo htmlspecialchars($item['description']); ?></small></td><td><?php echo htmlspecialchars($item['category']); ?></td><td>$<?php echo number_format($item['price'], 2); ?></td><td><?php echo (int)$item['stock']; ?></td><td class="action-links"><a href="watches.php?action=edit&id=<?php echo $item['id']; ?>">Edit</a><form method="post" action="watches.php?action=delete&id=<?php echo $item['id']; ?>" onsubmit="return confirm('Delete this watch?');"><?php echo csrf_field(); ?><button type="submit">Delete</button></form></td></tr><?php endforeach; ?></tbody></table></div>
<?php endif; admin_page_end(); ?>