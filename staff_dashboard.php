<?php
session_start();
include 'db_connect.php';

// Authentication: Only Staff members can access this
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'staff') {
    header("Location: login.php");
    exit();
}

// 1. Fetch PENDING orders count - Column name updated to 'order_status'
$order_query = "SELECT COUNT(*) as total FROM orders WHERE order_status = 'Pending'";
$order_res = $conn->query($order_query);
$order_count = ($order_res) ? $order_res->fetch_assoc()['total'] : 0;

// 2. Fetch low stock count - Using your 'stock_quantity' column
$stock_res = $conn->query("SELECT COUNT(*) as total FROM products WHERE stock_quantity <= 5");
$low_stock = ($stock_res) ? $stock_res->fetch_assoc()['total'] : 0;

// Get display name from session
$display_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : "Staff Member";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Dashboard | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep:    #0a0a0f;
            --bg-card:    #111118;
            --border:     rgba(0,229,195,0.1);
            --teal:       #00e5c3;
            --teal-dim:   rgba(0,229,195,0.08);
            --teal-glow:  rgba(0,229,195,0.22);
            --amber:      #e8c86e;
            --amber-dim:  rgba(232,200,110,0.08);
            --amber-glow: rgba(232,200,110,0.2);
            --rose:       #ff4f7b;
            --text:       #e0ddf5;
            --text-muted: #5a5a72;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            background: var(--bg-deep);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* ── LAYOUT ── */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 48px 44px;
            box-sizing: border-box;
            min-height: 100vh;
            position: relative;
        }

        /* ambient glow */
        .main-content::before {
            content: '';
            position: fixed;
            top: -100px; right: -100px;
            width: 500px; height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0,229,195,.05), transparent 70%);
            pointer-events: none; z-index: 0;
        }
        .main-content::after {
            content: '';
            position: fixed;
            bottom: -80px; left: 180px;
            width: 380px; height: 380px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(232,200,110,.04), transparent 70%);
            pointer-events: none; z-index: 0;
        }

        /* ── WELCOME SECTION ── */
        .welcome-section {
            margin-bottom: 44px;
            padding-bottom: 28px;
            border-bottom: 1px solid var(--border);
            position: relative; z-index: 1;
            animation: fadeUp .8s cubic-bezier(.16,1,.3,1) both;
        }

        .welcome-tag {
            font-size: .68rem;
            letter-spacing: 4px;
            text-transform: uppercase;
            color: var(--teal);
            font-weight: 600;
            display: block;
            margin-bottom: 10px;
        }

        .welcome-section h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(1.8rem, 4vw, 2.6rem);
            font-weight: 900;
            line-height: 1.1;
            background: linear-gradient(135deg, #fff 40%, var(--amber) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        .welcome-section p {
            color: var(--text-muted);
            margin-top: 8px;
            font-size: .9rem;
            letter-spacing: .3px;
        }

        /* avatar + name row */
        .welcome-row {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 10px;
        }
        .staff-avatar {
            width: 52px; height: 52px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--teal), #00bfa0);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 1.3rem;
            color: #08080e;
            box-shadow: 0 0 20px var(--teal-glow), 0 0 0 3px rgba(0,229,195,.15);
            flex-shrink: 0;
            animation: iconPop .7s .2s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes iconPop {
            from { transform:scale(0); opacity:0; }
            to   { transform:scale(1); opacity:1; }
        }

        /* ── STAT GRID ── */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            position: relative; z-index: 1;
        }

        .stat-card {
            background: rgba(17,17,24,.9);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            padding: 38px 32px;
            border-radius: 24px;
            border: 1px solid var(--border);
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,.35), inset 0 1px 0 rgba(255,255,255,.03);
            transition: all .45s cubic-bezier(.16,1,.3,1);
            position: relative; overflow: hidden;
            opacity: 0; transform: translateY(24px);
            animation: fadeUp .7s cubic-bezier(.16,1,.3,1) forwards;
        }
        .stat-card:nth-child(1) { animation-delay: .15s; }
        .stat-card:nth-child(2) { animation-delay: .28s; }

        /* top accent */
        .stat-card.c-amber { border-top: 3px solid var(--amber); }
        .stat-card.c-rose  { border-top: 3px solid var(--rose);  }

        /* inner glow blob */
        .stat-card .card-glow {
            position: absolute;
            width: 160px; height: 160px;
            border-radius: 50%;
            top: -40px; left: 50%;
            transform: translateX(-50%);
            filter: blur(50px);
            opacity: .14;
            transition: opacity .4s;
        }
        .stat-card.c-amber .card-glow { background: var(--amber); }
        .stat-card.c-rose  .card-glow { background: var(--rose);  }
        .stat-card:hover .card-glow { opacity: .28; }

        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 30px 70px rgba(0,0,0,.45);
        }
        .stat-card.c-amber:hover { border-color: rgba(232,200,110,.4); }
        .stat-card.c-rose:hover  { border-color: rgba(255,79,123,.4);  }

        /* icon badge */
        .stat-icon {
            width: 64px; height: 64px;
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            margin: 0 auto 20px;
            transition: transform .4s;
        }
        .stat-card.c-amber .stat-icon {
            background: var(--amber-dim);
            border: 1px solid rgba(232,200,110,.2);
            color: var(--amber);
            box-shadow: 0 0 22px var(--amber-glow);
        }
        .stat-card.c-rose .stat-icon {
            background: rgba(255,79,123,.08);
            border: 1px solid rgba(255,79,123,.2);
            color: var(--rose);
            box-shadow: 0 0 22px rgba(255,79,123,.2);
        }
        .stat-card:hover .stat-icon { transform: scale(1.12) rotate(-6deg); }

        .stat-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem; font-weight: 900;
            margin: 0 0 6px;
            line-height: 1;
        }
        .stat-card.c-amber h3 { color: var(--amber); }
        .stat-card.c-rose  h3 { color: var(--rose);  }

        .stat-card p {
            color: var(--text-muted);
            margin: 0 0 24px;
            font-size: .85rem;
            letter-spacing: .5px;
        }

        /* ── ACTION BUTTON ── */
        .btn-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 11px 24px;
            border-radius: 30px;
            font-size: .82rem;
            font-weight: 600;
            text-decoration: none;
            letter-spacing: .5px;
            text-transform: uppercase;
            transition: all .35s;
            position: relative; z-index: 1;
        }
        .stat-card.c-amber .btn-link {
            color: var(--amber);
            border: 1.5px solid rgba(232,200,110,.35);
            background: transparent;
        }
        .stat-card.c-amber .btn-link:hover {
            background: var(--amber);
            color: #08080e;
            border-color: var(--amber);
            box-shadow: 0 0 28px var(--amber-glow);
            transform: translateY(-2px);
        }
        .stat-card.c-rose .btn-link {
            color: var(--rose);
            border: 1.5px solid rgba(255,79,123,.3);
            background: transparent;
        }
        .stat-card.c-rose .btn-link:hover {
            background: var(--rose);
            color: #fff;
            border-color: var(--rose);
            box-shadow: 0 0 28px rgba(255,79,123,.3);
            transform: translateY(-2px);
        }

        @keyframes fadeUp {
            from { opacity:0; transform:translateY(24px); }
            to   { opacity:1; transform:translateY(0);    }
        }

        /* ── RESPONSIVE ── */
        @media(max-width: 768px) {
            .main-content { margin-left:0; width:100%; padding:28px 20px; }
            .stat-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'staff_sidebar.php'; ?>

<div class="main-content">
    <div class="welcome-section">
        <div class="welcome-row">
            <div class="staff-avatar"><?php echo strtoupper(substr($display_name, 0, 1)); ?></div>
            <div>
                <span class="welcome-tag">✦ Staff Portal</span>
                <h1>Hello, <?php echo htmlspecialchars($display_name); ?>!</h1>
            </div>
        </div>
        <p>Here's an overview of the store's current status.</p>
    </div>

    <div class="stat-grid">
        <div class="stat-card c-amber">
            <div class="card-glow"></div>
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <h3><?php echo $order_count; ?></h3>
            <p>Orders Awaiting Action</p>
            <a href="manage_orders.php" class="btn-link">
                Process Orders <i class="fas fa-arrow-right"></i>
            </a>
        </div>

        <div class="stat-card c-rose">
            <div class="card-glow"></div>
            <div class="stat-icon"><i class="fas fa-box-open"></i></div>
            <h3><?php echo $low_stock; ?></h3>
            <p>Low Stock Items</p>
            <a href="manage_products.php" class="btn-link">
                View Inventory <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

</body>
</html>