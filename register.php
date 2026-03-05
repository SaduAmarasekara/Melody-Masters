<?php
/**
 * Melody Masters - Premium Registration System
 * Title color changed to White
 */
include 'db_connect.php';
include 'navbar.php'; 

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email     = mysqli_real_escape_string($conn, trim($_POST['email']));
    $contact   = mysqli_real_escape_string($conn, trim($_POST['contact_number']));
    $address   = mysqli_real_escape_string($conn, trim($_POST['address']));
    $password  = $_POST['password']; 

    $checkEmail = $conn->prepare("SELECT email FROM users WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        $error = "This email is already registered. Please try to Login.";
    } 
    else if (strlen($password) < 8) {
        $error = "Security Policy: Password must be at least 8 characters long.";
    }
    else {
        $insertStmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, contact_number, address) VALUES (?, ?, ?, 'Customer', ?, ?)");
        $insertStmt->bind_param("sssss", $full_name, $email, $password, $contact, $address);
        
        if ($insertStmt->execute()) {
            $success = "Registration successful! You can now <a href='login.php' style='color:var(--teal); font-weight:bold;'>Login</a>";
        } else {
            $error = "System Error: Registration failed.";
        }
        $insertStmt->close();
    }
    $checkEmail->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-deep:     #0a0a0f;
            --bg-card:     #111118;
            --bg-input:    #0d0d15;
            --border:      rgba(0,229,195,0.1);
            --teal:        #00e5c3;
            --teal-dim:    rgba(0,229,195,0.08);
            --teal-glow:   rgba(0,229,195,0.22);
            --amber:       #e8c86e;
            --amber-dim:   rgba(232,200,110,0.08);
            --amber-glow:  rgba(232,200,110,0.2);
            --rose:        #ff4f7b;
            --rose-dim:    rgba(255,79,123,0.08);
            --green:       #00e5a0;
            --green-dim:   rgba(0,229,160,0.08);
            --text:        #e0ddf5;
            --text-muted:  #5a5a72;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--bg-deep);
            font-family: 'DM Sans', sans-serif;
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── BACKGROUND ── */
        .bg-scene {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
            overflow: hidden;
        }
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(90px);
            opacity: 0.15;
            animation: blobDrift ease-in-out infinite alternate;
        }
        .blob-1 { width:550px; height:550px; background:var(--teal);  top:-150px; right:-100px; animation-duration:14s; }
        .blob-2 { width:400px; height:400px; background:var(--amber); bottom:-80px; left:-80px;  animation-duration:17s; animation-delay:4s; }
        .blob-3 { width:280px; height:280px; background:#8b5cf6;      top:50%; left:40%;         animation-duration:20s; animation-delay:7s; opacity:0.08; }
        @keyframes blobDrift {
            from { transform:translate(0,0) scale(1); }
            to   { transform:translate(35px,25px) scale(1.08); }
        }

        .note {
            position: absolute;
            opacity: 0;
            color: var(--amber);
            filter: drop-shadow(0 0 5px var(--amber-glow));
            animation: noteRise linear infinite;
            pointer-events: none;
        }
        @keyframes noteRise {
            0%   { opacity:0; transform:translateY(0) rotate(0deg); }
            15%  { opacity:0.65; }
            85%  { opacity:0.35; }
            100% { opacity:0; transform:translateY(-100vh) rotate(360deg); }
        }

        #regWave {
            position: fixed;
            bottom:0; left:0;
            width:100%; height:90px;
            opacity:0.22;
            pointer-events:none;
            z-index:0;
        }

        /* ── LAYOUT ── */
        .page-wrapper {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 50px 20px 60px;
            min-height: 88vh;
        }

        /* ── CARD ── */
        .register-card {
            background: rgba(17,17,24,0.88);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            width: 100%;
            max-width: 720px;
            padding: 52px 48px 46px;
            border-radius: 28px;
            border: 1px solid var(--border);
            box-shadow:
                0 0 0 1px rgba(0,229,195,0.04),
                0 30px 80px rgba(0,0,0,0.55),
                inset 0 1px 0 rgba(255,255,255,0.04);
            animation: cardIn 0.9s cubic-bezier(.16,1,.3,1) both;
            position: relative;
            overflow: hidden;
        }
        .register-card::before {
            content: '';
            position: absolute;
            top:0; left:10%; right:10%;
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--teal), var(--amber), var(--teal), transparent);
            opacity: 0.6;
        }
        @keyframes cardIn {
            from { opacity:0; transform:translateY(40px) scale(0.97); }
            to   { opacity:1; transform:translateY(0)    scale(1); }
        }

        /* ── HEADER ── */
        .header-text {
            text-align: center;
            margin-bottom: 40px;
        }
        .card-icon {
            width: 60px; height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--teal-dim), var(--amber-dim));
            border: 1px solid rgba(0,229,195,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.7rem;
            margin: 0 auto 18px;
            box-shadow: 0 0 24px var(--teal-glow);
            animation: iconPop 0.7s .3s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes iconPop {
            from { transform:scale(0); opacity:0; }
            to   { transform:scale(1); opacity:1; }
        }
        .header-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: #fff;
            font-weight: 900;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .header-text p {
            color: var(--teal);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 500;
        }

        /* ── ALERTS ── */
        .alert {
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 28px;
            font-size: 0.86rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-error {
            background: var(--rose-dim);
            color: #ff8fa3;
            border: 1px solid rgba(255,79,123,0.2);
            animation: shake 0.5s ease;
        }
        .alert-success {
            background: var(--green-dim);
            color: #4dffc0;
            border: 1px solid rgba(0,229,160,0.2);
            animation: cardIn 0.5s ease;
        }
        @keyframes shake {
            0%,100% { transform:translateX(0); }
            20%      { transform:translateX(-8px); }
            40%      { transform:translateX(8px); }
            60%      { transform:translateX(-5px); }
            80%      { transform:translateX(5px); }
        }

        /* ── FORM GRID ── */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group { margin-bottom: 0; }
        .form-group label {
            display: block;
            margin-bottom: 9px;
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            transition: color 0.3s;
        }
        .form-group:focus-within label { color: var(--teal); }

        .input-box { position: relative; }
        .input-box i {
            position: absolute;
            left: 16px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.9rem;
            transition: color 0.3s, transform 0.3s;
            pointer-events: none;
        }
        .input-box:focus-within i {
            color: var(--teal);
            transform: translateY(-50%) scale(1.15);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px 14px 46px;
            background: var(--bg-input);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.35s ease;
            box-sizing: border-box;
        }
        .form-group textarea {
            padding-left: 16px;
            height: 108px;
            resize: none;
        }
        .form-group input::placeholder,
        .form-group textarea::placeholder { color: var(--text-muted); }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: rgba(0,229,195,0.45);
            background: rgba(0,229,195,0.04);
            box-shadow: 0 0 0 3px rgba(0,229,195,0.08), 0 0 18px rgba(0,229,195,0.07);
        }

        /* underline sweep */
        .input-box::after {
            content: '';
            position: absolute;
            bottom: 0; left: 50%;
            transform: translateX(-50%) scaleX(0);
            width: 80%; height: 2px;
            background: linear-gradient(90deg, var(--teal), var(--amber));
            border-radius: 1px;
            transition: transform 0.4s cubic-bezier(.16,1,.3,1);
        }
        .input-box:focus-within::after { transform: translateX(-50%) scaleX(1); }

        .full-row { grid-column: span 2; }

        /* ── BUTTON ── */
        .btn-container {
            grid-column: span 2;
            margin-top: 8px;
        }
        .register-btn {
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
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 24px var(--teal-glow);
        }
        .register-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255,255,255,0.18), transparent);
            opacity: 0;
            transition: opacity 0.3s;
        }
        .register-btn:hover::before { opacity: 1; }
        .register-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 36px var(--teal-glow);
            filter: brightness(1.08);
        }
        .register-btn:active { transform: translateY(0) scale(0.99); }

        /* ── FOOTER LINK ── */
        .footer-link {
            text-align: center;
            margin-top: 32px;
            color: var(--text-muted);
            font-size: 0.86rem;
        }
        .footer-link a {
            color: var(--amber);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            position: relative;
        }
        .footer-link a::after {
            content: '';
            position: absolute;
            bottom: -2px; left: 0;
            width: 0; height: 1px;
            background: var(--amber);
            transition: width 0.3s;
        }
        .footer-link a:hover::after { width: 100%; }
        .footer-link a:hover { color: #fff; }

        /* ── RESPONSIVE ── */
        @media (max-width: 650px) {
            .register-card { padding: 40px 24px 36px; }
            .form-grid { grid-template-columns: 1fr; }
            .full-row, .btn-container { grid-column: span 1; }
        }
    </style>
</head>
<body>

    <!-- Background -->
    <div class="bg-scene" id="bgScene">
        <div class="blob blob-1"></div>
        <div class="blob blob-2"></div>
        <div class="blob blob-3"></div>
    </div>
    <canvas id="regWave"></canvas>

    <div class="page-wrapper">
        <div class="register-card">

            <div class="header-text">
                <div class="card-icon">🎼</div>
                <h2>Create Account</h2>
                <p>Start your musical journey</p>
            </div>

            <?php if($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <?php if($success): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <div class="input-box">
                            <i class="fas fa-user"></i>
                            <input type="text" name="full_name" placeholder="John Doe" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <div class="input-box">
                            <i class="fas fa-envelope"></i>
                            <input type="email" name="email" placeholder="john@example.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password</label>
                        <div class="input-box">
                            <i class="fas fa-lock"></i>
                            <input type="password" name="password" placeholder="Min. 8 characters" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <div class="input-box">
                            <i class="fas fa-phone"></i>
                            <input type="text" name="contact_number" placeholder="07XXXXXXXX" required>
                        </div>
                    </div>

                    <div class="form-group full-row">
                        <label>Shipping Address</label>
                        <textarea name="address" placeholder="Enter your street and city..." required></textarea>
                    </div>

                    <div class="btn-container">
                        <button type="submit" class="register-btn">Register Now &nbsp;→</button>
                    </div>
                </div>
            </form>

            <div class="footer-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script>
    // ── FLOATING NOTES ───────────────────────────────────────
    const notes   = ['♩','♪','♫','♬','𝄞','♭','♯'];
    const scene   = document.getElementById('bgScene');
    for (let i = 0; i < 14; i++) {
        const n   = document.createElement('span');
        n.className = 'note';
        n.textContent = notes[Math.floor(Math.random() * notes.length)];
        const left  = Math.random() * 100;
        const dur   = 10 + Math.random() * 10;
        const delay = Math.random() * 16;
        const size  = 1  + Math.random() * 1.3;
        n.style.cssText = `left:${left}%;bottom:-40px;animation-duration:${dur}s;animation-delay:${delay}s;font-size:${size}rem;`;
        scene.appendChild(n);
    }

    // ── WAVEFORM CANVAS ──────────────────────────────────────
    const canvas = document.getElementById('regWave');
    const ctx    = canvas.getContext('2d');
    let W, H;
    function resize() { W = canvas.width = window.innerWidth; H = canvas.height = 90; }
    resize();
    window.addEventListener('resize', resize);
    const waves = [
        { freq:0.018, speed:0.012, amp:0.55, phase:0   },
        { freq:0.032, speed:0.022, amp:0.30, phase:1.4 },
        { freq:0.055, speed:0.036, amp:0.16, phase:2.9 },
    ];
    let t = 0;
    function draw() {
        ctx.clearRect(0,0,W,H);
        waves.forEach((w,wi) => {
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
            ctx.shadowColor = wi%2===0?'rgba(0,229,195,0.4)':'rgba(232,200,110,0.35)';
            for (let x=0;x<=W;x++) {
                const y = H-10-(H*w.amp*0.42)*Math.sin(x*w.freq+t*w.speed+w.phase);
                x===0?ctx.moveTo(x,y):ctx.lineTo(x,y);
            }
            ctx.stroke();
        });
        t++;
        requestAnimationFrame(draw);
    }
    draw();

    // ── BUTTON RIPPLE ────────────────────────────────────────
    document.querySelector('.register-btn').addEventListener('click', function(e) {
        const btn    = this;
        const ripple = document.createElement('span');
        const rect   = btn.getBoundingClientRect();
        const size   = Math.max(rect.width, rect.height) * 1.4;
        ripple.style.cssText = `
            position:absolute;
            width:${size}px;height:${size}px;
            border-radius:50%;
            background:rgba(255,255,255,0.18);
            top:${e.clientY-rect.top-size/2}px;
            left:${e.clientX-rect.left-size/2}px;
            transform:scale(0);
            animation:rippleAnim 0.6s ease-out forwards;
            pointer-events:none;
        `;
        btn.appendChild(ripple);
        setTimeout(()=>ripple.remove(), 650);
    });
    const s = document.createElement('style');
    s.textContent = `@keyframes rippleAnim{to{transform:scale(1);opacity:0;}}`;
    document.head.appendChild(s);

    // ── STAGGER INPUT ANIMATIONS ─────────────────────────────
    document.querySelectorAll('.form-group').forEach((g,i) => {
        g.style.cssText = `opacity:0;transform:translateY(18px);animation:cardIn 0.6s ${0.3+i*0.07}s cubic-bezier(.16,1,.3,1) both;`;
    });
    const s2 = document.createElement('style');
    s2.textContent = `@keyframes cardIn{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}`;
    document.head.appendChild(s2);
    </script>
</body>
</html>