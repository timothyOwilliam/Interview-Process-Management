<?php
session_start();

//check of there are errors when the webpage is loaded to the screen.
if (isset($_SESSION["errors"]) && !empty($_SESSION["errors"])) {
    //if there are any errors display the the errors in the webpage
    echo '<div class="error-message">';
    foreach ($_SESSION["errors"] as $error) {
        echo "<p>" . $error . "</p>";
    }
    echo '</div>';
    unset($_SESSION["errors"]);
} else {
    //get all of the applicants form the database to be used in displaying the applicants
require_once "../../backend/config/database.php";

//start a database connection
$database = new Database();
$conn = $database -> getConnection();

//Prepare the SQL statements
$stmt = $conn -> prepare("SELECT * FROM applicants");
$stmt -> execute();

//if there are applicants fetch all the values
if ($stmt -> rowCount() > 0){
    $applicants = $stmt -> fetchAll(PDO::FETCH_ASSOC); //fetch all rows affected
} else {
    exit(); //TO DO : Write a code to display error messages.
}

//all rows are in the $applicants variable
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../style.css">
    <title>Create Session - Admin</title>
</head>
<body>

    <header class="topbar">
        <img src="../assets/NCIA-Logo-2022.png" alt="NCIA logo" width="150" height="53">
    </header>

    <main class="page-content">
        <a href="admin-dashboard.php" class="back-link">&larr; Back to Dashboard</a>

        <h1>Create Interview Session</h1>

        <div class="login-section">
            <form action="../../backend/create-session.php" method="POST" class="login-details">
                <label for="applicant_id">Applicant</label>
                <select name="applicant_id" id="applicant_id" required>
                    <option value="" disabled selected>Select an Applicant</option>

                    <!-- php code for getting all the applicants from the database -->
                    <?php foreach ($applicants as $a)://get the appplicants from the database ?>
                        <option value= " <?= $a['id'] ?> ">
                            <?= htmlspecialchars($a['name']) //display the name of the applicant?>
                        </option>

                        <?php endforeach;//ends the foreach loop, for displaying the names ?>
                </select>

                <label for="zoom_link">Zoom Link</label>
                <input type="url" name="zoom_link" id="zoom_link" placeholder="https://zoom.us/j/..." required>

                <label for="scheduled_date">Scheduled Date &amp; Time</label>
                <input type="datetime-local" name="scheduled_date" id="scheduled_date" required>

                <button type="submit" class="sign-in-button">Create Session</button>
            </form>
        </div>
    </main>

</body>
</html>
