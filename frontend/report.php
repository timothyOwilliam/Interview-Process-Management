<?php

session_start();
require_once "../backend/config/database.php";


$database = new Database();
$conn = $database->getConnection();

//Fetch the scores and applicant position
$stmt = $conn->prepare("SELECT applicant_name, applicants.position AS position_applied, technical_skills, communication, confidence, work_ethic, team_fit, total, sessions.scheduled_date AS scheduled_date
                        FROM scores
                        JOIN applicants ON scores.applicant_id = applicants.id
                        JOIN sessions ON scores.session_id = sessions.id ");

$stmt->execute();

$applicant_scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
//$applicant_scores, contains the details of applicant and scores

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="assets/favicon.ico">
    <title>Interview Reports</title>
</head>
<body>

    <header class="topbar">
        <img src="assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <main class="page-content">
        <a href="admin-pages/admin-dashboard.php" class="back-link">&larr; Back to Dashboard</a>

        <div class="page-header">
            <h1>Interview Reports</h1>
            <a href="#" class="export-btn">Export CSV</a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Applicant Name</th>
                        <th>Position Applied</th>
                        <th>Technical Skills</th>
                        <th>Communication</th>
                        <th>Confidence</th>
                        <th>Work Ethic</th>
                        <th>Team Fit</th>
                        <th>Total</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($applicant_scores as $applicant_score): ?>
                    <tr>
                        <td><?= htmlspecialchars($applicant_score["applicant_name"])?></td>
                        <td><?= htmlspecialchars($applicant_score["position_applied"])?></td>
                        <td><?= htmlspecialchars($applicant_score["technical_skills"])?></td>
                        <td><?= htmlspecialchars($applicant_score["communication"])?></td>
                        <td><?= htmlspecialchars($applicant_score["confidence"])?></td>
                        <td><?= htmlspecialchars($applicant_score["work_ethic"])?></td>
                        <td><?= htmlspecialchars($applicant_score["team_fit"])?></td>
                        <td><?= htmlspecialchars($applicant_score["total"])?></td>
                        <td><?= htmlspecialchars($applicant_score["scheduled_date"])?></td>
                    </tr>
                    
                    <?php endforeach?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>