<?php
/**
 * MY ORDERS & DIGITAL DOWNLOADS PAGE
 * Features: Order History Tracking and Instant Digital Asset Access.
 */
include 'db_connect.php'; 
include 'navbar.php';

// Access Control: Ensure only registered customers can view this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/**
 * UPDATED SQL QUERY:
 * Using GROUP BY to avoid duplicate rows and correctly fetching digital paths.
 */
$sql = "SELECT o.*, dp.file_path, p.product_name 
        FROM orders o 
        JOIN order_items oi ON o.order_id = oi.order_id 
        JOIN products p ON oi.product_id = p.product_id 
        LEFT JOIN digital_products dp ON p.product_id = dp.product_id 
        WHERE o.user_id = '$user_id' 
        GROUP BY o.order_id 
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Melody Masters</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { 
            --bg-deep: #0a0a0f;
            --teal: #00e5c3;
            --teal-dim: rgba(0, 229, 195, 0.08);
            --teal-glow: rgba(0, 229, 195, 0.22);
            --border: rgba(0, 229, 195, 0.15);
            --card-bg: rgba(17, 17, 24, 0.85);
            --text-muted: #5a5a72;
            --success: #00e5a0;
        }

        body { 
            font-family: 'DM Sans', sans-serif; 
            background: var(--bg-deep); 
            color: #e0ddf5; 
            margin: 0; 
            line-height: 1.6;
        }

        /* Ambient Glow Effect */
        body::before {
            content: ''; position: fixed; top: -100px; left: -100px;
            width: 400px; height: 400px; border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 229, 195, 0.04), transparent 70%);
            z-index: -1;
        }

        .order-history-container { 
            max-width: 1000px; 
            margin: 60px auto; 
            padding: 0 20px; 
            position: relative;
        }

        .header-section { 
            margin-bottom: 50px; 
            border-left: 3px solid var(--teal); 
            padding-left: 25px; 
            animation: fadeIn 0.8s ease-out;
        }

        .header-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.5rem;
            background: linear-gradient(135deg, #fff, var(--teal));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        .order-card { 
            background: var(--card-bg); 
            backdrop-filter: blur(15px);
            border: 1px solid var(--border); 
            border-radius: 24px; 
            padding: 30px; 
            margin-bottom: 22px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        .order-card:hover { 
            border-color: rgba(0, 229, 195, 0.4);
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 229, 195, 0.1);
            background: rgba(20, 20, 30, 0.95);
        }

        .status-badge { 
            padding: 6px 16px; 
            border-radius: 50px; 
            font-size: 0.7rem; 
            font-weight: 700; 
            text-transform: uppercase; 
            letter-spacing: 1.5px;
            display: inline-block;
            margin-top: 12px;
        }

        .btn-group { display: flex; align-items: center; gap: 14px; }
        
        .btn-view { 
            background: linear-gradient(135deg, var(--teal), #00bfa0); 
            color: #08080e; 
            padding: 12px 28px; 
            border-radius: 14px; 
            text-decoration: none; 
            font-weight: 700; 
            font-size: 0.85rem; 
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px var(--teal-glow);
        }

        .btn-download { 
            background: transparent; 
            color: var(--success); 
            border: 1.5px solid rgba(0, 229, 160, 0.3);
            padding: 11px 22px; 
            border-radius: 14px; 
            text-decoration: none; 
            font-weight: 600; 
            font-size: 0.85rem; 
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-download:hover { 
            background: rgba(0, 229, 160, 0.1); 
            border-color: var(--success);
            transform: translateY(-2px);
        }

        .btn-view:hover { 
            filter: brightness(1.1);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px var(--teal-glow);
        }

        .price-text {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            font-weight: 700;
            color: #fff;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @media (max-width: 768px) {
            .order-card { flex-direction: column; align-items: flex-start; gap: 25px; }
            .btn-group { width: 100%; justify-content: space-between; }
        }
    </style>
</head>
<body>

<div class="order-history-container">
    <div class="header-section">
        <h1>Purchase History</h1>
        <p style="color: var(--text-muted); margin-top: 8px;">Track your gear orders and access your digital assets instantly.</p>
    </div>

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while($row = $result->fetch_assoc()): ?>
            <div class="order-card">
                <div class="order-info">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--teal); letter-spacing: 2px; text-transform: uppercase;">
                        Order #ORD-<?php echo str_pad($row['order_id'], 5, '0', STR_PAD_LEFT); ?>
                    </div>
                    <div style="font-size: 1.2rem; font-weight: 600; margin: 8px 0; color: #fff;">
                        <?php echo htmlspecialchars($row['product_name']); ?>
                    </div>
                    <div style="color: var(--text-muted); font-size: 0.85rem; display: flex; align-items: center; gap: 8px;">
                        <i class="far fa-calendar-alt"></i> <?php echo date('M d, Y', strtotime($row['order_date'])); ?>
                    </div>
                    
                    <div>
                        <?php 
                        $status = $row['order_status']; 
                        if($status == 'Delivered') $color = '#00e5a0';
                        elseif($status == 'Shipped') $color = '#3498db';
                        else $color = '#e8c86e';
                        ?>
                        <span class="status-badge" style="border: 1px solid <?php echo $color; ?>22; background: <?php echo $color; ?>08; color: <?php echo $color; ?>;">
                            <i class="fas fa-circle" style="font-size: 6px; vertical-align: middle; margin-right: 8px;"></i>
                            <?php echo $status; ?>
                        </span>
                    </div>
                </div>

                <div class="order-right" style="text-align: right;">
                    <div style="margin-bottom: 20px;">
                        <div style="color: var(--text-muted); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 4px;">Total Amount</div>
                        <div class="price-text">£<?php echo number_format($row['total_amount'], 2); ?></div>
                    </div>

                    <div class="btn-group">
                        <?php if ($status == 'Delivered' && !empty($row['file_path'])): 
                            // Check if the path in database already contains 'uploads'
                            $final_path = (strpos($row['file_path'], 'uploads/') !== false) 
                                          ? $row['file_path'] 
                                          : 'uploads/digital_assets/' . $row['file_path'];
                        ?>
                            <a href="<?php echo $final_path; ?>" download class="btn-download">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </a>
                        <?php endif; ?>

                        <a href="order_success.php?id=<?php echo $row['order_id']; ?>" class="btn-view">Details</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div style="text-align: center; padding: 100px 40px; background: var(--card-bg); border-radius: 30px; border: 1px dashed var(--border);">
            <i class="fas fa-shopping-bag" style="font-size: 3.5rem; color: var(--teal); opacity: 0.2; margin-bottom: 25px;"></i>
            <h3 style="font-family: 'Playfair Display', serif; font-size: 1.8rem; margin-bottom: 10px;">No purchases yet</h3>
            <p style="color: var(--text-muted); max-width: 400px; margin: 0 auto 30px;">Your collection is waiting for its first masterpiece.</p>
            <a href="shop.php" class="btn-view" style="display:inline-block;">Start Shopping</a>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>