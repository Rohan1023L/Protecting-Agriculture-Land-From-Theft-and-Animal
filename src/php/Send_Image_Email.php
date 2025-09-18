<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../lib/Email/vendor/autoload.php'; 

// DB credentials
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'protecting_agriculture_land_form_thef_animal';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

// Validate POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id'])) {
    http_response_code(400);
    echo "Invalid request.";
    exit();
}

$userId = (int)$_POST['user_id'];
$table_name = "captured_images_user_" . $userId;

// Fetch user info
$stmt = $conn->prepare("SELECT name, email FROM user_profile WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "User not found.";
    exit();
}

// Fetch last captured image
$sql = "SELECT image_path, captured_at FROM `$table_name` ORDER BY captured_at DESC LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    echo "No images found.";
    exit();
}

$row = $result->fetch_assoc();
$imagePath = $row['image_path'];
$capturedAt = $row['captured_at'];

// Format captured time to 12-hour format with AM/PM
$capturedAtFormatted = date("d/m/Y h:i:s A", strtotime($capturedAt));

// Normalize path slashes
$imagePath = str_replace("\\", "/", $imagePath);

// Get absolute path
$absolutePath = realpath(__DIR__ . '/../upload/' . $imagePath);

// Check if file exists
if (!$absolutePath || !file_exists($absolutePath)) {
    echo "File not found: " . (__DIR__ . '/../upload/' . $imagePath);
    exit();
}

// Send email
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    // 🔐 Use App Password for Gmail
    $mail->Username = 'protectingagriculture@gmail.com';
    $mail->Password = 'ixdqeytmfaatljej';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('protectingagriculture@gmail.com', 'Protecting Agriculture Land From Animals/Theft');
    $mail->addAddress($user['email'], $user['name']);

    $mail->isHTML(true);
    $mail->Subject = 'ALERT NOTIFICATION FROM PALFAT';
    $mail->Body = "

    <div style='font-family: 'Josefin Sans', sans-serif;font-size:14px;'>
        <b>Hello <u>{$user['name']}</u>,</b>
        <p>Someone entered in your farm at : <br>
         <strong>$capturedAtFormatted</strong>.</p>
        <p>This is the picture of human/animal :</p>
        <img src='cid:capturedImage' width='300'/>
        <p>If the captured image includes a human and you are not able to recognize them, you can use our voice message system to protect your farm. </p>
        <br>
        <p>Stay alert and safe!</p>

        <b>Security Alert from,</b>
        <br>
       <u> Team DSY </u>
     </div>
        
    ";

    $mail->addEmbeddedImage($absolutePath, 'capturedImage');

    $mail->send();
    echo "Email sent to {$user['email']}";
} catch (Exception $e) {
    echo "Mailer Error: " . $mail->ErrorInfo;
}
