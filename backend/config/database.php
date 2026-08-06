<?php

//create a class for database, we wil use this class to start a database object for CRUD operations
class Database {

// below are the database credentials all in a private variables
    private $host = "localhost";
    private $db_name = "interview_management_db";
    private $username = "root";
    private $password = "";
    private $conn;


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
