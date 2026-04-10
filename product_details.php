<?php
/**
 * PRODUCT DETAILS & VERIFIED REVIEWS PAGE
 * Features: Product Information, Purchase Verification, and Interactive Star Reviews.
 */

include 'db_connect.php';
include 'navbar.php';

// 1. DATA RETRIEVAL
if (isset($_GET['id'])) {
    $product_id = mysqli_real_escape_string($conn, $_GET['id']);
    
    $sql = "SELECT p.*, c.category_name FROM products p 
            JOIN categories c ON p.category_id = c.category_id 
            WHERE p.product_id = '$product_id'";
    
    $result = $conn->query($sql);
    
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $available_stock = isset($product['stock_quantity']) ? $product['stock_quantity'] : 0;
    } else {
        echo "<div style='height:80vh;display:flex;align-items:center;justify-content:center;flex-direction:column;font-family:DM Sans,sans-serif;background:#0a0a0f;'>
                <h1 style='color:#00e5c3;margin-bottom:20px;'>Product not found</h1>
                <a href='shop.php' style='color:#08080e;text-decoration:none;background:#00e5c3;padding:12px 28px;border-radius:50px;font-weight:700;'>Back to Shop</a>
              </div>";
        exit();
    }
} else {
    header("Location: shop.php");
    exit();
}

