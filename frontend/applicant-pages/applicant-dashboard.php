<?php
require_once __DIR__ . "/../../backend/config/database.php"; //creates absolute path for the, which is used for routing purposes

//if the there is no identity in the session stop it
if(!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "applicant"){

    header("Location: " . APP_BASE . "/login");
    exit();    
    
}
$database = new Database(); //start a database instance
$conn = $database->getConnection();

$applicant_id = $_SESSION["user_id"]; //user_id attached to a session is applied to $applicant_id


//latest session supersedes earlier ones; shortlisting is a manual HR decision at applicant level
$stmt = $conn->prepare("SELECT zoom_link FROM sessions WHERE applicant_id= :id ORDER BY scheduled_date DESC LIMIT 1");
$stmt -> execute([':id' => $applicant_id]);//execute the statement
$session_details = $stmt -> fetch(PDO::FETCH_ASSOC); //Get the row and values

//session_details has the zoomlink, we give it to the applicant_zoom_link 
$applicant_zoom_link = $session_details ? $session_details["zoom_link"] : "";

//fetch the applicant's application status (set by HR on the manage-applicants page)
$statusStmt = $conn->prepare("SELECT application_status, position FROM applicants WHERE id = :id");
$statusStmt->execute([':id' => $applicant_id]); //execute the statement
$applicant_record = $statusStmt->fetch(PDO::FETCH_ASSOC); //Get the row and values

$application_status = $applicant_record['application_status'] ?? 'pending';
$applied_position = $applicant_record['position'] ?? '';

//pick the badge colour matching the status
$statusClass = 'status-pending';
if ($application_status === 'shortlisted') {
    $statusClass = 'status-shortlisted';
} elseif ($application_status === 'rejected') {
    $statusClass = 'status-rejected';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= APP_BASE ?>/frontend/style.css">
    <link rel="icon" href="<?= APP_BASE ?>/frontend/assets/favicon.ico">
    <title>Dashboard - Applicant</title>
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
                <a href="<?= APP_BASE ?>/applicant" class="active">Dashboard</a>
                <a href="<?= APP_BASE ?>/applicant/profile">My Profile</a>
                <a href="<?= APP_BASE ?>/applicant/application-status">Application Status</a>
            </nav>
            <a href="<?= APP_BASE ?>/login" class="logout">Logout</a>
        </aside>
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

        <main class="dashboard-content">
            <h1>Welcome, <?=$_SESSION["user_name"]?></h1>

            <p style="margin-bottom: 1.5rem;">Application Status: <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($application_status)) ?></span></p>

            <div class="cards-grid">
                <div class="card-full">
                    <h2>Join Session</h2>
                    <p><?=$applicant_zoom_link ? 'Your interview session is ready' : 'No active session at the moment' ?></p>
                    <?php if (!empty($applicant_zoom_link)): ?>
                        <a href="<?= htmlspecialchars($applicant_zoom_link) ?>" target="_blank" rel="noopener">
                            <button class="join-btn">Join Now</button>
                        </a>
                    <?php else: ?>
                        <button class="join-btn" disabled>Waiting for Session</button>
                    <?php endif; ?>
                </div>

                <a href="<?= APP_BASE ?>/applicant/profile" class="card">
                    <h2>My Profile</h2>
                    <p>View and update your information</p>
                </a>

                <a href="<?= APP_BASE ?>/applicant/application-status" class="card">
                    <h2>Application Status</h2>
                    <p>Track your application progress</p>
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
