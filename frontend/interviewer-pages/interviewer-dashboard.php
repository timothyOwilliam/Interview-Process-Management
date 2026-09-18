<?php
require_once __DIR__ . "/../../backend/config/database.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "interviewer") {
    header("Location: " . APP_BASE . "/login");
    exit();
}

$database = new Database();
$conn = $database->getConnection();

$interviewer_id = $_SESSION["user_id"];

$stmt = $conn->prepare("
    SELECT s.id AS session_id, s.zoom_link, s.scheduled_date,
           a.name AS applicant_name, a.position, a.email, a.resume_link
    FROM sessions s
    JOIN applicants a ON s.applicant_id = a.id
    JOIN session_interviewers si ON s.id = si.session_id
    WHERE si.interviewer_id = :interviewer_id
    ORDER BY s.scheduled_date DESC
");
$stmt->execute([':interviewer_id' => $interviewer_id]);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= APP_BASE ?>/frontend/style.css">
    <link rel="icon" href="<?= APP_BASE ?>/frontend/assets/favicon.ico">
    <title>Dashboard - Interviewer</title>
    <style>
        .session-card {
            background-color: var(--secondary-color);
            color: var(--text-color);
            border-radius: 10px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            transition: transform 0.2s;
        }
        .session-card:hover {
            transform: translateY(-3px);
        }
        .session-card h2 {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
        }
        .session-card .detail {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .session-card .detail strong {
            opacity: 1;
        }
        .session-card .card-actions {
            display: flex;
            gap: 0.75rem;
            margin-top: 0.5rem;
        }
        .session-card .card-actions a {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .btn-zoom {
            background-color: var(--third-color);
            color: white;
        }
        .btn-zoom:hover {
            opacity: 0.9;
        }
        .btn-score {
            background-color: white;
            color: var(--secondary-color);
        }
        .btn-score:hover {
            opacity: 0.9;
        }
        .empty-state {
            text-align: center;
            color: #666;
            font-size: 1rem;
            margin-top: 3rem;
        }
    </style>
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
                <a href="<?= APP_BASE ?>/interviewer" class="active">Dashboard</a>
            </nav>
            <a href="<?= APP_BASE ?>/login" class="logout">Logout</a>
        </aside>
        <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

        <main class="dashboard-content">
            <h1>Welcome, <?= htmlspecialchars($_SESSION["user_name"]) ?></h1>

            <?php if (empty($sessions)): ?>
                <p class="empty-state">You have no assigned sessions yet.</p>
            <?php else: ?>
                <div class="cards-grid">
                    <?php foreach ($sessions as $s): ?>
                        <div class="session-card">
                            <h2><?= htmlspecialchars($s['applicant_name']) ?></h2>
                            <div class="detail"><strong>Position:</strong> <?= htmlspecialchars($s['position'] ?: 'N/A') ?></div>
                            <div class="detail"><strong>Date:</strong> <?= htmlspecialchars(date('M d, Y \T\i\m\e\: g:i A', strtotime($s['scheduled_date']))) ?></div>
                            <div class="detail"><strong>Email:</strong> <?= htmlspecialchars($s['email']) ?></div>
                            <?php if (!empty($s['resume_link'])): ?>
                                <div class="detail"><a href="<?= htmlspecialchars($s['resume_link']) ?>" target="_blank" style="color: #fff; text-decoration: underline;">View Resume</a></div>
                            <?php endif; ?>
                            <div class="card-actions">
                                <?php if (!empty($s['zoom_link'])): ?>
                                    <a href="<?= htmlspecialchars($s['zoom_link']) ?>" target="_blank" class="btn-zoom">Join Zoom</a>
                                <?php endif; ?>
                                <a href="<?= APP_BASE ?>/score-card?session_id=<?= $s['session_id'] ?>" class="btn-score">Score</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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
