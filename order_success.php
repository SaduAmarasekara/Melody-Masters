<?php
ob_start();
session_start();
include 'db_connect.php';
include 'navbar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if Order ID is provided in URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$order_id = mysqli_real_escape_string($conn, $_GET['id']);

// Fetch order and customer details
$sql = "SELECT o.*, u.full_name, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = '$order_id'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "<div style='color:white; text-align:center; margin-top:50px;'><h2>Order Not Found!</h2></div>";
    exit();
}

$order = $result->fetch_assoc();

/**
 * UPDATED QUERY: 
 * Included 'p.product_type' and 'dp.file_path' to support digital downloads.
 */
$items_sql = "SELECT oi.*, p.product_name, p.product_image, p.product_type, c.category_name, dp.file_path 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.product_id 
              JOIN categories c ON p.category_id = c.category_id
              LEFT JOIN digital_products dp ON p.product_id = dp.product_id
              WHERE oi.order_id = '$order_id'";
$items_result = $conn->query($items_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Receipt | Melody Masters</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --teal: #00e5c3; 
            --bg: #0a0a0f; 
            --card: rgba(17, 17, 24, 0.9); 
            --border: rgba(0, 229, 195, 0.15); 
            --success: #00e5a0; 
            --text-muted: #5a5a72;
        }

        body { 
            font-family: 'DM Sans', sans-serif; 
            background: var(--bg); 
            color: #e0ddf5; 
            margin: 0; 
            -webkit-print-color-adjust: exact;
        }

        /* Ambient Background Glow */
        body::before {
            content:''; position:fixed; top:-10%; right:-10%; width:400px; height:400px;
            background: radial-gradient(circle, rgba(0, 229, 195, 0.05), transparent 70%);
            z-index: -1;
        }

        .receipt-container { max-width: 1000px; margin: 60px auto; padding: 0 20px; }

        .premium-card { 
            background: var(--card); 
            backdrop-filter: blur(25px); 
            border: 1px solid var(--border); 
            border-radius: 40px; 
            padding: 60px; 
            position: relative; 
            box-shadow: 0 30px 70px rgba(0,0,0,0.5);
            overflow: hidden;
        }

        /* Diagonal Accent Line */
        .premium-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 5px;
            background: linear-gradient(90deg, transparent, var(--teal), transparent);
        }

        .paid-stamp { 
            position: absolute; 
            top: 50px; 
            right: 50px; 
            border: 3px solid var(--success); 
            color: var(--success); 
            padding: 12px 30px; 
            border-radius: 15px; 
            font-weight: 900; 
            transform: rotate(12deg); 
            font-size: 1.2rem; 
            text-transform: uppercase; 
            letter-spacing: 2px;
            opacity: 0.8;
            background: rgba(0, 229, 160, 0.05);
            box-shadow: 0 0 20px rgba(0, 229, 160, 0.1);
        }

        h1 { 
            font-family: 'Playfair Display', serif; 
            font-size: 2.2rem; 
            background: linear-gradient(135deg, #fff, var(--teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
            letter-spacing: 1px;
        }

        .meta-label { color: var(--teal); font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px; display: block; }

        table { width: 100%; border-collapse: collapse; margin-top: 50px; }
        th { text-align: left; padding: 20px; color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 1px solid var(--border); }
        td { padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.03); }

        .btn-download { 
            background: var(--teal); 
            color: #08080e; 
            padding: 8px 18px; 
            border-radius: 10px; 
            text-decoration: none; 
            font-size: 0.75rem; 
            font-weight: 700; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            margin-top: 10px; 
            transition: 0.3s;
            text-transform: uppercase;
        }
        .btn-download:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 229, 195, 0.3); }

        .total-box { 
            background: rgba(255,255,255,0.02); 
            padding: 40px; 
            border-radius: 30px; 
            border: 1px solid rgba(255,255,255,0.05);
        }
        
        .row { display: flex; justify-content: space-between; margin-bottom: 18px; font-size: 1rem; color: var(--text-muted); }
        
        .grand-total { 
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem; 
            font-weight: 700; 
            color: #fff; 
            border-top: 1px solid var(--border); 
            padding-top: 25px; 
            margin-top: 20px;
        }

        .no-print { margin-top: 50px; text-align: center; }
        
        .btn-action { 
            padding: 16px 40px; 
            border-radius: 16px; 
            font-weight: 700; 
            cursor: pointer; 
            border: none; 
            text-decoration: none; 
            margin: 0 12px; 
            display: inline-block; 
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 0.85rem;
        }

        .btn-print { background: var(--teal); color: #08080e; box-shadow: 0 10px 25px rgba(0, 229, 195, 0.2); }
        .btn-print:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0, 229, 195, 0.3); }
        
        .btn-return { border: 1.5px solid var(--border); color: #fff; }
        .btn-return:hover { background: rgba(0, 229, 195, 0.05); border-color: var(--teal); }

        @media print { 
            .no-print, nav, footer { display: none !important; } 
            body { background: white; color: black; } 
            .premium-card { border: 2px solid #eee; background: white; box-shadow: none; border-radius: 0; padding: 20px; } 
            .paid-stamp { border-color: #27ae60; color: #27ae60; }
            h1 { background: none; -webkit-text-fill-color: black; color: black; }
            .grand-total { color: black; border-top: 2px solid #000; }
        }
    </style>
</head>
<body>

<div class="receipt-container">
    <div class="premium-card">
        <div class="paid-stamp">Paid & Confirmed</div>
        
        <div style="display: flex; flex-direction: column; gap: 5px;">
            <h1>MELODY MASTERS</h1>
            <p style="color: var(--text-muted); font-size: 0.9rem; margin: 0; letter-spacing: 1px;">Where every note finds its perfect gear.</p>
        </div>

        <div style="display: flex; justify-content: space-between; margin-top: 60px; gap: 40px;">
            <div>
                <span class="meta-label">Billed To</span>
                <div style="font-size: 1.2rem; font-weight: 700; color: #fff;"><?php echo htmlspecialchars($order['full_name']); ?></div>
                <div style="color: var(--text-muted); margin-top: 5px;"><?php echo htmlspecialchars($order['email']); ?></div>
            </div>
            <div style="text-align: right;">
                <span class="meta-label">Invoice Details</span>
                <div style="font-size: 1.2rem; font-weight: 700; color: #fff;">#ORD-<?php echo str_pad($order_id, 5, '0', STR_PAD_LEFT); ?></div>
                <div style="color: var(--text-muted); margin-top: 5px;"><?php echo date('F d, Y', strtotime($order['order_date'])); ?></div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th style="text-align: right;">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $items_result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div style="font-weight: 700; color: #fff; font-size: 1.05rem;"><?php echo htmlspecialchars($item['product_name']); ?></div>
                        <div style="color: var(--teal); font-size: 0.75rem; text-transform: uppercase; margin-top: 4px;"><?php echo htmlspecialchars($item['category_name']); ?></div>
                        
                        <?php 
                        if ($item['product_type'] === 'Digital' && !empty($item['file_path'])) {
                            echo '<a href="' . htmlspecialchars($item['file_path']) . '" download class="btn-download">
                                    <i class="fas fa-arrow-down"></i> Get Digital Assets
                                  </a>';
                        }
                        ?>
                    </td>
                    <td style="color: #fff;">x<?php echo $item['quantity']; ?></td>
                    <td style="color: #fff;">£<?php echo number_format($item['unit_price'], 2); ?></td>
                    <td style="text-align: right; font-weight: 700; color: var(--teal);">£<?php echo number_format($item['unit_price'] * $item['quantity'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 50px; margin-top: 60px;">
            <div style="color: var(--text-muted); font-size: 0.8rem; line-height: 1.8; border-left: 1px solid var(--border); padding-left: 25px;">
                <div style="color: #fff; font-weight: 700; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px;">Security & Support</div>
                <p>Thank you for choosing Melody Masters. Your transaction is secured with 256-bit SSL encryption. 
                   Digital items are immediately available for download. For any inquiries, please contact our support team with your Invoice ID.</p>
            </div>
            
            <div class="total-box">
                <div class="row">
                    <span>Subtotal</span>
                    <span style="color: #fff;">£<?php echo number_format($order['total_amount'] - $order['shipping_cost'], 2); ?></span>
                </div>
                <div class="row">
                    <span>Shipping Fee</span>
                    <span>
                        <?php 
                        if ($order['shipping_cost'] == 0) {
                            echo "<b style='color:var(--success)'>FREE</b>";
                        } else {
                            echo "<span style='color:#fff;'>£" . number_format($order['shipping_cost'], 2) . "</span>";
                        }
                        ?>
                    </span>
                </div>
                <div class="row grand-total">
                    <span>Total</span>
                    <span>£<?php echo number_format($order['total_amount'], 2); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="no-print">
        <button onclick="window.print()" class="btn-action btn-print"><i class="fas fa-print" style="margin-right:10px;"></i> Save as PDF</button>
        <a href="shop.php" class="btn-action btn-return">Continue Shopping</a>
    </div>
</div>

<?php include 'footer.php'; ?>
</body>
</html>