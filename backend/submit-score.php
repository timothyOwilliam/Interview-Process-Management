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

    $database = new Database();
    $conn = $database-> getConnection();

    //Sum of the values input by the interviewer
    $total = $technical_skills + $communication + $confidence + $work_ethic + $team_fit;

    //Atomic UPDATE: adds the new scores to the existing values in a single step.
    //This avoids the race condition where concurrent submissions overwrite each other's data.
    $stmt = $conn->prepare("UPDATE scores
                            SET technical_skills = technical_skills + :technical_skills,
                                communication = communication + :communication,
                                confidence = confidence + :confidence,
                                work_ethic = work_ethic + :work_ethic,
                                team_fit = team_fit + :team_fit,
                                total = total + :total
                            WHERE session_id = :session_id");

    //bind values to placeholder
    $stmt ->bindParam(":session_id",$session_id);
    $stmt ->bindParam(":technical_skills", $technical_skills);
    $stmt ->bindParam(":communication", $communication);
    $stmt ->bindParam(":confidence", $confidence);
    $stmt ->bindParam(":work_ethic", $work_ethic);
    $stmt ->bindParam(":team_fit", $team_fit);
    $stmt ->bindParam(":total", $total);

    $stmt->execute();

    //Once submit is done redirect back to the score-card.php and notify the scorer its been submitted and prevent a resubmission

    header("Location: ../frontend/score-card.php?session_id=" . $session_id . "&success=Score+submitted+successfully");
    exit();
} else {
    header("Location: ../frontend/score-card.php");
    exit();
}
