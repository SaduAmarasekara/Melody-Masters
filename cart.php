<?php
ob_start(); 
session_start();
include 'db_connect.php';
include 'navbar.php';

// 1. UPDATE QUANTITIES LOGIC WITH STOCK CHECK
if (isset($_POST['update_cart'])) {
    foreach ($_POST['qty'] as $p_id => $new_qty) {
        $p_id = mysqli_real_escape_string($conn, $p_id);
        
        // Fetch current stock from DB
        $stock_res = $conn->query("SELECT stock_quantity FROM products WHERE product_id = '$p_id'");
        $stock_data = $stock_res->fetch_assoc();
        $available_stock = $stock_data['stock_quantity'] ?? 0;

        if ($new_qty <= 0) {
            unset($_SESSION['cart'][$p_id]);
        } else if ($new_qty > $available_stock) {
            // If user tries to update more than available, set to max available
            $_SESSION['cart'][$p_id] = $available_stock;
            $msg = "Some items were limited to available stock.";
        } else {
            $_SESSION['cart'][$p_id] = $new_qty;
        }
    }
    header("Location: cart.php?msg=" . ($msg ?? 'Cart Updated'));
    exit();
}

// 2. REMOVE ITEM LOGIC
if (isset($_GET['remove'])) {
    $id = $_GET['remove'];
    unset($_SESSION['cart'][$id]);
    header("Location: cart.php?msg=Item Removed");
    exit();
}