// 2. PURCHASE VERIFICATION
$can_review = false;
if (isset($_SESSION['user_id'])) {
    $u_id = $_SESSION['user_id'];
    $p_id = $product['product_id'];
    
    $check_purchase = "SELECT oi.item_id FROM order_items oi 
                       JOIN orders o ON oi.order_id = o.order_id 
                       WHERE o.user_id = '$u_id' 
                       AND oi.product_id = '$p_id' 
                       AND o.order_status = 'Delivered'";
    $purchase_res = $conn->query($check_purchase);
    if ($purchase_res && $purchase_res->num_rows > 0) {
        $can_review = true;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['product_name']; ?> | Melody Masters</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-deep:     #0a0a0f;
            --bg-card:    rgba(17,17,24,.85);
            --border:     rgba(0,229,195,0.12);
            --teal:       #00e5c3;
            --teal-glow:  rgba(0,229,195,0.25);
            --amber:      #e8c86e;
            --amber-glow: rgba(232,200,110,0.2);
            --rose:       #ff4f7b;
            --green:      #00e5a0;
            --text:       #e0ddf5;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'DM Sans', sans-serif; background: var(--bg-deep); color: var(--text); overflow-x: hidden; }

        #detailWave { position:fixed; bottom:0; left:0; width:100%; height:90px; opacity:.18; pointer-events:none; z-index:0; }

        .main-container { max-width: 1200px; margin: 50px auto; padding: 20px; position: relative; z-index: 1; }

        /* ── PRODUCT GRID ── */
        .product-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 50px;
            background: rgba(17,17,24,.88); backdrop-filter: blur(20px);
            padding: 44px; border-radius: 40px; border: 1px solid var(--border);
            box-shadow: 0 30px 80px rgba(0,0,0,.45); animation: fadeUp .9s ease both;
        }

        @keyframes fadeUp { from{opacity:0;transform:translateY(30px);} to{opacity:1;transform:translateY(0);} }

        .image-card {
            border-radius: 25px; background: linear-gradient(135deg, #0f0f1a, #1a1a28);
            display: flex; align-items: center; justify-content: center;
            border: 1px solid var(--border); overflow: hidden; height: 100%;
        }
        .image-card img { width:100%; height:auto; padding:30px; transition: 0.5s ease; }
        .image-card:hover img { transform:scale(1.05); }

        .brand-label { color: var(--amber); text-transform: uppercase; letter-spacing: 3px; font-weight: 700; font-size: .75rem; margin-bottom: 10px; display: block; }
        .product-title { font-family: 'Playfair Display', serif; font-size: 2.8rem; font-weight: 900; margin-bottom: 20px; color: #fff; }
        .price-box { font-family: 'Playfair Display', serif; font-size: 2.5rem; font-weight: 700; color: var(--amber); margin-bottom: 30px; }

        .spec-list { list-style:none; margin-bottom: 30px; }
        .spec-list li { display:flex; gap:12px; margin-bottom:12px; color:#a0a0bc; font-size:.95rem; }
        .spec-list li i { color:var(--teal); margin-top:5px; }

        .qty-input { width: 80px; background: #0d0d15; color: #fff; border: 1px solid var(--border); border-radius: 12px; padding: 15px; text-align: center; margin-right: 15px; }
        .btn-premium {
            background: linear-gradient(135deg, var(--teal), #00bfa0); color: #08080e;
            border: none; padding: 16px 40px; border-radius: 15px; font-weight: 800;
            text-transform: uppercase; letter-spacing: 1.5px; cursor: pointer; transition: 0.3s;
        }
        .btn-premium:hover:not(:disabled) { transform: translateY(-3px); box-shadow: 0 10px 25px var(--teal-glow); }

        /* ── LUXURY REVIEWS ── */
        .reviews-container {
            margin-top: 50px; padding: 50px; background: var(--bg-card);
            border-radius: 40px; border: 1px solid var(--border);
        }

        .reviews-container h2 { font-family: 'Playfair Display', serif; font-size: 2.2rem; margin-bottom: 40px; color: #fff; }

        .review-form-card {
            background: rgba(255,255,255,0.02); border: 1px solid rgba(0,229,195,0.2);
            padding: 35px; border-radius: 30px; margin-bottom: 50px;
        }

        .star-input { margin: 15px 0 25px; display: flex; gap: 8px; }
        .star-input i { font-size: 1.8rem; color: #1a1a25; cursor: pointer; transition: 0.3s; }
        .star-input i.active { color: var(--amber); text-shadow: 0 0 15px var(--amber-glow); }

        textarea {
            width: 100%; background: #050508; border: 1px solid var(--border);
            border-radius: 20px; padding: 20px; color: #fff; font-family: inherit;
            resize: none; margin-bottom: 25px; outline: none; transition: 0.3s;
        }
        textarea:focus { border-color: var(--teal); box-shadow: 0 0 20px var(--teal-glow); }

        .btn-review {
            background: transparent; border: 1px solid var(--teal); color: var(--teal);
            padding: 14px 30px; border-radius: 14px; font-weight: 700; cursor: pointer; transition: 0.3s;
        }
        .btn-review:hover { background: var(--teal); color: #000; }

        .comment-item {
            background: rgba(255,255,255,0.03); padding: 30px; border-radius: 25px;
            margin-bottom: 25px; border: 1px solid var(--glass-border); transition: 0.3s;
        }
        .comment-item:hover { background: rgba(255,255,255,0.05); border-color: var(--teal); }

        .comment-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .reviewer-name { font-weight: 700; font-size: 1.1rem; color: #fff; display: flex; align-items: center; gap: 10px; }
        .verified-badge { background: rgba(0,229,160,0.1); color: var(--green); padding: 4px 12px; border-radius: 50px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase; }

        .review-stars { color: var(--amber); font-size: 0.85rem; }
        .comment-text { color: #94a3b8; font-style: italic; line-height: 1.8; margin-bottom: 10px; font-size: 1rem; }
        .comment-date { color: var(--text-muted); font-size: 0.75rem; }

        .review-lock {
            background: rgba(255,79,123,0.05); border: 1px dashed var(--rose);
            padding: 25px; border-radius: 20px; text-align: center; color: var(--rose);
            display: flex; align-items: center; justify-content: center; gap: 15px; font-weight: 600;
        }
    </style>
</head>
<body>

<canvas id="detailWave"></canvas>

<div class="main-container">
    <div class="product-grid">
        <div class="image-section">
            <div class="image-card">
                <img src="uploads/<?php echo $product['product_image']; ?>" alt="Product">
            </div>
        </div>

        <div class="details-section">
            <span class="brand-label"><?php echo htmlspecialchars($product['brand'] ?? 'Premium Collection'); ?></span>
            <h1 class="product-title"><?php echo $product['product_name']; ?></h1>

            <div class="price-box">$<?php echo number_format($product['price'], 2); ?></div>

            <div class="stock-indicator" style="margin-bottom: 20px;">
                <?php if ($available_stock > 0): ?>
                    <span style="color: var(--green); font-weight: 700;"><i class="fas fa-box-open"></i> AVAILABLE IN VAULT: <?php echo $available_stock; ?></span>
                <?php else: ?>
                    <span style="color: var(--rose); font-weight: 700;"><i class="fas fa-times-circle"></i> OUT OF ARCHIVE</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($product['specifications'])): ?>
                <ul class="spec-list">
                    <?php 
                    $spec_lines = explode("\n", $product['specifications']);
                    foreach ($spec_lines as $line): 
                        if (trim($line) != ""): ?>
                            <li><i class="fas fa-check-circle"></i><span><?php echo htmlspecialchars($line); ?></span></li>
                        <?php endif; 
                    endforeach; ?>
                </ul>
            <?php endif; ?>

            <form action="cart.php" method="POST">
                <div style="display:flex; align-items:center;">
                    <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                    <input type="number" name="quantity" id="order_qty" value="1" min="1" max="<?php echo $available_stock; ?>" class="qty-input" <?php echo ($available_stock <= 0) ? 'disabled' : ''; ?>>
                    <button type="submit" name="add_to_cart" class="btn-premium" <?php echo ($available_stock <= 0) ? 'disabled' : ''; ?>>
                        <i class="fas fa-shopping-bag"></i> <?php echo ($available_stock > 0) ? 'Add to Cart' : 'Sold Out'; ?>
                    </button>
                </div>
                <p id="stockError" style="color: var(--rose); font-size: 0.8rem; margin-top: 10px; display: none;">Quantity exceeds available stock.</p>
            </form>
        </div>
    </div>

    <div class="reviews-container">
        <h2>Customer Experience</h2>

        <?php if ($can_review): ?>
            <div class="review-form-card">
                <form action="submit_review.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                    <label style="color: var(--teal); font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 1px;">Rating</label>
                    <div class="star-input" id="star-selector">
                        <i class="fas fa-star active" data-value="1"></i>
                        <i class="fas fa-star active" data-value="2"></i>
                        <i class="fas fa-star active" data-value="3"></i>
                        <i class="fas fa-star active" data-value="4"></i>
                        <i class="fas fa-star active" data-value="5"></i>
                    </div>
                    <input type="hidden" name="rating" id="rating-value" value="5">
                    <textarea name="comment" rows="4" required placeholder="Describe your experience with the sound and quality..."></textarea>
                    <button type="submit" name="submit_review" class="btn-review">
                        <i class="fas fa-paper-plane"></i> SUBMIT REVIEW
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="review-lock">
                <i class="fas fa-shield-halved"></i>
                <span>Exclusive to verified owners. Purchase and receive this instrument to share your thoughts.</span>
            </div>
        <?php endif; ?>

        <div class="comments-list" style="margin-top: 40px;">
            <?php
            $reviews_query = "SELECT r.*, u.full_name FROM reviews r 
                              JOIN users u ON r.user_id = u.user_id 
                              WHERE r.product_id = '$product_id' 
                              ORDER BY r.review_date DESC";
            $reviews = $conn->query($reviews_query);

            if ($reviews && $reviews->num_rows > 0):
                while($rev = $reviews->fetch_assoc()): ?>
                    <div class="comment-item">
                        <div class="comment-top">
                            <span class="reviewer-name">
                                <?php echo htmlspecialchars($rev['full_name']); ?>
                                <span class="verified-badge">✓ Verified purchase</span>
                            </span>
                            <div class="review-stars">
                                <?php for($i=1; $i<=5; $i++) echo ($i <= $rev['rating']) ? '★' : '☆'; ?>
                            </div>
                        </div>
                        <p class="comment-text">"<?php echo htmlspecialchars($rev['comment']); ?>"</p>
                        <small class="comment-date"><?php echo date('M d, Y', strtotime($rev['review_date'])); ?></small>
                    </div>
                <?php endwhile;
            else: ?>
                <p style="text-align: center; color: #5a5a72; padding: 40px;">No reviews yet. Be the first verified owner to share your experience!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<script>
// STAR RATING LOGIC
document.querySelectorAll('#star-selector i').forEach(star => {
    star.addEventListener('click', function() {
        const value = this.getAttribute('data-value');
        document.getElementById('rating-value').value = value;
        document.querySelectorAll('#star-selector i').forEach(s => {
            s.classList.toggle('active', s.getAttribute('data-value') <= value);
        });
    });
});

// WAVEFORM ANIMATION
const canvas = document.getElementById('detailWave');
const ctx = canvas.getContext('2d');
let W, H;
function resize(){ W = canvas.width = window.innerWidth; H = canvas.height = 90; }
resize(); window.addEventListener('resize', resize);
let t = 0;
function draw(){
    ctx.clearRect(0,0,W,H);
    ctx.beginPath();
    const g=ctx.createLinearGradient(0,0,W,0);
    g.addColorStop(0,'transparent'); g.addColorStop(0.5, 'rgba(0,229,195,0.4)'); g.addColorStop(1,'transparent');
    ctx.strokeStyle=g; ctx.lineWidth=2;
    for(let x=0;x<=W;x++){
        const y=H/2 + Math.sin(x*0.02+t*0.05)*15;
        x===0?ctx.moveTo(x,y):ctx.lineTo(x,y);
    }
    ctx.stroke(); t++; requestAnimationFrame(draw);
}
draw();
</script>
</body>
</html>