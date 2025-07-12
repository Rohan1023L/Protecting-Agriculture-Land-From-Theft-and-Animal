<?php
session_start();
header('Content-Type: application/json');

$host = 'localhost';
$user = 'root';
$password = 'Rohan1023L';
$database = 'protecting_agriculture_land_form_thef_animal';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => '❌ Database connection failed.']);
    exit();
}

$name = isset($_POST['Name']) ? trim($_POST['Name']) : '';
$email = isset($_POST['Email']) ? trim($_POST['Email']) : '';
$mobile = isset($_POST['MobileNumber']) ? trim($_POST['MobileNumber']) : '';
$password_input = isset($_POST['Password']) ? $_POST['Password'] : '';

if (empty($name) || empty($email) || empty($mobile) || empty($password_input)) {
    echo json_encode(['success' => false, 'message' => '⚠️ Please fill all fields.']);
    exit();
}

$sql = "SELECT * FROM user_profile WHERE name=? AND email=? AND mobile=?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => '❌ Server error.']);
    exit();
}

$stmt->bind_param('sss', $name, $email, $mobile);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password_input, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];

        echo json_encode(['success' => true, 'message' => '✅ Login successful ! wait ...']);
    } else {
        echo json_encode(['success' => false, 'message' => '❌ Incorrect password.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => '❌ User not found with provided credentials.']);
}

$stmt->close();
$conn->close();
