<?php
require_once 'db.php'; // Your database connection

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Get the raw POST data and decode it as JSON
    $data = json_decode(file_get_contents("php://input"), true);

    // Check if the data is valid
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["status" => "error", "message" => "Invalid JSON input."]);
        exit();
    }

    // Extract values from the JSON data
    $username = trim($data['username']);
    $email = trim($data['email']);
    $password = $data['password'];

    // Validate fields
    if (empty($username) || empty($email) || empty($password)) {
        echo json_encode(["status" => "error", "message" => "All fields are required."]);
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
        exit();
    }

    // Validate password strength
    if (!preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/', $password)) {
        echo json_encode(["status" => "error", "message" => "Password must be at least 8 characters long, contain a number, and a special character."]);
        exit();
    }

    // Check if email already exists using PDO
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(["status" => "error", "message" => "Email is already registered."]);
        exit();
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into the database using PDO
    $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':password_hash', $hashedPassword, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Account created successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Something went wrong. Try again."]);
    }

    $stmt = null;
    $conn = null;
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
