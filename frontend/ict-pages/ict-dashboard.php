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
    </header>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="<?= APP_BASE ?>/ict" class="active">Dashboard</a>
                <a href="<?= APP_BASE ?>/ict/manage-users">Manage Users</a>
            </nav>
            <a href="<?= APP_BASE ?>/login" class="logout">Logout</a>
        </aside>

        <main class="dashboard-content">
            <h1>Welcome, <?=$_SESSION["user_name"]?></h1>
<!-- Cards for accessing manage users and departments for ICT department -->
            <div class="cards-grid">
                <a href="<?= APP_BASE ?>/ict/manage-users" class="card">
                    <h2>Manage Users</h2>
                    <p>Create, edit, or delete user accounts</p>
                </a>

                <a href="<?= APP_BASE ?>/ict/departments" class="card">
                    <h2>Departments</h2>
                    <p>Manage departments and roles</p>
                </a>
            </div>
        </main>
    </div>

</body>
</html>
