<style>
.modal {
    display: none;
    position: fixed;
    z-index: 999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
}
.modal-content {
    background-color: #fff;
    color: #333;
    margin: 15% auto;
    padding: 20px;
    border-radius: 10px;
    width: 350px;
    text-align: center;
}
.modal-buttons {
    margin-top: 20px;
    display: flex;
    justify-content: space-around;
}
.modal-buttons .confirm {
    background-color: #2e7d32;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
}
.modal-buttons .cancel {
    background-color: #ccc;
    color: #333;
    border: none;
    padding: 10px 20px;
    border-radius: 5px;
    cursor: pointer;
}
.modal-buttons .confirm:hover {
    background-color: #1b5e20;
}
.modal-buttons .cancel:hover {
    background-color: #b0b0b0;
}
</style>

<div class="sidebar">
    <div class="sidebar-header">
        <h2><i class="fa-solid fa-leaf"></i> BukidPro</h2>
        <p>Technician Panel</p>
    </div>
    <ul class="nav-links">
        <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
        <li><a href="manage_farmers.php"><i class="fa-solid fa-users"></i> Manage Farmers</a></li>
        <li><a href="record_production.php"><i class="fa-solid fa-wheat-awn"></i> Record Production</a></li>
        <li><a href="record_expense.php"><i class="fa-solid fa-coins"></i> Record Expense</a></li>
        <li><a href="#" id="logoutBtn" class="logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
    </ul>
</div>

<div id="logoutModal" class="modal">
    <div class="modal-content">
        <h3>Confirm Logout</h3>
        <p>Are you sure you want to log out?</p>
        <div class="modal-buttons">
            <button id="confirmLogout" class="confirm">Yes, Logout</button>
            <button id="cancelLogout" class="cancel">Cancel</button>
        </div>
    </div>
</div>

<script>
// Highlight active sidebar link automatically
document.addEventListener("DOMContentLoaded", function() {
    const currentPage = window.location.pathname.split("/").pop();
    const navLinks = document.querySelectorAll(".nav-links a");

    navLinks.forEach(link => {
        if (link.getAttribute("href") === currentPage) {
            link.classList.add("active");
        }
    });
});

const logoutBtn = document.getElementById("logoutBtn");
const logoutModal = document.getElementById("logoutModal");
const confirmLogout = document.getElementById("confirmLogout");
const cancelLogout = document.getElementById("cancelLogout");

logoutBtn.addEventListener("click", function(e) {
    e.preventDefault();
    logoutModal.style.display = "block";
});

cancelLogout.addEventListener("click", function() {
    logoutModal.style.display = "none";
});

confirmLogout.addEventListener("click", function() {
    window.location.href = "../logout.php";
});

window.addEventListener("click", function(event) {
    if (event.target === logoutModal) {
        logoutModal.style.display = "none";
    }
});
</script>
