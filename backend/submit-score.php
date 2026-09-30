<?php
// Score submission endpoint. Enforces: only interviewers assigned to the
// session (via session_interviewers) may score, and each one scores once.
// The score_submissions table records who scored; its composite primary key
// rejects duplicates, which also covers double-clicks/double POSTs.

session_start();

require_once "config/app.php";
require_once "config/database.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $applicant_name = trim($_POST['applicant_name'] ?? '');

    $technical_skills = isset($_POST['technical_skills']) ? intval($_POST['technical_skills']) : 0;
    $communication    = isset($_POST['communication']) ? intval($_POST['communication']) : 0;
    $confidence       = isset($_POST['confidence']) ? intval($_POST['confidence']) : 0;
    $work_ethic       = isset($_POST['work_ethic']) ? intval($_POST['work_ethic']) : 0;
    $team_fit         = isset($_POST['team_fit']) ? intval($_POST['team_fit']) : 0;

    $scorer_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
    $scorer_role = $_SESSION['user_role'] ?? '';

    $scoreCardUrl = APP_BASE . "/score-card?session_id=" . $session_id;

    $database = new Database();
    $conn = $database-> getConnection();

    //1. Only staff may score. Applicants share the same numeric id space as
    //employees, so an applicant id must never be looked up in session_interviewers.
    if ($scorer_role === 'applicant' || $scorer_id <= 0) {
        header("Location: " . $scoreCardUrl . "&error=Only+assigned+interviewers+can+score");
        exit();
    }

    //2. The scorer must be assigned to this session.
    $stmt = $conn->prepare("SELECT 1 FROM session_interviewers WHERE session_id = :session_id AND interviewer_id = :interviewer_id");
    $stmt->execute([':session_id' => $session_id, ':interviewer_id' => $scorer_id]);
    if ($stmt->fetchColumn() === false) {
        header("Location: " . $scoreCardUrl . "&error=You+are+not+assigned+to+this+session");
        exit();
    }

    //Sum of the values input by the interviewer
    $total = $technical_skills + $communication + $confidence + $work_ethic + $team_fit;

    try {
        $conn->beginTransaction();

        //3. Record the submission first. A second submission violates the
        //composite primary key and is rejected below.
        $stmt = $conn->prepare("INSERT INTO score_submissions (session_id, scorer_id) VALUES (:session_id, :scorer_id)");
        $stmt->execute([':session_id' => $session_id, ':scorer_id' => $scorer_id]);

        //4. Atomic UPDATE: adds the new scores to the existing values in a single step.
        //This avoids the race condition where concurrent submissions overwrite each other's data.
        $stmt = $conn->prepare("UPDATE scores
                                SET technical_skills = technical_skills + :technical_skills,
                                    communication = communication + :communication,
                                    confidence = confidence + :confidence,
                                    work_ethic = work_ethic + :work_ethic,
                                    team_fit = team_fit + :team_fit,
                                    total = total + :total,
                                    submitted_at = CURRENT_TIMESTAMP
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

        $conn->commit();
    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        //23000 = integrity constraint violation: this interviewer already scored this session
        if ($e->getCode() === '23000') {
            header("Location: " . $scoreCardUrl . "&error=You+have+already+scored+this+session");
            exit();
        }
        error_log("Error submitting score: " . $e->getMessage());
        header("Location: " . $scoreCardUrl . "&error=Failed+to+submit+score.+Try+again");
        exit();
    }


    //Once submit is done redirect back to the score-card.php and notify the scorer its been submitted and prevent a resubmission
    header("Location: " . $scoreCardUrl . "&success=Score+submitted+successfully");

    exit();
} else {
    header("Location: " . APP_BASE . "/score-card");
    exit();
}
