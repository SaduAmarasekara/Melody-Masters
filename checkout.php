<?php
ob_start();
session_start();
include 'db_connect.php';
include 'navbar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit();
}

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>alert('Your cart is empty!'); window.location.href='shop.php';</script>";
    exit();
}

$user_id = $_SESSION['user_id'];
$total = 0;
$cart_items = [];
$has_physical_item = false; 

foreach ($_SESSION['cart'] as $id => $qty) {
    $res = $conn->query("SELECT * FROM products WHERE product_id = '$id'");
    $p = $res->fetch_assoc();
    
    if(isset($p['product_type']) && $p['product_type'] == 'Physical') {
        $has_physical_item = true;
    }

    $subtotal = $p['price'] * $qty;
    $total += $subtotal;
    $cart_items[] = ['id' => $id, 'name' => $p['product_name'], 'qty' => $qty, 'price' => $p['price'], 'sub' => $subtotal];
}

if (!$has_physical_item) {
    $shipping = 0;
} else {
    $shipping = ($total > 100) ? 0 : 15.00;
}
$grand_total = $total + $shipping;

if (isset($_POST['place_order'])) {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $payment = mysqli_real_escape_string($conn, $_POST['payment_method']);
    $order_status = ($has_physical_item) ? 'Pending' : 'Delivered';

    $conn->begin_transaction();
    try {
        $sql_order = "INSERT INTO orders (user_id, total_amount, shipping_cost, order_status) 
                      VALUES ('$user_id', '$grand_total', '$shipping', '$order_status')";
        $conn->query($sql_order);
        $order_id = $conn->insert_id;

        foreach ($cart_items as $item) {
            $p_id = $item['id'];
            $qty = $item['qty'];
            $u_price = $item['price'];
            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES ('$order_id', '$p_id', '$qty', '$u_price')");
            $conn->query("UPDATE products SET stock_quantity = stock_quantity - $qty WHERE product_id = '$p_id'");
        }

        $conn->commit();
        unset($_SESSION['cart']);
        echo "<script>window.location.href='order_success.php?id=$order_id';</script>";
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Transaction Failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Checkout | Melody Masters</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --teal: #00e5c3; 
            --teal-glow: rgba(0, 229, 195, 0.3);
            --bg: #0a0a0f; 
            --glass: rgba(17, 17, 24, 0.85); 
            --border: rgba(0, 229, 195, 0.15); 
            --success: #00e5a0;
            --text-muted: #5a5a72;
        }

        body { 
            font-family: 'DM Sans', sans-serif; 
            background: var(--bg); 
            color: #e0ddf5; 
            margin: 0; 
            line-height: 1.6;
        }

        /* Ambient Glow */
        body::before {
            content: ''; position: fixed; top: -10%; left: -10%; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(0, 229, 195, 0.05), transparent 70%);
            z-index: -1;
        }

        .checkout-wrapper { 
            max-width: 1300px; 
            margin: 60px auto; 
            padding: 0 30px; 
            display: grid; 
            grid-template-columns: 1.6fr 1fr; 
            gap: 40px; 
        }

        .checkout-card { 
            background: var(--glass); 
            backdrop-filter: blur(25px); 
            border: 1px solid var(--border); 
            border-radius: 40px; 
            padding: 60px; 
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
        }

        .summary-card { 
            background: linear-gradient(165deg, #111118, #0a0a0f); 
            border-radius: 40px; 
            padding: 50px; 
            border: 1px solid var(--border); 
            height: fit-content; 
            position: sticky; 
            top: 100px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.5);
        }

        h2 { 
            font-family: 'Playfair Display', serif; 
            font-size: 1.8rem; 
            margin-bottom: 35px; 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            color: #fff;
        }
        
        h2 i { color: var(--teal); font-size: 1.4rem; }

        .form-group { margin-bottom: 30px; }
        
        label { 
            display: block; 
            margin-bottom: 12px; 
            color: var(--teal); 
            font-weight: 700; 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 2px;
        }

        textarea, select { 
            width: 100%; 
            background: #0d0d15; 
            border: 1px solid var(--border); 
            padding: 18px; 
            border-radius: 18px; 
            color: white; 
            font-family: inherit; 
            resize: none; 
            transition: 0.3s;
            outline: none;
        }

        textarea:focus, select:focus { 
            border-color: var(--teal); 
            background: rgba(0, 229, 195, 0.03);
            box-shadow: 0 0 15px rgba(0, 229, 195, 0.1);
        }

        .item-row { 
            display: flex; 
            justify-content: space-between; 
            margin-bottom: 20px; 
            font-size: 0.9rem; 
            color: #a0a0bc; 
        }

        .total-row { 
            border-top: 1px solid var(--border); 
            padding-top: 25px; 
            margin-top: 25px; 
            font-family: 'Playfair Display', serif;
            font-size: 2rem; 
            font-weight: 700; 
            color: #fff; 
            display: flex; 
            justify-content: space-between; 
        }

        .btn-confirm { 
            background: linear-gradient(135deg, var(--teal), #00bfa0); 
            color: #08080e; 
            border: none; 
            padding: 22px; 
            width: 100%; 
            border-radius: 20px; 
            font-weight: 800; 
            font-size: 1rem; 
            cursor: pointer; 
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); 
            text-transform: uppercase; 
            letter-spacing: 2px; 
            margin-top: 40px; 
            box-shadow: 0 10px 30px var(--teal-glow);
        }

        .btn-confirm:hover { 
            transform: translateY(-5px); 
            filter: brightness(1.1);
            box-shadow: 0 15px 40px var(--teal-glow); 
        }

        .badge-qty { 
            background: rgba(255, 255, 255, 0.1); 
            color: var(--teal); 
            padding: 3px 10px; 
            border-radius: 8px; 
            font-size: 0.7rem; 
            font-weight: 700; 
            margin-left: 8px;
            border: 1px solid var(--border);
        }

        .security-box { 
            margin-top: 40px; 
            padding: 25px; 
            background: rgba(0, 229, 160, 0.05); 
            border: 1px solid rgba(0, 229, 160, 0.2); 
            border-radius: 24px; 
            display: flex; 
            gap: 20px; 
            align-items: center; 
            font-size: 0.8rem; 
            color: var(--success); 
        }

        .security-box i { font-size: 1.8rem; }

        /* Custom Scrollbar for summary list */
        .summary-list::-webkit-scrollbar { width: 5px; }
        .summary-list::-webkit-scrollbar-track { background: transparent; }
        .summary-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }

        @media (max-width: 992px) { 
            .checkout-wrapper { grid-template-columns: 1fr; } 
            .checkout-card, .summary-card { padding: 40px 30px; }
        }
    </style>
