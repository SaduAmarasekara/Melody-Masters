<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<nav class="premium-navbar">
    <div class="navbar-container">
        <div class="nav-brand" onclick="window.location.href='index.php'">
            <div class="brand-logo">
                <i class="fas fa-compact-disc logo-spin"></i>
            </div>
            <div class="brand-text">
                <strong>Melody Masters</strong>
                <span class="tagline">Premium Audio Boutique</span>
            </div>
        </div>
        
        <button class="mobile-toggle" onclick="toggleMobileMenu()">
            <span></span>
            <span></span>
            <span></span>
        </button>

        <div class="nav-menu" id="navMenu">
            <a href="index.php" class="nav-item">
                <i class="fas fa-house-chimney"></i>
                <span>The Studio</span>
            </a>
            <a href="shop.php" class="nav-item">
                <i class="fas fa-guitar"></i>
                <span>The Showroom</span>
            </a>
            <a href="about_contact.php#contact" class="nav-item">
                <i class="fas fa-headset"></i>
                <span>Contact</span>
            </a>

            <?php if(isset($_SESSION['role'])): ?>
                
                <?php if($_SESSION['role'] == 'Customer'): ?>
                    <a href="cart.php" class="nav-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>My cart</span>
                    </a>
                    <a href="my_orders.php" class="nav-item">
                        <i class="fas fa-box-open"></i>
                        <span>My Orders</span>
                    </a>
                <?php endif; ?>

                <?php if($_SESSION['role'] == 'Admin'): ?>
                    <a href="admin_dashboard.php" class="nav-item admin-link">
                        <i class="fas fa-shield-halved"></i>
                        <span>Directorate</span>
                    </a>
                <?php endif; ?>

                <?php if($_SESSION['role'] == 'Staff'): ?>
                    <a href="staff_dashboard.php" class="nav-item staff-link">
                        <i class="fas fa-chart-line"></i>
                        <span>Inventory</span>
                    </a>
                <?php endif; ?>

                <div class="nav-user-section">
                    <a href="profile.php" class="user-profile-trigger">
                        <div class="user-avatar-hex">
                            <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                        </div>
                        <span class="user-name"><?php echo explode(' ', htmlspecialchars($_SESSION['full_name']))[0]; ?></span>
                    </a>
                    <a href="logout.php" class="btn-logout-minimal" title="Sign Out">
                        <i class="fas fa-power-off"></i>
                    </a>
                </div>

            <?php else: ?>
                <div class="auth-group">
                    <a href="login.php" class="btn-login-posh">Sign In</a>
                    <a href="register.php" class="btn-register-posh">Create Account</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700;900&display=swap');

    :root {
        --n-bg: #050508;
        --n-teal: #00e5c3;
        --n-teal-glow: rgba(0, 229, 195, 0.25);
        --n-border: rgba(0, 229, 195, 0.15);
        --n-text: #e0ddf5;
        --n-glass: rgba(10, 10, 15, 0.85);
    }

    .premium-navbar {
        background: var(--n-glass);
        backdrop-filter: blur(25px);
        -webkit-backdrop-filter: blur(25px);
        border-bottom: 1px solid var(--n-border);
        position: sticky;
        top: 0;
        z-index: 1000;
        height: 80px;
        display: flex;
        align-items: center;
    }

    /* Top Accent Line */
    .premium-navbar::before {
        content: ''; position: absolute; top: 0; left: 0; width: 100%; height: 2px;
        background: linear-gradient(90deg, transparent, var(--n-teal), transparent);
        opacity: 0.8;
    }

    .navbar-container {
        max-width: 1400px;
        width: 100%;
        margin: 0 auto;
        padding: 0 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    /* ── BRAND LOGO ── */
    .nav-brand {
        display: flex;
        align-items: center;
        gap: 15px;
        cursor: pointer;
    }

    .brand-logo {
        font-size: 1.8rem;
        color: var(--n-teal);
        filter: drop-shadow(0 0 10px var(--n-teal-glow));
    }

    .logo-spin {
        animation: discRotate 8s linear infinite;
    }
    @keyframes discRotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .brand-text { display: flex; flex-direction: column; line-height: 1.1; }
    .brand-text strong {
        font-family: 'Playfair Display', serif;
        font-size: 1.5rem;
        color: #fff;
        letter-spacing: 0.5px;
    }
    .tagline {
        font-size: 0.65rem;
        color: var(--n-teal);
        text-transform: uppercase;
        letter-spacing: 3px;
        font-weight: 700;
    }

    /* ── MENU ITEMS ── */
    .nav-menu {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .nav-item {
        color: #94a3b8;
        text-decoration: none;
        padding: 10px 18px;
        border-radius: 12px;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .nav-item i { font-size: 0.9rem; transition: 0.3s; }

    .nav-item:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.03);
        transform: translateY(-2px);
    }

    .nav-item:hover i {
        color: var(--n-teal);
        transform: scale(1.2);
        filter: drop-shadow(0 0 5px var(--n-teal-glow));
    }

    /* ── USER SECTION ── */
    .nav-user-section {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-left: 20px;
        padding-left: 20px;
        border-left: 1px solid rgba(255,255,255,0.08);
    }

    .user-profile-trigger {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
        transition: 0.3s;
    }

    .user-avatar-hex {
        width: 38px;
        height: 38px;
        background: var(--n-teal);
        color: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1rem;
        border-radius: 10px; /* Modern squircle */
        box-shadow: 0 5px 15px var(--n-teal-glow);
    }

    .user-name {
        color: #fff;
        font-weight: 700;
        font-size: 0.9rem;
    }

    .btn-logout-minimal {
        color: #5a5a72;
        font-size: 1rem;
        text-decoration: none;
        transition: 0.3s;
    }
    .btn-logout-minimal:hover { color: #ff4f7b; transform: rotate(90deg); }

    /* ── AUTH BUTTONS ── */
    .auth-group { display: flex; gap: 15px; align-items: center; }

    .btn-login-posh {
        color: #fff;
        text-decoration: none;
        font-size: 0.85rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .btn-register-posh {
        background: #fff;
        color: #000;
        padding: 12px 28px;
        border-radius: 50px;
        text-decoration: none;
        font-size: 0.8rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        transition: 0.3s;
        box-shadow: 0 10px 20px rgba(255,255,255,0.1);
    }
    .btn-register-posh:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(255,255,255,0.2); }

    /* Special Links */
    .admin-link { color: #ffb347 !important; border: 1px solid rgba(255, 179, 71, 0.2); }
    .staff-link { color: #63b3ed !important; border: 1px solid rgba(99, 179, 237, 0.2); }

    /* ── MOBILE ── */
    .mobile-toggle {
        display: none;
        flex-direction: column;
        gap: 6px;
        background: none;
        border: none;
        cursor: pointer;
    }
    .mobile-toggle span { width: 25px; height: 2px; background: #fff; border-radius: 2px; }

    @media (max-width: 1100px) {
        .navbar-container { padding: 0 20px; }
        .nav-item span { display: none; } /* Show only icons on smaller laptops */
        .nav-item { padding: 10px; }
    }

    @media (max-width: 968px) {
        .mobile-toggle { display: flex; }
        .nav-menu {
            position: fixed; top: 80px; left: -100%; width: 100%; height: calc(100vh - 80px);
            background: #050508; flex-direction: column; padding: 40px; align-items: flex-start;
            transition: 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .nav-menu.active { left: 0; }
        .nav-item span { display: inline; }
        .nav-item { width: 100%; font-size: 1.2rem; }
        .nav-user-section { width: 100%; margin: 20px 0; padding: 20px 0; border-left: none; border-top: 1px solid var(--n-border); }
    }
</style>

<script>
function toggleMobileMenu() {
    document.getElementById('navMenu').classList.toggle('active');
}
</script>