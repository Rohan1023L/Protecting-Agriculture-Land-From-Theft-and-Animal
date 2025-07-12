<?php
// Database config
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'protecting_agriculture_land_form_thef_animal'; // Change to your DB name

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize input
$name = $_POST['name'];
$mobile = $_POST['mobile'];
$email = $_POST['email'];
$stream_link = $_POST['stream_link'];
$password_plain = $_POST['password'];
$hashed_password = password_hash($password_plain, PASSWORD_DEFAULT);

// Handle image upload
$image_path = null;
if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === 0) {
    $allowedTypes = ['image/jpeg', 'image/png'];
    $fileType = mime_content_type($_FILES['profileImage']['tmp_name']);

    if (in_array($fileType, $allowedTypes)) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $imageName = time() . "_" . basename($_FILES['profileImage']['name']);
        $targetFilePath = $targetDir . $imageName;

        if (move_uploaded_file($_FILES['profileImage']['tmp_name'], $targetFilePath)) {
            $image_path = $targetFilePath;
        } else {
            die("Failed to upload image.");
        }
    } else {
        die("Only JPG and PNG images are allowed.");
    }
}

// Insert into database
$sql = "INSERT INTO user_profile (name, mobile, email, stream_link, password, image_path) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $name, $mobile, $email, $stream_link, $hashed_password, $image_path);

if ($stmt->execute()) {
    // Redirect to success HTML
    header("Location: Dashboard.html");
    exit();
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