</head>
<body>

<div class="checkout-wrapper">
    <div class="checkout-card">
        <h2><i class="fas fa-truck-fast"></i> Logistics & Destination</h2>
        <form method="POST">
            <div class="form-group">
                <label>Shipping Address <?php echo !$has_physical_item ? '<span style="color:var(--text-muted)">(Digital Assets Only)</span>' : ''; ?></label>
                <textarea name="address" rows="5" placeholder="Building, Street, City, ZIP..." <?php echo $has_physical_item ? 'required' : ''; ?>><?php echo !$has_physical_item ? 'Digital Warehouse Delivery - No physical address required for this selection.' : ''; ?></textarea>
            </div>

            <h2 style="margin-top: 60px;"><i class="fas fa-vault"></i> Secure Payment</h2>
            <div class="form-group">
                <label>Method of Settlement</label>
                <select name="payment_method">
                    <option value="COD">cash on Delivery</option>
                    <option value="Card" disabled>Digital Payment Gateway (Coming Soon)</option>
                </select>
            </div>

            <button type="submit" name="place_order" class="btn-confirm">
                Finalize Purchase <i class="fas fa-circle-check" style="margin-left: 10px;"></i>
            </button>
        </form>
    </div>

    <div class="summary-card">
        <h3 style="font-family:'Playfair Display'; color: #fff; margin-top: 0; display: flex; justify-content: space-between; font-size: 1.5rem;">
            Your Selection <span><i class="fas fa-layer-group" style="color:var(--teal)"></i></span>
        </h3>
        <div style="height: 1px; background: var(--border); margin: 25px 0;"></div>
        
        <div class="summary-list" style="max-height: 280px; overflow-y: auto; padding-right: 15px; margin-bottom: 30px;">
            <?php foreach ($cart_items as $item): ?>
                <div class="item-row">
                    <span><?php echo $item['name']; ?> <span class="badge-qty">x<?php echo $item['qty']; ?></span></span>
                    <span style="color: #fff; font-weight: 700;">£<?php echo number_format($item['sub'], 2); ?></span>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="background: rgba(255,255,255,0.02); padding: 25px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.03);">
            <div class="item-row" style="margin-bottom: 12px;">
                <span>Portfolio Subtotal</span>
                <span style="color:#fff;">£<?php echo number_format($total, 2); ?></span>
            </div>
            <div class="item-row" style="margin-bottom: 0;">
                <span>Logistics Fee</span>
                <span style="color: <?php echo ($shipping == 0) ? var_export('--success', true) : '#fff'; ?>; font-weight: 700;">
                    <?php 
                    if (!$has_physical_item) { echo "COMPLIMENTARY"; }
                    else { echo ($shipping == 0) ? "COMPLIMENTARY" : "£" . number_format($shipping, 2); }
                    ?>
                </span>
            </div>

            <div class="total-row">
                <span>Total</span>
                <span>£<?php echo number_format($grand_total, 2); ?></span>
            </div>
        </div>

        <div class="security-box">
            <i class="fas fa-shield-halved"></i>
            <div>
                <strong style="text-transform: uppercase; letter-spacing: 1px;">End-to-End Encryption</strong><br>
                Your transaction is protected by 256-bit SSL security.
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

</body>
</html>