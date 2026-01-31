<style>
    /* Google Font for Premium Look */
    @import url('https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700&display=swap');

    .sidebar {
        background: #0f172a; /* Deep Dark Blue */
        border-right: 1px solid #1e293b;
        width: 270px; /* Width increased slightly */
        height: 100vh;
        position: fixed;
        top: 0; left: 0;
        z-index: 1000;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        font-family: 'Manrope', sans-serif; /* New Premium Font */
    }

    /* Brand Section */
    .sidebar-brand {
        padding: 35px 30px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .brand-icon {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #0ea5e9, #2563eb);
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 22px;
        box-shadow: 0 0 20px rgba(14, 165, 233, 0.3);
    }
    .brand-text {
        font-size: 24px;
        font-weight: 800;
        color: #f8fafc;
        letter-spacing: 0.5px;
    }

    /* Menu Items */
    .nav-container { padding: 0 15px; flex-grow: 1; overflow-y: auto; }
    .nav-header {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #64748b;
        font-weight: 700;
        margin: 25px 0 10px 20px;
    }

    .nav-item { list-style: none; margin-bottom: 5px; }
    
    .nav-link {
        display: flex;
        align-items: center;
        padding: 14px 20px;
        color: #94a3b8; /* Soft Grey Text */
        text-decoration: none;
        border-radius: 12px;
        font-weight: 500;
        font-size: 16px; /* Text Size Increased */
        transition: all 0.3s ease;
        letter-spacing: 0.3px;
    }

    .nav-link i {
        width: 24px;
        font-size: 20px; /* Icon Size Increased */
        margin-right: 14px;
        color: #64748b;
        transition: 0.3s;
    }

    /* Hover State */
    .nav-link:hover {
        background: rgba(255, 255, 255, 0.03);
        color: #fff;
        transform: translateX(5px);
    }
    .nav-link:hover i { color: #38bdf8; }

    /* Active State */
    .nav-link.active {
        background: linear-gradient(90deg, rgba(14, 165, 233, 0.15), transparent);
        color: #38bdf8; /* Neon Blue Text */
        font-weight: 600;
        border-left: 4px solid #38bdf8;
    }
    .nav-link.active i { color: #38bdf8; filter: drop-shadow(0 0 8px rgba(56, 189, 248, 0.5)); }

    /* Footer Profile */
    .sidebar-footer {
        margin: 20px;
        padding: 15px 20px;
        background: #1e293b;
        border-radius: 16px;
        border: 1px solid #334155;
        display: flex; justify-content: space-between; align-items: center;
    }
    .user-info h6 { margin: 0; color: #fff; font-size: 15px; font-weight: 600; }
    .user-info span { font-size: 12px; color: #94a3b8; }
    .logout-btn {
        width: 38px; height: 38px;
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        transition: 0.3s;
        text-decoration: none;
    }
    .logout-btn:hover { background: #ef4444; color: white; box-shadow: 0 0 15px rgba(239, 68, 68, 0.4); }
</style>

<div class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fas fa-bolt"></i></div>
        <div class="brand-text">Wilko</div>
    </div>
    
    <div class="nav-container">
        
        <div class="nav-header">Overview</div>
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>" href="dashboard.php">
                <i class="fas fa-th-large"></i> <span>Dashboard</span>
            </a>
        </li>

        <div class="nav-header">Business</div>
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>" href="orders.php">
                <i class="fas fa-shopping-cart"></i> <span>All Orders</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'providers.php' ? 'active' : '' ?>" href="providers.php">
                <i class="fas fa-user-tie"></i> <span>Providers</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'list.php' ? 'active' : '' ?>" href="list.php">
                <i class="fas fa-users"></i> <span>Customers</span>
            </a>
        </li>
        
        <div class="nav-header">System</div>
        <li class="nav-item">
            <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>" href="settings.php">
                <i class="fas fa-cog"></i> <span>App Settings</span>
            </a>
        </li>
    </div>

    <div class="sidebar-footer">
        <div class="d-flex align-items-center gap-3">
            <img src="https://ui-avatars.com/api/?name=Admin&background=0ea5e9&color=fff" class="rounded-circle" width="40" height="40">
            <div class="user-info">
                <h6>Super Admin</h6>
                <span>Online</span>
            </div>
        </div>
        <a href="logout.php" class="logout-btn"><i class="fas fa-power-off"></i></a>
    </div>
</div>

<div class="main-content">