<div class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fa-solid fa-leaf"></i> FPSMS</h2>
        <p>Admin Panel</p>
    </div>
    <ul class="nav-links">
        <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
        <li><a href="manage_technicians.php"><i class="fa-solid fa-users-gear"></i> Manage Technicians</a></li>
        <li><a href="manage_barangays.php"><i class="fa-solid fa-map-location-dot"></i> Manage Barangays</a></li>
        <li><a href="manage_farmers.php"><i class="fa-solid fa-users"></i> Manage Farmers</a></li>
        <li><a href="view_production.php"><i class="fa-solid fa-wheat-awn"></i> Production Records</a></li>
        <li><a href="view_expenses.php"><i class="fa-solid fa-coins"></i> Expense Records</a></li>
        <li><a href="../logout.php" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<style>
.sidebar {
    position: fixed;
    left: 0;
    top: 0;
    width: 250px;
    height: 100vh;
    background: linear-gradient(135deg, #047857 0%, #065f46 100%);
    color: white;
    padding: 20px;
    box-shadow: 2px 0 10px rgba(0,0,0,0.1);
    z-index: 1000;
}

.sidebar-header {
    text-align: center;
    padding-bottom: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.2);
    margin-bottom: 20px;
}

.sidebar-header h2 {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
}

.sidebar-header p {
    font-size: 14px;
    opacity: 0.8;
    margin: 5px 0 0 0;
}

.nav-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-links li {
    margin-bottom: 10px;
}

.nav-links a {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s;
}

.nav-links a:hover {
    background: rgba(255,255,255,0.1);
    transform: translateX(5px);
}

.nav-links a i {
    margin-right: 10px;
    width: 20px;
}

.nav-links a.logout {
    background: rgba(239, 68, 68, 0.2);
    margin-top: 20px;
}

.nav-links a.logout:hover {
    background: rgba(239, 68, 68, 0.4);
}

.main-content {
    margin-left: 250px;
    padding: 20px;
}
</style>
