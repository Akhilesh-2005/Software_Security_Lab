<?php
// SHOW ERRORS (VERY IMPORTANT FOR DEBUG)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// DB CONNECTION
$conn = mysqli_connect("localhost", "root", "", "practice_db");

if (!$conn) {
    die("Database Connection Failed");
}

$message = "";
$showLoginPopup = false;

// LOG FUNCTION
function log_event($username, $status) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $time = date("Y-m-d H:i:s");

    $log = "$time | IP: $ip | Username: $username | Status: $status\n";
    file_put_contents("login_logs.txt", $log, FILE_APPEND);
}

// ================= REGISTER =================
if (isset($_POST['register'])) {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        $message = "Registration Successful!";
        $showLoginPopup = true;
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

// ================= LOGIN =================
if (isset($_POST['login'])) {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {

        $row = mysqli_fetch_assoc($result);

        $stored_hash = $row['password'];

        // SAFE FETCH (prevents crash)
        $failed_attempts = isset($row['failed_attempts']) ? $row['failed_attempts'] : 0;
        $lock_time = isset($row['lock_time']) ? $row['lock_time'] : NULL;

        // 🔒 CHECK LOCK
        if ($lock_time != NULL && strtotime($lock_time) > time()) {
            $message = "Account is locked. Try again later!";
            log_event($username, "LOCKED");
            $showLoginPopup = true;

        } else {

            // ✅ CORRECT PASSWORD
            if (password_verify($password, $stored_hash)) {

                mysqli_query($conn, "UPDATE users SET failed_attempts=0, lock_time=NULL WHERE username='$username'");

                $message = "Login Successful!";
                log_event($username, "SUCCESS");

            } else {

                $failed_attempts++;

                // 🔒 LOCK AFTER 5 ATTEMPTS
                if ($failed_attempts >= 5) {

                    $lock_until = date("Y-m-d H:i:s", strtotime("+5 minutes"));

                    mysqli_query($conn, "UPDATE users 
                        SET failed_attempts=$failed_attempts, lock_time='$lock_until' 
                        WHERE username='$username'");

                    $message = "Account locked for 5 minutes!";
                    log_event($username, "ACCOUNT LOCKED");

                } else {

                    mysqli_query($conn, "UPDATE users 
                        SET failed_attempts=$failed_attempts 
                        WHERE username='$username'");

                    $message = "Wrong Password! Attempt $failed_attempts/5";
                    log_event($username, "FAILED");
                }

                $showLoginPopup = true;
            }
        }

    } else {
        $message = "Username not found!";
        log_event($username, "USER NOT FOUND");
        $showLoginPopup = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>PHP Login System</title>

<style>
body { font-family: Arial; }

.modal {
    display: none;
    position: fixed;
    top: 0; left: 0;
    width: 100%; height: 100%;
    background: rgba(0,0,0,0.5);
}

.modal-content {
    background: white;
    padding: 20px;
    margin: 10% auto;
    width: 300px;
    border-radius: 10px;
    text-align: center;
}
</style>

</head>

<body>

<h2>Register</h2>

<form method="post">
Username:<br>
<input type="text" name="username" required><br><br>

Password:<br>
<input type="password" name="password" required><br><br>

<button type="submit" name="register">Register</button>
</form>

<!-- LOGIN LINK -->
<p>
Already have an account? 
<a href="#" onclick="openModal()">Login here</a>
</p>

<p><?php echo $message; ?></p>

<!-- LOGIN MODAL -->
<div id="loginModal" class="modal">
  <div class="modal-content">
    <h2>Login</h2>

    <form method="post">
        Username:<br>
        <input type="text" name="username" required><br><br>

        Password:<br>
        <input type="password" name="password" required><br><br>

        <button type="submit" name="login">Login</button>
    </form>

    <br>
    <button onclick="closeModal()">Close</button>
  </div>
</div>

<script>
function openModal() {
    document.getElementById("loginModal").style.display = "block";
}

function closeModal() {
    document.getElementById("loginModal").style.display = "none";
}
</script>

<?php if ($showLoginPopup): ?>
<script>
    openModal();
</script>
<?php endif; ?>

</body>
</html>