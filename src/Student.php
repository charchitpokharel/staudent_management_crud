<?php
namespace Muhammadwasim\StudentCrudTwig;
use PDO;

class Student
{
    public $id;
    public $name;
    public $email;
    public $age;
    public $course;

    // Assume we have a PDO instance in the Database class
    public static $db;

    // Set the PDO database connection
    public static function setDb($db)
    {
        self::$db = $db;
    }

    public static function all()
    {
        // Fetch all students from the database
        $stmt = self::$db->query("SELECT * FROM students");
        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    // Fetch a student by ID
    public static function find($id)
    {
        // Ensure db connection is set
        if (self::$db === null) {
            throw new \Exception("Database connection is not set.");
        }

        $stmt = self::$db->prepare("SELECT * FROM students WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public function save()
    {
        // Insert or update the student
        if ($this->id) {
            // Update existing record
            $stmt = self::$db->prepare("UPDATE students SET name = :name, email = :email, age = :age, course = :course WHERE id = :id");
            $stmt->bindParam(':id', $this->id);
        } else {
            // Insert new record
            $stmt = self::$db->prepare("INSERT INTO students (name, email, age, course) VALUES (:name, :email, :age, :course)");
        }

        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':age', $this->age);
        $stmt->bindParam(':course', $this->course);
        $stmt->execute();
    }
}


