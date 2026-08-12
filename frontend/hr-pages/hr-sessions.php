<?php
session_start();
require_once "../../backend/config/database.php";

$database = new Database();
$conn = $database->getConnection();

$stmt = $conn->prepare("
    SELECT sessions.id, sessions.zoom_link, sessions.scheduled_date, sessions.created_at,
           applicants.name AS applicant_name, applicants.email AS applicant_email,
           applicants.resume_link, applicants.position
    FROM sessions 
    JOIN applicants ON sessions.applicant_id = applicants.id
    ORDER BY sessions.scheduled_date DESC
");
$stmt->execute(); //execute the statement
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);//get all session rows to be displayed.


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <link rel="icon" href="../assets/favicon.ico">
    <title>All Sessions - HR</title>
</head>
<body>

    <header class="topbar">
        <img src="../assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <main class="page-content">
        <a href="hr-dashboard.php" class="back-link">&larr; Back to Dashboard</a>

        <h1>All Interview Sessions</h1>

        <?php if (empty($sessions)): ?>
            <p class="empty-state">No sessions found.</p> 
        <?php else: ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Applicant</th>
                            <th>Position</th>
                            <th>Resume</th>
                            <th>Scheduled Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- List all of the rows from the database into the page in a table form, looping through each. -->
                        <?php foreach ($sessions as $s)://s for 1 session row ?>
                            <tr>
                                <td><?= htmlspecialchars($s['applicant_name']) ?></td>
                                <td><?= htmlspecialchars($s['position']) ?></td>
                                <td>
                                    <?php if (!empty($s['resume_link'])): ?>
                                        <a href="<?= htmlspecialchars($s['resume_link']) ?>" target="_blank" class="table-link">View Resume</a>
                                    <?php else: ?>
                                        <span class="text-muted">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($s['scheduled_date']) ?></td>
                                <td>
                                    <?php if (!empty($s['zoom_link']))://this just checks if there is a link attached, meaning the session is on ?>
                                        <a href="../score-card.php?session_id=<?=$s['id'] ?>" class="join-btn">Join</a>
                                    <?php else: ?>
                                        <span class="text-muted">No link</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    
</body>
</html>
