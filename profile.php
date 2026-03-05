<?php
session_start();
include 'db_connect.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role']; 
$message = "";

// --- UPDATE LOGIC (UNTOUCHED) ---
if (isset($_POST['update_profile'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    $update_sql = "UPDATE users SET full_name = '$full_name', email = '$email' WHERE user_id = '$user_id'";
    if ($conn->query($update_sql)) {
        $_SESSION['full_name'] = $full_name;
        $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Profile updated successfully!</div>";

        if (!empty($new_pass)) {
            if (strlen($new_pass) < 6) {
                $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Password must be at least 6 characters long!</div>";
            } elseif ($new_pass === $confirm_pass) {
                $conn->query("UPDATE users SET password = '$new_pass' WHERE user_id = '$user_id'");
                $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Profile and Password updated!</div>";
            } else {
                $message = "<div class='alert error'><i class='fas fa-exclamation-circle'></i> Passwords do not match!</div>";
            }
        }
    }
}

$user_data = $conn->query("SELECT * FROM users WHERE user_id = '$user_id'")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings | Melody Masters</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-deep: #050508;
            --teal: #00e5c3;
            --teal-glow: rgba(0, 229, 195, 0.25);
            --amber: #e8c86e;
            --border: rgba(0, 229, 195, 0.12);
            --glass: rgba(17, 17, 24, 0.75);
            --text-muted: #5a5a72;
            --rose: #ff4f7b;
        }

        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            background: var(--bg-deep);
            color: #e0ddf5;
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ── BACKGROUND ACCENTS ── */
        .orb {
            position: fixed; border-radius: 50%; filter: blur(120px); opacity: 0.12; z-index: -1;
            animation: orbMove 20s infinite alternate;
        }
        .orb-1 { width: 500px; height: 500px; background: var(--teal); top: -100px; right: -50px; }
        .orb-2 { width: 400px; height: 400px; background: var(--amber); bottom: -50px; left: -50px; animation-delay: -5s; }

        @keyframes orbMove {
            from { transform: translate(0, 0) scale(1); }
            to { transform: translate(50px, 30px) scale(1.1); }
        }

        /* ── LAYOUT ── */
        .main-content {
            margin-left: <?php echo ($role !== 'Customer') ? '260px' : '0'; ?>;
            padding: 80px 20px;
            display: flex; justify-content: center; align-items: center;
        }

        .settings-card {
            background: var(--glass);
            backdrop-filter: blur(30px);
            -webkit-backdrop-filter: blur(30px);
            border: 1px solid var(--border);
            border-radius: 40px;
            width: 100%;
            max-width: 550px;
            padding: 60px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.6);
            position: relative;
            animation: cardFadeIn 1s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardFadeIn {
            from { opacity: 0; transform: translateY(40px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ── AVATAR SECTION ── */
        .profile-header { text-align: center; margin-bottom: 40px; }
        
        .avatar-container {
            position: relative;
            width: 110px; height: 110px;
            margin: 0 auto 20px;
        }

        .avatar-circle {
            width: 100%; height: 100%;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--teal), #00bfa0);
            display: flex; align-items: center; justify-content: center;
            font-size: 2.5rem; font-weight: 700; color: #000;
            box-shadow: 0 0 30px var(--teal-glow);
            border: 4px solid rgba(10, 10, 15, 1);
        }

        .role-tag {
            position: absolute; bottom: -5px; right: 50%; transform: translateX(50%);
            background: #fff; color: #000; padding: 4px 12px;
            border-radius: 20px; font-size: 0.65rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 1px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }

        .settings-card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem; margin-bottom: 10px;
            background: linear-gradient(to bottom, #fff, #888);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }

        /* ── ALERTS ── */
        .alert {
            padding: 15px 20px; border-radius: 16px; margin-bottom: 30px;
            font-size: 0.85rem; display: flex; align-items: center; gap: 12px;
            animation: alertIn 0.5s ease;
        }
        .success { background: rgba(0, 229, 160, 0.1); color: #00e5a0; border: 1px solid rgba(0, 229, 160, 0.2); }
        .error { background: rgba(255, 79, 123, 0.1); color: #ff4f7b; border: 1px solid rgba(255, 79, 123, 0.2); }
        
        @keyframes alertIn { from { opacity:0; transform:scale(0.95); } to { opacity:1; transform:scale(1); } }

        /* ── FORM ── */
        .form-section-title {
            display: flex; align-items: center; gap: 15px; margin: 30px 0 20px;
        }
        .form-section-title span { font-size: 0.65rem; color: var(--teal); font-weight: 700; text-transform: uppercase; letter-spacing: 2px; }
        .form-section-title::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .group { margin-bottom: 25px; text-align: left; }
        .group label { display: block; font-size: 0.7rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px; }

        .input-box { position: relative; }
        .input-box i.icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--teal); font-size: 0.9rem; }
        
        input {
            width: 100%; background: #0d0d15; border: 1px solid var(--border);
            padding: 15px 15px 15px 50px; border-radius: 18px; color: #fff;
            font-size: 0.9rem; outline: none; transition: 0.3s;
        }
        input:focus { border-color: var(--teal); box-shadow: 0 0 15px rgba(0, 229, 195, 0.15); }

        .eye-btn { position: absolute; right: 18px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--text-muted); transition: 0.3s; }
        .eye-btn:hover { color: var(--teal); }

        .btn-save {
            width: 100%; background: var(--teal); color: #000;
            padding: 18px; border: none; border-radius: 18px;
            font-weight: 800; text-transform: uppercase; letter-spacing: 2px;
            cursor: pointer; transition: 0.3s; margin-top: 20px;
            box-shadow: 0 10px 25px var(--teal-glow);
            display: flex; align-items: center; justify-content: center; gap: 10px;
        }
        .btn-save:hover { transform: translateY(-3px); filter: brightness(1.1); box-shadow: 0 15px 35px var(--teal-glow); }

    </style>
</head>
<body>

<div class="orb orb-1"></div>
<div class="orb orb-2"></div>

<?php 
if ($role === 'Admin') { include 'admin_sidebar.php'; } 
elseif ($role === 'Staff') { include 'staff_sidebar.php'; } 
else { include 'navbar.php'; }
?>

<div class="main-content">
    <div class="settings-card">
        <div class="profile-header">
            <div class="avatar-container">
                <div class="avatar-circle"><?php echo strtoupper(substr($user_data['full_name'], 0, 1)); ?></div>
                <div class="role-tag"><?php echo $role; ?></div>
            </div>
            <h2>Account Details</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem;">Manage your personal identity and security.</p>
        </div>

        <?php echo $message; ?>

        <form method="POST">
            <div class="form-section-title"><span>Identity</span></div>

            <div class="group">
                <label>Full Name</label>
                <div class="input-box">
                    <i class="fas fa-id-card icon"></i>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($user_data['full_name']); ?>" required>
                </div>
            </div>

            <div class="group">
                <label>Email Address</label>
                <div class="input-box">
                    <i class="fas fa-at icon"></i>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>
            </div>

            <div class="form-section-title"><span>Security</span></div>

            <div class="group">
                <label>New Password <small>(Leave blank to keep current)</small></label>
                <div class="input-box">
                    <i class="fas fa-shield-alt icon"></i>
                    <input type="password" id="p1" name="new_password" placeholder="••••••••">
                    <i class="fas fa-eye eye-btn" onclick="toggle('p1', this)"></i>
                </div>
            </div>

            <div class="group">
                <label>Confirm New Password</label>
                <div class="input-box">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" id="p2" name="confirm_password" placeholder="••••••••">
                    <i class="fas fa-eye eye-btn" onclick="toggle('p2', this)"></i>
                </div>
            </div>

            <button type="submit" name="update_profile" class="btn-save">
                <i class="fas fa-check-circle"></i> Save All Changes
            </button>
        </form>
    </div>
</div>

<script>
    function toggle(id, icon) {
        const input = document.getElementById(id);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.replace("fa-eye", "fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.replace("fa-eye-slash", "fa-eye");
        }
    }
</script>

</body>
</html>