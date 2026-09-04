<?php
require_once __DIR__ . "/../backend/config/database.php";


$database = new Database();
$conn = $database->getConnection();

//Fetch the scores and applicant position
$stmt = $conn->prepare("SELECT applicant_name, applicants.position AS position_applied, technical_skills, communication, confidence, work_ethic, team_fit, total, submitted_at
                        FROM scores
                        JOIN applicants ON scores.applicant_id = applicants.id
                        JOIN sessions ON scores.session_id = sessions.id ");

$stmt->execute();

$applicant_scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
//$applicant_scores, contains the details of applicant and scores

//Export logic 
//Exports the scores to a csv file
if (isset($_GET["export"]) && $_GET["export"] === "csv"){
    header('Content-type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="interview-reports.csv"');

    $csv= "\xEF\xBB\xBF";

    $escapeField = function ($value) {
        if ($value === null) { $value = '';}

        if (strpbrk($value, ",\"\n\r") !== false) {
            return '"' . str_replace('"', '""', $value) . '"';
        }

        return $value;
    };

    $csv .= implode(',', array_map($escapeField, [
        'Applicant Name', 'Position Applied', 'Technical Skills', 'Communication', 'Confidence', 'Work Ethic', 'Team Fit', 'Total', 'Submitted At'
    ])) . "\r\n";

    foreach ($applicant_scores as $row){
        $csv .= implode(',', array_map($escapeField, [
            $row['applicant_name'],
            $row['position_applied'],
            $row['technical_skills'],
            $row['communication'],
            $row['confidence'],
            $row['work_ethic'],
            $row['team_fit'],
            $row['total'],
            $row['submitted_at']
        ])) . "\r\n";
    }

    echo $csv;


    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= APP_BASE ?>/frontend/style.css">
    <link rel="icon" href="<?= APP_BASE ?>/frontend/assets/favicon.ico">
    <title>Interview Reports</title>
</head>
<body>

    <header class="topbar">
        <img src="<?= APP_BASE ?>/frontend/assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <main class="page-content">
        <a href="<?= APP_BASE ?>/<?= $_SESSION["user_role"]?>" class="back-link">&larr; Back to Dashboard</a>

        <div class="page-header">
            <h1>Interview Reports</h1>
            <a href="?export=csv" class="export-btn">Export CSV</a>
        </div>

        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Applicant Name</th>
                        <th>Position Applied</th>
                        <th>Technical Skills</th>
                        <th>Communication</th>
                        <th>Confidence</th>
                        <th>Work Ethic</th>
                        <th>Team Fit</th>
                        <th>Total</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($applicant_scores as $applicant_score): ?>
                    <tr>
                        <td><?= htmlspecialchars($applicant_score["applicant_name"])?></td>
                        <td><?= htmlspecialchars($applicant_score["position_applied"])?></td>
                        <td><?= htmlspecialchars($applicant_score["technical_skills"])?></td>
                        <td><?= htmlspecialchars($applicant_score["communication"])?></td>
                        <td><?= htmlspecialchars($applicant_score["confidence"])?></td>
                        <td><?= htmlspecialchars($applicant_score["work_ethic"])?></td>
                        <td><?= htmlspecialchars($applicant_score["team_fit"])?></td>
                        <td><?= htmlspecialchars($applicant_score["total"])?></td>
                        <td><?= htmlspecialchars($applicant_score["submitted_at"])?></td>
                    </tr>
                    
                    <?php endforeach?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>