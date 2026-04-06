<?php
session_start();

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);

$conn = new mysqli("localhost", "app_user", "StrongPass@123", "practice_db");

if ($conn->connect_error) {
    die("Connection failed!");
}

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['attempts'])) {
    $_SESSION['attempts'] = 0;
}

function clean_input($data) {
    return htmlspecialchars(trim($data));
}

$message = "";
$showLoginPopup = false;

if (isset($_POST['register'])) {

    if (!hash_equals($_SESSION['token'], $_POST['token'])) {
        die("Invalid request!");
    }

    $username = clean_input($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $message = "All fields are required!";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters!";
    } else {

        // Check duplicate username
        $stmt = $conn->prepare("SELECT id FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $message = "Username already exists!";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                $message = "Registration Successful!";
                $showLoginPopup = true;
            } else {
                $message = "Something went wrong!";
            }
        }
        $stmt->close();
    }
}

if (isset($_POST['login'])) {

    if (!hash_equals($_SESSION['token'], $_POST['token'])) {
        die("Invalid request!");
    }

    // Brute force protection
    if ($_SESSION['attempts'] >= 5) {
        $message = "Too many attempts. Try later.";
        $showLoginPopup = true;
    } else {

        $username = clean_input($_POST["username"]);
        $password = $_POST["password"];

        $stmt = $conn->prepare("SELECT password FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 1) {

            $stmt->bind_result($stored_hash);
            $stmt->fetch();

            if (password_verify($password, $stored_hash)) {

                session_regenerate_id(true);
                $_SESSION['username'] = $username;
                $_SESSION['attempts'] = 0;

                $message = "Login Successful!";
            } else {
                $_SESSION['attempts']++;
                $message = "Invalid credentials!";
                $showLoginPopup = true;
            }

        } else {
            $_SESSION['attempts']++;
            $message = "Invalid credentials!";
            $showLoginPopup = true;
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Secure PHP Login</title>

<style>
body { font-family: Arial; }

/* modal */
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
<input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">

Username:<br>
<input type="text" name="username" maxlength="50" required><br><br>

Password:<br>
<input type="password" name="password" minlength="6" required><br><br>

<button type="submit" name="register">Register</button>
</form>

<p><?php echo htmlspecialchars($message); ?></p>

<div id="loginModal" class="modal">
  <div class="modal-content">
    <h2>Login</h2>

    <form method="post">
        <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">

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
<script>openModal();</script>
<?php endif; ?>

</body>
</html>