// 3. ADD TO CART LOGIC (With Stock Validation)
if (isset($_POST['add_to_cart'])) {
    $p_id = $_POST['product_id'];
    $qty = (int)$_POST['quantity'];
    
    // Check DB Stock
    $stock_res = $conn->query("SELECT stock_quantity FROM products WHERE product_id = '$p_id'");
    $stock_data = $stock_res->fetch_assoc();
    $available_stock = $stock_data['stock_quantity'] ?? 0;

    $current_in_cart = $_SESSION['cart'][$p_id] ?? 0;
    $total_requested = $current_in_cart + $qty;

    if ($total_requested > $available_stock) {
        $_SESSION['cart'][$p_id] = $available_stock;
    } else {
        $_SESSION['cart'][$p_id] = $total_requested;
    }
    
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Bag | Melody Masters</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --gold: #00e5c3; /* Updated to Teal for consistency */
            --gold-muted: rgba(0, 229, 195, 0.1);
            --bg: #0a0a0f; 
            --glass: rgba(255, 255, 255, 0.03); 
            --border: rgba(0, 229, 195, 0.15); 
            --rose: #ff4f7b;
        }
        body { font-family: 'Poppins', sans-serif; background: var(--bg); color: #e0ddf5; margin: 0; }
        
        /* Subtle Glow */
        body::after {
            content: ''; position: fixed; top: 0; right: 0; width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(0,229,195,0.05), transparent 70%);
            z-index: -1;
        }

        .wrapper { max-width: 1200px; margin: 50px auto; padding: 0 20px; display: grid; grid-template-columns: 1.8fr 1fr; gap: 40px; }
        
        .cart-container { 
            background: rgba(17, 17, 24, 0.8); 
            backdrop-filter: blur(20px); 
            border: 1px solid var(--border); 
            border-radius: 30px; 
            padding: 40px; 
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        
        h2 { font-size: 2.2rem; margin-bottom: 30px; font-weight: 700; color: #fff; }
        
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding-bottom: 20px; color: #5a5a72; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 2px; border-bottom: 1px solid var(--border); }
        td { padding: 25px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        
        .item-info { display: flex; align-items: center; gap: 20px; }
        .item-img { width: 80px; height: 80px; object-fit: cover; border-radius: 18px; border: 1px solid var(--border); background: #111118; }
        
        .qty-input { 
            background: #0d0d15; 
            border: 1px solid var(--border); 
            color: #fff; 
            padding: 10px; 
            width: 60px; 
            border-radius: 12px; 
            text-align: center; 
            outline: none;
            transition: 0.3s;
        }
        .qty-input:focus { border-color: var(--gold); box-shadow: 0 0 10px rgba(0,229,195,0.1); }
        
        .stock-warning { color: var(--rose); font-size: 0.7rem; display: block; margin-top: 5px; font-weight: 500; }

        .btn-update { 
            background: transparent; 
            border: 1.5px solid var(--gold); 
            color: var(--gold); 
            padding: 12px 24px; 
            border-radius: 14px; 
            cursor: pointer; 
            font-weight: 600; 
            transition: 0.3s; 
            margin-top: 25px;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 1px;
        }
        .btn-update:hover { background: var(--gold); color: #08080e; box-shadow: 0 0 20px rgba(0,229,195,0.3); }
        
        .summary-card { 
            background: linear-gradient(165deg, #111118, #0a0a0f); 
            border-radius: 30px; 
            padding: 40px; 
            border: 1px solid var(--border); 
            height: fit-content; 
            position: sticky; 
            top: 30px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }
        
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 20px; font-size: 1rem; color: #a0a0bc; }
        .total-row { border-top: 1px solid var(--border); padding-top: 20px; margin-top: 20px; font-size: 1.8rem; font-weight: 700; color: var(--gold); }
        
        .btn-checkout { 
            background: linear-gradient(135deg, var(--gold), #00bfa0); 
            color: #08080e; 
            display: block; 
            text-align: center; 
            padding: 18px; 
            border-radius: 16px; 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 1rem; 
            margin-top: 30px; 
            transition: 0.3s; 
            text-transform: uppercase; 
            letter-spacing: 1.5px; 
        }
        .btn-checkout:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(0, 229, 195, 0.4); filter: brightness(1.1); }
        
        .promo-box { 
            background: var(--gold-muted); 
            border: 1px dashed var(--gold); 
            padding: 18px; 
            border-radius: 15px; 
            color: var(--gold); 
            font-size: 0.85rem; 
            margin-bottom: 30px; 
            text-align: center; 
        }
        
        .remove-btn { color: #5a5a72; text-decoration: none; font-size: 1.3rem; transition: 0.3s; }
        .remove-btn:hover { color: var(--rose); transform: scale(1.1); }

        @media (max-width: 992px) { .wrapper { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="cart-container">
        <h2>Shopping Bag</h2>

        <?php 
        if (!empty($_SESSION['cart'])): 
            $total = 0;
            $has_physical_item = false;
        ?>
            <form method="POST" id="cartForm">
                <table>
                    <thead>
                        <tr>
                            <th>Instrument</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        foreach ($_SESSION['cart'] as $id => $qty): 
                            $id = mysqli_real_escape_string($conn, $id);
                            $res = $conn->query("SELECT * FROM products WHERE product_id = '$id'");
                            $product = $res->fetch_assoc();
                            if(!$product) continue;

                            $available = $product['stock_quantity'];
                            if(isset($product['product_type']) && $product['product_type'] == 'Physical') {
                                $has_physical_item = true;
                            }

                            $subtotal = $product['price'] * $qty;
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td>
                                <div class="item-info">
                                    <img src="uploads/<?php echo $product['product_image']; ?>" class="item-img">
                                    <div>
                                        <div style="font-weight:600; font-size:1.05rem; color:#fff;"><?php echo $product['product_name']; ?></div>
                                        <div style="color:var(--gold); font-size:0.75rem; text-transform:uppercase; letter-spacing:1px;"><?php echo $product['brand']; ?></div>
                                        <small style="color:#5a5a72; font-size:0.7rem;">Stock: <?php echo $available; ?> units</small>
                                    </div>
                                </div>
                            </td>
                            <td>£<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <input type="number" name="qty[<?php echo $id; ?>]" 
                                       value="<?php echo $qty; ?>" 
                                       min="1" 
                                       max="<?php echo $available; ?>" 
                                       class="qty-input">
                                <?php if($qty >= $available): ?>
                                    <span class="stock-warning">Max reached</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight:600; color:#fff;">£<?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $id; ?>" class="remove-btn" onclick="return confirm('Remove this item?')">
                                    <i class="fas fa-times"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <button type="submit" name="update_cart" class="btn-update">
                    <i class="fas fa-sync-alt" style="margin-right:8px;"></i> Update Cart
                </button>
            </form>
        <?php else: ?>
            <div style="text-align: center; padding: 80px 0;">
                <i class="fas fa-shopping-basket" style="font-size: 4rem; color: var(--gold); opacity: 0.2; margin-bottom: 25px;"></i>
                <p style="color:#a0a0bc; font-size:1.1rem;">Your bag is empty.</p>
                <a href="shop.php" class="btn-update" style="display:inline-block; text-decoration:none;">Discover Instruments</a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['cart'])): ?>
    <div class="summary-card">
        <h3 style="margin-top:0; color:#fff; font-size:1.4rem; margin-bottom:25px;">Summary</h3>
        
        <?php 
        $shipping_limit = 100;
        if (!$has_physical_item) {
            $shipping = 0;
            $shipping_msg = "Digital Delivery (Free)";
        } else {
            $shipping = ($total > $shipping_limit) ? 0 : 15.00; 
            $shipping_msg = ($shipping == 0) ? "FREE" : "£" . number_format($shipping, 2);
        }
        ?>

        <?php if($has_physical_item): ?>
            <?php if($total < $shipping_limit): ?>
                <div class="promo-box">
                    <i class="fas fa-bolt" style="margin-right:8px;"></i> Spend <b>£<?php echo ($shipping_limit - $total); ?></b> more for <b>Free Shipping!</b>
                </div>
            <?php else: ?>
                <div class="promo-box" style="background:rgba(0, 229, 160, 0.08); border-color:#00e5a0; color:#00e5a0;">
                    <i class="fas fa-check-circle" style="margin-right:8px;"></i> You've unlocked <b>Free Shipping!</b>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="summary-row">
            <span>Subtotal</span>
            <span>£<?php echo number_format($total, 2); ?></span>
        </div>
        <div class="summary-row">
            <span>Shipping</span>
            <span><?php echo $shipping_msg; ?></span>
        </div>

        <div class="summary-row total-row">
            <span>Total</span>
            <span>£<?php echo number_format($total + $shipping, 2); ?></span>
        </div>

        <a href="checkout.php" class="btn-checkout">Proceed to Checkout</a>
        
        <div style="margin-top:30px; text-align:center; color:#5a5a72; font-size:0.75rem; letter-spacing:0.5px;">
            <i class="fas fa-lock" style="margin-right:5px;"></i> Secure 256-bit SSL Encryption
        </div>
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>

<script>
    // Prevent typing numbers higher than stock manually
    document.querySelectorAll('.qty-input').forEach(input => {
        input.addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            if (parseInt(this.value) > max) {
                this.value = max;
                alert("Only " + max + " items available in stock.");
            }
        });
    });
</script>

</body>
</html>