<?php

session_start();
require_once "../../backend/config/database.php";

//if the there is no identity in the session stop it
if(!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "applicant"){

    header("Location: ../login.php");
    exit();    
    
}
$database = new Database(); //start a database instance
$conn = $database->getConnection();

$applicant_id = $_SESSION["user_id"]; //user_id attached to a session is applied to $applicant_id


$stmt = $conn->prepare("SELECT zoom_link FROM sessions WHERE applicant_id= :id ");
$stmt -> execute([':id' => $applicant_id]);//execute the statement
$session_details = $stmt -> fetch(PDO::FETCH_ASSOC); //Get the row and values

//session_details has the zoomlink, we give it to the applicant_zoom_link 
$applicant_zoom_link = $session_details ? $session_details["zoom_link"] : "";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" href="../assets/favicon.ico">
    <title>Dashboard - Applicant</title>
</head>
<body>

    <header class="topbar">
        <img src="../assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <div class="dashboard-layout">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="applicant-dashboard.php" class="active">Dashboard</a>
                <a href="profile.php">My Profile</a>
                <a href="application-status.php">Application Status</a>
            </nav>
            <a href="../login.php" class="logout">Logout</a>
        </aside>

        <main class="dashboard-content">
            <h1>Welcome, <?=$_SESSION["user_name"]?></h1>
<!-- View cards for the applicants  -->


            <div class="cards-grid">
                <div class="card-full">
                    <h2>Join Session</h2>
                    <p><?=$applicant_zoom_link ? 'Your interview session is ready' : 'No active session at the moment' ?></p>
                    <!-- if there is a session, the link will be embeded on th ejoin button, if none the button disables -->
                    <a href=<?php echo htmlspecialchars($applicant_zoom_link); ?> target="blank">
                        <button class="join-btn" <?php echo empty($applicant_zoom_link)? "disabled" : '';?> >Join</button>
                    </a>
                </div>

                <a href="profile.php" class="card">
                    <h2>My Profile</h2>
                    <p>View your personal information</p>
                </a>

                <a href="application-status.php" class="card">
                    <h2>Application Status</h2>
                    <p>Track your application progress</p>
                </a>
            </div>
        </main>
    </div>

</body>
</html>
