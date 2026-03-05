<?php
session_start();
include 'db_connect.php';

// --- 1. Access Control (No changes to your logic) ---
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

$current_role = strtolower(trim($_SESSION['role']));
if ($current_role !== 'admin' && $current_role !== 'staff') {
    header("Location: login.php");
    exit();
}

$message = "";

// --- 2. LOGIC TO HANDLE FORM SUBMISSION ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    
    $cat_id = $_POST['category_id'];
    if ($cat_id == "new") {
        $new_cat_name = mysqli_real_escape_string($conn, $_POST['new_category_name']);
        $check = $conn->query("SELECT category_id FROM categories WHERE category_name = '$new_cat_name'");
        if ($check->num_rows > 0) {
            $row = $check->fetch_assoc();
            $cat_id = $row['category_id'];
        } else {
            $conn->query("INSERT INTO categories (category_name) VALUES ('$new_cat_name')");
            $cat_id = $conn->insert_id;
        }
    }

    $name  = mysqli_real_escape_string($conn, $_POST['product_name']);
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);
    $price = $_POST['price'];
    $stock = (int)$_POST['stock'];
    $spec  = mysqli_real_escape_string($conn, $_POST['specification']);
    $p_type = $_POST['product_type']; 
    
    $image_name = $_FILES['product_image']['name'];
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    $target_file = $target_dir . basename($image_name);

    if (move_uploaded_file($_FILES['product_image']['tmp_name'], $target_file)) {
        $stmt = $conn->prepare("INSERT INTO products (category_id, product_name, brand, price, stock_quantity, product_image, specifications, product_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssisss", $cat_id, $name, $brand, $price, $stock, $image_name, $spec, $p_type);
        
        if ($stmt->execute()) {
            $new_id = $stmt->insert_id;

            if ($p_type == 'Digital' && isset($_FILES['digital_file'])) {
                $file_name = $_FILES['digital_file']['name'];
                $file_dir  = "uploads/digital_assets/";
                if (!is_dir($file_dir)) { mkdir($file_dir, 0777, true); }
                $file_path = $file_dir . basename($file_name);
                if (move_uploaded_file($_FILES['digital_file']['tmp_name'], $file_path)) {
                    $conn->query("INSERT INTO digital_products (product_id, file_path) VALUES ('$new_id', '$file_path')");
                }
            }

            $message = "success";
        } else {
            $message = "db_error";
        }
        $stmt->close();
    } else {
        $message = "upload_error";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Product | Melody Masters</title>
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

        /* ── LAYOUT ── */
        .main-content {
            margin-left: 260px;
            width: calc(100% - 260px);
            padding: 44px 40px;
            box-sizing: border-box;
            position: relative;
        }
        .main-content::before {
            content:'';
            position:fixed;
            top:-80px;right:-80px;
            width:420px;height:420px;
            border-radius:50%;
            background:radial-gradient(circle,rgba(0,229,195,.05),transparent 70%);
            pointer-events:none;z-index:0;
        }

        /* ── PAGE HEADER ── */
        .page-top {
            display:flex;align-items:center;gap:16px;
            margin-bottom:36px;
            padding-bottom:24px;
            border-bottom:1px solid var(--border);
            position:relative;z-index:1;
        }
        .page-icon {
            width:48px;height:48px;border-radius:14px;
            background:var(--teal-dim);border:1px solid rgba(0,229,195,.2);
            display:flex;align-items:center;justify-content:center;
            color:var(--teal);font-size:1.1rem;
            box-shadow:0 0 18px var(--teal-glow);
        }
        .page-top h2 {
            font-family:'Playfair Display',serif;
            font-size:1.7rem;font-weight:700;
            background:linear-gradient(135deg,#fff,var(--amber));
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
        }
        .page-top p { color:var(--text-muted);font-size:.8rem;margin-top:3px;letter-spacing:.5px; }

        /* ── ALERTS ── */
        .alert {
            padding:14px 18px;border-radius:14px;
            margin-bottom:28px;font-size:.87rem;
            display:flex;align-items:center;gap:10px;
            animation:fadeUp .5s ease;
        }
        .alert-success { background:var(--green-dim);color:#4dffc0;border:1px solid rgba(0,229,160,.25); }
        .alert-error   { background:rgba(255,79,123,.08);color:#ff8fa3;border:1px solid rgba(255,79,123,.2);animation:shake .5s ease; }
        @keyframes fadeUp { from{opacity:0;transform:translateY(10px);}to{opacity:1;transform:translateY(0);} }
        @keyframes shake  { 0%,100%{transform:translateX(0);}20%{transform:translateX(-7px);}40%{transform:translateX(7px);}60%{transform:translateX(-4px);}80%{transform:translateX(4px);} }

        /* ── FORM CARD ── */
        .form-card {
            background:rgba(17,17,24,.9);
            backdrop-filter:blur(20px);
            -webkit-backdrop-filter:blur(20px);
            padding:44px 40px;
            border-radius:26px;
            max-width:620px;
            margin:0 auto;
            border:1px solid var(--border);
            box-shadow:0 30px 70px rgba(0,0,0,.45),inset 0 1px 0 rgba(255,255,255,.03);
            position:relative;overflow:hidden;
            animation:fadeUp .8s cubic-bezier(.16,1,.3,1) both;
        }
        .form-card::before {
            content:'';position:absolute;top:0;left:10%;right:10%;height:1px;
            background:linear-gradient(90deg,transparent,var(--teal),var(--amber),transparent);
            opacity:.4;
        }

        /* ── FORM FIELDS ── */
        .form-field {
            margin-bottom:20px;
        }
        .form-field label {
            display:block;margin-bottom:8px;
            font-size:.7rem;color:var(--text-muted);
            font-weight:600;letter-spacing:2px;text-transform:uppercase;
            transition:color .3s;
        }
        .form-field:focus-within label { color:var(--teal); }

        .input-wrap { position:relative; }
        .input-wrap i {
            position:absolute;left:15px;top:50%;
            transform:translateY(-50%);
            color:var(--text-muted);font-size:.88rem;
            transition:color .3s,transform .3s;
            pointer-events:none;
        }
        .input-wrap.ta-icon i { top:16px;transform:none; }
        .input-wrap:focus-within i { color:var(--teal); transform:translateY(-50%) scale(1.1); }
        .input-wrap.ta-icon:focus-within i { transform:scale(1.1); }

        input, select, textarea {
            width:100%;
            padding:13px 14px 13px 44px;
            background:var(--bg-input);
            border:1px solid rgba(255,255,255,.06);
            border-radius:14px;
            color:var(--text);
            font-family:'DM Sans',sans-serif;
            font-size:.9rem;
            box-sizing:border-box;
            outline:none;
            transition:all .35s;
            -webkit-appearance:none;
        }
        select {
            cursor:pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2300e5c3' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            padding-right: 40px;
        }
        /* Force dropdown panel colors across browsers */
        select option {
            background: #14141f;
            color: #e0ddf5;
            padding: 10px 14px;
            font-family: 'DM Sans', sans-serif;
            font-size: .9rem;
        }
        select option:checked,
        select option:hover {
            background: #1a2a28;
            color: #00e5c3;
        }
        select option[value="new"] {
            color: #00e5c3;
            font-weight: 600;
        }
        select option[value=""] {
            color: #5a5a72;
        }
        textarea { padding:13px 14px 13px 44px;height:110px;resize:none; }
        input[type="file"] { padding:11px 14px;cursor:pointer; }

        input::placeholder,textarea::placeholder { color:var(--text-muted); }
        input:focus,select:focus,textarea:focus {
            border-color:rgba(0,229,195,.45);
            background:rgba(0,229,195,.04);
            box-shadow:0 0 0 3px rgba(0,229,195,.08);
        }

        /* underline sweep */
        .input-wrap::after {
            content:'';position:absolute;bottom:0;left:50%;
            transform:translateX(-50%) scaleX(0);
            width:80%;height:2px;
            background:linear-gradient(90deg,var(--teal),var(--amber));
            border-radius:1px;
            transition:transform .4s cubic-bezier(.16,1,.3,1);
        }
        .input-wrap:focus-within::after { transform:translateX(-50%) scaleX(1); }

        /* price / stock row */
        .field-row { display:flex;gap:16px; }
        .field-row .form-field { flex:1; }

        /* ── NEW CATEGORY BOX ── */
        #new-cat-box {
            display:none;
            background:rgba(0,229,195,.04);
            border:1px dashed rgba(0,229,195,.3);
            border-radius:14px;
            padding:18px;
            margin:4px 0 16px;
            animation:fadeUp .4s ease;
        }
        #new-cat-box label { color:var(--teal) !important; }

        /* ── DIGITAL FILE AREA ── */
        #digital_file_area {
            display:none;
            background:rgba(232,200,110,.04);
            border:1px dashed rgba(232,200,110,.3);
            border-radius:14px;
            padding:18px;
            margin:4px 0 16px;
            animation:fadeUp .4s ease;
        }
        #digital_file_area label { color:var(--amber) !important; }

        /* ── SUBMIT BUTTON ── */
        .btn-gold {
            width:100%;padding:16px;
            background:linear-gradient(135deg,var(--teal),#00bfa0);
            color:#08080e;border:none;
            border-radius:14px;
            font-family:'DM Sans',sans-serif;
            font-weight:700;font-size:.9rem;
            cursor:pointer;
            text-transform:uppercase;letter-spacing:2px;
            transition:all .35s;
            position:relative;overflow:hidden;
            box-shadow:0 6px 24px var(--teal-glow);
            margin-top:8px;
            display:flex;align-items:center;justify-content:center;gap:10px;
        }
        .btn-gold::before {
            content:'';position:absolute;inset:0;
            background:linear-gradient(135deg,rgba(255,255,255,.18),transparent);
            opacity:0;transition:opacity .3s;
        }
        .btn-gold:hover::before { opacity:1; }
        .btn-gold:hover {
            transform:translateY(-3px) scale(1.02);
            box-shadow:0 12px 36px var(--teal-glow);
            filter:brightness(1.08);
        }
        .btn-gold:active { transform:translateY(0) scale(.99); }

        /* ── SECTION DIVIDER ── */
        .form-divider {
            display:flex;align-items:center;gap:12px;
            margin:8px 0 20px;
        }
        .form-divider::before,.form-divider::after {
            content:'';flex:1;height:1px;
            background:rgba(255,255,255,.05);
        }
        .form-divider span {
            font-size:.65rem;letter-spacing:3px;text-transform:uppercase;
            color:var(--text-muted);white-space:nowrap;
        }

        /* responsive */
        @media(max-width:768px) {
            .main-content { margin-left:0;width:100%;padding:24px; }
            .field-row { flex-direction:column;gap:0; }
            .form-card { padding:32px 22px; }
        }
    </style>
</head>
<body>

<?php 
if ($current_role === 'admin') { include 'admin_sidebar.php'; } 
else { include 'staff_sidebar.php'; }
?>

<div class="main-content">

    <div class="page-top">
        <div class="page-icon"><i class="fas fa-plus-circle"></i></div>
        <div>
            <h2>Add New Product</h2>
            <p>Logged in as: <?php echo ucfirst($current_role); ?></p>
        </div>
    </div>

    <?php if($message === 'success'): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> Product successfully added to inventory!
        </div>
    <?php elseif($message === 'db_error'): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> Database Error: Failed to save product.
        </div>
    <?php elseif($message === 'upload_error'): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> Error: Image upload failed.
        </div>
    <?php endif; ?>

    <div class="form-card">
        <form method="POST" enctype="multipart/form-data">

            <div class="form-divider"><span>Product Info</span></div>

            <div class="form-field">
                <label>Product Name</label>
                <div class="input-wrap">
                    <i class="fas fa-tag"></i>
                    <input type="text" name="product_name" placeholder="e.g. Yamaha F310" required>
                </div>
            </div>

            <div class="form-field">
                <label>Category</label>
                <div class="input-wrap">
                    <i class="fas fa-layer-group"></i>
                    <select name="category_id" id="cat_select" onchange="toggleCategoryInput()" required>
                        <option value="">Select Category</option>
                        <?php 
                        $cats = $conn->query("SELECT * FROM categories ORDER BY category_name ASC");
                        while($c = $cats->fetch_assoc()) {
                            echo "<option value='{$c['category_id']}'>{$c['category_name']}</option>";
                        }
                        ?>
                        <option value="new">+ Add New Category...</option>
                    </select>
                </div>
            </div>

            <div id="new-cat-box">
                <div class="form-field" style="margin-bottom:0;">
                    <label>New Category Name</label>
                    <div class="input-wrap">
                        <i class="fas fa-folder-plus"></i>
                        <input type="text" name="new_category_name" id="new_cat_input" placeholder="e.g. Electric Guitars">
                    </div>
                </div>
            </div>

            <div class="form-field">
                <label>Product Type</label>
                <div class="input-wrap">
                    <i class="fas fa-boxes-stacked"></i>
                    <select name="product_type" id="product_type" onchange="toggleDigitalField()" required>
                        <option value="Physical">Physical Instrument</option>
                        <option value="Digital">Digital (Lyrics PDF)</option>
                    </select>
                </div>
            </div>

            <div id="digital_file_area">
                <div class="form-field" style="margin-bottom:0;">
                    <label>Upload PDF File</label>
                    <div class="input-wrap">
                        <i class="fas fa-file-pdf"></i>
                        <input type="file" name="digital_file" accept=".pdf">
                    </div>
                </div>
            </div>

            <div class="form-field">
                <label>Brand</label>
                <div class="input-wrap">
                    <i class="fas fa-trademark"></i>
                    <input type="text" name="brand" placeholder="e.g. Yamaha" required>
                </div>
            </div>

            <div class="field-row">
                <div class="form-field">
                    <label>Price (£)</label>
                    <div class="input-wrap">
                        <i class="fas fa-sterling-sign"></i>
                        <input type="number" step="0.01" name="price" placeholder="0.00" required>
                    </div>
                </div>
                <div class="form-field">
                    <label>Stock Quantity</label>
                    <div class="input-wrap">
                        <i class="fas fa-cubes"></i>
                        <input type="number" name="stock" placeholder="0" required>
                    </div>
                </div>
            </div>

            <div class="form-divider"><span>Details</span></div>

            <div class="form-field">
                <label>Specifications</label>
                <div class="input-wrap ta-icon">
                    <i class="fas fa-align-left"></i>
                    <textarea name="specification" placeholder="Describe the instrument..."></textarea>
                </div>
            </div>

            <div class="form-field">
                <label>Product Display Image</label>
                <div class="input-wrap">
                    <i class="fas fa-image"></i>
                    <input type="file" name="product_image" accept="image/*" required>
                </div>
            </div>

            <button type="submit" name="add_product" class="btn-gold">
                <i class="fas fa-plus-circle"></i> Add Product to Inventory
            </button>
        </form>
    </div>
</div>

<script>
    function toggleCategoryInput() {
        var select   = document.getElementById("cat_select");
        var inputBox = document.getElementById("new-cat-box");
        if (select.value === "new") { inputBox.style.display = "block"; } 
        else { inputBox.style.display = "none"; }
    }

    function toggleDigitalField() {
        var type = document.getElementById("product_type").value;
        var area = document.getElementById("digital_file_area");
        area.style.display = (type === "Digital") ? "block" : "none";
    }

    // ripple on submit
    document.querySelector('.btn-gold').addEventListener('click', function(e) {
        const btn    = this;
        const ripple = document.createElement('span');
        const rect   = btn.getBoundingClientRect();
        const size   = Math.max(rect.width, rect.height) * 1.4;
        ripple.style.cssText = `position:absolute;width:${size}px;height:${size}px;border-radius:50%;background:rgba(255,255,255,.15);top:${e.clientY-rect.top-size/2}px;left:${e.clientX-rect.left-size/2}px;transform:scale(0);animation:rpl .6s ease-out forwards;pointer-events:none;`;
        btn.appendChild(ripple);
        setTimeout(()=>ripple.remove(),650);
    });
    const s = document.createElement('style');
    s.textContent = `@keyframes rpl{to{transform:scale(1);opacity:0;}}`;
    document.head.appendChild(s);
</script>
</body>
</html>