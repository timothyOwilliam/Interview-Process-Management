<?php
session_start();
require_once "config/app.php";
require_once "config/database.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($confirm_password)) {
        header("Location: " . APP_BASE . "/register?error=Please+fill+in+all+fields");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: " . APP_BASE . "/register?error=Passwords+do+not+match");
        exit();
    }

    $database = new Database();
    $conn = $database->getConnection();

//This will try to check if the email exists and bring out an error if it exists

//placeholders prevent SQL Injection
    $stmt = $conn->prepare("SELECT * FROM applicants WHERE email = :email"); //:email is only a placeholder
    $stmt->bindParam(':email', $email); // bind the $email(actual email) to the placeholder
    $stmt->execute();


//Confirms how many rows were affected by the previous query
// if true it means the email exists 
    if ($stmt->rowCount() > 0) {
        header("Location: " . APP_BASE . "/register?error=Email+already+registered");
        exit(); //stop the whole process/script
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

//Prepare SQL statement and bind the values to placeholders

    $stmt = $conn->prepare("INSERT INTO applicants (name, email, phone, password) VALUES (:name, :email, :phone, :password)");
    $stmt->bindParam(':name', $full_name);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':password', $hashed_password);
        

//the $stmt->execute will execute the query and give success mesage if not an error is given and an exit is done.
    if ($stmt->execute()) {
        header("Location: " . APP_BASE . "/login?success=Account+created+successfully");
        exit();
    } else {
        header("Location: " . APP_BASE . "/register?error=Registration+failed.+Please+try+again.");
        exit();
    }

} else {
    header("Location: " . APP_BASE . "/register");// if the REQUEST_METHOD is not a post stop the whole script.
    exit();
}
?>
