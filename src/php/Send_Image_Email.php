<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../lib/Email/vendor/autoload.php'; 

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'protecting_agriculture_land_form_thef_animal';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("DB Error: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['user_id'])) {
    http_response_code(400);
    echo "Invalid request.";
    exit();
}

$userId = (int)$_POST['user_id'];
$table_name = "captured_images_user_" . $userId;

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

$sql = "SELECT image_path, object_name, captured_at FROM `$table_name` ORDER BY captured_at DESC LIMIT 1";
$result = $conn->query($sql);
if ($result->num_rows === 0) {
    echo "No images found.";
    exit();
}

$row = $result->fetch_assoc();
$imagePath = $row['image_path'];
$capturedAt = $row['captured_at'];
$objectName = $row['object_name'] ?? ''; 
$objectName = implode(', ', array_map('ucfirst', explode(',', $objectName)));

$capturedAtFormatted = date("d/m/Y h:i:s A", strtotime($capturedAt));

$imagePath = str_replace("\\", "/", $imagePath);

$absolutePath = realpath(__DIR__ . '/../upload/' . $imagePath);

if (!$absolutePath || !file_exists($absolutePath)) {
    echo "File not found: " . (__DIR__ . '/../upload/' . $imagePath);
    exit();
}

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'xxxxxxxxx@gmail.com'; // gmail
    $mail->Password = 'xxxxxxxxxxxxxxxxxxx'; // 2FA
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    $mail->setFrom('protectingagriculture@gmail.com', 'Protecting Agriculture Land From Animals/Theft');
    $mail->addAddress($user['email'], $user['name']);

    $mail->isHTML(true);
    $mail->Subject = 'ALERT NOTIFICATION FROM PALFAT';
    $mail->Body = "

    <div style='font-family: \"Josefin Sans\", sans-serif;font-size:14px;'>
        <b>Hello <u>{$user['name']}</u>,</b>
        <p> A <strong style='color:red;'>{$objectName}</strong> entered in your farm at : <br>
         <strong>$capturedAtFormatted</strong>.</p>
        <p>This is the picture of {$objectName} :</p>
        <img src='cid:capturedImage' width='300'/>
        <p>If the captured image includes a person and you are not able to recognize them, you can use our voice message system to protect your farm. </p>
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
