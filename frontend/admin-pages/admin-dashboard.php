<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Dashboard - Admin</title>
</head>
<body>

    <header class="topbar">
        <img src="../assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="admin-dashboard.php" class="active">Dashboard</a>
                <a href="create-session.php">Create Session</a>
                <a href="manage-applicants.php">Manage Applicants</a>
            </nav>
            <a href="../login.php" class="logout">Logout</a>
        </aside>

        <main class="dashboard-content">
            <h1>Welcome, Admin</h1>

            <div class="cards-grid">
<!--The a tag below has the top card - Create session card-->
                <a href="create-session.php" class="card">
                    <h2>Create Session</h2>
                    <p>Generate a new interview link</p>
                </a>
<!-- The tags below have the -reports and last interview -->
                <a href="reports.php" class="card">
                    <h2>Interview Reports</h2>
                    <p>View past interview reports</p>
                </a>
                    
                <a href="sessions.php" class="card">
                    <h2>Interview Sessions</h2>
                    <p>List of all the sessions created </p>
                </a>
<!-- The tag below is for departments.php -->
                <a href="departments.php" class="card">
                    <h2>Departments</h2>
                    <p>Manage departments and roles</p>
                </a>
            </div>

        </main>
    </div>

</body>
</html>