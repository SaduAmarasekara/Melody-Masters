<?php
session_start();
include 'db_connect.php';

// Authentication: Only Admin OR Staff can access
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$current_role = strtolower(trim($_SESSION['role']));

if ($current_role !== 'admin' && $current_role !== 'staff') {
    header("Location: login.php");
    exit();
}

$message = "";

// --- DELETE PRODUCT LOGIC (Admin Only) ---
if (isset($_GET['delete_id']) && $current_role === 'admin') {
    $id = (int)$_GET['delete_id'];
    
    $conn->begin_transaction();

    try {
        // 1. Delete linked records from order_items (Error එක එන්නේ මෙතනින්)
        $stmt_oi = $conn->prepare("DELETE FROM order_items WHERE product_id = ?");
        $stmt_oi->bind_param("i", $id);
        $stmt_oi->execute();
        $stmt_oi->close();

        // 2. Delete linked digital assets
        $stmt1 = $conn->prepare("DELETE FROM digital_products WHERE product_id = ?");
        $stmt1->bind_param("i", $id);
        $stmt1->execute();
        $stmt1->close();

        // 3. Finally, delete the main product
        $stmt2 = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        
        if ($stmt2->affected_rows > 0) {
            $conn->commit();
            $message = "<div class='msg success'><i class='fas fa-check-circle'></i> Product and related records successfully deleted!</div>";
        } else {
            throw new Exception("Product not found.");
        }
        $stmt2->close();

    } catch (Exception $e) {
        $conn->rollback();
        $message = "<div class='msg error'><i class='fas fa-times-circle'></i> Operational Error: Could not delete product due to linked data.</div>";
    }
}

// --- SEARCH LOGIC ---
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : "";

