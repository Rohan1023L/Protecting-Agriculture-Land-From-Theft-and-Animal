<?php
session_start();
header('Content-Type: application/json');

// Database config
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'protecting_agriculture_land_form_thef_animal';

// Connect to database
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '? Database connection failed.']);
    exit();
}

// Get POST data
$email = isset($_POST['Email']) ? trim($_POST['Email']) : '';
$password_input = isset($_POST['Password']) ? $_POST['Password'] : '';

// Check if fields are empty
if (empty($email) || empty($password_input)) {
    echo json_encode(['success' => false, 'message' => '?? Please fill all fields.']);
    exit();
}

// Prepare and execute query
$sql = "SELECT * FROM user_profile WHERE email=?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => '? Server error.']);
    exit();
}

$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

// Check result
if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password_input, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];

        echo json_encode(['success' => true, 'message' => 'Login successful! Welcome ...S']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Email not found.']);
}

$stmt->close();
$conn->close();
