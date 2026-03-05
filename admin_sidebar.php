<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">🎵</div>
        <div class="brand-text">
            <strong>Melody</strong><span>Masters</span>
            <small>Admin Panel</small>
        </div>
    </div>

    <ul class="sidebar-menu">
        <li class="menu-label">Main</li>
        <li>
            <a href="admin_dashboard.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php') ? 'active' : '' ?>">
                <i class="fas fa-th-large"></i> <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="manage_orders.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage_orders.php') ? 'active' : '' ?>">
                <i class="fas fa-shopping-cart"></i> <span>Manage Orders</span>
            </a>
        </li>

        <li class="menu-label">Inventory</li>
        <li>
            <a href="add_product.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'add_product.php') ? 'active' : '' ?>">
                <i class="fas fa-plus-circle"></i> <span>Add Products</span>
            </a>
        </li>
        <li>
            <a href="manage_products.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage_products.php') ? 'active' : '' ?>">
                <i class="fas fa-guitar"></i> <span>View Inventory</span>
            </a>
        </li>

        <li class="menu-label">People</li>
        <li>
            <a href="manage_customers.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage_customers.php') ? 'active' : '' ?>">
                <i class="fas fa-users"></i> <span>Manage Customers</span>
            </a>
        </li>
        <li>
            <a href="manage_staff.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage_staff.php') ? 'active' : '' ?>">
                <i class="fas fa-users-cog"></i> <span>Manage Staff</span>
            </a>
        </li>
        <li>
            <a href="add_staff.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'add_staff.php') ? 'active' : '' ?>">
                <i class="fas fa-user-plus"></i> <span>Add Staff</span>
            </a>
        </li>

        <li class="menu-label">Content</li>
        <li>
            <a href="manage_reviews.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'manage_reviews.php') ? 'active' : '' ?>">
                <i class="fas fa-star"></i> <span>Manage Reviews</span>
            </a>
        </li>

        <li class="menu-label">Account</li>
        <li>
            <a href="profile.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'profile.php') ? 'active' : '' ?>">
                <i class="fas fa-user-circle"></i> <span>My Profile</span>
            </a>
        </li>

        <li style="margin-top: 50px;">
            <a href="logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> <span>Logout</span>
            </a>
        </li>
    </ul>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap');

    .sidebar {
        width: 260px;
        height: 100vh;
        background: #08080e;
        position: fixed;
        left: 0; top: 0;
        padding: 0;
        border-right: 1px solid rgba(0,229,195,0.08);
        z-index: 1000;
        box-sizing: border-box;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    /* subtle inner glow */
    .sidebar::before {
        content: '';
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: radial-gradient(ellipse 120% 40% at 50% 0%, rgba(0,229,195,0.05) 0%, transparent 60%);
        pointer-events: none;
        z-index: 0;
    }

    /* top accent line */
    .sidebar::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 2px;
        background: linear-gradient(90deg, transparent, #00e5c3, #e8c86e, #00e5c3, transparent);
        opacity: .6;
    }

    /* ── BRAND ── */
    .sidebar-brand {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 26px 22px 22px;
        border-bottom: 1px solid rgba(0,229,195,0.07);
        position: relative; z-index: 1;
        flex-shrink: 0;
    }

    .brand-icon {
        font-size: 1.7rem;
        filter: drop-shadow(0 0 10px rgba(0,229,195,0.5));
        animation: iconFloat 3.5s ease-in-out infinite;
    }
    @keyframes iconFloat {
        0%,100% { transform: translateY(0) rotate(0deg); }
        50%      { transform: translateY(-4px) rotate(-8deg); }
    }

    .brand-text {
        display: flex;
        flex-direction: column;
        line-height: 1;
    }
    .brand-text strong {
        font-family: 'DM Sans', sans-serif;
        font-size: 1.05rem;
        font-weight: 700;
        color: #fff;
        letter-spacing: .5px;
    }
    .brand-text span {
        font-size: 1.05rem;
        font-weight: 700;
        color: #00e5c3;
        letter-spacing: .5px;
    }
    .brand-text small {
        font-size: .58rem;
        color: rgba(0,229,195,.5);
        letter-spacing: 3px;
        text-transform: uppercase;
        margin-top: 4px;
    }

    /* ── MENU ── */
    .sidebar-menu {
        list-style: none;
        padding: 14px 14px 20px;
        margin: 0;
        overflow-y: auto;
        flex: 1;
        position: relative; z-index: 1;
        scrollbar-width: thin;
        scrollbar-color: rgba(0,229,195,.15) transparent;
    }
    .sidebar-menu::-webkit-scrollbar { width: 3px; }
    .sidebar-menu::-webkit-scrollbar-thumb { background: rgba(0,229,195,.2); border-radius: 2px; }

    /* section labels */
    .menu-label {
        font-size: .58rem;
        letter-spacing: 3px;
        text-transform: uppercase;
        color: rgba(0,229,195,.35);
        font-weight: 700;
        padding: 16px 10px 6px;
        font-family: 'DM Sans', sans-serif;
    }

    .sidebar-menu li { margin-bottom: 3px; }

    .sidebar-menu a {
        color: #5a5a72;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 11px 14px;
        border-radius: 12px;
        transition: all .3s ease;
        font-size: .88rem;
        font-family: 'DM Sans', sans-serif;
        font-weight: 500;
        border: 1px solid transparent;
        position: relative;
        overflow: hidden;
    }

    .sidebar-menu a i {
        width: 18px;
        text-align: center;
        font-size: .88rem;
        transition: color .3s, transform .3s;
        flex-shrink: 0;
    }

    .sidebar-menu a:hover {
        background: rgba(0,229,195,0.07);
        color: #c8c8dc;
        border-color: rgba(0,229,195,0.1);
        transform: translateX(4px);
    }
    .sidebar-menu a:hover i { color: #00e5c3; transform: scale(1.15); }

    .sidebar-menu a.active {
        background: rgba(0,229,195,0.1);
        color: #00e5c3;
        border-color: rgba(0,229,195,0.2);
        box-shadow: 0 0 20px rgba(0,229,195,0.08);
    }
    .sidebar-menu a.active i { color: #00e5c3; }

    /* active left pill */
    .sidebar-menu a.active::before {
        content: '';
        position: absolute;
        left: 0; top: 20%; bottom: 20%;
        width: 3px;
        border-radius: 0 3px 3px 0;
        background: linear-gradient(180deg, #00e5c3, #e8c86e);
        box-shadow: 0 0 10px rgba(0,229,195,.5);
    }

    /* logout */
    .logout-link {
        color: rgba(255,79,123,.6) !important;
        border-color: transparent !important;
        background: transparent !important;
    }
    .logout-link:hover {
        background: rgba(255,79,123,0.08) !important;
        color: #ff4f7b !important;
        border-color: rgba(255,79,123,0.2) !important;
        transform: translateX(4px);
        box-shadow: none !important;
    }
    .logout-link:hover i { color: #ff4f7b !important; }
    .logout-link::before { display: none !important; }
</style>