<?php
include 'db_connect.php';
include 'navbar.php'; 

// 1. SEARCH, FILTER & SORT LOGIC (REMAINED UNTOUCHED)
$where_clauses = [];

if (isset($_GET['cat_id']) && !empty($_GET['cat_id'])) {
    $cat_id = mysqli_real_escape_string($conn, $_GET['cat_id']);
    $where_clauses[] = "category_id = '$cat_id'";
}

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = mysqli_real_escape_string($conn, $_GET['search']);
    $where_clauses[] = "(product_name LIKE '%$search%' OR brand LIKE '%$search%')";
}

$order_by = "ORDER BY product_id DESC"; 
if (isset($_GET['sort'])) {
    if ($_GET['sort'] == 'price_low') { $order_by = "ORDER BY price ASC"; }
    elseif ($_GET['sort'] == 'price_high') { $order_by = "ORDER BY price DESC"; }
}

$sql = "SELECT * FROM products";
if (count($where_clauses) > 0) {
    $sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$sql .= " $order_by";

$result = $conn->query($sql);
$categories = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>The Premium Showroom | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,900;1,700&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep:     #050508;
            --bg-surface:  #0a0a0f;
            --bg-card:     rgba(14,14,22,0.85);
            --border:      rgba(0,229,195,0.12);
            --border-amber:rgba(232,200,110,0.15);
            --teal:        #00e5c3;
            --teal-dim:    rgba(0,229,195,0.07);
            --teal-glow:   rgba(0,229,195,0.28);
            --amber:       #e8c86e;
            --amber-dim:   rgba(232,200,110,0.07);
            --amber-glow:  rgba(232,200,110,0.22);
            --rose:        #ff4f7b;
            --green:       #00e5a0;
            --text:        #e0ddf5;
            --text-muted:  #4a4a62;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg-deep);
            color: var(--text);
            overflow-x: hidden;
        }

        /* ─────────────────────────────────────────
           CANVAS WAVEFORM
        ───────────────────────────────────────── */
        #heroWave {
            position: absolute;
            bottom: 0; left: 0;
            width: 100%; height: 140px;
            pointer-events: none; z-index: 2;
            opacity: .45;
        }

        /* ─────────────────────────────────────────
           HERO SECTION
        ───────────────────────────────────────── */
        .shop-hero {
            height: 72vh;
            min-height: 500px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
            text-align: center;
            isolation: isolate;
        }

        /* layered bg */
        .shop-hero::before {
            content: '';
            position: absolute; inset: 0;
            background:
                url('https://images.unsplash.com/photo-1511379938547-c1f69419868d?auto=format&fit=crop&q=80&w=2070')
                center/cover no-repeat;
            filter: brightness(.22) saturate(.6);
            transform: scale(1.04);
            animation: heroZoom 18s ease-in-out infinite alternate;
            z-index: 0;
        }
        @keyframes heroZoom {
            from { transform: scale(1.04); }
            to   { transform: scale(1.12); }
        }

        /* vignette */
        .shop-hero::after {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(ellipse 90% 70% at 50% 110%, var(--bg-deep) 30%, transparent 70%),
                radial-gradient(ellipse 60% 40% at 20% 20%, rgba(0,229,195,.06), transparent 60%),
                radial-gradient(ellipse 60% 40% at 80% 80%, rgba(232,200,110,.05), transparent 60%);
            z-index: 1;
        }

        /* floating note particles */
        .hero-notes { position:absolute;inset:0;z-index:2;pointer-events:none;overflow:hidden; }
        .hn {
            position:absolute;bottom:-40px;
            color:var(--amber);
            opacity:0;
            animation:noteFloat linear infinite;
            filter:drop-shadow(0 0 8px var(--amber-glow));
        }
        @keyframes noteFloat {
            0%   {opacity:0;transform:translateY(0) rotate(0deg) scale(.7);}
            10%  {opacity:.7;}
            85%  {opacity:.4;}
            100% {opacity:0;transform:translateY(-80vh) rotate(400deg) scale(1.3);}
        }

        /* animated ring */
        .hero-ring {
            position:absolute;z-index:2;pointer-events:none;
            width:580px;height:580px;border-radius:50%;
            border:1px solid rgba(0,229,195,.1);
            box-shadow:0 0 80px 10px rgba(0,229,195,.04),inset 0 0 80px rgba(0,229,195,.03);
            animation:ringPulse 7s ease-in-out infinite;
        }
        .hero-ring.r2 {
            width:900px;height:900px;
            border-color:rgba(232,200,110,.05);
            animation-delay:2s;animation-duration:10s;
        }
        @keyframes ringPulse {
            0%,100%{transform:scale(1);opacity:.4;}
            50%    {transform:scale(1.06);opacity:1;}
        }

        .hero-content {
            position:relative;z-index:3;
            padding:20px;
        }

        .hero-eyebrow {
            font-size:.72rem;
            letter-spacing:6px;
            text-transform:uppercase;
            color:var(--teal);
            font-weight:700;
            display:block;
            margin-bottom:20px;
            animation:slideDown .9s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes slideDown {
            from{opacity:0;transform:translateY(-20px);}
            to  {opacity:1;transform:translateY(0);}
        }

        .shop-hero h1 {
            font-family:'Playfair Display',serif;
            font-size:clamp(3.5rem,11vw,7.5rem);
            font-weight:900;
            letter-spacing:-3px;
            line-height:.9;
            background:linear-gradient(160deg,#ffffff 20%,rgba(255,255,255,.7) 50%,var(--amber) 100%);
            -webkit-background-clip:text;
            -webkit-text-fill-color:transparent;
            background-clip:text;
            animation:titleReveal 1.1s .1s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes titleReveal {
            from{opacity:0;transform:translateY(40px) skewX(-3deg);}
            to  {opacity:1;transform:translateY(0) skewX(0deg);}
        }

        .hero-sub {
            display:block;
            font-family:'Playfair Display',serif;
            font-style:italic;
            font-size:clamp(1rem,2.5vw,1.5rem);
            color:rgba(255,255,255,.35);
            margin-top:12px;
            letter-spacing:2px;
            animation:titleReveal 1s .25s cubic-bezier(.16,1,.3,1) both;
        }

        /* hero stats */
        .hero-stats {
            display:flex;gap:36px;justify-content:center;
            margin-top:40px;
            animation:titleReveal .9s .4s cubic-bezier(.16,1,.3,1) both;
        }
        .h-stat { text-align:center; }
        .h-stat strong {
            display:block;
            font-family:'Playfair Display',serif;
            font-size:1.8rem;color:var(--teal);
            font-weight:700;
        }
        .h-stat span { font-size:.65rem;color:var(--text-muted);letter-spacing:2px;text-transform:uppercase; }

        /* ─────────────────────────────────────────
           FILTER SHELF
        ───────────────────────────────────────── */
        .filter-shelf {
            background:rgba(5,5,8,.95);
            backdrop-filter:blur(28px);
            -webkit-backdrop-filter:blur(28px);
            padding:20px 0;
            position:sticky;
            top:0;
            z-index:1000;
            border-bottom:1px solid var(--border);
            box-shadow:0 12px 50px rgba(0,0,0,.6);
        }
        /* teal top line */
        .filter-shelf::before {
            content:'';
            position:absolute;top:0;left:0;right:0;height:1px;
            background:linear-gradient(90deg,transparent,var(--teal),var(--amber),var(--teal),transparent);
            opacity:.5;
        }

        .filter-container {
            max-width:1300px;
            margin:0 auto;
            display:flex;
            gap:12px;flex-wrap:wrap;
            justify-content:center;
            padding:0 20px;
            align-items:center;
        }

        .input-group {
            position:relative;
            display:flex;align-items:center;
        }
        .input-group i {
            position:absolute;left:15px;
            color:var(--teal);font-size:.85rem;
            pointer-events:none;
        }

        .filter-container input,
        .filter-container select {
            background:#0a0a12;
            border:1px solid rgba(0,229,195,.12);
            color:var(--text);
            padding:12px 16px 12px 44px;
            border-radius:14px;
            outline:none;
            min-width:210px;
            font-family:'DM Sans',sans-serif;
            font-size:.87rem;
            transition:all .35s;
            -webkit-appearance:none;
        }
        .filter-container select {
            background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2300e5c3' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat:no-repeat;background-position:right 14px center;
            padding-right:38px;
        }
        .filter-container select option { background:#0e0e18;color:var(--text); }
        .filter-container input::placeholder { color:var(--text-muted); }
        .filter-container input:focus,
        .filter-container select:focus {
            border-color:rgba(0,229,195,.4);
            background:rgba(0,229,195,.04);
            box-shadow:0 0 0 3px rgba(0,229,195,.07);
        }

        .btn-premium-search {
            background:linear-gradient(135deg,var(--teal),#00bfa0);
            color:#050508;
            border:none;
            padding:12px 28px;
            border-radius:14px;
            font-family:'DM Sans',sans-serif;
            font-weight:800;
            font-size:.82rem;
            text-transform:uppercase;
            letter-spacing:1.5px;
            cursor:pointer;
            box-shadow:0 4px 20px var(--teal-glow);
            transition:all .35s;
            display:flex;align-items:center;gap:8px;
            position:relative;overflow:hidden;
        }
        .btn-premium-search::before {
            content:'';position:absolute;inset:0;
            background:linear-gradient(135deg,rgba(255,255,255,.2),transparent);
            opacity:0;transition:opacity .3s;
        }
        .btn-premium-search:hover::before { opacity:1; }
        .btn-premium-search:hover {
            transform:translateY(-2px) scale(1.03);
            box-shadow:0 8px 28px var(--teal-glow);
        }

        .reset-link {
            color:var(--text-muted);
            text-decoration:none;
            font-size:.72rem;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:2px;
            align-self:center;
            transition:color .3s;
            padding:4px 8px;
        }
        .reset-link:hover { color:var(--rose); }

        /* ─────────────────────────────────────────
           SHOP WRAPPER & GRID
        ───────────────────────────────────────── */
        .shop-wrapper {
            max-width:1400px;
            margin:70px auto 80px;
            padding:0 5%;
        }

        .results-bar {
            display:flex;align-items:center;justify-content:space-between;
            margin-bottom:44px;
            padding-bottom:20px;
            border-bottom:1px solid rgba(255,255,255,.04);
        }
        .results-count {
            font-size:.72rem;letter-spacing:3px;text-transform:uppercase;
            color:var(--text-muted);
        }
        .results-count em { color:var(--teal);font-style:normal;font-weight:700;font-size:.9rem; }

        /* EQ bars decoration */
        .eq-mini { display:flex;align-items:flex-end;gap:3px;height:28px; }
        .eq-bar-mini {
            width:3px;border-radius:2px;
            background:linear-gradient(to top,var(--teal),var(--amber));
            animation:eqM linear infinite alternate;
            opacity:.45;
        }
        @keyframes eqM {
            from{transform:scaleY(.15);}
            to  {transform:scaleY(1);}
        }

        .product-grid {
            display:grid;
            grid-template-columns:repeat(auto-fill,minmax(310px,1fr));
            gap:32px;
        }

        /* ─────────────────────────────────────────
           PRODUCT CARD
        ───────────────────────────────────────── */
        .p-card {
            background:var(--bg-card);
            border:1px solid var(--border);
            border-radius:28px;
            padding:0;
            transition:all .55s cubic-bezier(.16,1,.3,1);
            display:flex;flex-direction:column;
            position:relative;
            backdrop-filter:blur(12px);
            -webkit-backdrop-filter:blur(12px);
            overflow:hidden;
            opacity:0;transform:translateY(32px);
            animation:cardReveal .7s cubic-bezier(.16,1,.3,1) forwards;
            box-shadow:0 8px 30px rgba(0,0,0,.3);
        }
        @keyframes cardReveal {
            to{opacity:1;transform:translateY(0);}
        }

        /* inner top shimmer line */
        .p-card::before {
            content:'';
            position:absolute;top:0;left:15%;right:15%;height:1px;
            background:linear-gradient(90deg,transparent,var(--teal),transparent);
            opacity:0;transition:opacity .4s;z-index:2;
        }
        .p-card:hover::before { opacity:.6; }

        .p-card:hover {
            transform:translateY(-16px);
            border-color:rgba(0,229,195,.35);
            box-shadow:
                0 40px 80px rgba(0,0,0,.55),
                0 0 0 1px rgba(0,229,195,.1),
                0 0 60px rgba(0,229,195,.04);
        }

        /* ── IMAGE AREA ── */
        .card-img-box {
            height:260px;
            display:flex;align-items:center;justify-content:center;
            position:relative;
            background:linear-gradient(135deg,#0c0c18,#14141f);
            border-radius:28px 28px 0 0;
            overflow:hidden;
        }

        /* animated gradient overlay */
        .card-img-box::after {
            content:'';
            position:absolute;inset:0;
            background:conic-gradient(from 0deg at 50% 120%,
                rgba(0,229,195,.08) 0deg,
                transparent 90deg,
                rgba(232,200,110,.06) 180deg,
                transparent 270deg);
            opacity:0;transition:opacity .5s;
        }
        .p-card:hover .card-img-box::after { opacity:1; }

        .p-card img {
            max-width:82%;max-height:88%;
            object-fit:contain;
            filter:drop-shadow(0 16px 40px rgba(0,0,0,.7));
            transition:transform .6s cubic-bezier(.16,1,.3,1),filter .4s;
            position:relative;z-index:1;
        }
        .p-card:hover img {
            transform:scale(1.1) translateY(-6px) rotate(-2deg);
            filter:drop-shadow(0 24px 50px rgba(0,0,0,.8)) brightness(1.06);
        }

        /* type badge */
        .type-badge {
            position:absolute;top:14px;left:14px;
            background:rgba(0,229,195,.1);
            color:var(--teal);
            padding:5px 14px;
            border-radius:30px;
            font-size:.62rem;font-weight:800;
            border:1px solid rgba(0,229,195,.25);
            text-transform:uppercase;letter-spacing:1.5px;
            backdrop-filter:blur(8px);z-index:3;
        }

        /* ── CARD BODY ── */
        .card-body {
            padding:26px 26px 28px;
            display:flex;flex-direction:column;flex:1;
        }

        .brand-name {
            color:var(--amber);
            font-size:.68rem;font-weight:700;
            letter-spacing:3px;text-transform:uppercase;
            margin-bottom:8px;
        }

        .p-card h3 {
            font-family:'Playfair Display',serif;
            font-size:1.35rem;
            color:#fff;
            margin-bottom:14px;
            line-height:1.25;
            font-weight:700;
        }

        /* rating */
        .rating-row {
            display:flex;align-items:center;gap:6px;
            margin-bottom:16px;
            color:var(--amber);
            font-size:.88rem;
        }
        .rating-row .r-count {
            color:var(--text-muted);
            font-size:.68rem;
            letter-spacing:.5px;
            margin-left:2px;
        }

        /* price */
        .price-tag {
            font-family:'Playfair Display',serif;
            font-size:1.9rem;font-weight:700;
            color:var(--amber);
            margin-bottom:16px;
            letter-spacing:-1px;
        }

        /* stock */
        .stock-indicator {
            font-size:.75rem;font-weight:600;
            margin-bottom:22px;
            display:flex;align-items:center;gap:7px;
            height:24px;
        }
        .stock-in  { color:var(--green); }
        .stock-low { color:var(--rose);  }
        .stock-out { color:var(--text-muted); }

        .card-spacer { flex:1; }

        /* CTA button */
        .btn-view-product {
            display:flex;align-items:center;justify-content:center;gap:8px;
            background:transparent;
            border:1.5px solid rgba(0,229,195,.3);
            color:var(--teal);
            padding:14px;
            border-radius:16px;
            text-align:center;
            text-decoration:none;
            font-weight:700;
            text-transform:uppercase;
            font-size:.75rem;
            letter-spacing:2px;
            transition:all .4s;
            position:relative;overflow:hidden;
        }
        .btn-view-product::before {
            content:'';
            position:absolute;inset:0;
            background:linear-gradient(135deg,var(--teal),#00bfa0);
            opacity:0;transition:opacity .35s;z-index:0;
        }
        .btn-view-product span,.btn-view-product i { position:relative;z-index:1; }
        .btn-view-product:hover {
            color:#050508;
            border-color:var(--teal);
            box-shadow:0 0 40px var(--teal-glow);
            transform:translateY(-2px);
        }
        .btn-view-product:hover::before { opacity:1; }

        .btn-sold-out {
            display:block;
            border:1px solid rgba(255,255,255,.06);
            color:var(--text-muted);
            padding:14px;
            border-radius:16px;
            text-align:center;
            font-size:.75rem;font-weight:700;
            text-transform:uppercase;letter-spacing:2px;
            pointer-events:none;
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            grid-column:1/-1;
            text-align:center;padding:120px 40px;
            background:rgba(14,14,22,.6);
            border-radius:28px;
            border:1px dashed rgba(0,229,195,.15);
        }
        .empty-state i { font-size:3rem;color:var(--teal);opacity:.3;display:block;margin-bottom:20px; }
        .empty-state h3 { color:#fff;margin-bottom:10px;font-family:'Playfair Display',serif; }
        .empty-state p  { color:var(--text-muted);font-size:.9rem; }

        /* ─── RESPONSIVE ─── */
        @media(max-width:768px) {
            .shop-hero { height:60vh; }
            .hero-stats { gap:20px; }
            .h-stat strong { font-size:1.3rem; }
            .shop-wrapper { padding:0 4%;margin-top:50px; }
            .product-grid { grid-template-columns:1fr;gap:22px; }
            .filter-container { gap:8px; }
            .filter-container input,.filter-container select { min-width:100%; }
        }
    </style>
</head>
<body>

    <!-- ── HERO ── -->
    <div class="shop-hero">
        <div class="hero-notes" id="heroNotes"></div>
        <div class="hero-ring"></div>
        <div class="hero-ring r2"></div>

        <div class="hero-content">
            <span class="hero-eyebrow">✦ Premium Audio Boutique ✦</span>
            <h1>THE SHOWROOM</h1>
            <span class="hero-sub">Curated Excellence in Every Note</span>

            <div class="hero-stats">
                <div class="h-stat">
                    <strong><?php echo $result->num_rows; ?>+</strong>
                    <span>Instruments</span>
                </div>
                <div class="h-stat">
                    <strong>A+</strong>
                    <span>Quality</span>
                </div>
                <div class="h-stat">
                    <strong>24h</strong>
                    <span>Dispatch</span>
                </div>
            </div>
        </div>

        <canvas id="heroWave"></canvas>
    </div>

    <!-- ── FILTER SHELF ── -->
    <div class="filter-shelf">
        <div class="filter-container">
            <form method="GET" style="display:contents;">
                <div class="input-group">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search the collection..." value="<?php echo $_GET['search'] ?? ''; ?>">
                </div>

                <div class="input-group">
                    <i class="fas fa-layer-group"></i>
                    <select name="cat_id">
                        <option value="">All Categories</option>
                        <?php while($cat = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $cat['category_id']; ?>" <?php echo (isset($_GET['cat_id']) && $_GET['cat_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                <?php echo $cat['category_name']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="input-group">
                    <i class="fas fa-sort-amount-down"></i>
                    <select name="sort" onchange="this.form.submit()">
                        <option value="">Sort By</option>
                        <option value="price_low"  <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_low')  ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                    </select>
                </div>

                <button type="submit" class="btn-premium-search">
                    <i class="fas fa-sliders-h"></i> Apply Filters
                </button>
                <a href="shop.php" class="reset-link">Reset</a>
            </form>
        </div>
    </div>

    <!-- ── SHOP WRAPPER ── -->
    <div class="shop-wrapper">
        <?php if ($result->num_rows > 0): ?>
        <div class="results-bar">
            <p class="results-count"><em><?php echo $result->num_rows; ?></em> instruments in collection</p>
            <div class="eq-mini" id="eqMini"></div>
        </div>
        <?php endif; ?>

        <div class="product-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php $delay = 0; while($row = $result->fetch_assoc()): $delay += 0.06; ?>
                    <div class="p-card" style="animation-delay:<?php echo $delay; ?>s;">

                        <div class="card-img-box">
                            <span class="type-badge"><?php echo $row['product_type'] ?? 'Signature'; ?></span>
                            <img src="uploads/<?php echo $row['product_image']; ?>" alt="Metadata Asset">
                        </div>

                        <div class="card-body">
                            <div class="brand-name"><?php echo $row['brand']; ?></div>
                            <h3><?php echo $row['product_name']; ?></h3>

                            <div class="rating-row">
                                <?php
                                $pid = $row['product_id'];
                                $rating_res = $conn->query("SELECT AVG(rating) as avg, COUNT(review_id) as count FROM reviews WHERE product_id = '$pid'");
                                $r_data = $rating_res->fetch_assoc();
                                $avg = round($r_data['avg'] ?? 0);
                                for($j=1; $j<=5; $j++) echo ($j <= $avg) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star" style="opacity:0.25"></i>';
                                ?>
                                <span class="r-count">(<?php echo $r_data['count'] ?? 0; ?>)</span>
                            </div>

                            <div class="price-tag">$<?php echo number_format($row['price'], 2); ?></div>

                            <div class="stock-indicator">
                                <?php if($row['stock_quantity'] > 5): ?>
                                    <span class="stock-in"><i class="fas fa-check-circle"></i> Available In Vault</span>
                                <?php elseif($row['stock_quantity'] > 0): ?>
                                    <span class="stock-low"><i class="fas fa-bolt"></i> Only <?php echo $row['stock_quantity']; ?> left</span>
                                <?php else: ?>
                                    <span class="stock-out"><i class="fas fa-times-circle"></i> Out of Archive</span>
                                <?php endif; ?>
                            </div>

                            <div class="card-spacer"></div>

                            <?php if($row['stock_quantity'] > 0): ?>
                                <a href="product_details.php?id=<?php echo $row['product_id']; ?>" class="btn-view-product">
                                    <span>View Details</span>
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            <?php else: ?>
                                <div class="btn-sold-out">Sold Out</div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-music"></i>
                    <h3>No instruments found</h3>
                    <p>Try adjusting your filters or search keywords.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    // ── FLOATING NOTES ───────────────────────────────────────
    const noteSyms = ['♩','♪','♫','♬','𝄞','♭','♯','𝄢'];
    const noteWrap = document.getElementById('heroNotes');
    for (let i = 0; i < 18; i++) {
        const n = document.createElement('span');
        n.className = 'hn';
        n.textContent = noteSyms[Math.floor(Math.random() * noteSyms.length)];
        const left  = Math.random() * 100;
        const dur   = 12 + Math.random() * 14;
        const delay = Math.random() * 20;
        const size  = .9 + Math.random() * 1.4;
        n.style.cssText = `left:${left}%;font-size:${size}rem;animation-duration:${dur}s;animation-delay:${delay}s;`;
        noteWrap.appendChild(n);
    }

    // ── HERO WAVEFORM ────────────────────────────────────────
    const heroCanvas = document.getElementById('heroWave');
    const hCtx = heroCanvas.getContext('2d');
    let hW, hH;
    function hResize() { hW = heroCanvas.width = heroCanvas.offsetWidth; hH = heroCanvas.height = 140; }
    hResize(); window.addEventListener('resize', hResize);

    const hWaves = [
        {freq:.014,speed:.010,amp:.65,phase:0,   col:'rgba(0,229,195,'},
        {freq:.024,speed:.018,amp:.40,phase:1.2, col:'rgba(232,200,110,'},
        {freq:.040,speed:.028,amp:.22,phase:2.5, col:'rgba(0,229,195,'},
        {freq:.008,speed:.006,amp:.80,phase:.6,  col:'rgba(232,200,110,'},
    ];
    let ht = 0;
    function hDraw() {
        hCtx.clearRect(0,0,hW,hH);
        hWaves.forEach((w,wi) => {
            hCtx.beginPath();
            const g = hCtx.createLinearGradient(0,0,hW,0);
            g.addColorStop(0, w.col + '0)');
            g.addColorStop(.35, w.col + '.8)');
            g.addColorStop(.65, w.col + '.8)');
            g.addColorStop(1, w.col + '0)');
            hCtx.strokeStyle = g;
            hCtx.lineWidth = wi === 3 ? 1.2 : 2;
            hCtx.shadowBlur = 14;
            hCtx.shadowColor = wi%2===0 ? 'rgba(0,229,195,.5)' : 'rgba(232,200,110,.4)';
            for (let x = 0; x <= hW; x++) {
                const y = hH - 10 - (hH * w.amp * .38) * Math.sin(x * w.freq + ht * w.speed + w.phase);
                x === 0 ? hCtx.moveTo(x,y) : hCtx.lineTo(x,y);
            }
            hCtx.stroke();
        });
        ht++; requestAnimationFrame(hDraw);
    }
    hDraw();

    // ── EQ MINI BARS ─────────────────────────────────────────
    const eqEl = document.getElementById('eqMini');
    if (eqEl) {
        for (let i = 0; i < 16; i++) {
            const b = document.createElement('div');
            b.className = 'eq-bar-mini';
            const h   = 4 + Math.random() * 20;
            const dur = .3 + Math.random() * .6;
            const del = Math.random() * 1;
            b.style.cssText = `height:${h}px;animation-duration:${dur}s;animation-delay:${del}s;`;
            eqEl.appendChild(b);
        }
    }

    // ── CARD SCROLL REVEAL ───────────────────────────────────
    const obs = new IntersectionObserver((entries) => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                e.target.style.animationPlayState = 'running';
                obs.unobserve(e.target);
            }
        });
    }, {threshold:.1});
    document.querySelectorAll('.p-card').forEach(c => {
        c.style.animationPlayState = 'paused';
        obs.observe(c);
    });

    // ── BTN RIPPLE ───────────────────────────────────────────
    document.querySelectorAll('.btn-view-product').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect   = btn.getBoundingClientRect();
            const size   = Math.max(rect.width, rect.height) * 1.4;
            ripple.style.cssText = `position:absolute;width:${size}px;height:${size}px;border-radius:50%;background:rgba(0,229,195,.25);top:${e.clientY-rect.top-size/2}px;left:${e.clientX-rect.left-size/2}px;transform:scale(0);animation:rpl .6s ease-out forwards;pointer-events:none;z-index:2;`;
            btn.appendChild(ripple);
            setTimeout(()=>ripple.remove(),650);
        });
    });
    const rplStyle = document.createElement('style');
    rplStyle.textContent = `@keyframes rpl{to{transform:scale(1);opacity:0;}}`;
    document.head.appendChild(rplStyle);
    </script>

</body>
</html>