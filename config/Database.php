<?php
namespace Muhammadwasim\StudentCrudTwig;

use PDO;
use PDOException;

class Database
{
    // Database connection settings
    private $host = '127.0.0.1';      // Localhost IP address
    private $db_name = 'student_management';  // Your database name
    private $username = 'root';          // Database username
    private $password = '';              // Database password (if none, leave as empty string)
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, 
                $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