if ($search != "") {
    $query = "SELECT * FROM products WHERE product_name LIKE '%$search%' OR brand LIKE '%$search%' ORDER BY product_id DESC";
} else {
    $query = "SELECT * FROM products ORDER BY product_id DESC";
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Registry | Melody Masters Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root { 
            --teal: #00e5c3; 
            --teal-glow: rgba(0, 229, 195, 0.3);
            --bg: #0a0a0f; 
            --glass: rgba(17, 17, 24, 0.85); 
            --border: rgba(0, 229, 195, 0.15); 
            --text-muted: #5a5a72;
            --danger: #ff4f7b;
            --success: #00e5a0;
            --amber: #e8c86e;
        }

        body { background: var(--bg); color: #e0ddf5; font-family: 'DM Sans', sans-serif; display: flex; margin: 0; overflow-x: hidden; }
        body::before { content: ''; position: fixed; top: -10%; right: -10%; width: 400px; height: 400px; background: radial-gradient(circle, rgba(0, 229, 195, 0.05), transparent 70%); z-index: -1; }
        .main-content { margin-left: 260px; width: calc(100% - 260px); padding: 50px; box-sizing: border-box; min-height: 100vh; }
        .top-section { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header-section { border-left: 3px solid var(--teal); padding-left: 25px; }
        .header-section h2 { font-family: 'Playfair Display', serif; font-size: 2.2rem; margin: 0; background: linear-gradient(to bottom, #fff, #888); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .btn-add { background: var(--teal); color: #000; text-decoration: none; padding: 14px 28px; border-radius: 16px; font-weight: 800; transition: 0.3s; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px; box-shadow: 0 5px 15px var(--teal-glow); display: flex; align-items: center; gap: 10px; }
        .search-box { display: flex; gap: 12px; margin-bottom: 35px; background: var(--glass); padding: 10px; border-radius: 20px; border: 1px solid var(--border); max-width: 550px; backdrop-filter: blur(15px); }
        .search-input { flex: 1; padding: 12px 18px; border: none; background: transparent; color: white; outline: none; font-size: 0.9rem; }
        .btn-search { background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border); padding: 10px 22px; border-radius: 12px; cursor: pointer; font-weight: 700; transition: 0.3s; text-transform: uppercase; font-size: 0.7rem; }
        .glass-card { background: var(--glass); backdrop-filter: blur(25px); border-radius: 35px; padding: 20px; border: 1px solid var(--border); box-shadow: 0 30px 60px rgba(0,0,0,0.4); animation: fadeIn 0.8s ease-out; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; color: var(--teal); padding: 20px; border-bottom: 1px solid var(--border); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2.5px; }
        td { padding: 22px 20px; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 0.95rem; vertical-align: middle; }
        .product-meta { display: flex; flex-direction: column; gap: 4px; }
        .product-name { font-weight: 700; color: #fff; font-size: 1rem; }
        .brand-label { font-size: 0.7rem; color: var(--amber); text-transform: uppercase; letter-spacing: 1.5px; font-weight: 600; }
        .price-text { font-family: 'Playfair Display', serif; font-weight: 700; color: #fff; font-size: 1.2rem; }
        .stock-badge { padding: 6px 16px; border-radius: 50px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 8px; }
        .low-stock { background: rgba(255, 79, 123, 0.1); color: var(--danger); border: 1px solid rgba(255, 79, 123, 0.3); }
        .in-stock { background: rgba(0, 229, 160, 0.1); color: var(--success); border: 1px solid rgba(0, 229, 160, 0.3); }
        .action-btns { display: flex; gap: 12px; justify-content: center; }
        .action-link { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; text-decoration: none; transition: 0.3s; font-size: 1rem; border: 1px solid rgba(255,255,255,0.05); }
        .btn-edit { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .btn-delete { background: rgba(255, 79, 123, 0.1); color: var(--danger); cursor: pointer; }
        .msg { padding: 18px 25px; border-radius: 18px; margin-bottom: 30px; font-size: 0.9rem; display: flex; align-items: center; gap: 12px; }
        .success { background: rgba(0, 229, 160, 0.1); color: var(--success); border: 1px solid rgba(0, 229, 160, 0.2); }
        .error { background: rgba(255, 79, 123, 0.1); color: var(--danger); border: 1px solid rgba(255, 79, 123, 0.2); }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
</head>
<body>

<?php 
if ($current_role === 'admin') {
    include 'admin_sidebar.php';
} else {
    include 'staff_sidebar.php';
}
?>

<div class="main-content">
    <div class="top-section">
        <div class="header-section">
            <h2>Inventory Registry</h2>
            <p style="color: var(--text-muted); margin-top: 5px;">Manage product stock and digital assets (Level: <?php echo ucfirst($current_role); ?>)</p>
        </div>
        <?php if($current_role === 'admin'): ?>
            <a href="add_product.php" class="btn-add"><i class="fas fa-plus"></i> Initialize Product</a>
        <?php endif; ?>
    </div>

    <form method="GET" class="search-box">
        <i class="fas fa-search" style="margin-left: 15px; color: var(--text-muted);"></i>
        <input type="text" name="search" class="search-input" placeholder="Search product identifier or brand..." value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn-search">Filter</button>
        <?php if($search): ?>
            <a href="manage_products.php" style="color: var(--danger); text-decoration: none; font-size: 0.7rem; font-weight: 800; margin-right: 15px; align-self: center;">RESET</a>
        <?php endif; ?>
    </form>

    <?php echo $message; ?>

    <div class="glass-card">
        <table>
            <thead>
                <tr>
                    <th>Asset Description</th>
                    <th>Valuation</th>
                    <th>Availability</th>
                    <th style="text-align: center;">Administrative Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="product-meta">
                                <span class="product-name"><?php echo htmlspecialchars($row['product_name']); ?></span>
                                <span class="brand-label"><?php echo htmlspecialchars($row['brand']); ?></span>
                            </div>
                        </td>
                        <td class="price-text">
                            £<?php echo number_format($row['price'], 2); ?>
                        </td>
                        <td>
                            <?php if($row['stock_quantity'] <= 5): ?>
                                <span class="stock-badge low-stock">
                                    <i class="fas fa-bolt"></i> Low Portfolio: <?php echo $row['stock_quantity']; ?>
                                </span>
                            <?php else: ?>
                                <span class="stock-badge in-stock">
                                    <i class="fas fa-check-circle"></i> Secure: <?php echo $row['stock_quantity']; ?>
                                </span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-btns">
                                <a href="edit_product.php?id=<?php echo $row['product_id']; ?>" class="action-link btn-edit" title="Edit Product">
                                    <i class="fas fa-pen-fancy"></i>
                                </a>
                                
                                <?php if($current_role === 'admin'): ?>
                                    <a href="manage_products.php?delete_id=<?php echo $row['product_id']; ?>" 
                                       class="action-link btn-delete" 
                                       onclick="return confirm('Are you sure you want to delete this product? All related order history records will also be removed.')" title="Delete Product">
                                        <i class="fas fa-trash-can"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 80px; color: var(--text-muted);">
                            <i class="fas fa-archive" style="font-size: 3rem; opacity: 0.1; display: block; margin-bottom: 20px;"></i>
                            Archival record empty for current query.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>