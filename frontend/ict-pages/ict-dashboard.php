<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= APP_BASE ?>/frontend/style.css">
    <link rel="icon" href="<?= APP_BASE ?>/frontend/assets/favicon.ico">
    <title>Dashboard - ICT</title>
</head>
<body>

    <header class="topbar">
        <img src="<?= APP_BASE ?>/frontend/assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
        <button class="hamburger" onclick="toggleSidebar()">
            <span></span><span></span><span></span>
        </button>
    </header>

    <div class="dashboard-layout">
        
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="<?= APP_BASE ?>/ict" class="active">Dashboard</a>
                <a href="<?= APP_BASE ?>/ict/manage-users">Manage Users</a>
            </nav>
            <a href="<?= APP_BASE ?>/login" class="logout">Logout</a>
        </aside>
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

        <main class="dashboard-content">
            <h1>Welcome, <?=$_SESSION["user_name"]?></h1>
<!-- Cards for accessing manage users and departments for ICT department -->
            <div class="cards-grid">
                <a href="<?= APP_BASE ?>/manage-users" class="card">
                    <h2>Manage Employees</h2>
                    <p>Create, edit, or delete employees accounts</p>
                </a>

                <a href="<?= APP_BASE ?>/manage-applicants" class="card">
                    <h2>Manage Applicants</h2>
                    <p>Delete and view all applicants</p>
                </a>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }
    </script>
</body>
</html>
