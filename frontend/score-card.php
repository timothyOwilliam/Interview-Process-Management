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
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($session) {
        $stmt = $conn->prepare("SELECT * FROM scores WHERE session_id = :session_id");
        $stmt->execute([':session_id' => $session_id]);
        $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $count = count($scores);
        $agg = [
            'technical_skills' => 0,
            'communication'    => 0,
            'confidence'       => 0,
            'work_ethic'       => 0,
            'team_fit'         => 0,
            'total'            => 0,
            'count'            => $count,
        ];

        foreach ($scores as $row) {
            $agg['technical_skills'] += $row['technical_skills'];
            $agg['communication']    += $row['communication'];
            $agg['confidence']       += $row['confidence'];
            $agg['work_ethic']       += $row['work_ethic'];
            $agg['team_fit']         += $row['team_fit'];
            $agg['total']            += $row['total'];
        }

        $aggregates = $agg;
    }
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

        <?php if (!empty($message)): ?>
            <div class="<?= $messageType === 'success' ? 'success-message' : 'error-message' ?>" style="display:block; max-width:900px; margin: 0 auto 1.5rem;">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!$session): ?>

            <p class="empty-state">Session not found or missing session ID.</p>

        <?php else: ?>

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

            <!-- Submitted Scores Summary -->
            <div class="table-wrapper" style="margin-top: 2rem;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="2">Submitted Scores (<?= $aggregates['count'] ?> total)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>Technical Skills</th>
                            <td><?= $aggregates['technical_skills'] ?></td>
                        </tr>
                        <tr>
                            <th>Communication</th>
                            <td><?= $aggregates['communication'] ?></td>
                        </tr>
                        <tr>
                            <th>Confidence</th>
                            <td><?= $aggregates['confidence'] ?></td>
                        </tr>
                        <tr>
                            <th>Work Ethic</th>
                            <td><?= $aggregates['work_ethic'] ?></td>
                        </tr>
                        <tr>
                            <th>Team Fit</th>
                            <td><?= $aggregates['team_fit'] ?></td>
                        </tr>
                        <tr>
                            <th>Total</th>
                            <td><?= $aggregates['total'] ?></td>
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

                    <label for="total">Total</label>
                    <input type="number" name="total_display" id="total" min="5" max="25" readonly placeholder="Auto-calculated">

                    <button type="submit" class="sign-in-button">Submit Score</button>
                </form>
            </div>

        <?php endif; ?>
    </main>

    <script>
        (function() {
            var fields = ['technical_skills', 'communication', 'confidence', 'work_ethic', 'team_fit'];
            var totalField = document.getElementById('total');

            function sum() {
                var t = 0;
                for (var i = 0; i < fields.length; i++) {
                    var v = parseInt(document.getElementById(fields[i]).value);
                    if (!isNaN(v)) t += v;
                }
                totalField.value = t;
            }

            for (var i = 0; i < fields.length; i++) {
                document.getElementById(fields[i]).addEventListener('input', sum);
            }
        })();
    </script>

</body>
</html>
