<?php
session_start();
include 'db_connect.php';

// Authentication check: Only Admin or Staff
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$current_role = strtolower(trim($_SESSION['role']));

if ($current_role !== 'staff' && $current_role !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// --- 1. SECURE STATUS UPDATE LOGIC ---
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        $message = "Order #$order_id updated to $new_status successfully!";
    }
    $stmt->close();
}

// --- 2. SEARCH & FILTER LOGIC ---
$filter_query = " WHERE 1=1 "; 

if (isset($_GET['filter']) && !empty($_GET['filter'])) {
    $filter_type = $_GET['filter'];
    if ($filter_type == 'today') {
        $filter_query .= " AND DATE(o.order_date) = CURDATE() ";
    } elseif ($filter_type == 'week') {
        $filter_query .= " AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) ";
    } elseif ($filter_type == 'month') {
        $filter_query .= " AND MONTH(o.order_date) = MONTH(CURDATE()) AND YEAR(o.order_date) = YEAR(CURDATE()) ";
    }
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $filter_query .= " AND (o.order_id = '$search' OR u.full_name LIKE '%$search%') ";
}

$sql = "SELECT o.*, u.full_name FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        $filter_query
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Logistics | Melody Masters Admin</title>
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
            --warning: #f39c12;
            --info: #3498db;
            --success: #00e5a0;
        }

        body { 
            background: var(--bg); 
            color: #e0ddf5; 
            font-family: 'DM Sans', sans-serif; 
            display: flex; 
            margin: 0; 
            overflow-x: hidden;
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
            min-height: 100vh; 
        }

        /* ── HEADER ── */
        .header-section { margin-bottom: 40px; border-left: 3px solid var(--teal); padding-left: 25px; }
        .header-section h1 { 
            font-family: 'Playfair Display', serif; 
            font-size: 2.2rem; 
            margin: 0; 
            background: linear-gradient(to bottom, #fff, #888);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent;
        }

        /* ── FILTER TOOLBAR ── */
        .filter-shelf { 
            background: var(--glass); 
            padding: 20px 30px; 
            border-radius: 24px; 
            border: 1px solid var(--border);
            margin-bottom: 35px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(15px);
        }

        .filter-bar { display: flex; align-items: center; gap: 15px; }

        input, select { 
            background: #0d0d15; 
            border: 1px solid var(--border); 
            color: white; 
            padding: 12px 18px; 
            border-radius: 14px; 
            font-size: 0.85rem;
            outline: none;
            transition: 0.3s;
        }

        input:focus, select:focus { border-color: var(--teal); box-shadow: 0 0 12px rgba(0, 229, 195, 0.1); }

        .btn-filter { 
            background: var(--teal); 
            color: #000; 
            border: none; 
            padding: 12px 25px; 
            border-radius: 12px; 
            font-weight: 700; 
            cursor: pointer; 
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            transition: 0.3s;
        }
        .btn-filter:hover { transform: translateY(-2px); box-shadow: 0 5px 15px var(--teal-glow); }

        /* ── DATA TABLE ── */
        .glass-card { 
            background: var(--glass); 
            border-radius: 30px; 
            padding: 20px; 
            border: 1px solid var(--border); 
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
            animation: fadeIn 0.8s ease-out;
        }

        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; 
            color: var(--teal); 
            padding: 20px; 
            border-bottom: 1px solid var(--border); 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            letter-spacing: 2px;
        }
        td { padding: 22px 20px; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 0.9rem; vertical-align: middle; }
        
        tr:hover td { background: rgba(255, 255, 255, 0.01); }

        .order-id { font-family: monospace; color: var(--teal); font-weight: 700; font-size: 1rem; }
        .customer-name { font-weight: 600; color: #fff; }
        .price-text { font-weight: 700; color: #fff; }

        /* ── STATUS PILLS ── */
        .status-pill { 
            padding: 6px 14px; 
            border-radius: 50px; 
            font-size: 0.65rem; 
            font-weight: 800; 
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .status-pill i { font-size: 6px; }

        /* ── UPDATE FORM ── */
        .update-form { display: flex; align-items: center; gap: 8px; }
        .select-status { padding: 8px 12px; font-size: 0.75rem; border-radius: 10px; width: 120px; }
        .btn-update { 
            background: transparent; 
            color: var(--teal); 
            border: 1px solid var(--teal); 
            padding: 8px 12px; 
            border-radius: 10px; 
            font-size: 0.7rem; 
            font-weight: 700; 
            cursor: pointer;
            transition: 0.3s;
        }
        .btn-update:hover { background: var(--teal); color: #000; }

        /* ── SUCCESS MESSAGE ── */
        .msg-success { 
            background: rgba(0, 229, 160, 0.1); 
            color: var(--success); 
            padding: 18px 25px; 
            border-radius: 18px; 
            margin-bottom: 30px; 
            border: 1px solid rgba(0, 229, 160, 0.2); 
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.5s ease;
        }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

        @media (max-width: 1100px) {
            .main-content { margin-left: 0; width: 100%; padding: 30px; }
            .filter-shelf { flex-direction: column; align-items: flex-start; gap: 20px; }
        }
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
    <div class="header-section">
        <h1>Order Logistics</h1>
        <p style="color: var(--text-muted); margin-top: 5px;">Archival management and fulfillment tracking (Level: <?php echo ucfirst($current_role); ?>)</p>
    </div>

    <div class="filter-shelf">
        <form method="GET" class="filter-bar">
            <input type="text" name="search" placeholder="Reference ID or Client..." value="<?php echo $_GET['search'] ?? ''; ?>" style="width: 280px;">
            <select name="filter">
                <option value="">Temporal Filter: All</option>
                <option value="today" <?php if(isset($_GET['filter']) && $_GET['filter'] == 'today') echo 'selected'; ?>>Today's Manifest</option>
                <option value="week" <?php if(isset($_GET['filter']) && $_GET['filter'] == 'week') echo 'selected'; ?>>Weekly Archive</option>
                <option value="month" <?php if(isset($_GET['filter']) && $_GET['filter'] == 'month') echo 'selected'; ?>>Monthly Summary</option>
            </select>
            <button type="submit" class="btn-filter">Process Filter</button>
            <a href="manage_orders.php" style="color: #ff4f7b; text-decoration: none; font-size: 0.7rem; font-weight: 800; letter-spacing: 1px;">RESET</a>
        </form>
        <div style="font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px;">
            Entries Found: <span style="color: var(--teal); font-weight: 700;"><?php echo $result->num_rows; ?></span>
        </div>
    </div>

    <?php if(!empty($message)) echo "<div class='msg-success'><i class='fas fa-check-double'></i> $message</div>"; ?>

    <div class="glass-card">
        <table>
            <thead>
                <tr>
                    <th>Ref. Code</th>
                    <th>Client Portfolio</th>
                    <th>Timestamp</th>
                    <th>Valuation</th>
                    <th>Logistics Status</th>
                    <th>Administrative Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): 
                        $status = $row['order_status'];
                        if($status == 'Pending') { $color = '#f39c12'; }
                        elseif($status == 'Shipped') { $color = '#3498db'; }
                        else { $color = '#00e5a0'; }
                    ?>
                    <tr>
                        <td><span class="order-id">#<?php echo str_pad($row['order_id'], 5, '0', STR_PAD_LEFT); ?></span></td>
                        <td class="customer-name"><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td style="color: var(--text-muted);"><?php echo date('d M, Y', strtotime($row['order_date'])); ?></td>
                        <td class="price-text">$<?php echo number_format($row['total_amount'], 2); ?></td>
                        <td>
                            <span class="status-pill" style="background: <?php echo $color; ?>15; color: <?php echo $color; ?>; border: 1px solid <?php echo $color; ?>40;">
                                <i class="fas fa-circle"></i> <?php echo $status; ?>
                            </span>
                        </td>
                        <td>
                            <form method="POST" class="update-form">
                                <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                <select name="status" class="select-status">
                                    <option value="Pending" <?php if($status == 'Pending') echo 'selected'; ?>>Pending</option>
                                    <option value="Shipped" <?php if($status == 'Shipped') echo 'selected'; ?>>Shipped</option>
                                    <option value="Delivered" <?php if($status == 'Delivered') echo 'selected'; ?>>Delivered</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-update">Update</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" style="text-align:center; padding: 60px; color: var(--text-muted);">No logistics records found for the current query.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>