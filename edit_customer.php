<?php
session_start();
include 'db_connect.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// --- UPDATE LOGIC ---
if (isset($_POST['update_customer'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $update_sql = "UPDATE users SET full_name = '$full_name', email = '$email' WHERE user_id = '$user_id' AND role = 'Customer'";
    
    if ($conn->query($update_sql)) {
        $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Profile credentials updated successfully!</div>";
    } else {
        $message = "<div class='alert error'><i class='fas fa-times-circle'></i> Operational Error: Could not update details.</div>";
    }
}

// Fetch current details
$customer_query = $conn->query("SELECT * FROM users WHERE user_id = '$user_id' AND role = 'Customer'");
$customer = $customer_query->fetch_assoc();

if (!$customer) {
    die("<div style='color:white; text-align:center; margin-top:50px; font-family:DM Sans, sans-serif;'><h2>Customer Profile Not Found!</h2></div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Client Profile | Melody Masters Admin</title>
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
            --success: #00e5a0;
            --danger: #ff4f7b;
        }

        body { 
            background: var(--bg); 
            color: #e0ddf5; 
            font-family: 'DM Sans', sans-serif; 
            display: flex; 
            margin: 0; 
            overflow-x: hidden;
            min-height: 100vh;
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
            display: flex; 
            justify-content: center; 
            align-items: center;
        }

        .edit-card { 
            background: var(--glass); 
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 60px; 
            border-radius: 40px; 
            width: 100%; 
            max-width: 550px; 
            border: 1px solid var(--border); 
            box-shadow: 0 40px 100px rgba(0,0,0,0.6);
            position: relative;
            animation: cardFade 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes cardFade {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 { 
            font-family: 'Playfair Display', serif; 
            font-size: 2.2rem; 
            margin: 0; 
            background: linear-gradient(to bottom, #fff, #888);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-meta {
            color: var(--teal);
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 3px;
            font-weight: 700;
            margin-top: 10px;
            display: block;
        }

        .form-group { margin-bottom: 30px; text-align: left; }
        
        label { 
            display: block; 
            color: var(--text-muted); 
            font-size: 0.7rem; 
            text-transform: uppercase; 
            letter-spacing: 2px;
            font-weight: 700;
            margin-bottom: 12px; 
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper i {
            position: absolute;
            left: 20px;
            color: var(--teal);
            font-size: 0.9rem;
        }

        input { 
            width: 100%; 
            padding: 16px 16px 16px 50px; 
            background: #0d0d15; 
            border: 1px solid var(--border); 
            color: white; 
            border-radius: 18px; 
            box-sizing: border-box; 
            font-size: 0.95rem;
            outline: none;
            transition: 0.3s;
        }

        input:focus { 
            border-color: var(--teal); 
            background: rgba(0, 229, 195, 0.03);
            box-shadow: 0 0 15px rgba(0, 229, 195, 0.1);
        }

        .btn-container { display: flex; gap: 15px; margin-top: 40px; }
        
        .btn-save { 
            background: linear-gradient(135deg, var(--teal), #00bfa0); 
            color: #08080e; 
            border: none; 
            padding: 18px; 
            border-radius: 16px; 
            font-weight: 800; 
            cursor: pointer; 
            flex: 2;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 0.85rem;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 25px var(--teal-glow);
        }

        .btn-save:hover { transform: translateY(-3px); filter: brightness(1.1); box-shadow: 0 15px 35px var(--teal-glow); }

        .btn-back { 
            background: transparent; 
            color: #fff; 
            border: 1.5px solid var(--border);
            text-decoration: none; 
            padding: 18px; 
            border-radius: 16px; 
            text-align: center; 
            flex: 1; 
            font-size: 0.85rem; 
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
        }

        .btn-back:hover { background: rgba(255, 255, 255, 0.05); border-color: #fff; }

        .alert { 
            padding: 18px; 
            border-radius: 16px; 
            margin-bottom: 30px; 
            font-size: 0.85rem; 
            text-align: left; 
            display: flex; 
            align-items: center; 
            gap: 12px;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

        .success { background: rgba(0, 229, 160, 0.1); color: var(--success); border: 1px solid rgba(0, 229, 160, 0.2); }
        .error { background: rgba(255, 79, 123, 0.1); color: var(--danger); border: 1px solid rgba(255, 79, 123, 0.2); }

        @media (max-width: 1024px) {
            .main-content { margin-left: 0; width: 100%; padding: 30px; }
            .edit-card { padding: 40px; }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="edit-card">
        <h2><i class="fas fa-user-pen"></i> Profile Auditor</h2>
        <span class="header-meta">Registry Identifier: #CLIENT-<?php echo str_pad($user_id, 4, '0', STR_PAD_LEFT); ?></span>
        <div style="height: 1px; background: var(--border); margin: 30px 0;"></div>

        <?php echo $message; ?>

        <form method="POST">
            <div class="form-group">
                <label>Master Name</label>
                <div class="input-wrapper">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($customer['full_name']); ?>" required placeholder="Enter full name">
                </div>
            </div>
            <div class="form-group">
                <label>Email Credentials</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope-open"></i>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($customer['email']); ?>" required placeholder="Enter email address">
                </div>
            </div>
            
            <div class="btn-container">
                <a href="manage_customers.php" class="btn-back">Cancel</a>
                <button type="submit" name="update_customer" class="btn-save">Commit Changes</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>