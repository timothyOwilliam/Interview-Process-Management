<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" href="../assets/favicon.ico">
    <title>Dashboard - HR</title>
</head>
<body>

    <header class="topbar">
        <img src="../assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="hr-dashboard.php" class="active">Dashboard</a>
                <a href="hr-sessions.php">All Sessions</a>
                <a href="reports.php">Reports</a>
                <a href="manage-applicants.php">Manage Applicants</a>
                <a href="score-applicants.php">Score Applicants</a>
            </nav>
            <a href="../login.php" class="logout">Logout</a>
        </aside>

        <main class="dashboard-content">
            <h1>Welcome, HR</h1>

            <div class="cards-grid">
                <a href="hr-sessions.php" class="card">
                    <h2>All Sessions</h2>
                    <p>View all interview sessions</p>
                </a>

                <a href="reports.php" class="card">
                    <h2>Interview Reports</h2>
                    <p>View interview reports</p>
                </a>

                <a href="manage-applicants.php" class="card">
                    <h2>Manage Applicants</h2>
                    <p>View and manage applicant status</p>
                </a>

                <a href="score-applicants.php" class="card">
                    <h2>Score Applicants</h2>
                    <p>Score applicants after interviews</p>
                </a>
            </div>
        </main>
    </div>

</body>
</html>
