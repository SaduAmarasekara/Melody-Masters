<?php
/**
 * Melody Masters - Complete Plain Text Login
 * Optimized with Soft Gold Premium Theme
 */
session_start();
include 'db_connect.php'; 

$error = "";

// --- 1. PHP LOGIN LOGIC (PLAIN TEXT) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $password = $_POST['password']; 

    $stmt = $conn->prepare("SELECT user_id, full_name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if ($password === $user['password']) {
            session_regenerate_id(true); 
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = trim($user['role']); 

            $checkRole = strtolower($_SESSION['role']);
            
            if ($checkRole === 'admin') {
                header("Location: admin_dashboard.php");
            } 
            elseif ($checkRole === 'staff') {
                header("Location: staff_dashboard.php");
            } 
            else {
                header("Location: index.php");
            }
            exit(); 
            
        } else { 
            $error = "Invalid password. Access denied."; 
        }
    } else { 
        $error = "Account not found. Please register first."; 
    }
    $stmt->close();
}

include 'navbar.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-deep:      #0a0a0f;
            --bg-card:      #111118;
            --bg-input:     #0d0d15;
            --border:       rgba(0,229,195,0.1);
            --teal:         #00e5c3;
            --teal-dim:     rgba(0,229,195,0.08);
            --teal-glow:    rgba(0,229,195,0.25);
            --amber:        #e8c86e;
            --amber-dim:    rgba(232,200,110,0.08);
            --amber-glow:   rgba(232,200,110,0.22);
            --rose:         #ff4f7b;
            --rose-dim:     rgba(255,79,123,0.08);
            --text:         #e0ddf5;
            --text-muted:   #5a5a72;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--bg-deep);
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* ── ANIMATED BACKGROUND ── */
        .bg-scene {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }

        /* mesh blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.18;
            animation: blobDrift ease-in-out infinite alternate;
        }
        .blob-1 { width:500px; height:500px; background:var(--teal);    top:-120px; left:-100px; animation-duration:12s; }
        .blob-2 { width:400px; height:400px; background:var(--amber);   bottom:-80px; right:-80px; animation-duration:15s; animation-delay:3s; }
        .blob-3 { width:300px; height:300px; background:#8b5cf6;        top:40%; left:60%; animation-duration:18s; animation-delay:6s; opacity:0.10; }
        @keyframes blobDrift {
            from { transform: translate(0,0) scale(1); }
            to   { transform: translate(40px,30px) scale(1.1); }
        }

        /* floating notes */
        .note {
            position: absolute;
            opacity: 0;
            color: var(--amber);
            filter: drop-shadow(0 0 6px var(--amber-glow));
            animation: noteRise linear infinite;
            font-size: 1.3rem;
            pointer-events: none;
        }
        @keyframes noteRise {
            0%   { opacity:0; transform:translateY(0) rotate(0deg); }
            15%  { opacity:0.7; }
            85%  { opacity:0.4; }
            100% { opacity:0; transform:translateY(-100vh) rotate(360deg); }
        }

        /* canvas waveform */
        #loginWave {
            position: fixed;
            bottom: 0; left: 0;
            width: 100%; height: 90px;
            opacity: 0.25;
            pointer-events: none;
            z-index: 0;
        }

        /* ── PAGE LAYOUT ── */
        .page-container {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 88vh;
            padding: 40px 20px;
        }

        /* ── LOGIN CARD ── */
        .login-card {
            background: rgba(17,17,24,0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            width: 100%;
            max-width: 440px;
            padding: 52px 44px 44px;
            border-radius: 28px;
            border: 1px solid var(--border);
            box-shadow:
                0 0 0 1px rgba(0,229,195,0.04),
                0 30px 70px rgba(0,0,0,0.55),
                inset 0 1px 0 rgba(255,255,255,0.04);
            text-align: center;
            animation: cardIn 0.9s cubic-bezier(.16,1,.3,1) both;
            position: relative;
            overflow: hidden;
        }

        /* inner glow top edge */
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 10%; right: 10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--teal), var(--amber), var(--teal), transparent);
            opacity: 0.6;
        }

        @keyframes cardIn {
            from { opacity:0; transform:translateY(40px) scale(0.97); }
            to   { opacity:1; transform:translateY(0)    scale(1); }
        }

        /* icon badge */
        .card-icon {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--teal-dim), var(--amber-dim));
            border: 1px solid rgba(0,229,195,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 22px;
            box-shadow: 0 0 28px var(--teal-glow);
            animation: iconPop 0.7s .4s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes iconPop {
            from { transform:scale(0); opacity:0; }
            to   { transform:scale(1); opacity:1; }
        }

        .login-card h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 900;
            color: #fff;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            animation: cardIn 0.8s .2s both;
        }

        .subtitle {
            color: var(--teal);
            font-size: 0.72rem;
            margin-bottom: 36px;
            display: block;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 500;
            animation: cardIn 0.8s .3s both;
        }

        /* ── FORM ── */
        .form-group {
            text-align: left;
            margin-bottom: 22px;
            animation: cardIn 0.7s .35s both;
        }

        .form-group label {
            color: var(--text-muted);
            font-size: 0.78rem;
            font-weight: 600;
            margin-bottom: 9px;
            display: block;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: color 0.3s;
        }
        .form-group:focus-within label { color: var(--teal); }

        .input-group { position: relative; }

        .input-group i {
            position: absolute;
            left: 18px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.95rem;
            transition: color 0.3s, transform 0.3s;
            pointer-events: none;
        }
        .input-group:focus-within i {
            color: var(--teal);
            transform: translateY(-50%) scale(1.15);
        }

        .input-group input {
            width: 100%;
            padding: 15px 18px 15px 48px;
            background: var(--bg-input);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.92rem;
            box-sizing: border-box;
            transition: all 0.35s ease;
            outline: none;
        }
        .input-group input::placeholder { color: var(--text-muted); }
        .input-group input:focus {
            border-color: rgba(0,229,195,0.45);
            background: rgba(0,229,195,0.04);
            box-shadow: 0 0 0 3px rgba(0,229,195,0.08), 0 0 20px rgba(0,229,195,0.08);
        }

        /* ripple bar under input */
        .input-group::after {
            content: '';
            position: absolute;
            bottom: 0; left: 50%;
            transform: translateX(-50%) scaleX(0);
            width: 80%; height: 2px;
            background: linear-gradient(90deg, var(--teal), var(--amber));
            border-radius: 1px;
            transition: transform 0.4s cubic-bezier(.16,1,.3,1);
        }
        .input-group:focus-within::after { transform: translateX(-50%) scaleX(1); }

        /* ── SUBMIT BUTTON ── */
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, var(--teal), #00bfa0);
            color: #08080e;
            border: none;
            border-radius: 14px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 700;
            font-size: 0.9rem;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: all 0.35s ease;
            margin-top: 8px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 24px var(--teal-glow);
            animation: cardIn 0.7s .5s both;
        }
        .btn-submit::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.18), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .btn-submit:hover::before { opacity: 1; }
        .btn-submit:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 36px var(--teal-glow);
            filter: brightness(1.08);
        }
        .btn-submit:active { transform: translateY(0) scale(0.99); }

        /* ── ERROR BOX ── */
        .error-box {
            background: var(--rose-dim);
            color: #ff8fa3;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            border: 1px solid rgba(255,79,123,0.2);
            font-size: 0.86rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: shake 0.5s ease;
        }
        @keyframes shake {
            0%,100% { transform:translateX(0); }
            20%      { transform:translateX(-8px); }
            40%      { transform:translateX(8px); }
            60%      { transform:translateX(-5px); }
            80%      { transform:translateX(5px); }
        }

        /* ── DIVIDER ── */
        .divider {
            display: flex;
            align-items: center;
            gap: 14px;
            margin: 28px 0 20px;
            animation: cardIn 0.7s .55s both;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255,255,255,0.06);
        }
        .divider span {
            font-size: 0.72rem;
            color: var(--text-muted);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        /* ── SUPPORT LINK ── */
        .support-link {
            color: var(--text-muted);
            font-size: 0.86rem;
            animation: cardIn 0.7s .6s both;
        }
        .support-link a {
            color: var(--amber);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
        }
        .support-link a::after {
            content: '';
            position: absolute;
            bottom: -2px; left: 0;
            width: 0; height: 1px;
            background: var(--amber);
            transition: width 0.3s;
        }
        .support-link a:hover::after { width: 100%; }
        .support-link a:hover { color: #fff; }

        /* ── RESPONSIVE ── */
        @media (max-width: 480px) {
            .login-card { padding: 40px 28px 36px; }
        }
    </style>
</head>
<body>

    <!-- Animated background scene -->
    <div class="bg-scene" id="bgScene">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>
    <canvas id="loginWave"></canvas>

    <div class="page-container">
        <div class="login-card">

            <div class="card-icon">🎵</div>
            <h1>Welcome Back</h1>
            <span class="subtitle">Melody Masters Console</span>

            <?php if($error): ?>
                <div class="error-box">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <input type="email" name="email" required placeholder="name@example.com">
                    </div>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" required placeholder="••••••••">
                    </div>
                </div>

                <button type="submit" name="login" class="btn-submit">
                    Sign In &nbsp;→
                </button>
            </form>

            <div class="divider"><span>New here?</span></div>

            <div class="support-link">
                Don't have an account? <a href="register.php">Register Here</a>
            </div>

        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    // ── FLOATING NOTES ───────────────────────────────────────
    const noteSymbols = ['♩','♪','♫','♬','𝄞','♭','♯'];
    const scene = document.getElementById('bgScene');
    for (let i = 0; i < 16; i++) {
        const n = document.createElement('span');
        n.className = 'note';
        n.textContent = noteSymbols[Math.floor(Math.random() * noteSymbols.length)];
        const left  = Math.random() * 100;
        const dur   = 9  + Math.random() * 10;
        const delay = Math.random() * 14;
        const size  = 1  + Math.random() * 1.4;
        n.style.cssText = `left:${left}%;bottom:-40px;animation-duration:${dur}s;animation-delay:${delay}s;font-size:${size}rem;`;
        scene.appendChild(n);
    }

    // ── WAVEFORM CANVAS ──────────────────────────────────────
    const canvas = document.getElementById('loginWave');
    const ctx    = canvas.getContext('2d');
    let W, H;
    function resize() { W = canvas.width = window.innerWidth; H = canvas.height = 90; }
    resize();
    window.addEventListener('resize', resize);

    const waves = [
        { freq:0.018, speed:0.012, amp:0.55, phase:0   },
        { freq:0.032, speed:0.022, amp:0.32, phase:1.3 },
        { freq:0.052, speed:0.034, amp:0.18, phase:2.7 },
    ];
    let t = 0;
    function draw() {
        ctx.clearRect(0, 0, W, H);
        waves.forEach((w, wi) => {
            ctx.beginPath();
            const g = ctx.createLinearGradient(0,0,W,0);
            if (wi % 2 === 0) {
                g.addColorStop(0,'rgba(0,229,195,0)');
                g.addColorStop(.4,'rgba(0,229,195,0.7)');
                g.addColorStop(.6,'rgba(0,229,195,0.7)');
                g.addColorStop(1,'rgba(0,229,195,0)');
            } else {
                g.addColorStop(0,'rgba(232,200,110,0)');
                g.addColorStop(.4,'rgba(232,200,110,0.6)');
                g.addColorStop(.6,'rgba(232,200,110,0.6)');
                g.addColorStop(1,'rgba(232,200,110,0)');
            }
            ctx.strokeStyle = g;
            ctx.lineWidth   = 1.8;
            ctx.shadowBlur  = 10;
            ctx.shadowColor = wi % 2 === 0 ? 'rgba(0,229,195,0.4)' : 'rgba(232,200,110,0.35)';
            for (let x = 0; x <= W; x++) {
                const y = H - 12 - (H * w.amp * 0.42) * Math.sin(x * w.freq + t * w.speed + w.phase);
                x === 0 ? ctx.moveTo(x,y) : ctx.lineTo(x,y);
            }
            ctx.stroke();
        });
        t++;
        requestAnimationFrame(draw);
    }
    draw();

    // ── BUTTON RIPPLE ────────────────────────────────────────
    document.querySelector('.btn-submit').addEventListener('click', function(e) {
        const btn  = this;
        const ripple = document.createElement('span');
        const rect   = btn.getBoundingClientRect();
        const size   = Math.max(rect.width, rect.height) * 1.4;
        ripple.style.cssText = `
            position:absolute;
            width:${size}px; height:${size}px;
            border-radius:50%;
            background:rgba(255,255,255,0.18);
            top:${e.clientY - rect.top  - size/2}px;
            left:${e.clientX - rect.left - size/2}px;
            transform:scale(0);
            animation:rippleAnim 0.6s ease-out forwards;
            pointer-events:none;
        `;
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 650);
    });

    const styleEl = document.createElement('style');
    styleEl.textContent = `@keyframes rippleAnim { to { transform:scale(1); opacity:0; } }`;
    document.head.appendChild(styleEl);
    </script>
</body>
</html>