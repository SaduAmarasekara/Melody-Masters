<?php
session_start();
include 'db_connect.php';

// Authentication: Only Admin or Staff can edit products
if (!isset($_SESSION['role']) || (strtolower(trim($_SESSION['role'])) !== 'admin' && strtolower(trim($_SESSION['role'])) !== 'staff')) {
    header("Location: login.php");
    exit();
}

$message = "";

// 1. Fetch current product data to show in the form
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    
    if (!$product) {
        header("Location: manage_products.php");
        exit();
    }
} else {
    header("Location: manage_products.php");
    exit();
}

// 2. Handle the Update Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_product'])) {
    $name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock_quantity']; 
    
    $update_stmt = $conn->prepare("UPDATE products SET product_name = ?, brand = ?, price = ?, stock_quantity = ? WHERE product_id = ?");
    $update_stmt->bind_param("ssdii", $name, $brand, $price, $stock, $id);
    
    if ($update_stmt->execute()) {
        $message = "<div class='msg success'><i class='fas fa-check-circle'></i> Product metadata synchronized successfully!</div>";
        $product['product_name'] = $name;
        $product['brand'] = $brand;
        $product['price'] = $price;
        $product['stock_quantity'] = $stock;
    } else {
        $message = "<div class='msg error'><i class='fas fa-times-circle'></i> Operational Failure: Update rejected by system.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Asset | Melody Masters Admin</title>
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
            --success: #00e5a0;
            --danger: #ff4f7b;
            --amber: #e8c86e;
        }

        body { 
            background: var(--bg); 
            color: #e0ddf5; 
            font-family: 'DM Sans', sans-serif; 
            display: flex; 
            margin: 0; 
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Ambient Glow */
        body::before {
            content: ''; position: fixed; top: -10%; right: -10%; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(0, 229, 195, 0.05), transparent 70%);
            z-index: -1;
        }

        .main-content { 
            margin-left: 260px; 
            width: calc(100% - 260px); 
            padding: 50px; 
            box-sizing: border-box; 
            display: flex; 
            justify-content: center; 
            align-items: center;
        }

        .form-card { 
            background: var(--glass); 
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 60px; 
            border-radius: 40px; 
            width: 100%; 
            max-width: 550px; 
            border: 1px solid var(--border); 
            box-shadow: 0 40px 100px rgba(0,0,0,0.6);
            position: relative;
            animation: cardFade 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardFade {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 { 
            font-family: 'Playfair Display', serif; 
            font-size: 2.2rem; 
            margin: 0; 
            background: linear-gradient(to bottom, #fff, #888);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .header-meta {
            color: var(--teal);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 700;
            margin-top: 10px;
            display: block;
            text-align: center;
        }

        .form-group { margin-top: 35px; text-align: left; }
        
        label { 
            display: block; 
            color: var(--text-muted); 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            letter-spacing: 2px;
            font-weight: 700;
            margin-bottom: 12px; 
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }

        .input-wrapper i {
            position: absolute;
            left: 20px;
            color: var(--teal);
            font-size: 0.9rem;
        }

        input { 
            width: 100%; 
            padding: 16px 16px 16px 50px; 
            background: #0d0d15; 
            border: 1px solid var(--border); 
            color: white; 
            border-radius: 18px; 
            box-sizing: border-box; 
            font-size: 0.95rem;
            outline: none;
            transition: 0.3s;
        }

        input:focus { 
            border-color: var(--teal); 
            background: rgba(0, 229, 195, 0.03);
            box-shadow: 0 0 15px rgba(0, 229, 195, 0.1);
        }

        .btn-update { 
            background: linear-gradient(135deg, var(--teal), #00bfa0); 
            color: #08080e; 
            border: none; 
            padding: 20px; 
            border-radius: 18px; 
            font-weight: 800; 
            cursor: pointer; 
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 0.85rem;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 25px var(--teal-glow);
            margin-top: 10px;
        }

        .btn-update:hover { 
            transform: translateY(-5px); 
            filter: brightness(1.1);
            box-shadow: 0 15px 35px var(--teal-glow); 
        }

        .back-link { 
            display: block; 
            text-align: center; 
            margin-top: 25px; 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 0.8rem; 
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
            font-weight: 700;
        }

        .back-link:hover { color: #fff; transform: translateX(-5px); }

        .msg { 
            padding: 18px; 
            border-radius: 16px; 
            margin-bottom: 30px; 
            font-size: 0.85rem; 
            text-align: left; 
            display: flex; 
            align-items: center; 
            gap: 12px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

        .success { background: rgba(0, 229, 160, 0.1); color: var(--success); border: 1px solid rgba(0, 229, 160, 0.2); }
        .error { background: rgba(255, 79, 123, 0.1); color: var(--danger); border: 1px solid rgba(255, 79, 123, 0.2); }

        @media (max-width: 1024px) {
            .main-content { margin-left: 0; width: 100%; padding: 30px; }
            .form-card { padding: 40px; }
        }
    </style>
</head>
<body>

<?php 
// Logic to include correct sidebar based on role
if (isset($_SESSION['role']) && strtolower($_SESSION['role']) === 'admin') {
    include 'admin_sidebar.php';
} else {
    include 'staff_sidebar.php';
}
?>

<div class="main-content">
    <div class="form-card">
        <h2><i class="fas fa-pen-nib"></i> Asset Auditor</h2>
        <span class="header-meta">Inventory Ref: #PROD-<?php echo str_pad($id, 4, '0', STR_PAD_LEFT); ?></span>
        <div style="height: 1px; background: var(--border); margin: 30px 0;"></div>

        <?php echo $message; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Instrument Designation</label>
                <div class="input-wrapper">
                    <i class="fas fa-guitar"></i>
                    <input type="text" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>" required placeholder="Product name">
                </div>

                <label>Manufacturer / Brand</label>
                <div class="input-wrapper">
                    <i class="fas fa-copyright"></i>
                    <input type="text" name="brand" value="<?php echo htmlspecialchars($product['brand']); ?>" required placeholder="Brand label">
                </div>

                <label>Valuation (GBP £)</label>
                <div class="input-wrapper">
                    <i class="fas fa-sterling-sign"></i>
                    <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required placeholder="0.00">
                </div>

                <label>Portfolio Stock Balance</label>
                <div class="input-wrapper">
                    <i class="fas fa-boxes-stacked"></i>
                    <input type="number" name="stock_quantity" value="<?php echo $product['stock_quantity']; ?>" required placeholder="Units available">
                </div>
            </div>

            <button type="submit" name="update_product" class="btn-update">Update Product Information</button>
        </form>
        
        <a href="manage_products.php" class="back-link">
            <i class="fas fa-arrow-left-long"></i> cancel and go back
        </a>
    </div>
</div>

</body>
</html>