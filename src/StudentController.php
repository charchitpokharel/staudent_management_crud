<?php

namespace Muhammadwasim\StudentCrudTwig;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use PDO;

class StudentController {
    private $twig;
    private $conn;
    private $table = 'students';
    protected $db;

    // Define student properties
    public $name;
    public $email;
    public $age;
    public $course;

    public function __construct($db)
    {
        // Initialize the database connection
        // Initialize the database connection
        $this->conn = $db;
        $this->db = $db;  // Set $db first

        // Set the DB connection in the Student model
        Student::setDb($this->db);  // Now the database is set in the model

        // Initialize Twig
            $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../templates'); // Update the path if necessary
            $this->twig = new \Twig\Environment($loader);
    }

    // Method to display all students
    public function index() {
        session_start();

        // Fetch all students
        $students = $this->getAllStudents();
    
        // Retrieve the session message if it exists
        $message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
    
        // Clear the session message after retrieving it
        unset($_SESSION['message']);
    
        // Render the Twig template with students and message
        echo $this->twig->render('index.html.twig', [
            'students' => $students,
            'message' => $message,
        ]);
    }

    // Method to display the create student form
    public function create() {
        echo $this->twig->render('create.html.twig');
    }

    // Method to store a new student
    public function store($data)
    {
        // Sanitize and assign data
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->age = $data['age'];
        $this->course = $data['course'];

        // SQL query to insert student into the database
        $query = "INSERT INTO " . $this->table . " (name, email, age, course) 
                  VALUES (:name, :email, :age, :course)";
    
        // Prepare the query
        $stmt = $this->conn->prepare($query);
    
        // Bind values
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':age', $this->age);
        $stmt->bindParam(':course', $this->course);
        session_start();
        // Execute the query and return success or failure
        if ($stmt->execute()) {
            $_SESSION['message'] = "Student Created successfully.";

                // Redirect back to the index route (e.g., '/')
                header("Location: /");
                exit;
        }
    
        $_SESSION['message'] = "Student not found.";
        header("Location: /");
        exit;
    }

    // Method to display the edit form with student data

    public function edit($id)
    {
        $student = Student::find($id);
        if ($student) {
            // Render the edit form with student data
            echo $this->twig->render('edit.html.twig', ['student' => $student]);
        } else {
            http_response_code(404); // If student not found
            echo "Student not found.";
        }
    }
    

    public function update($data)
    {
        if (isset($data['id'])) {
            // Use the Student model to find the student
            $student = Student::find($data['id']);
            
            if ($student) {
                // Now use the model's database connection to update the student data
                $stmt = Student::$db->prepare("UPDATE students SET name = :name, email = :email, age = :age, course = :course WHERE id = :id");
                $stmt->bindParam(':id', $data['id']);
                $stmt->bindParam(':name', $data['name']);
                $stmt->bindParam(':email', $data['email']);
                $stmt->bindParam(':age', $data['age']);
                $stmt->bindParam(':course', $data['course']);
                $stmt->execute();
                session_start();

                // Store the success message in the session
                $_SESSION['message'] = "Student updated successfully.";
              
                // Redirect back to the index route (e.g., '/')
                header("Location: /");
                exit;           
             } else {
                   // Store the error message in the session
                    $_SESSION['message'] = "Student not found.";
                    header("Location: /");
                    exit;
            }
        } else {
            $_SESSION['message'] = "Invalid data.";
            header("Location: /");
            exit;
        }
    }
    
    public function delete($id) {
        session_start(); // Start the session
    
        // Assuming $this->deleteStudentById($id) deletes the student from the database
        if ($this->deleteStudentById($id)) {
            $_SESSION['message'] = "Student deleted successfully.";
            http_response_code(200); // Success response
            echo "Student deleted successfully.";
        } else {
            $_SESSION['message'] = "Failed to delete the student.";
            http_response_code(400); // Error response
            echo "Failed to delete the student.";
        }
    
        // Redirect to the index page
        // header("Location: /");
        exit;
    }
    

    // Method to get all students
    private function getAllStudents() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Method to find a student by ID
    private function findStudentById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function search($query)
    {
        // Prepare the SQL query to search by name, email, age, or course
        $stmt = $this->conn->prepare("SELECT * FROM students WHERE name LIKE :query OR email LIKE :query OR age LIKE :query OR course LIKE :query");
        $searchTerm = "%$query%"; // Add wildcard for searching
        $stmt->bindParam(':query', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
    
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        // Loop through the students and create table rows
        $html = '';
        foreach ($students as $student) {
            $html .= '<tr>';
            $html .= '<td>' . htmlspecialchars($student['name']) . '</td>';
            $html .= '<td>' . htmlspecialchars($student['email']) . '</td>';
            $html .= '<td>' . htmlspecialchars($student['age']) . '</td>';
            $html .= '<td>' . htmlspecialchars($student['course']) . '</td>';
            $html .= '<td>';
            $html .= '<a href="/edit/' . $student['id'] . '" class="btn btn-warning btn-sm me-2" style="margin-right: 5px; padding: 5px 15px;">Edit</a>';
            $html .= '<a href="#" class="btn btn-danger btn-sm" style="padding: 5px 10px;" 
               onclick="if (confirm(\'Are you sure you want to delete this student?\')) { 
                   fetch(\'/delete/' . $student['id'] . '\', { 
                       method: \'POST\' 
                   })
                   .then(response => {
                       if (response.ok) {
                           return response.text(); // Expecting a session message to be set
                       } else {
                           throw new Error(\'Failed to delete\');
                       }
                   })
                   .then(() => {
                       location.reload(); // Reload after delete is successful
                   })
                   .catch(error => {
                       console.error(\'Error:\', error);
                   }); 
               } return false;">
               Delete
            </a>';
            $html .= '</td>';
            $html .= '</tr>';
        }
    
        // Return the filtered table rows directly for Ajax to process
        echo $html;
        exit; // Ensure nothing else is sent
    }
    private function deleteStudentById($id) {
        // Assuming $this->db is your database connection
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");
        return $stmt->execute([$id]);
    }
    

}
