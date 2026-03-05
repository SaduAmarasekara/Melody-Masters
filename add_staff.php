<?php
session_start();
include 'db_connect.php';

// Authentication: Strictly only Admin can access this page
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// --- LOGIC TO REGISTER NEW STAFF ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register_staff'])) {
    
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; 
    $role = 'Staff';

    $check_email = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check_email->bind_param("s", $email);
    $check_email->execute();
    $result = $check_email->get_result();

    if ($result->num_rows > 0) {
        $message = "<div class='msg error'><i class='fas fa-exclamation-triangle'></i> Error: This email is already assigned!</div>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $full_name, $email, $password, $role);
        
        if ($stmt->execute()) {
            $message = "<div class='msg success'><i class='fas fa-check-circle'></i> Staff account for <b>$full_name</b> created successfully!</div>";
        } else {
            $message = "<div class='msg error'><i class='fas fa-times-circle'></i> Error: Failed to create staff account.</div>";
        }
        $stmt->close();
    }
    $check_email->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Staff | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep:   #0a0a0f;
            --bg-card:   #111118;
            --bg-input:  #0d0d15;
            --border:    rgba(0,229,195,0.1);
            --teal:      #00e5c3;
            --teal-dim:  rgba(0,229,195,0.08);
            --teal-glow: rgba(0,229,195,0.22);
            --amber:     #e8c86e;
            --amber-dim: rgba(232,200,110,0.08);
            --amber-glow:rgba(232,200,110,0.2);
            --rose:      #ff4f7b;
            --green-dim: rgba(0,229,160,0.08);
            --text:      #e0ddf5;
            --text-muted:#5a5a72;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            background: var(--bg-deep);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            display: flex;
            min-height: 100vh;
        }

        /* subtle bg glow */
        body::before {
            content:'';
            position:fixed;
            top:-100px; right:-100px;
            width:500px; height:500px;
            border-radius:50%;
            background:radial-gradient(circle,rgba(0,229,195,.05),transparent 70%);
            pointer-events:none; z-index:0;
        }
        body::after {
            content:'';
            position:fixed;
            bottom:-100px; left:200px;
            width:400px; height:400px;
            border-radius:50%;
            background:radial-gradient(circle,rgba(232,200,110,.04),transparent 70%);
            pointer-events:none; z-index:0;
        }

        /* ── LAYOUT ── */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            box-sizing: border-box;
            position: relative;
            z-index: 1;
        }

        /* ── FORM CARD ── */
        .form-card {
            background: rgba(17,17,24,.9);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            padding: 50px 44px 44px;
            border-radius: 28px;
            width: 100%;
            max-width: 460px;
            border: 1px solid var(--border);
            box-shadow: 0 30px 70px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.04);
            position: relative;
            overflow: hidden;
            animation: cardIn .9s cubic-bezier(.16,1,.3,1) both;
        }
        /* top gradient line */
        .form-card::before {
            content:'';
            position:absolute; top:0; left:10%; right:10%;
            height:1px;
            background:linear-gradient(90deg,transparent,var(--teal),var(--amber),transparent);
            opacity:.55;
        }
        @keyframes cardIn {
            from { opacity:0; transform:translateY(36px) scale(.97); }
            to   { opacity:1; transform:translateY(0)    scale(1); }
        }

        /* ── CARD HEADER ── */
        .card-header {
            text-align: center;
            margin-bottom: 36px;
        }
        .card-header .header-icon {
            width: 62px; height: 62px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--teal-dim), var(--amber-dim));
            border: 1px solid rgba(0,229,195,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem;
            margin: 0 auto 18px;
            box-shadow: 0 0 26px var(--teal-glow);
            animation: iconPop .7s .3s cubic-bezier(.16,1,.3,1) both;
        }
        @keyframes iconPop {
            from { transform:scale(0); opacity:0; }
            to   { transform:scale(1); opacity:1; }
        }
        .card-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.9rem; font-weight: 700;
            background: linear-gradient(135deg, #fff 30%, var(--amber));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 6px;
        }
        .card-header p {
            color: var(--teal);
            font-size: .68rem;
            letter-spacing: 3px;
            text-transform: uppercase;
            font-weight: 500;
        }

        /* ── ALERTS ── */
        .msg {
            padding: 13px 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: .86rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success {
            background: var(--green-dim);
            color: #4dffc0;
            border: 1px solid rgba(0,229,160,.25);
            animation: fadeUp .5s ease;
        }
        .error {
            background: rgba(255,79,123,.08);
            color: #ff8fa3;
            border: 1px solid rgba(255,79,123,.2);
            animation: shake .5s ease;
        }
        @keyframes fadeUp { from{opacity:0;transform:translateY(8px);}to{opacity:1;transform:translateY(0);} }
        @keyframes shake  { 0%,100%{transform:translateX(0);}20%{transform:translateX(-7px);}40%{transform:translateX(7px);}60%{transform:translateX(-4px);}80%{transform:translateX(4px);} }

        /* ── FORM FIELDS ── */
        .form-field {
            margin-bottom: 20px;
            animation: fadeUp .6s both;
        }
        .form-field:nth-child(1){ animation-delay:.1s; }
        .form-field:nth-child(2){ animation-delay:.18s; }
        .form-field:nth-child(3){ animation-delay:.26s; }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: .7rem;
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: color .3s;
        }
        .form-field:focus-within label { color: var(--teal); }

        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: .9rem;
            transition: color .3s, transform .3s;
            pointer-events: none;
        }
        .input-wrap:focus-within i {
            color: var(--teal);
            transform: translateY(-50%) scale(1.15);
        }

        input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            background: var(--bg-input);
            border: 1px solid rgba(255,255,255,.06);
            border-radius: 14px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
            box-sizing: border-box;
            outline: none;
            transition: all .35s;
        }
        input::placeholder { color: var(--text-muted); }
        input:focus {
            border-color: rgba(0,229,195,.45);
            background: rgba(0,229,195,.04);
            box-shadow: 0 0 0 3px rgba(0,229,195,.08), 0 0 18px rgba(0,229,195,.07);
        }

        /* underline sweep */
        .input-wrap::after {
            content:'';
            position:absolute; bottom:0; left:50%;
            transform:translateX(-50%) scaleX(0);
            width:80%; height:2px;
            background:linear-gradient(90deg,var(--teal),var(--amber));
            border-radius:1px;
            transition:transform .4s cubic-bezier(.16,1,.3,1);
        }
        .input-wrap:focus-within::after { transform:translateX(-50%) scaleX(1); }

        /* ── SUBMIT BUTTON ── */
        .btn-gold {
            width: 100%; padding: 15px;
            background: linear-gradient(135deg, var(--teal), #00bfa0);
            color: #08080e;
            border: none;
            border-radius: 14px;
            font-family: 'DM Sans', sans-serif;
            font-weight: 700; font-size: .9rem;
            cursor: pointer;
            text-transform: uppercase; letter-spacing: 2px;
            transition: all .35s;
            position: relative; overflow: hidden;
            box-shadow: 0 6px 24px var(--teal-glow);
            margin-top: 6px;
            display: flex; align-items: center; justify-content: center; gap: 10px;
            animation: fadeUp .6s .34s both;
        }
        .btn-gold::before {
            content:''; position:absolute; inset:0;
            background:linear-gradient(135deg,rgba(255,255,255,.18),transparent);
            opacity:0; transition:opacity .3s;
        }
        .btn-gold:hover::before { opacity:1; }
        .btn-gold:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 12px 36px var(--teal-glow);
            filter: brightness(1.08);
        }
        .btn-gold:active { transform: translateY(0) scale(.99); }

        /* ── BACK LINK ── */
        .back-link {
            text-align: center;
            margin-top: 26px;
            animation: fadeUp .6s .42s both;
        }
        .back-link a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: .82rem;
            display: inline-flex; align-items: center; gap: 7px;
            transition: all .3s;
            padding: 8px 16px;
            border-radius: 20px;
            border: 1px solid transparent;
        }
        .back-link a:hover {
            color: var(--teal);
            background: var(--teal-dim);
            border-color: rgba(0,229,195,.15);
            transform: translateX(-4px);
        }

        /* responsive */
        @media(max-width:768px) {
            .main-content { margin-left:0; width:100%; padding:24px; }
            .form-card { padding:36px 24px 32px; }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="form-card">

        <div class="card-header">
            <div class="header-icon">👤</div>
            <h2>Add New Staff</h2>
            <p>Admin Access Only</p>
        </div>

        <?php echo $message; ?>

        <form method="POST">
            <div class="form-field">
                <label>Full Name</label>
                <div class="input-wrap">
                    <i class="fas fa-user"></i>
                    <input type="text" name="full_name" placeholder="Enter Full Name" required>
                </div>
            </div>

            <div class="form-field">
                <label>Email Address</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope"></i>
                    <input type="email" name="email" placeholder="staff@melodymasters.com" required>
                </div>
            </div>

            <div class="form-field">
                <label>Temporary Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock"></i>
                    <input type="password" name="password" placeholder="Create a password" required>
                </div>
            </div>

            <button type="submit" name="register_staff" class="btn-gold" id="submitBtn">
                <i class="fas fa-user-plus"></i> Register Staff Member
            </button>
        </form>

        <div class="back-link">
            <a href="admin_dashboard.php">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

<script>
    // Button ripple effect
    document.getElementById('submitBtn').addEventListener('click', function(e) {
        const btn    = this;
        const ripple = document.createElement('span');
        const rect   = btn.getBoundingClientRect();
        const size   = Math.max(rect.width, rect.height) * 1.4;
        ripple.style.cssText = `
            position:absolute; width:${size}px; height:${size}px;
            border-radius:50%; background:rgba(255,255,255,.15);
            top:${e.clientY - rect.top - size/2}px;
            left:${e.clientX - rect.left - size/2}px;
            transform:scale(0);
            animation:rpl .6s ease-out forwards;
            pointer-events:none;
        `;
        btn.appendChild(ripple);
        setTimeout(() => ripple.remove(), 650);
    });
    const s = document.createElement('style');
    s.textContent = `@keyframes rpl { to { transform:scale(1); opacity:0; } }`;
    document.head.appendChild(s);
</script>

</body>
</html>