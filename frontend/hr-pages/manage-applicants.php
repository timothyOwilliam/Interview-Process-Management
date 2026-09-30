<?php
require_once __DIR__ . "/../../backend/config/database.php";


// Ensure only HR can access this page
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'hr') {
    header("Location: " . APP_BASE . "/login?error=Access+denied.+HR+role+required.");
    exit();
}

// Handle flash messages
$message = '';
$messageType = '';
if (isset($_GET['success'])) {
    $message = 'Operation completed successfully';
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
    $messageType = 'error';
}

$database = new Database();
$conn = $database->getConnection();

// Fetch all applicants with session count and best score
$stmt = $conn->prepare("
    SELECT applicants.*, 
           COUNT(DISTINCT sessions.id) AS session_count,
           MAX(scores.total) AS best_score
    FROM applicants
    LEFT JOIN sessions ON sessions.applicant_id = applicants.id
    LEFT JOIN scores ON scores.applicant_id = applicants.id
    GROUP BY applicants.id
    ORDER BY applicants.name ASC
");
$stmt->execute();
$applicants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= APP_BASE ?>/frontend/style.css">
    <link rel="icon" href="<?= APP_BASE ?>/frontend/assets/favicon.ico">
    <title>Manage Applicants - HR</title>
</head>
<body>

    <header class="topbar">
        <img src="<?= APP_BASE ?>/frontend/assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <main class="page-content">
        <a href="<?= APP_BASE ?>/hr" class="back-link">&larr; Back to Dashboard</a>

        <h1>Manage Applicants</h1>

        <!-- Success/Error message -->
        <?php if (!empty($message)): ?>
            <div class="<?= $messageType === 'success' ? 'success-message' : 'error-message' ?>" style="display:block;">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <?php if (empty($applicants)): ?>
            <div class="empty-state">No applicants found.</div>
        <?php else: ?>
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Status</th>
                            <th>Sessions</th>
                            <th>Best Score</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applicants as $applicant): ?>
                        <tr>
                            <td><?= htmlspecialchars($applicant['name']) ?></td>
                            <td><?= htmlspecialchars($applicant['email']) ?></td>
                            <td><?= htmlspecialchars($applicant['position'] ?? '—') ?></td>
                            <td>
                                <?php 
                                    $statusClass = '';
                                    if ($applicant['application_status'] === 'shortlisted') {
                                        $statusClass = 'status-shortlisted';
                                    } elseif ($applicant['application_status'] === 'rejected') {
                                        $statusClass = 'status-rejected';
                                    } else {
                                        $statusClass = 'status-pending';
                                    }
                                ?>
                                <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars(ucfirst($applicant['application_status'])) ?></span>
                            </td>
                            <td><?= htmlspecialchars($applicant['session_count']) ?></td>
                            <td><?= htmlspecialchars($applicant['best_score'] ?? '—') ?></td>
                            <td>
                                <form action="<?= APP_BASE ?>/backend/update-applicant-status.php" method="POST" style="display:inline-block;">
                                    <input type="hidden" name="applicant_id" value="<?= $applicant['id'] ?>">
                                    <select name="new_status" onchange="this.form.submit()">
                                        <option value="pending" <?= $applicant['application_status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="shortlisted" <?= $applicant['application_status'] === 'shortlisted' ? 'selected' : '' ?>>Shortlist</option>
                                        <option value="rejected" <?= $applicant['application_status'] === 'rejected' ? 'selected' : '' ?>>Reject</option>
                                    </select>
                                </form>
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