<?php
session_start();
include 'db_connect.php'; 

// Authentication check
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- DATA FETCHING ---
$income = $conn->query("SELECT SUM(total_amount) as total FROM orders WHERE order_status='Delivered'")->fetch_assoc();
$totalProds = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc();
$pendingOrders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE order_status = 'Pending'")->fetch_assoc();
$staffCount = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'Staff'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Melody Masters</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep:    #0a0a0f;
            --bg-surface: #0f0f18;
            --bg-card:    #111118;
            --border:     rgba(0,229,195,0.1);
            --teal:       #00e5c3;
            --teal-dim:   rgba(0,229,195,0.08);
            --teal-glow:  rgba(0,229,195,0.22);
            --amber:      #e8c86e;
            --amber-dim:  rgba(232,200,110,0.08);
            --amber-glow: rgba(232,200,110,0.2);
            --rose:       #ff4f7b;
            --blue:       #63b3ed;
            --green:      #00e5a0;
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

        /* ── MAIN CONTENT ── */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 40px;
            box-sizing: border-box;
            position: relative;
            z-index: 1;
        }

        /* subtle bg glow */
        .main-content::before {
            content:'';
            position:fixed;
            top:-100px;right:-100px;
            width:500px;height:500px;
            border-radius:50%;
            background:radial-gradient(circle,rgba(0,229,195,.06),transparent 70%);
            pointer-events:none;z-index:0;
        }

        /* ── PAGE TITLE ── */
        .page-top {
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin-bottom:36px;
            padding-bottom:24px;
            border-bottom:1px solid var(--border);
        }
        .page-top h2 {
            font-family:'Playfair Display',serif;
            font-size:1.9rem;font-weight:700;color:#fff;
            background:linear-gradient(135deg,#fff,var(--amber));
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
        }
        .page-top p { color:var(--text-muted);font-size:.85rem;margin-top:5px;letter-spacing:.5px; }

        /* ── PRINT BUTTON ── */
        .btn-gold {
            background:linear-gradient(135deg,var(--amber),#c9a84c);
            color:#08080e;
            border:none;
            padding:11px 22px;
            border-radius:30px;
            font-weight:700;
            font-size:.82rem;
            text-decoration:none;
            cursor:pointer;
            display:inline-flex;
            align-items:center;
            gap:8px;
            letter-spacing:.5px;
            text-transform:uppercase;
            transition:all .35s;
            box-shadow:0 4px 18px var(--amber-glow);
        }
        .btn-gold:hover {
            transform:translateY(-3px) scale(1.04);
            box-shadow:0 8px 28px var(--amber-glow);
            filter:brightness(1.08);
        }

        /* ── STATS GRID ── */
        .stats-grid {
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:20px;
            margin-bottom:28px;
        }

        .stat-card {
            background:rgba(17,17,24,.9);
            backdrop-filter:blur(16px);
            padding:28px 26px;
            border-radius:22px;
            border:1px solid var(--border);
            position:relative;
            overflow:hidden;
            cursor:default;
            transition:all .4s cubic-bezier(.16,1,.3,1);
            opacity:0;transform:translateY(20px);
            animation:statIn .6s cubic-bezier(.16,1,.3,1) forwards;
        }
        .stat-card:nth-child(1){animation-delay:.05s;}
        .stat-card:nth-child(2){animation-delay:.12s;}
        .stat-card:nth-child(3){animation-delay:.19s;}
        .stat-card:nth-child(4){animation-delay:.26s;}
        @keyframes statIn{to{opacity:1;transform:translateY(0);}}

        /* top accent bar */
        .stat-card::before {
            content:'';
            position:absolute;
            top:0;left:0;right:0;height:3px;
            border-radius:22px 22px 0 0;
        }
        .stat-card.c-teal  ::before,.stat-card.c-teal  { border-top:3px solid var(--teal); }
        .stat-card.c-amber ::before,.stat-card.c-amber { border-top:3px solid var(--amber); }
        .stat-card.c-blue  ::before,.stat-card.c-blue  { border-top:3px solid var(--blue); }
        .stat-card.c-rose  ::before,.stat-card.c-rose  { border-top:3px solid var(--rose); }

        /* glow blob inside card */
        .stat-card .card-glow {
            position:absolute;
            width:120px;height:120px;
            border-radius:50%;
            top:-30px;right:-30px;
            filter:blur(40px);
            opacity:.18;
            transition:opacity .4s;
        }
        .stat-card:hover .card-glow { opacity:.32; }
        .stat-card.c-teal  .card-glow { background:var(--teal); }
        .stat-card.c-amber .card-glow { background:var(--amber); }
        .stat-card.c-blue  .card-glow { background:var(--blue); }
        .stat-card.c-rose  .card-glow { background:var(--rose); }

        .stat-card:hover {
            transform:translateY(-8px);
            box-shadow:0 20px 50px rgba(0,0,0,.4);
        }

        .stat-icon {
            width:44px;height:44px;
            border-radius:12px;
            display:flex;align-items:center;justify-content:center;
            font-size:1.1rem;
            margin-bottom:18px;
        }
        .c-teal  .stat-icon { background:var(--teal-dim);color:var(--teal);  border:1px solid rgba(0,229,195,.2);  }
        .c-amber .stat-icon { background:var(--amber-dim);color:var(--amber); border:1px solid rgba(232,200,110,.2);}
        .c-blue  .stat-icon { background:rgba(99,179,237,.08);color:var(--blue);border:1px solid rgba(99,179,237,.2);}
        .c-rose  .stat-icon { background:rgba(255,79,123,.08); color:var(--rose); border:1px solid rgba(255,79,123,.2);}

        .stat-card h4 {
            color:var(--text-muted);
            font-size:.7rem;
            text-transform:uppercase;
            letter-spacing:2px;
            font-weight:600;
            margin-bottom:8px;
        }
        .stat-card h2 { font-size:2rem;font-weight:700;line-height:1; }
        .c-teal  h2 { color:var(--teal);  }
        .c-amber h2 { color:var(--amber); }
        .c-blue  h2 { color:var(--blue);  }
        .c-rose  h2 { color:var(--rose);  }

        .stat-sub {
            margin-top:10px;
            font-size:.75rem;
            color:var(--text-muted);
        }

        /* ── CHART CARD ── */
        .glass-card {
            background:rgba(17,17,24,.9);
            backdrop-filter:blur(16px);
            border-radius:22px;
            padding:32px;
            margin-bottom:28px;
            border:1px solid var(--border);
            box-shadow:0 20px 50px rgba(0,0,0,.3);
            position:relative;
            overflow:hidden;
            animation:statIn .7s .3s cubic-bezier(.16,1,.3,1) both;
        }
        .glass-card::before {
            content:'';position:absolute;top:0;left:10%;right:10%;height:1px;
            background:linear-gradient(90deg,transparent,var(--teal),var(--amber),transparent);
            opacity:.35;
        }

        .chart-header {
            display:flex;align-items:center;justify-content:space-between;
            margin-bottom:28px;
        }
        .chart-header h3 {
            font-family:'Playfair Display',serif;
            font-size:1.3rem;color:#fff;
            display:flex;align-items:center;gap:10px;
        }
        .chart-header h3 i { color:var(--teal);font-size:1.1rem; }

        /* chart type toggle */
        .chart-toggle { display:flex;gap:8px; }
        .toggle-btn {
            padding:7px 16px;border-radius:20px;font-size:.75rem;font-weight:600;
            letter-spacing:.5px;text-transform:uppercase;cursor:pointer;
            border:1px solid var(--border);
            background:transparent;color:var(--text-muted);
            transition:all .3s;
        }
        .toggle-btn.active, .toggle-btn:hover {
            background:var(--teal-dim);color:var(--teal);
            border-color:rgba(0,229,195,.3);
        }

        /* chart legend dots */
        .chart-legend {
            display:flex;gap:20px;margin-top:16px;
        }
        .legend-item { display:flex;align-items:center;gap:6px;font-size:.75rem;color:var(--text-muted); }
        .legend-dot { width:10px;height:10px;border-radius:50%; }

        /* ── PRINT ── */
        @media print {
            .sidebar,.no-print { display:none !important; }
            .main-content { margin-left:0;width:100%; }
        }

        @media(max-width:768px) {
            .main-content { margin-left:0;width:100%;padding:24px; }
            .page-top { flex-direction:column;align-items:flex-start;gap:16px; }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">

    <div class="page-top">
        <div>
            <h2>Admin Overview</h2>
            <p>Summary of Melody Masters performance</p>
        </div>
        <div class="no-print">
            <button onclick="window.print()" class="btn-gold">
                <i class="fas fa-print"></i> Generate Report
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        <div class="stat-card c-teal">
            <div class="card-glow"></div>
            <div class="stat-icon"><i class="fas fa-sterling-sign"></i></div>
            <h4>Total Revenue</h4>
            <h2>$<?php echo number_format($income['total'] ?? 0, 2); ?></h2>
            <p class="stat-sub">From delivered orders</p>
        </div>

        <div class="stat-card c-amber">
            <div class="card-glow"></div>
            <div class="stat-icon"><i class="fas fa-clock"></i></div>
            <h4>Pending Orders</h4>
            <h2><?php echo $pendingOrders['count']; ?></h2>
            <p class="stat-sub">Awaiting processing</p>
        </div>

        <div class="stat-card c-blue">
            <div class="card-glow"></div>
            <div class="stat-icon"><i class="fas fa-guitar"></i></div>
            <h4>Products</h4>
            <h2><?php echo $totalProds['count']; ?></h2>
            <p class="stat-sub">Active listings</p>
        </div>

        <div class="stat-card c-rose">
            <div class="card-glow"></div>
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <h4>Staff Members</h4>
            <h2><?php echo $staffCount['count']; ?></h2>
            <p class="stat-sub">Registered staff</p>
        </div>
    </div>

    <!-- Chart Card -->
    <div class="glass-card">
        <div class="chart-header">
            <h3><i class="fas fa-chart-bar"></i> Sales Performance</h3>
            <div class="chart-toggle no-print">
                <button class="toggle-btn active" onclick="setChart('bar',this)">Bar</button>
                <button class="toggle-btn"        onclick="setChart('line',this)">Line</button>
            </div>
        </div>
        <canvas id="salesChart" height="100"></canvas>
        <div class="chart-legend">
            <div class="legend-item">
                <div class="legend-dot" style="background:var(--teal);"></div>
                <span>Weekly Revenue</span>
            </div>
            <div class="legend-item">
                <div class="legend-dot" style="background:var(--amber);"></div>
                <span>Current Total</span>
            </div>
        </div>
    </div>

</div>

<script>
const chartData = {
    labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
    datasets: [{
        label: 'Revenue ($)',
        data: [400, 900, 600, <?php echo $income['total'] ?? 0; ?>],
        backgroundColor: [
            'rgba(0,229,195,0.25)',
            'rgba(0,229,195,0.4)',
            'rgba(232,200,110,0.3)',
            'rgba(232,200,110,0.55)'
        ],
        borderColor: ['#00e5c3','#00e5c3','#e8c86e','#e8c86e'],
        borderWidth: 2,
        borderRadius: 10,
        borderSkipped: false,
        fill: true,
        tension: 0.45,
        pointBackgroundColor: '#00e5c3',
        pointBorderColor: '#08080e',
        pointBorderWidth: 2,
        pointRadius: 5,
        pointHoverRadius: 8,
    }]
};

const chartOptions = {
    responsive: true,
    animation: { duration: 900, easing: 'easeInOutQuart' },
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'rgba(17,17,24,0.95)',
            borderColor: 'rgba(0,229,195,0.3)',
            borderWidth: 1,
            titleColor: '#00e5c3',
            bodyColor: '#e0ddf5',
            padding: 14,
            cornerRadius: 12,
            displayColors: false,
            callbacks: {
                label: ctx => `  $${ctx.parsed.y.toLocaleString()}`
            }
        }
    },
    scales: {
        y: {
            grid: { color: 'rgba(255,255,255,0.04)', drawBorder: false },
            ticks: { color: '#5a5a72', font: { size: 12 }, callback: v => '$' + v.toLocaleString() },
            border: { display: false }
        },
        x: {
            grid: { display: false },
            ticks: { color: '#5a5a72', font: { size: 12 } },
            border: { display: false }
        }
    }
};

let currentType = 'bar';
let chart = new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: chartData,
    options: chartOptions
});

function setChart(type, btn) {
    document.querySelectorAll('.toggle-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    chart.destroy();
    chart = new Chart(document.getElementById('salesChart'), {
        type: type,
        data: chartData,
        options: chartOptions
    });
}
</script>
</body>
</html>