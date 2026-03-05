<?php
session_start();
include 'db_connect.php';

// Authentication: Only Admin access
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// Check if a valid ID is provided in the URL
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Fetch current details of the staff member
    $stmt = $conn->prepare("SELECT full_name, email FROM users WHERE user_id = ? AND role = 'Staff'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();
    
    // Redirect if staff record does not exist
    if (!$staff) {
        header("Location: manage_staff.php");
        exit();
    }
} else {
    header("Location: manage_staff.php");
    exit();
}

// Handle Update Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_staff'])) {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Secure update using Prepared Statement
    $update_stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE user_id = ? AND role = 'Staff'");
    $update_stmt->bind_param("ssi", $full_name, $email, $id);
    
    if ($update_stmt->execute()) {
        $message = "<div class='msg success'><i class='fas fa-check-circle'></i> Staff personnel credentials updated successfully!</div>";
        // Update local variables to show updated data in the form fields
        $staff['full_name'] = $full_name;
        $staff['email'] = $email;
    } else {
        $message = "<div class='msg error'><i class='fas fa-times-circle'></i> Operational Error: Could not update details.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Personnel | Melody Masters Admin</title>
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

        /* Ambient Glow Background */
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

        .form-card { 
            background: var(--glass); 
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            padding: 60px; 
            border-radius: 40px; 
            width: 100%; 
            max-width: 500px; 
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
            justify-content: center;
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
            text-align: center;
        }

        .form-group { margin-top: 35px; text-align: left; }
        
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
            margin-bottom: 25px;
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

        .btn-gold { 
            background: linear-gradient(135deg, var(--teal), #00bfa0); 
            color: #08080e; 
            border: none; 
            padding: 20px; 
            border-radius: 18px; 
            font-weight: 800; 
            cursor: pointer; 
            width: 100%;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            font-size: 0.85rem;
            transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 25px var(--teal-glow);
            margin-top: 10px;
        }

        .btn-gold:hover { 
            transform: translateY(-5px); 
            filter: brightness(1.1);
            box-shadow: 0 15px 35px var(--teal-glow); 
        }

        .back-link { 
            display: block; 
            text-align: center; 
            margin-top: 25px; 
            color: var(--text-muted); 
            text-decoration: none; 
            font-size: 0.8rem; 
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: 0.3s;
            font-weight: 700;
        }

        .back-link:hover { color: #fff; transform: translateX(-5px); }

        .msg { 
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
            .form-card { padding: 40px; }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="form-card">
        <h2><i class="fas fa-user-pen"></i> Personnel Auditor</h2>
        <span class="header-meta">Designation Ref: #STAFF-<?php echo str_pad($id, 3, '0', STR_PAD_LEFT); ?></span>
        <div style="height: 1px; background: var(--border); margin: 30px 0;"></div>

        <?php echo $message; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Official Name</label>
                <div class="input-wrapper">
                    <i class="fas fa-id-card"></i>
                    <input type="text" name="full_name" value="<?php echo htmlspecialchars($staff['full_name']); ?>" required placeholder="Enter full name">
                </div>

                <label>Institutional Email</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope-open"></i>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($staff['email']); ?>" required placeholder="Enter email address">
                </div>
            </div>

            <button type="submit" name="update_staff" class="btn-gold">Synchronize Credentials</button>
        </form>
        
        <a href="manage_staff.php" class="back-link">
            <i class="fas fa-arrow-left-long"></i> Return to Directorate
        </a>
    </div>
</div>

</body>
</html>