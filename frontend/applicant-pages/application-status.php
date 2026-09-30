<?php
require_once __DIR__ . "/../../backend/config/database.php"; //creates absolute path, used for routing purposes

//if there is no identity in the session, or the user is not an applicant, stop it
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "applicant") {
    header("Location: " . APP_BASE . "/login");
    exit();
}

$database = new Database(); //start a database instance
$conn = $database->getConnection();

$applicant_id = $_SESSION["user_id"]; //user_id attached to a session is applied to $applicant_id

//fetch the applicant's status together with any scheduled session (read-only for the applicant)
//latest session supersedes earlier ones; shortlisting is a manual HR decision at applicant level
$stmt = $conn->prepare("SELECT applicants.name, applicants.email, applicants.position, applicants.application_status,
                               sessions.zoom_link, sessions.scheduled_date
                        FROM applicants
                        LEFT JOIN sessions ON sessions.applicant_id = applicants.id
                        WHERE applicants.id = :id
                        ORDER BY sessions.scheduled_date DESC LIMIT 1");
$stmt->execute([':id' => $applicant_id]); //execute the statement
$application = $stmt->fetch(PDO::FETCH_ASSOC); //Get the row and values

if (!$application) {
    //applicant record missing although the session is valid — force a fresh login
    header("Location: " . APP_BASE . "/login?error=Applicant+record+not+found.");
    exit();
}

$application_status = $application['application_status'] ?? 'pending';

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
    <title>Application Status - Applicant</title>
</head>
<body>

    <header class="topbar">
        <img src="<?= APP_BASE ?>/frontend/assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <main class="page-content">
        <a href="<?= APP_BASE ?>/applicant" class="back-link">&larr; Back to Dashboard</a>

        <h1>Application Status</h1>

        <div class="table-wrapper">
            <table class="data-table">
                <tbody>
                    <tr>
                        <th>Name</th>
                        <td><?= htmlspecialchars($application['name']) ?></td>
                    </tr>
                    <tr>
                        <th>Position Applied</th>
                        <td><?= htmlspecialchars($application['position'] ?? '—') ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($application_status)) ?></span></td>
                    </tr>
                    <tr>
                        <th>Scheduled Interview</th>
                        <td><?= !empty($application['scheduled_date']) ? htmlspecialchars($application['scheduled_date']) : 'Not scheduled yet' ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>
