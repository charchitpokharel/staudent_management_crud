<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/Database.php'; // Correct path

use Muhammadwasim\StudentCrudTwig\Database;
use Muhammadwasim\StudentCrudTwig\StudentController;

// Create a new Database instance and get the connection
$database = new Database(); 
$db = $database->getConnection();
// Pass the connection to the StudentController
$controller = new StudentController($db);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

switch ($path) {
    case '/':
        $controller->index();
        break;
    case '/create':
        $controller->create();
        break;
    case '/store':
        if ($method == 'POST') {
            $controller->store($_POST); // Store the data
        }
        break;
    case (preg_match('/^\/edit\/(\d+)$/', $path, $matches) ? true : false):
        // Check if the path is like '/edit/1'
        $controller->edit($matches[1]); // Pass the captured ID to the edit method
        break;
    case '/update':
        if ($method == 'POST' && isset($_POST['id'])) {
            $controller->update($_POST); // Update the student data
        }
        break;
        case (preg_match('/^\/delete\/(\d+)$/', $path, $matches) ? true : false):
            if ($method === 'POST') { // Only accept POST for deleting
                $controller->delete($matches[1]); // Call the delete method with the captured ID
            } else {
                http_response_code(405); // Method not allowed
                echo "Method not allowed.";
            }
            break;
    case '/search': // New case for handling search requests
        if ($method == 'GET' && isset($_GET['search'])) {
            echo $controller->search($_GET['search']); // Echoing the search results directly
        }
        break;
    default:
        http_response_code(404);
        echo "Page not found eeror";
}
