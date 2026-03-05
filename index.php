<?php 
include 'db_connect.php'; 
include 'navbar.php'; 

// Database එකෙන් Categories ටික ගන්න
$cat_res = $conn->query("SELECT * FROM categories LIMIT 4");

// අලුත්ම භාණ්ඩ 4ක් Featured ලෙස පෙන්වීමට ගන්න
$featured_res = $conn->query("SELECT * FROM products ORDER BY product_id DESC LIMIT 4");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Melody Masters - Premium Musical Instruments</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --bg-deep:     #050508;
            --bg-surface:  #0a0a0f;
            --bg-card:     rgba(17, 17, 24, 0.7);
            --border:      rgba(0, 229, 195, 0.15);
            --accent-teal: #00e5c3;
            --accent-amber:#ffb347;
            --accent-rose: #ff4f7b;
            --text-primary:#f8fafc;
            --text-muted:  #5a5a72;
            --gold:        #e8c86e;
            --teal-glow:   rgba(0, 229, 195, 0.3);
        }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'DM Sans', sans-serif;
            background: var(--bg-deep);
            color: var(--text-primary);
            overflow-x: hidden;
        }

        /* ─── CANVAS AUDIO VISUALISER ─── */
        #visualiser {
            position: fixed; bottom: 0; left: 0; width: 100%; height: 120px;
            pointer-events: none; z-index: 0; opacity: 0.45;
        }

        /* ─── FLOATING MUSIC NOTES ─── */
        .notes-wrap { position: absolute; inset: 0; overflow: hidden; pointer-events: none; z-index: 1; }
        .note {
            position: absolute; font-size: 1.6rem; opacity: 0;
            animation: floatNote linear infinite; color: var(--gold);
            filter: drop-shadow(0 0 8px var(--teal-glow));
        }
        @keyframes floatNote {
            0%   { opacity: 0; transform: translateY(0) rotate(0deg) scale(0.6); }
            15%  { opacity: 0.9; }
            85%  { opacity: 0.6; }
            100% { opacity: 0; transform: translateY(-110vh) rotate(360deg) scale(1.2); }
        }

        /* ─── HERO SECTION ─── */
        .hero {
            position: relative; min-height: 100vh;
            background: radial-gradient(circle at center, rgba(10, 10, 15, 0.2), var(--bg-deep)),
                        url('https://images.unsplash.com/photo-1511379938547-c1f69419868d?auto=format&fit=crop&w=1920&q=80');
            background-size: cover; background-position: center; background-attachment: fixed;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            text-align: center; padding: 20px; overflow: hidden;
        }

        .hero::before {
            content:''; position: absolute; inset: 0;
            background: radial-gradient(circle at 50% 50%, transparent 0%, var(--bg-deep) 85%);
            z-index: 2;
        }

        .hero::after {
            content: ''; position: absolute; width: 650px; height: 650px;
            border-radius: 50%; border: 1px solid var(--border);
            box-shadow: 0 0 100px var(--teal-glow), inset 0 0 50px var(--teal-glow);
            animation: ringPulse 6s ease-in-out infinite;
            z-index: 1;
        }
        @keyframes ringPulse {
            0%,100% { transform: scale(1); opacity: 0.4; }
            50%      { transform: scale(1.1); opacity: 0.8; }
        }

        .hero-content { position: relative; z-index: 10; animation: fadeUp 1.2s cubic-bezier(.16,1,.3,1) both; }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(50px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .hero-eyebrow {
            font-size: 0.75rem; letter-spacing: 7px; text-transform: uppercase;
            color: var(--accent-teal); margin-bottom: 25px; font-weight: 700;
        }

        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3.5rem, 12vw, 7.5rem);
            font-weight: 900; line-height: 0.9;
            background: linear-gradient(to bottom, #fff 40%, #888 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
            margin-bottom: 30px;
        }

        .btn-shop {
            background: var(--accent-teal); color: #000;
            padding: 20px 50px; border-radius: 100px;
            text-decoration: none; font-weight: 800; font-size: 0.9rem;
            text-transform: uppercase; letter-spacing: 2px;
            transition: 0.4s; box-shadow: 0 10px 40px var(--teal-glow);
            display: inline-flex; align-items: center; gap: 12px;
        }
        .btn-shop:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 15px 50px var(--teal-glow);
            filter: brightness(1.1);
        }

        /* ─── SECTIONS ─── */
        .section { padding: 120px 5%; position: relative; z-index: 5; }

        .section-title { text-align: center; margin-bottom: 80px; }
        .section-title .tag {
            font-size: 0.7rem; letter-spacing: 5px; text-transform: uppercase;
            color: var(--accent-teal); font-weight: 700; margin-bottom: 15px; display: block;
        }
        .section-title h2 {
            font-family: 'Playfair Display', serif; font-size: 3.5rem; color: #fff;
        }

        /* ─── CATEGORY CARDS ─── */
        .categories-section { background: var(--bg-surface); }
        
        .grid-container {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px; max-width: 1400px; margin: 0 auto;
        }

        .category-card {
            background: var(--bg-card); backdrop-filter: blur(15px);
            border: 1px solid var(--border); padding: 60px 40px; border-radius: 35px;
            text-align: center; transition: 0.5s cubic-bezier(.16,1,.3,1);
            position: relative; overflow: hidden;
        }
        .category-card:hover {
            transform: translateY(-15px); border-color: var(--accent-teal);
            box-shadow: 0 30px 60px rgba(0,0,0,0.5);
        }

        .category-card .icon { font-size: 3.5rem; margin-bottom: 25px; display: block; transition: 0.4s; }
        .category-card:hover .icon { transform: scale(1.2) rotate(-5deg); }

        .category-card h3 { font-family: 'Playfair Display', serif; font-size: 1.8rem; color: #fff; margin-bottom: 15px; }

        /* ─── PRODUCT CARDS ─── */
        .product-card {
            background: var(--bg-card); border-radius: 35px; overflow: hidden;
            border: 1px solid var(--border); transition: 0.5s cubic-bezier(.16,1,.3,1);
            display: flex; flex-direction: column;
        }
        .product-card:hover {
            transform: translateY(-12px); border-color: var(--gold);
            box-shadow: 0 35px 70px rgba(0,0,0,0.6);
        }

        .product-image-container {
            height: 300px; background: #0d0d15; position: relative; padding: 20px;
        }
        .product-card img {
            width: 100%; height: 100%; object-fit: contain;
            transition: 0.7s cubic-bezier(.16,1,.3,1);
        }
        .product-card:hover img { transform: scale(1.1); }

        .product-badge {
            position: absolute; top: 20px; right: 20px;
            background: var(--accent-rose); color: #fff; padding: 6px 15px;
            border-radius: 50px; font-size: 0.65rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 2px;
        }

        .product-info { padding: 35px; flex: 1; display: flex; flex-direction: column; }
        .product-price { font-family: 'Playfair Display', serif; font-size: 2rem; color: var(--gold); margin-bottom: 20px; }

        .btn-view {
            margin-top: auto; border: 1px solid var(--accent-teal);
            color: var(--accent-teal); padding: 15px; border-radius: 18px;
            text-decoration: none; font-weight: 700; text-align: center;
            text-transform: uppercase; font-size: 0.8rem; transition: 0.3s;
        }
        .btn-view:hover { background: var(--accent-teal); color: #000; box-shadow: 0 0 20px var(--teal-glow); }

        /* ─── EQ DIVIDER ─── */
        .eq-divider {
            display: flex; align-items: flex-end; justify-content: center;
            gap: 6px; height: 60px; opacity: 0.2;
        }
        .eq-bar {
            width: 5px; border-radius: 50px; background: var(--accent-teal);
            animation: eqAnim linear infinite alternate;
        }
        @keyframes eqAnim { from { transform: scaleY(0.2); } to { transform: scaleY(1); } }

        @media (max-width: 768px) {
            .hero h1 { font-size: 4rem; }
            .section-title h2 { font-size: 2.5rem; }
        }
    </style>
</head>
<body>

    <canvas id="visualiser"></canvas>

    <header class="hero">
        <div class="notes-wrap" id="notesWrap"></div>
        <div class="hero-content">
            <span class="hero-eyebrow">The Ultimate Collection</span>
            <h1>UNLEASH YOUR<br>SYMPHONY</h1>
            <p>Experience artisanal craftsmanship and professional acoustics in every instrument we curate.</p>
            <a href="shop.php" class="btn-shop">
                Explore The Showroom <i class="fas fa-chevron-right"></i>
            </a>
        </div>
        <div class="scroll-hint" style="position:absolute; bottom:40px; opacity:0.5; text-align:center;">
            <div style="width:2px; height:60px; background:linear-gradient(to bottom, var(--accent-teal), transparent); margin:0 auto;"></div>
            <p style="font-size:0.6rem; letter-spacing:3px; margin-top:10px; color:var(--text-muted);">SCROLL</p>
        </div>
    </header>

    <div class="eq-divider" id="eqDivider1"></div>

    <section class="section categories-section">
        <div class="section-title">
            <span class="tag">Curated Selection</span>
            <h2>Masterpiece Collections</h2>
        </div>
        <div class="grid-container">
            <?php
            $catIcons = ['🎸','🎹','🥁','🎻'];
            $i = 0;
            while($cat = $cat_res->fetch_assoc()):
                $icon = $catIcons[$i % 4]; $i++;
            ?>
                <a href="shop.php?cat_id=<?php echo $cat['category_id']; ?>" style="text-decoration:none;">
                    <div class="category-card">
                        <span class="icon"><?php echo $icon; ?></span>
                        <h3><?php echo htmlspecialchars($cat['category_name']); ?></h3>
                        <small style="color:var(--accent-teal); letter-spacing:2px; font-weight:700; text-transform:uppercase;">View →</small>
                    </div>
                </a>
            <?php endwhile; ?>
        </div>
    </section>

    <div class="eq-divider" id="eqDivider2"></div>

    <section class="section product-section">
        <div class="section-title">
            <span class="tag">Newly Unveiled</span>
            <h2>Premier Arrivals</h2>
        </div>
        <div class="grid-container">
            <?php while($p = $featured_res->fetch_assoc()): ?>
                <div class="product-card">
                    <div class="product-image-container">
                        <span class="product-badge">Premium</span>
                        <img src="uploads/<?php echo htmlspecialchars($p['product_image']); ?>" alt="Gear">
                    </div>
                    <div class="product-info">
                        <span style="color:var(--text-muted); font-size:0.7rem; letter-spacing:1px; text-transform:uppercase; margin-bottom:5px; display:block;"><?php echo $p['brand'] ?? 'Artisan Series'; ?></span>
                        <h4 style="font-family:'Playfair Display'; font-size:1.5rem; color:#fff; margin-bottom:15px;"><?php echo htmlspecialchars($p['product_name']); ?></h4>
                        <span class="product-price">£<?php echo number_format($p['price'], 2); ?></span>
                        <a href="product_details.php?id=<?php echo $p['product_id']; ?>" class="btn-view">View Details</a>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </section>

    <?php include 'footer.php'; ?>

    <script>
    // ─── ANIMATIONS (UNTOUCHED LOGIC) ───
    const noteSymbols = ['♩','♪','♫','♬','𝄞','𝄢'];
    const wrap = document.getElementById('notesWrap');
    for (let i = 0; i < 22; i++) {
        const n = document.createElement('span');
        n.className = 'note';
        n.textContent = noteSymbols[Math.floor(Math.random() * noteSymbols.length)];
        const left = Math.random() * 100;
        const delay = Math.random() * 14;
        const dur = 8 + Math.random() * 10;
        const size = 1.2 + Math.random() * 1.8;
        n.style.cssText = `left:${left}%;bottom:-60px;animation-duration:${dur}s;animation-delay:${delay}s;font-size:${size}rem;`;
        wrap.appendChild(n);
    }

    const canvas = document.getElementById('visualiser');
    const ctx = canvas.getContext('2d');
    let W, H;
    function resize() { W = canvas.width = window.innerWidth; H = canvas.height = 120; }
    resize();
    window.addEventListener('resize', resize);

    const waves = [
        { freq: 0.018, speed: 0.012, amp: 0.55, phase: 0 },
        { freq: 0.030, speed: 0.020, amp: 0.35, phase: 1.2 },
        { freq: 0.050, speed: 0.030, amp: 0.20, phase: 2.5 },
    ];
    let t = 0;

    function drawVisualiser() {
        ctx.clearRect(0, 0, W, H);
        waves.forEach((w, wi) => {
            ctx.beginPath();
            const grad = ctx.createLinearGradient(0, 0, W, 0);
            grad.addColorStop(0, 'rgba(0,229,195,0)');
            grad.addColorStop(0.5, 'rgba(0,229,195,0.4)');
            grad.addColorStop(1, 'rgba(0,229,195,0)');
            ctx.strokeStyle = grad;
            ctx.lineWidth = 2;
            for (let x = 0; x <= W; x++) {
                const y = H - 20 - (H * w.amp * 0.45) * Math.sin(x * w.freq + t * w.speed + w.phase);
                x === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
            }
            ctx.stroke();
        });
        t++;
        requestAnimationFrame(drawVisualiser);
    }
    drawVisualiser();

    function buildEQ(id) {
        const el = document.getElementById(id);
        if (!el) return;
        for (let i = 0; i < 40; i++) {
            const b = document.createElement('div');
            b.className = 'eq-bar';
            const h = 10 + Math.random() * 40;
            const dur = 0.4 + Math.random() * 0.8;
            b.style.cssText = `height:${h}px;animation-duration:${dur}s;animation-delay:${Math.random()}s;`;
            el.appendChild(b);
        }
    }
    buildEQ('eqDivider1');
    buildEQ('eqDivider2');

    // Intersection Observer for Reveal
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                e.target.style.opacity = "1";
                e.target.style.transform = "translateY(0)";
                observer.unobserve(e.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.category-card, .product-card').forEach(c => {
        c.style.opacity = "0";
        c.style.transform = "translateY(30px)";
        c.style.transition = "all 0.8s cubic-bezier(.16,1,.3,1)";
        observer.observe(c);
    });
    </script>
</body>
</html>