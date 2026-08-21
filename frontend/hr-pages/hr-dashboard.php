<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= APP_BASE ?>/frontend/style.css">
    <link rel="icon" href="<?= APP_BASE ?>/frontend/assets/favicon.ico">
    <title>Dashboard - HR</title>
</head>
<body>

    <header class="topbar">
        <img src="<?= APP_BASE ?>/frontend/assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="<?= APP_BASE ?>/hr" class="active">Dashboard</a>
                <a href="<?= APP_BASE ?>/hr/sessions">All Sessions</a>
                <a href="<?= APP_BASE ?>/reports">Reports</a>
                <a href="<?= APP_BASE ?>/hr/manage-applicants">Manage Applicants</a>
                <a href="<?= APP_BASE ?>/hr/score-applicants">Score Applicants</a>
            </nav>
            <a href="<?= APP_BASE ?>/login" class="logout">Logout</a>
        </aside>

        <main class="dashboard-content">
            <h1>Welcome <?=$_SESSION["user_name"]?></h1>

            <div class="cards-grid">
                <a href="<?= APP_BASE ?>/sessions" class="card">
                    <h2>All Sessions</h2>
                    <p>View all interview sessions</p>
                </a>

                <a href="<?= APP_BASE ?>/reports" class="card">
                    <h2>Interview Reports</h2>
                    <p>View interview reports</p>
                </a>

                <a href="<?= APP_BASE ?>/hr/manage-applicants" class="card">
                    <h2>Manage Applicants</h2>
                    <p>View and manage applicant status</p>
                </a>

            </div>
        </main>
    </div>

</body>
</html>
