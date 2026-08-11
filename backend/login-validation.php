<?php
session_start();
require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {


//get the email and the password if empty stop the whole script
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        header("Location: ../frontend/login.php?error=Please+fill+in+all+fields");
        exit();
    }


//start a new database connection
    $database = new Database();
    $conn = $database->getConnection();

// Check employees table first if the user exists
    $stmt = $conn->prepare("SELECT * FROM employees WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();


//If the email exists (meaning the rowCount() for the previous query return some rows),we go on to do the redirects
//The $stmt stores the rows that were returned from the SELECT statement
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC); //fetch the values and gets them in an associative array and stores it in the $stmt variable

//Verify if the password entered (stored in $passsword ) matches the one stored in $user, the one returned when the whole row was fetched
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_department'] = $user['department'];
            $_SESSION['user_role'] = $user['role'];

//Redirect the user to the appropriate dashboard when depending on the role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../frontend/admin-pages/admin-dashboard.php");
                    break;
                case 'ict':
                    header("Location: ../frontend/dashboards/ict-dashboard.php");
                    break;
                case 'hr':
                    header("Location: ../frontend/hr-pages/hr-dashboard.php");
                    break;
                default:
                    header("Location: ../frontend/login.php?error=Unknown+role");
                    break;
            }
            exit();
        } else {
            header("Location: ../frontend/login.php?error=Invalid+email+or+password");//if password is wrong exit the script and redirect back to the login page
            exit();
        }
    }

// Check applicants table if not found in employees
    $stmt = $conn->prepare("SELECT * FROM applicants WHERE email = :email");
    $stmt->bindParam(':email', $email);
    $stmt->execute();

//Redirect to the applicant dashboard after the verifying the password 
    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);//get an associative array for the row


        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = 'applicant';

            header("Location: ../frontend/dashboards/applicant-dashboard.php");
            exit();
        } else {
            header("Location: ../frontend/login.php?error=Invalid+email+or+password");
            exit();
        }
    }

    // Not found in either table
    header("Location: ../frontend/login.php?error=Invalid+email+or+password");
    exit();

} else {
    header("Location: ../frontend/login.php");//The form upload was not a POST
    exit();
}
?>
