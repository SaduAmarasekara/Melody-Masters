<?php
session_start();
include 'db_connect.php';

// --- AUTHENTICATION CHECK ---
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// --- DELETE CUSTOMER LOGIC ---
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'Customer'");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "<div class='msg success'><i class='fas fa-check-circle'></i> Customer account removed successfully!</div>";
    } else {
        $message = "<div class='msg error'><i class='fas fa-times-circle'></i> Error: Could not delete customer.</div>";
    }
    $stmt->close();
}

// --- SEARCH AND FETCH LOGIC ---
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    $query = "SELECT user_id, full_name, email FROM users 
              WHERE role = 'Customer' AND (full_name LIKE '%$search_query%' OR email LIKE '%$search_query%')
              ORDER BY user_id DESC";
} else {
    $query = "SELECT user_id, full_name, email FROM users WHERE role = 'Customer' ORDER BY user_id DESC";
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Customers | Melody Masters Admin</title>
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
            --danger: #ff4f7b;
            --blue: #3b82f6;
        }

        body { 
            background: var(--bg); 
            color: #e0ddf5; 
            font-family: 'DM Sans', sans-serif; 
            display: flex; 
            margin: 0; 
            overflow-x: hidden;
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
            min-height: 100vh; 
        }

        /* ── TOP BAR ── */
        .header-section { margin-bottom: 40px; border-left: 3px solid var(--teal); padding-left: 25px; }
        .header-section h2 { 
            font-family: 'Playfair Display', serif; 
            font-size: 2.2rem; 
            margin: 0; 
            background: linear-gradient(to bottom, #fff, #888);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent;
        }

        /* ── SEARCH BAR ── */
        .search-form { 
            display: flex; 
            gap: 12px; 
            margin-bottom: 40px; 
            background: var(--glass); 
            padding: 10px; 
            border-radius: 20px; 
            border: 1px solid var(--border);
            max-width: 600px;
        }
        .search-input { 
            flex: 1; 
            padding: 12px 20px; 
            border: none; 
            background: transparent; 
            color: white; 
            outline: none; 
            font-size: 0.9rem;
        }
        .btn-search { 
            background: var(--teal); 
            color: #000; 
            border: none; 
            padding: 12px 25px; 
            border-radius: 14px; 
            cursor: pointer; 
            font-weight: 700; 
            transition: 0.3s;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px var(--teal-glow);
        }
        .btn-search:hover { filter: brightness(1.1); transform: translateY(-2px); }

        /* ── TABLE CARD ── */
        .glass-card { 
            background: var(--glass); 
            backdrop-filter: blur(25px);
            border-radius: 30px; 
            padding: 40px; 
            border: 1px solid var(--border); 
            box-shadow: 0 30px 60px rgba(0,0,0,0.4);
            animation: fadeIn 0.8s ease-out;
        }

        table { width: 100%; border-collapse: collapse; }
        th { 
            text-align: left; 
            color: var(--teal); 
            padding: 20px; 
            border-bottom: 1px solid var(--border); 
            font-size: 0.75rem; 
            text-transform: uppercase; 
            letter-spacing: 2px;
        }
        td { padding: 22px 20px; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 0.95rem; }
        
        tr:hover td { background: rgba(0, 229, 195, 0.02); }

        .user-id-pill {
            background: rgba(255, 255, 255, 0.05);
            padding: 4px 10px;
            border-radius: 8px;
            font-family: monospace;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        /* ── ACTIONS ── */
        .action-btns { display: flex; gap: 15px; }
        .btn-edit { 
            width: 40px; height: 40px; border-radius: 12px;
            background: rgba(59, 130, 246, 0.1); color: var(--blue);
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; transition: 0.3s; border: 1px solid rgba(59, 130, 246, 0.2);
        }
        .btn-edit:hover { background: var(--blue); color: #fff; transform: translateY(-3px); }

        .btn-delete { 
            width: 40px; height: 40px; border-radius: 12px;
            background: rgba(255, 79, 123, 0.1); color: var(--danger);
            display: flex; align-items: center; justify-content: center;
            border: 1px solid rgba(255, 79, 123, 0.2); cursor: pointer; transition: 0.3s;
        }
        .btn-delete:hover { background: var(--danger); color: #fff; transform: translateY(-3px); }

        /* ── MESSAGES ── */
        .msg { padding: 15px 25px; border-radius: 15px; margin-bottom: 30px; font-size: 0.9rem; display: flex; align-items: center; gap: 12px; }
        .success { background: rgba(0, 229, 160, 0.1); color: #00e5a0; border: 1px solid rgba(0, 229, 160, 0.2); }
        .error { background: rgba(255, 79, 123, 0.1); color: #ff4f7b; border: 1px solid rgba(255, 79, 123, 0.2); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 1024px) {
            .main-content { margin-left: 0; width: 100%; padding: 30px; }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="header-section">
        <h2>Customer Registry</h2>
        <p style="color: var(--text-muted); margin-top: 5px;">Manage global customer profiles and account access.</p>
    </div>

    <form method="GET" class="search-form">
        <i class="fas fa-search" style="margin-left: 15px; color: var(--text-muted);"></i>
        <input type="text" name="search" class="search-input" placeholder="Search name or credentials..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" class="btn-search">Filter</button>
        <?php if($search_query): ?>
            <a href="manage_customers.php" style="color: var(--danger); text-decoration: none; font-size: 0.7rem; font-weight: 800; margin-right: 15px; align-self: center;">RESET</a>
        <?php endif; ?>
    </form>

    <?php echo $message; ?>

    <div class="glass-card">
        <table>
            <thead>
                <tr>
                    <th>Identifier</th>
                    <th>Master Name</th>
                    <th>Contact Credentials</th>
                    <th style="text-align: right;">Administrative Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><span class="user-id-pill">#<?php echo str_pad($row['user_id'], 4, '0', STR_PAD_LEFT); ?></span></td>
                        <td style="font-weight: 700; color: #fff;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td style="color: var(--text-muted);"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <div class="action-btns" style="justify-content: flex-end;">
                                <a href="edit_customer.php?id=<?php echo $row['user_id']; ?>" class="btn-edit" title="Modify Credentials">
                                    <i class="fas fa-pen-nib"></i>
                                </a>
                                <a href="manage_customers.php?delete_id=<?php echo $row['user_id']; ?>" 
                                   class="btn-delete" 
                                   onclick="return confirm('Attention: Are you sure you want to permanently revoke this customer\'s access?')" title="Revoke Access">
                                    <i class="fas fa-user-slash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align:center; padding:60px; color:var(--text-muted);">No archival records match your current filter.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>