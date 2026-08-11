<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Dashboard - Applicant</title>
</head>
<body>

    <header class="topbar">
        <img src="../assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="applicant-dashboard.html" class="active">Dashboard</a>
                <a href="profile.html">My Profile</a>
                <a href="application-status.html">Application Status</a>
            </nav>
            <a href="../login.html" class="logout">Logout</a>
        </aside>

        <main class="dashboard-content">
            <h1>Welcome, Applicant</h1>
<!-- View cards for the applicants  -->


            <div class="cards-grid">
                <a href="#" class="card-full">
                    <h2>Join Session</h2>
                    <p>No active session at the moment</p>
                    <button class="join-btn" disabled>Join</button>
                </a>

                <a href="profile.html" class="card">
                    <h2>My Profile</h2>
                    <p>View your personal information</p>
                </a>

                <a href="application-status.html" class="card">
                    <h2>Application Status</h2>
                    <p>Track your application progress</p>
                </a>
            </div>
        </main>
    </div>

</body>
</html>
