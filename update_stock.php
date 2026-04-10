<?php
include 'db_connect.php';
include 'navbar.php'; 

// 1. ACCESS CONTROL - Staff or admin only
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Staff' && $_SESSION['role'] !== 'Admin')) {
    header("Location: login.php");
    exit();
}

// 2. UPDATE LOGIC WITH VALIDATION
if (isset($_POST['update'])) {
    $p_id = mysqli_real_escape_string($conn, $_POST['p_id']);
    $new_stock = mysqli_real_escape_string($conn, $_POST['stock_qty']);
    
    if ($new_stock >= 0) {
        $sql = "UPDATE products SET stock_quantity = '$new_stock' WHERE product_id = '$p_id'";
        if ($conn->query($sql)) {
            header("Location: staff_dashboard.php?success=Stock updated for " . urlencode($_POST['p_name']));
            exit();
        }
    } else {
        $error = "Stock cannot be a negative value!";
    }
}

// 3. FETCH CURRENT DATA
$id = mysqli_real_escape_string($conn, $_GET['id']);
$res = $conn->query("SELECT product_name, stock_quantity FROM products WHERE product_id = '$id'");
$product = $res->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Adjust Inventory | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --teal: #00e5c3;
            --teal-glow: rgba(0, 229, 195, 0.3);
            --bg: #0a0a0f;
            --glass: rgba(17, 17, 24, 0.85);
            --border: rgba(0, 229, 195, 0.15);
            --danger: #ff4f7b;
            --text-muted: #5a5a72;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg);
            color: #e0ddf5;
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Ambient Glow Background */
        body::before {
            content: ''; position: fixed; top: -10%; right: -10%; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(0, 229, 195, 0.05), transparent 70%);
            z-index: -1;
        }

        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 60px 20px;
        }

        .update-box { 
            background: var(--glass); 
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 50px; 
            border-radius: 40px; 
            border: 1px solid var(--border);
            box-shadow: 0 40px 100px rgba(0,0,0,0.6); 
            width: 100%; 
            max-width: 450px; 
            text-align: center;
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
            color: #fff; 
            margin-bottom: 10px; 
        }

        .product-label { 
            color: var(--teal); 
            font-size: 0.9rem; 
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
            margin-bottom: 35px; 
            display: block; 
        }

        .input-group { text-align: left; margin-bottom: 30px; }
        
        label { 
            font-size: 0.75rem;
            font-weight: 700; 
            color: var(--text-muted); 
            display: block; 
            margin-bottom: 12px; 
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        input[type="number"] {
            width: 100%;
            padding: 18px;
            background: #0d0d15;
            border: 1px solid var(--border);
            border-radius: 18px;
            font-size: 1.5rem;
            color: #fff;
            text-align: center;
            box-sizing: border-box;
            outline: none;
            transition: 0.3s;
            font-family: 'Playfair Display', serif;
        }

        input[type="number"]:focus { 
            border-color: var(--teal); 
            box-shadow: 0 0 20px rgba(0, 229, 195, 0.1);
        }

        .btn-update {
            background: linear-gradient(135deg, var(--teal), #00bfa0);
            color: #08080e;
            border: none;
            padding: 18px;
            width: 100%;
            border-radius: 20px;
            font-weight: 800;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            cursor: pointer;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 25px var(--teal-glow);
        }

        .btn-update:hover { 
            transform: translateY(-5px); 
            filter: brightness(1.1);
            box-shadow: 0 15px 40px var(--teal-glow); 
        }

        .back-link { 
            display: inline-block; 
            margin-top: 25px; 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 0.8rem; 
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .back-link:hover { color: #fff; transform: translateX(-5px); }

        /* Stock Warning Badge */
        .warning-box {
            background: rgba(232, 200, 110, 0.05);
            color: #e8c86e;
            padding: 15px;
            border-radius: 20px;
            margin-bottom: 30px;
            font-size: 0.85rem;
            border: 1px solid rgba(232, 200, 110, 0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
        }

        .error-msg {
            color: var(--danger);
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: block;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="main-content">
        <div class="update-box">
            <h2>Inventory Adjustment</h2>
            <span class="product-label"><?php echo htmlspecialchars($product['product_name']); ?></span>

            <?php if(isset($error)): ?>
                <span class="error-msg"><i class="fas fa-circle-exclamation"></i> <?php echo $error; ?></span>
            <?php endif; ?>

            <?php if($product['stock_quantity'] < 5): ?>
                <div class="warning-box">
                    <i class="fas fa-triangle-exclamation" style="font-size: 1.2rem;"></i>
                    <span><strong>Low Stock Alert</strong><br>Only <?php echo $product['stock_quantity']; ?> units remaining in the showroom.</span>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="p_id" value="<?php echo $id; ?>">
                <input type="hidden" name="p_name" value="<?php echo $product['product_name']; ?>">
                
                <div class="input-group">
                    <label>Updated Stock Quantity</label>
                    <input type="number" name="stock_qty" value="<?php echo $product['stock_quantity']; ?>" min="0" required>
                </div>

                <button type="submit" name="update" class="btn-update">Commit Changes</button>
                <a href="staff_dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Return to Directorate</a>
            </form>
        </div>
    </div>

    <?php include 'footer.php'; ?>

</body>
</html>