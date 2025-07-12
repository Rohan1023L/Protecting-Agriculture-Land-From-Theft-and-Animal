<?php
session_start();

header("Cache-Control: no-cache, no-store, must-revalidate"); 
header("Pragma: no-cache"); 
header("Expires: 0"); 


if (!isset($_SESSION['user_id'])) {
    header("Location: Login_Account.html");
    exit();
}

$host = 'localhost';
$user = 'root';
$password = 'Rohan1023L';
$database = 'protecting_agriculture_land_form_thef_animal';

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Database error: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$sql = "SELECT * FROM user_profile WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();
$conn->close();

$imageRelativePath = str_replace('..', '', $user['image_path']); 
$displayImagePath = '/' . ltrim($imageRelativePath, '/'); 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Protecting Agriculture Land From Animals/Theft | User Details</title>
    <link rel="stylesheet" href="../styles/Dashboard.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</head>

<body>
    <header>
        <div><b>Protecting Agriculture Land From Animals/Theft</b></div>

        <nav>

        </nav>
    </header>
    <main>
        <form>
            <div class=" user-details">
                <div class="create-account-heading">
                    <div class="box">
                        <div class="create-account-box"></div>
                    </div>

                    <div class="heading-title-subtitle">
                        <div id="heading-title"><b>ACCOUNT DETAILS </b></div>
                        <div id="heading-subtitle">PROFILE INFORMATION</div>
                    </div>
                </div>

                <div class="user-profile-management">
                    <br>
                    <div>
<div class="user-profile" style="background: url('<?php echo htmlspecialchars($displayImagePath); ?>'); background-size: 100% 100%;"></div>

                        <br>
                    </div>

                    <div class="user-details-n-e-p">
                        <div id="user-name"><b>Name </b> <br><?php echo htmlspecialchars($user['name']); ?></div>
                        <div id="user-email"><b>Email ID</b><br><?php echo htmlspecialchars($user['email']); ?></div>
                        <div id="user-phone"><b>Mobile Number </b><br><?php echo htmlspecialchars($user['mobile']); ?></div>
                        <a href="../php/logout.php"><button id="user-profile-button" type="button">Logout</button></a>
                    </div>

                </div>
            </div>

            <div class="index-selection-for-choose">
                <div class="create-account-heading">
                    <div class="box">
                        <div class="create-account-box"></div>
                    </div>

                    <div class="heading-title-subtitle">
                        <div id="heading-title"><b>INDEX</b></div>
                        <div id="heading-subtitle">SELECT YOUR NEEDS</div>
                    </div>
                </div>

                <div>
                    <div class="control-hardware-navigation">
                        <button>live farm view</button>
                        <button>vice massage</button>
                        <button>camera images [ by ai ]</button>
                    </div>
                </div>
            </div>


        </form>
        <aside>

            <div class="live-farm-view">
                <div class="create-account-heading">
                    <div class="box">
                        <div class="create-account-box"></div>
                    </div>

                    <div class="heading-title-subtitle">
                        <div id="heading-title"><b>LIVE FARM VIEW</b></div>
                        <div id="heading-subtitle">HERE YOU EXPLORE THE FARM.</div>
                    </div>
                </div>

                <div class="live-stream-video">
                    <div class="live-video-footage"></div>
                    <div class="live-video-text-analysis"></div>
                </div>
            </div>

            <div class="voice-massage">
                <div class="create-account-heading">
                    <div class="box">
                        <div class="create-account-box"></div>
                    </div>

                    <div class="heading-title-subtitle">
                        <div id="heading-title"><b>VICE MASSAGE</b></div>
                        <div id="heading-subtitle">SEND ALERT TO THE ANIMAL/THEFT.</div>
                    </div>
                </div>

                <div class="text-propmt">

                    <textarea id="output-text" placeholder="Type something or use mic" rows="4"></textarea>
                    <br>

                    <div class="button-adjust-ment">
                        <div class="i"> <i id="mic-icon" class="fa-solid fa-microphone-slash"></i></div><button id="text-propmt-button">Send</button>
                    </div>


                </div>
                <div class="camera-captured-images">
                    <div class="create-account-heading">
                        <div class="box">
                            <div class="create-account-box"></div>
                        </div>

                        <div class="heading-title-subtitle">
                            <div id="heading-title"><b>CAMERA IMAGES [ BY AI ]</b></div>
                            <div id="heading-subtitle">ACTIVITY THAT’S CAPTURED IN CAMERA</div>
                        </div>
                    </div>
                </div>
        </aside>
    </main>
    <footer>
        © 2025 TEAM DSY, Protecting Agriculture Land From Animals/Theft.
        <br>All Rights Reserved.
    </footer>
    <script src="../utils/Text_Translation.js"></script>
</body>

</html>
