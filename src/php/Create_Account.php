<?php

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'protecting_agriculture_land_form_thef_animal';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = trim($_POST['name'] ?? '');
$mobile = trim($_POST['mobile'] ?? '');
$email = trim($_POST['email'] ?? '');
$stream_link = trim($_POST['stream_link'] ?? '');
$password_plain = $_POST['password'] ?? '';

if (!$name || !$mobile || !$email || !$password_plain) {
    die("Please fill all required fields.");
}

$hashed_password = password_hash($password_plain, PASSWORD_DEFAULT);

$image_path = null;
if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
    $allowedTypes = ['image/jpeg', 'image/png'];
    $fileType = mime_content_type($_FILES['profileImage']['tmp_name']);

    if (in_array($fileType, $allowedTypes)) {
        $targetDir = realpath(__DIR__ . '/../upload/profiles');

        if ($targetDir === false || !is_dir($targetDir)) {
            die("Upload folder ../upload/profiles does not exist.");
        }

        $imageName = time() . "_" . basename($_FILES['profileImage']['name']);
        $targetFilePath = $targetDir . DIRECTORY_SEPARATOR . $imageName;

        $image_path = '../upload/profiles/' . $imageName;

        if (!move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetFilePath)) {
            die("Failed to upload image.");
        }
    } else {
        die("Only JPG and PNG images are allowed.");
    }
}

$sql = "INSERT INTO user_profile (name, mobile, email, stream_link, password, image_path) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $name, $mobile, $email, $stream_link, $hashed_password, $image_path);

if ($stmt->execute()) {
    session_start();
    $_SESSION['user_id'] = $stmt->insert_id;

    header("Location: ../pages/Dashboard.php");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
