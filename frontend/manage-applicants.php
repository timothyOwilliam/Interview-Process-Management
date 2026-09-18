<?php
require_once __DIR__ . "/../../backend/config/database.php";

// Handle flash messages
if (isset($_GET['success'])) {
    $message = 'Operation completed successfully';
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $message = htmlspecialchars($_GET['error']);
    $messageType = 'error';
}

$database = new Database();
$conn = $database->getConnection();

// Fetch all applicants
$stmt = $conn->prepare("SELECT * FROM applicants ORDER BY name ASC");
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
    <title>Manage Applicants - ICT</title>
</head>
<body>

    <header class="topbar">
        <img src="<?= APP_BASE ?>/frontend/assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <main class="page-content">
        <a href="<?= APP_BASE ?>/ict" class="back-link">&larr; Back to Dashboard</a>

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
                            <th>Phone</th>
                            <th>Position</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applicants as $a): ?>
                        <tr>
                            <td><?= htmlspecialchars($a['name']) ?></td>
                            <td><?= htmlspecialchars($a['email']) ?></td>
                            <td><?= htmlspecialchars($a['phone'] ?? '—') ?></td>
                            <td><?= htmlspecialchars($a['position'] ?? '—') ?></td>
                            <td><?= date('Y-m-d', strtotime($a['created_at'])) ?></td>
                            <td>
                                <a href="<?= APP_BASE ?>/backend/delete-applicant.php?id=<?= $a['id'] ?>" class="table-link" onclick="return confirm('Are you sure you want to delete this applicant? This will also remove all their sessions and scores.')">Delete</a>
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
