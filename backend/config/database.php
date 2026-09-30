<?php
// app.php already loads env.php + the .env file unconditionally,
// so $_ENV is populated before we get here — no separate loadEnv() needed.
require_once __DIR__ . '/app.php';

//create a class for database, we wil use this class to start a database object for CRUD operations
class Database {

// below are the database credentials loaded from .env
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $conn;

    //get the credentials from the .env file
    //if server is local it will default to 'localhost' credentials
    public function __construct() {
        $this->host     = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name  = $_ENV['DB_NAME'] ?? 'interview_management_db';
        $this->username  = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
    }

//class methods , its public thus can be accessed by other objects
    public function getConnection() {
        $this->conn = null;

        //attempt connecting to the database and catch the errors
        try {
            $this->conn = new PDO(
                //Concats the variable together to be used for credentials
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );

            //errror reporting is turned on for the connection
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
            echo "Connection Error: " . $e->getMessage();
        }
        return $this->conn;
    }
}
?>
