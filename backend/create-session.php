<?php

session_start();

require_once "config/app.php";
require_once "config/database.php";

//The session will be created and saved in the database

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    //1. Get the values entered from frontend create-session.php file
    $applicant_id = intval($_POST["applicant_id"]);//we'll get the applicant_id and use it to get the name and details of the applicant.
    $zoom_link = trim($_POST["zoom_link"]);

    //Process the date amd time
    $date_and_time = isset($_POST['date_and_time']) ? trim($_POST['date_and_time']) : ''; //if the value is a POST store it in the date_and_time variable else make it empty

    //if date_and_time is empty throw an error
    if (!empty($date_and_time)){
        // Parse using the HTML5 datetime-local format (Y-m-d\TH:i)
    $dt = DateTime::createFromFormat('Y-m-d\TH:i', $date_and_time);
    
    //Check if parsing succeeded
    if ($dt && $dt->format('Y-m-d\TH:i') === $date_and_time) {
        // Safe for MySQL DATETIME column (YYYY-MM-DD HH:MM:SS)
        $clean_date_and_time = $dt->format('Y-m-d H:i:s'); 
        
    }
    }
    //we have our three variables $applicant_id, $zoom_link and $clean_date_and_time

    //2. Save in the database
    $database = new Database();
    $conn = $database -> getConnection();

    //Get the name and other relevant details of the applicants

    $stmt = $conn->prepare("SELECT name, resume_link FROM  applicants WHERE id =:id");
    $stmt -> execute([':id' => $applicant_id]);//execute the statement
    $applicant = $stmt -> fetch(PDO::FETCH_ASSOC); //Get the row and values

    //We now have the applicant_name and resume_link

    //Lets create the session in teh database
    $stmt = $conn-> prepare("INSERT INTO sessions (applicant_id, zoom_link, scheduled_date) VALUES (:applicant_id, :zoom_link, :scheduled_date)");

    //bind the statements
    $stmt->bindParam(":applicant_id", $applicant_id);
    $stmt->bindParam(":zoom_link", $zoom_link);
    $stmt->bindParam(":scheduled_date", $clean_date_and_time);

    //confirm the session was created
    if($stmt -> execute()){

        $session_id = $conn-> lastInsertId(); //get the session_id
        //get the applicant_name from the last query
        $stmt = $conn->prepare("SELECT applicant_id, applicants.name AS applicant_name FROM sessions 
        JOIN applicants ON sessions.applicant_id = applicants.id
        WHERE sessions.id = :session_id");

        $stmt->bindParam(':session_id', $session_id);//bind the placeholder with the session_id
        $stmt-> execute(); 

        $applicant_details = $stmt->fetch(PDO::FETCH_ASSOC);//we get an assoc array of with the applicant details, contains applicant_id and applicant_name

        createRowForScoreSheet($session_id, $applicant_details["applicant_name"], $applicant_details["applicant_id"]); //Row initilization

      
        header("Location: " . APP_BASE . "/admin/create-session?success=1"); //TODO: Create a toast or a simple confirmation showing it worked, on the frontend



    } else {
        header("Location: " . APP_BASE . "/admin/create-session?error=Failed+to+create+session.+Try+again");
        exit();
    }    


} else {

    $_SESSION['error_msg'] = "Not a POST request.";
    header("Location: " . APP_BASE . "/admin/create-session");
}

//Function to initialise a row for the scores table
function createRowForScoreSheet($session_id, $applicant_name, $applicant_id){
    
    $database = new Database();
    $conn = $database -> getConnection();


    //All values are zeros for now
    $technical_skills = 0;
    $communication = 0;
    $confidence = 0;
    $work_ethic = 0;
    $team_fit = 0;
    $total = 0;

    $stmt = $conn->prepare("INSERT INTO scores (session_id, applicant_id, applicant_name,technical_skills, communication, confidence, work_ethic, team_fit, total)
                        VALUES (:session_id,:applicant_id, :applicant_name, :technical_skills, :communication, :confidence, :work_ethic, :team_fit, :total)");

    //bind values to placeholder
    $stmt ->bindParam(":session_id",$session_id);
    $stmt ->bindParam(":applicant_id",$applicant_id);
    $stmt ->bindParam(":applicant_name",$applicant_name);
    $stmt ->bindParam(":technical_skills", $technical_skills);
    $stmt ->bindParam(":communication", $communication);
    $stmt ->bindParam(":confidence", $confidence);
    $stmt ->bindParam(":work_ethic", $work_ethic);
    $stmt ->bindParam(":team_fit", $team_fit);
    $stmt ->bindParam(":total", $total);

    $stmt->execute();

    //Now we have the rows required
}



?>