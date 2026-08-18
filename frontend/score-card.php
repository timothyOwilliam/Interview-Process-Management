<?php
session_start();
require_once "../backend/config/database.php";

$database = new Database();
$conn = $database->getConnection();

$message = '';
$messageType = '';

if (isset($_GET['success'])) {
    $message = $_GET['success'];
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $message = $_GET['error'];
    $messageType = 'error';
}

$session = null;
$applicant = null;
$scores = [];
$aggregates = [];

if (isset($_GET['session_id']) && is_numeric($_GET['session_id'])) {

//This will fetch details of the applicant so that it can be displayed
    $session_id = intval($_GET['session_id']);

    $stmt = $conn->prepare("
        SELECT sessions.id AS session_id, sessions.zoom_link, sessions.scheduled_date,
               applicants.id AS applicant_id, applicants.name AS applicant_name,
               applicants.email, applicants.phone, applicants.position, applicants.resume_link
        FROM sessions
        JOIN applicants ON sessions.applicant_id = applicants.id
        WHERE sessions.id = :session_id
    ");
    $stmt->execute([':session_id' => $session_id]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC); //contains the session details and the details of the applicants that will be displayed in the UI

} else {
    header("Location: hr-pages/hr-dashboard.php?error=No+session+selected");
    exit();
}

if ($session === false) {
    header("Location: hr-pages/hr-dashboard.php?error=Session+not+found");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="assets/favicon.ico">
    <title>Score Card</title>
</head>
<body>

    <header class="topbar">
        <img src="assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <main class="page-content">
        <a href="hr-pages/hr-dashboard.php" class="back-link">&larr; Back to Dashboard</a>

        <h1>Score Card</h1>

            <!-- Success/Error message -->
            <?php if (!empty($message)): ?>
                <div class="<?= $messageType === 'success' ? 'success-message' : 'error-message' //makes the class either success or error and used to style in the .css file ?>" style="display:block;">
                    <?= htmlspecialchars($message)?>
                </div>
            <?php endif; ?>
            <!-- Applicant Details -->
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="2">Applicant Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Name</th>
                            <td><?= htmlspecialchars($session['applicant_name']) ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?= htmlspecialchars($session['email']) ?></td>
                        </tr>
                        <tr>
                            <th>Phone</th>
                            <td><?= htmlspecialchars($session['phone']) ?></td>
                        </tr>
                        <tr>
                            <th>Position</th>
                            <td><?= htmlspecialchars($session['position']) ?></td>
                        </tr>
                        <tr>
                            <th>Resume</th>
                            <td>
                                <?php if (!empty($session['resume_link'])): ?>
                                    <a href="<?= htmlspecialchars($session['resume_link']) ?>" target="_blank" class="table-link">View Resume</a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <tr>
                            <th>Scheduled Date</th>
                            <td><?= htmlspecialchars($session['scheduled_date']) ?></td>
                        </tr>
                        <tr>
                            <th>Zoom Link</th>
                            <td>
                                <?php if (!empty($session['zoom_link'])): ?>
                                    <a href="<?= htmlspecialchars($session['zoom_link']) ?>" target="_blank" class="table-link">Join</a>
                                <?php else: ?>
                                    <span class="text-muted">No link</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Scoring Form -->
            <div class="login-section" style="margin-top: 2rem;">
                <form action="../backend/submit-score.php" method="POST" class="login-details">
                    <input type="hidden" name="session_id" value="<?= $session['session_id'] ?>">
                    <input type="hidden" name="applicant_name" value="<?= htmlspecialchars($session['applicant_name']) ?>">

                    <label for="technical_skills">Technical Skills (1–5)</label>
                    <input type="number" name="technical_skills" id="technical_skills" min="1" max="5" required placeholder="1 – 5">

                    <label for="communication">Communication (1–5)</label>
                    <input type="number" name="communication" id="communication" min="1" max="5" required placeholder="1 – 5">

                    <label for="confidence">Confidence (1–5)</label>
                    <input type="number" name="confidence" id="confidence" min="1" max="5" required placeholder="1 – 5">

                    <label for="work_ethic">Work Ethic (1–5)</label>
                    <input type="number" name="work_ethic" id="work_ethic" min="1" max="5" required placeholder="1 – 5">

                    <label for="team_fit">Team Fit (1–5)</label>
                    <input type="number" name="team_fit" id="team_fit" min="1" max="5" required placeholder="1 – 5">

                    <button type="submit" class="sign-in-button">Submit Score</button>
                </form>
            </div>

    </main>



</body>
</html>
