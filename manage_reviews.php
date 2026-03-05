<?php
session_start();
include 'db_connect.php';

/**
 * ACCESS CONTROL:
 * Only users with 'Admin' role can manage reviews.
 */
if (!isset($_SESSION['role']) || strtolower(trim($_SESSION['role'])) !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

/**
 * DELETE LOGIC:
 * Removes a review record based on its ID.
 */
if (isset($_GET['delete_id'])) {
    $review_id = (int)$_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM reviews WHERE review_id = ?");
    $stmt->bind_param("i", $review_id);
    
    if ($stmt->execute()) {
        $message = "<div class='alert success'><i class='fas fa-check-circle'></i> Review purged from registry successfully!</div>";
    } else {
        $message = "<div class='alert error'><i class='fas fa-times-circle'></i> System Error: Could not delete the review.</div>";
    }
    $stmt->close();
}

/**
 * DATA FETCHING:
 * Fetches reviews along with Product Names and Customer Names using JOINs.
 */
$query = "SELECT r.*, p.product_name, u.full_name 
          FROM reviews r 
          JOIN products p ON r.product_id = p.product_id 
          JOIN users u ON r.user_id = u.user_id 
          ORDER BY r.review_date DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Archive | Melody Masters Admin</title>
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
            --amber: #e8c86e;
        }

        body {
            background-color: var(--bg);
            color: #e0ddf5;
            font-family: 'DM Sans', sans-serif;
            margin: 0;
            display: flex;
            overflow-x: hidden;
        }

        /* Ambient Background Glow */
        body::before {
            content: ''; position: fixed; top: -10%; right: -10%; width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(0, 229, 195, 0.05), transparent 70%);
            z-index: -1;
        }

        /* Layout Management */
        .main-content {
            margin-left: 260px; /* Sidebar offset */
            width: calc(100% - 260px);
            padding: 50px;
            box-sizing: border-box;
            min-height: 100vh;
        }

        /* Header Section */
        .header-section {
            margin-bottom: 40px;
            border-left: 3px solid var(--teal);
            padding-left: 25px;
            animation: slideIn 0.8s ease-out;
        }
        .header-section h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            margin: 0;
            background: linear-gradient(to bottom, #fff, #888);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .header-section p {
            color: var(--text-muted);
            margin: 5px 0 0;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        /* Glassmorphism Card Style */
        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(25px);
            border-radius: 35px;
            padding: 40px;
            border: 1px solid var(--border);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.4);
            animation: fadeIn 1s ease-out;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            color: var(--teal);
            padding: 20px;
            border-bottom: 1px solid var(--border);
            text-transform: uppercase;
            font-size: 0.7rem;
            letter-spacing: 2px;
        }
        td {
            padding: 25px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.03);
            font-size: 0.95rem;
            vertical-align: middle;
        }
        tr:hover td {
            background: rgba(0, 229, 195, 0.01);
        }

        /* Review Content Styling */
        .customer-meta { display: flex; flex-direction: column; gap: 4px; }
        .customer-name { font-weight: 700; color: #fff; font-size: 1rem; }
        .product-tag { color: var(--teal); font-size: 0.75rem; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; }
        
        .comment-text { 
            color: #a0a0bc; 
            font-style: italic; 
            max-width: 450px; 
            line-height: 1.6;
            font-size: 0.9rem;
            position: relative;
            padding-left: 15px;
            border-left: 1px solid rgba(255,255,255,0.1);
        }
        
        .rating-stars { color: var(--amber); font-size: 0.8rem; letter-spacing: 2px; }

        /* Action Buttons */
        .btn-delete {
            color: var(--danger);
            background: rgba(255, 79, 123, 0.1);
            border: 1px solid rgba(255, 79, 123, 0.2);
            padding: 10px 18px;
            border-radius: 12px;
            cursor: pointer;
            transition: 0.3s;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-delete:hover {
            background: var(--danger);
            color: #fff;
            box-shadow: 0 0 15px rgba(255, 79, 123, 0.3);
            transform: translateY(-2px);
        }

        /* Feedback Messages */
        .alert {
            padding: 18px 25px;
            border-radius: 18px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.9rem;
            animation: slideIn 0.5s ease;
        }
        .success { background: rgba(0, 229, 160, 0.1); color: #00e5a0; border: 1px solid rgba(0, 229, 160, 0.2); }
        .error { background: rgba(255, 79, 123, 0.1); color: var(--danger); border: 1px solid rgba(255, 79, 123, 0.2); }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes slideIn { from { opacity: 0; transform: translateX(-20px); } to { opacity: 1; transform: translateX(0); } }

        @media (max-width: 1100px) {
            .main-content { margin-left: 0; width: 100%; padding: 30px; }
        }
    </style>
</head>
<body>

<?php include 'admin_sidebar.php'; ?>

<div class="main-content">
    <div class="header-section">
        <h2>Feedback Archive</h2>
        <p>Curate and moderate testimonial data from the Melody Masters community.</p>
    </div>

    <?php echo $message; ?>

    <div class="glass-card">
        <table>
            <thead>
                <tr>
                    <th>Archived By</th>
                    <th>Evaluation</th>
                    <th>Testimonial</th>
                    <th>Timestamp</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="customer-meta">
                                <span class="customer-name"><?php echo htmlspecialchars($row['full_name']); ?></span>
                                <span class="product-tag">Gear: <?php echo htmlspecialchars($row['product_name']); ?></span>
                            </div>
                        </td>
                        <td>
                            <div class="rating-stars">
                                <?php 
                                for($i=1; $i<=5; $i++) {
                                    echo ($i <= $row['rating']) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <div class="comment-text">"<?php echo htmlspecialchars($row['comment']); ?>"</div>
                        </td>
                        <td style="color: var(--text-muted); font-size: 0.8rem;">
                            <?php echo date('M d, Y', strtotime($row['review_date'])); ?>
                        </td>
                        <td style="text-align: right;">
                            <a href="manage_reviews.php?delete_id=<?php echo $row['review_id']; ?>" 
                               class="btn-delete" 
                               onclick="return confirm('Administrative Alert: Are you sure you want to permanently purge this testimonial?')">
                                <i class="fas fa-eraser"></i> Purge
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 80px; color: var(--text-muted);">
                            <i class="fas fa-comment-slash" style="font-size: 3rem; opacity: 0.1; display: block; margin-bottom: 20px;"></i>
                            No archival feedback records detected in the current registry.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>