<?php
// TODO : Review all of the code below and add comments

session_start();

require_once "config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $applicant_name = trim($_POST['applicant_name'] ?? '');

    $technical_skills = isset($_POST['technical_skills']) ? intval($_POST['technical_skills']) : 0;
    $communication    = isset($_POST['communication']) ? intval($_POST['communication']) : 0;
    $confidence       = isset($_POST['confidence']) ? intval($_POST['confidence']) : 0;
    $work_ethic       = isset($_POST['work_ethic']) ? intval($_POST['work_ethic']) : 0;
    $team_fit         = isset($_POST['team_fit']) ? intval($_POST['team_fit']) : 0;

    if ($session_id <= 0 || empty($applicant_name)) {
        header("Location: ../frontend/score-card.php?session_id=" . $session_id . "&error=Invalid+session+or+applicant+data");
        exit();
    }

    $criteria = [$technical_skills, $communication, $confidence, $work_ethic, $team_fit];

    foreach ($criteria as $val) {
        if ($val < 1 || $val > 5) {
            header("Location: ../frontend/score-card.php?session_id=" . $session_id . "&error=Each+score+must+be+between+1+and+5");
            exit();
        }
    }

    $total = array_sum($criteria);

    $database = new Database();
    $conn = $database->getConnection();

    $stmt = $conn->prepare("
        INSERT INTO scores (session_id, applicant_name, technical_skills, communication, confidence, work_ethic, team_fit, total)
        VALUES (:session_id, :applicant_name, :technical_skills, :communication, :confidence, :work_ethic, :team_fit, :total)
    ");

    $stmt->execute([
        ':session_id'       => $session_id,
        ':applicant_name'   => $applicant_name,
        ':technical_skills' => $technical_skills,
        ':communication'    => $communication,
        ':confidence'       => $confidence,
        ':work_ethic'       => $work_ethic,
        ':team_fit'         => $team_fit,
        ':total'            => $total,
    ]);

    header("Location: ../frontend/score-card.php?session_id=" . $session_id . "&success=Score+submitted+successfully");
    exit();

} else {
    header("Location: ../frontend/score-card.php");
    exit();
}
