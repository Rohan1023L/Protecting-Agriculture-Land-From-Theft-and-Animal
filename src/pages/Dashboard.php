<?php
session_start();

// Prevent caching
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: Login_Account.html");
    exit();
}

// DB connection info
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'protecting_agriculture_land_form_thef_animal';

// Connect to DB
$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Database error: " . $conn->connect_error);
}

$userId = (int)$_SESSION['user_id'];
$table_name = "captured_images_user_" . $userId;

// Handle delete request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $deleteId = intval($_POST['delete_id']);

    // Get image path before deleting from DB
    $stmt = $conn->prepare("SELECT image_path FROM `$table_name` WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->bind_result($imagePath);
    $stmt->fetch();
    $stmt->close();

    // Delete image file from disk
    if ($imagePath) {
        // Resolve absolute path
        $fullPath = __DIR__ . '/' . $imagePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }

    // Delete DB record
    $stmt = $conn->prepare("DELETE FROM `$table_name` WHERE id = ?");
    $stmt->bind_param("i", $deleteId);
    $stmt->execute();
    $stmt->close();

    // Redirect to avoid resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch user profile info
$sql = "SELECT * FROM user_profile WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: Login_Account.html");
    exit();
}

// Fetch captured images for user
$sql_images = "SELECT id, image_path, captured_at FROM `$table_name` ORDER BY captured_at DESC";
$result_images = $conn->query($sql_images);

$base_url = "../upload/capture/";

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
        <nav></nav>
    </header>
    <main>
        <form>
            <div class="user-details">
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
                        <div class="user-profile" style="background: url('<?php echo htmlspecialchars($user['image_path']); ?>'); background-size: 100% 100%;"></div>
                        <br>
                    </div>

                    <div class="user-details-n-e-p">
                        <div id="user-name"><b>Name </b><br><?php echo htmlspecialchars($user['name']); ?></div>
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
                        <a href="#index-live">live farm view</a>
                        <a href="#index-voice">voice massage</a>
                        <a href="#index-img">camera images [ by ai ]</a>
                    </div>
                </div>
            </div>
        </form>

        <aside>
            <div class="live-farm-view" id="index-live">
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

                    <div class="live-video-footage">
                        <img id="liveStreamFrame" src="http://localhost:5001/live?user_id=<?php echo $_SESSION['user_id']; ?>" />
                        <!--Laptop Ip-->
                    </div>

                    <div class="detected-img">
                        <img id="detected-frame"
                            src="http://10.30.94.70:5001/latest?user_id=<?= $_SESSION['user_id'] ?>&rand=<?= time() ?>" />
                    </div>

                    <script>
                        setInterval(() => {
                            const img = document.getElementById('detected-frame');
                            const userId = <?= $_SESSION['user_id'] ?>;
                            img.src = `http://10.30.94.70:5001/latest?user_id=${userId}&rand=${new Date().getTime()}`;
                        }, 5000);
                    </script>


                </div>
            </div>

            <div class="voice-massage" id="index-voice">
                <div class="create-account-heading">
                    <div class="box">
                        <div class="create-account-box"></div>
                    </div>
                    <div class="heading-title-subtitle">
                        <div id="heading-title"><b>VOICE MASSAGE</b></div>
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

                <div class="camera-captured-images" id="index-img">
                    <div class="create-account-heading">
                        <div class="box">
                            <div class="create-account-box"></div>
                        </div>
                        <div class="heading-title-subtitle">
                            <div id="heading-title"><b>CAMERA IMAGES [ BY AI ]</b></div>
                            <div id="heading-subtitle">ACTIVITY THAT’S CAPTURED IN CAMERA</div>
                        </div>
                    </div>

                    <div class="ai-captured-images">
                        <div class="table-ai-captured-img">
                            <div class="sr-img">
                                SR.NO
                            </div>

                            <div class="ai-img">
                                CAPTURED IMAGES
                            </div>

                            <div class="modify-button">
                                MORE DETAILS
                            </div>
                        </div>
                        <?php
                        if (!$result_images || $result_images->num_rows === 0) {
                            echo '<div style="height:40px;display:flex;align-items:center;justify-content:center;">No captured images found.</div>';
                        } else {
                            $sr_no = 1;
                            $base_url = "../upload/capture/";

                            while ($row = $result_images->fetch_assoc()) {
                                $img_id = $row['id'];
                                $img_path = $row['image_path']; // e.g., detected_21072025_123456.jpg
                                $captured_at = $row['captured_at'];
                                $filename = basename($img_path);
                                $full_img_url = $base_url . $filename;
                        ?>
                                <div style="display: flex;">
                                    <div class="sr-img" style="display: flex; justify-content: center;margin-left: 20px;border: 1px solid black;width:9.60%;align-items: center;"><?= $sr_no ?></div>

                                    <div class="ai-img" style="display: flex; justify-content: center;border: 1px solid black;align-items: center;width:48%;">
                                        <img
                                            src="<?= htmlspecialchars($full_img_url) ?>"
                                            alt="Captured Image <?= $sr_no ?>"
                                            style="width:40%;height:98%;"
                                            title="Captured at <?= htmlspecialchars($captured_at) ?>" />
                                    </div>

                                    <div class="modify-button" style="display: flex;align-items:center;justify-content:center; border: 1px solid black;width:38.4%;">

                                        <!-- View button -->
                                        <center>
                                            <div class="button-arrangment-for-url">
                                                <a href="<?= htmlspecialchars($full_img_url) ?>" target="_blank">
                                                    <button type="button" id="button-for-url"> <i class="fa-solid fa-eye"></i>&nbsp;View Image</button>
                                                   
                                                </a>
                                            </div>

                                            <!-- Delete form -->

                                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this image?');">
                                                <input type="hidden" name="delete_id" value="<?= $img_id ?>">
                                                <div>
                                                    <button type="submit" id="button-arrangment-for-delete"><i class="fa-solid fa-trash"></i> Delete Image</button>
                                                </div>
                                            </form>
                                        </center>

                                    </div>

                                </div>
                        <?php
                                $sr_no++;
                            }
                        }
                        ?>

                    </div>
                </div>
            </div>
        </aside>
    </main>
    <footer>
        © 2025 TEAM DSY, Protecting Agriculture Land From Animals/Theft.<br>All Rights Reserved.
    </footer>
    <script src="../utils/Text_Translation.js"></script>
</body>

</html>

<?php
$conn->close();
?>