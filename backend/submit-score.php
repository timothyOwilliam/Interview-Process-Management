<?php
// TODO : Review all of the code below and add comments

session_start();

require_once "config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $total = 20;

    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $applicant_name = trim($_POST['applicant_name'] ?? '');

    $technical_skills = isset($_POST['technical_skills']) ? intval($_POST['technical_skills']) : 0;
    $communication    = isset($_POST['communication']) ? intval($_POST['communication']) : 0;
    $confidence       = isset($_POST['confidence']) ? intval($_POST['confidence']) : 0;
    $work_ethic       = isset($_POST['work_ethic']) ? intval($_POST['work_ethic']) : 0;
    $team_fit         = isset($_POST['team_fit']) ? intval($_POST['team_fit']) : 0;


    //Insert the totals once and add to the ones already there
    $database = new Database();
    $conn = $database-> getConnection();

    //fetch the values already in database
    $stmt= $conn->prepare("SELECT technical_skills, communication, confidence, work_ethic, team_fit, total FROM scores
                            WHERE session_id = :session_id");

    $stmt ->execute([':session_id' => $session_id]);//execute the session_id

    $fetchedScores = $stmt -> fetch(PDO::FETCH_ASSOC);//fetch the row already in the scores table.

    //add the values to POST values to the ones in the database
    $technical_skills += $fetchedScores["technical_skills"];
    $communication += $fetchedScores["communication"];
    $confidence += $fetchedScores["confidence"];
    $work_ethic += $fetchedScores["work_ethic"];
    $team_fit += $fetchedScores["team_fit"];
    $total += $fetchedScores["total"];

    $stmt = $conn->prepare("UPDATE scores
                            SET technical_skills = :technical_skills,
                                communication = :communication,
                                confidence = :confidence,
                                work_ethic = :work_ethic,
                                team_fit = :team_fit,
                                total = :total
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



} else {
    header("Location: ../frontend/score-card.php");
    exit();
}
