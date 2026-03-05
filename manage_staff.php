<?php
session_start();
include 'db_connect.php';

// Authentication: Strictly restrict access to Admin users only
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// --- DELETE STAFF LOGIC ---
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];
    
    // Using Prepared Statements for security against SQL Injection
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role = 'Staff'");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "<div class='msg success'><i class='fas fa-check-circle'></i> Staff personnel credentials revoked successfully!</div>";
    } else {
        $message = "<div class='msg error'><i class='fas fa-times-circle'></i> Protocol Error: Could not delete staff member.</div>";
    }
    $stmt->close();
}

// --- SEARCH AND FETCH LOGIC ---
$search_query = "";
if (isset($_GET['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_GET['search']);
    
    $query = "SELECT user_id, full_name, email FROM users 
              WHERE role = 'Staff' AND (full_name LIKE '%$search_query%' OR email LIKE '%$search_query%')
              ORDER BY user_id DESC";
} else {
    $query = "SELECT user_id, full_name, email FROM users WHERE role = 'Staff' ORDER BY user_id DESC";
}
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Staff Directorate | Melody Masters Admin</title>
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
            --amber: #e8c86e;
        }

        body { 
            background: var(--bg); 
            color: #e0ddf5; 
            font-family: 'DM Sans', sans-serif; 
            display: flex; 
            margin: 0; 
            overflow-x: hidden;
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
            min-height: 100vh; 
        }

        /* ── TOP BAR ── */
        .header-flex { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 45px; 
            border-left: 3px solid var(--teal); 
            padding-left: 25px;
        }
        
        .header-flex h2 { 
            font-family: 'Playfair Display', serif; 
            font-size: 2.2rem; 
            margin: 0; 
            background: linear-gradient(to bottom, #fff, #888);
            -webkit-background-clip: text; 
            -webkit-text-fill-color: transparent;
        }

        .btn-add { 
            background: var(--teal); 
            color: #000; 
            text-decoration: none; 
            padding: 14px 28px; 
            border-radius: 16px; 
            font-weight: 800; 
            transition: 0.3s;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            box-shadow: 0 5px 15px var(--teal-glow);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn-add:hover { transform: translateY(-3px); filter: brightness(1.1); box-shadow: 0 8px 25px var(--teal-glow); }

        /* ── SEARCH AREA ── */
        .search-shelf { 
            display: flex; 
            gap: 12px; 
            margin-bottom: 35px; 
            background: var(--glass); 
            padding: 10px; 
            border-radius: 20px; 
            border: 1px solid var(--border);
            max-width: 600px;
            backdrop-filter: blur(15px);
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
            background: rgba(255,255,255,0.05); 
            color: #fff; 
            border: 1px solid var(--border); 
            padding: 10px 22px; 
            border-radius: 12px; 
            cursor: pointer; 
            font-weight: 700; 
            transition: 0.3s;
            text-transform: uppercase;
            font-size: 0.7rem;
        }
        .btn-search:hover { background: var(--teal); color: #000; }

        /* ── TABLE CONTAINER ── */
        .glass-card { 
            background: var(--glass); 
            backdrop-filter: blur(25px);
            border-radius: 35px; 
            padding: 20px; 
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
            font-size: 0.7rem; 
            text-transform: uppercase; 
            letter-spacing: 2.5px;
        }
        td { padding: 25px 20px; border-bottom: 1px solid rgba(255,255,255,0.03); font-size: 0.95rem; vertical-align: middle; }
        
        tr:hover td { background: rgba(0, 229, 195, 0.01); }

        .staff-id {
            background: rgba(255, 255, 255, 0.05);
            padding: 4px 10px;
            border-radius: 8px;
            font-family: monospace;
            color: var(--text-muted);
            font-size: 0.8rem;
        }

        .staff-name { font-weight: 700; color: #fff; font-size: 1rem; }
        .staff-email { color: var(--text-muted); font-size: 0.9rem; }

        /* ── ACTION BUTTONS ── */
        .action-cluster { display: flex; gap: 12px; }
        .btn-action { 
            width: 42px; height: 42px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            text-decoration: none; transition: 0.3s; font-size: 1rem;
            border: 1px solid rgba(255,255,255,0.05);
        }
        .btn-edit { background: rgba(59, 130, 246, 0.1); color: #3b82f6; }
        .btn-edit:hover { background: #3b82f6; color: #fff; transform: translateY(-3px); }

        .btn-delete { background: rgba(255, 79, 123, 0.1); color: var(--danger); cursor: pointer; border: 1px solid rgba(255, 79, 123, 0.1); }
        .btn-delete:hover { background: var(--danger); color: #fff; transform: translateY(-3px); }

        /* ── ALERTS ── */
        .msg { padding: 18px 25px; border-radius: 18px; margin-bottom: 30px; font-size: 0.9rem; display: flex; align-items: center; gap: 12px; animation: slideIn 0.5s ease; }
        .success { background: rgba(0, 229, 160, 0.1); color: #00e5a0; border: 1px solid rgba(0, 229, 160, 0.2); }
        .error { background: rgba(255, 79, 123, 0.1); color: var(--danger); border: 1px solid rgba(255, 79, 123, 0.2); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

        @media (max-width: 1024px) {
            .main-content { margin-left: 0; width: 100%; padding: 30px; }
            .header-flex { flex-direction: column; align-items: flex-start; gap: 20px; }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="header-flex">
        <div>
            <h2>Staff Directorate</h2>
            <p style="color: var(--text-muted); margin-top: 5px;">Configure internal access and manage organizational personnel.</p>
        </div>
        <a href="add_staff.php" class="btn-add"><i class="fas fa-user-plus"></i> Initialize Personnel</a>
    </div>

    <form method="GET" class="search-shelf">
        <i class="fas fa-search" style="margin-left: 15px; color: var(--text-muted);"></i>
        <input type="text" name="search" class="search-input" placeholder="Query name or official email..." value="<?php echo htmlspecialchars($search_query); ?>">
        <button type="submit" class="btn-search">Filter</button>
        <?php if($search_query): ?>
            <a href="manage_staff.php" style="color: var(--danger); text-decoration: none; font-size: 0.7rem; font-weight: 800; margin-right: 15px; align-self: center;">RESET</a>
        <?php endif; ?>
    </form>

    <?php echo $message; ?>

    <div class="glass-card">
        <table>
            <thead>
                <tr>
                    <th>Designation ID</th>
                    <th>Personnel Name</th>
                    <th>Official Credentials</th>
                    <th style="text-align: right;">Administrative Control</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><span class="staff-id">#STAFF-<?php echo str_pad($row['user_id'], 3, '0', STR_PAD_LEFT); ?></span></td>
                        <td class="staff-name"><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td class="staff-email"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <div class="action-cluster" style="justify-content: flex-end;">
                                <a href="edit_staff.php?id=<?php echo $row['user_id']; ?>" class="btn-action btn-edit" title="Modify Personnel Profile">
                                    <i class="fas fa-user-gear"></i>
                                </a>
                                <a href="manage_staff.php?delete_id=<?php echo $row['user_id']; ?>" 
                                   class="btn-action btn-delete" 
                                   onclick="return confirm('Security Alert: Are you sure you want to revoke all access for this staff member?')">
                                    <i class="fas fa-user-xmark"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center; padding:80px; color:var(--text-muted);">
                            <i class="fas fa-user-secret" style="font-size: 3rem; opacity: 0.1; display: block; margin-bottom: 20px;"></i>
                            No personnel records detected in current archival query.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>