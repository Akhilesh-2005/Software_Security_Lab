<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "practice_db");

if (!$conn) {
    die("Connection failed");
}

$message = "";
$showLoginPopup = false;

function clean_input($data) {
    return htmlspecialchars(trim($data));
}

if (isset($_POST['register'])) {

    $username = clean_input($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($password)) {
        $message = "All fields are required!";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters!";
    } else {

        $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username=?");
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            $message = "Username already exists!";
        } else {

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            
            $stmt = mysqli_prepare($conn, "INSERT INTO users (username, password) VALUES (?, ?)");
            mysqli_stmt_bind_param($stmt, "ss", $username, $hashed_password);

            if (mysqli_stmt_execute($stmt)) {
                $message = "Registration Successful!";
                $showLoginPopup = true;
            } else {
                $message = "Something went wrong!";
            }
        }
    }
}

if (isset($_POST['login'])) {

    $username = clean_input($_POST["username"]);
    $password = trim($_POST["password"]);

    $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE username=?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);

    if (mysqli_stmt_num_rows($stmt) == 1) {

        mysqli_stmt_bind_result($stmt, $stored_hash);
        mysqli_stmt_fetch($stmt);

        if (password_verify($password, $stored_hash)) {
            $_SESSION['username'] = $username;
            $message = "Login Successful!";
        } else {
            $message = "Wrong Password!";
            $showLoginPopup = true;
        }

    } else {
        $message = "User not found!";
        $showLoginPopup = true;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Secure PHP Login</title>

<style>
body { font-family: Arial; }

* modal */.modal {
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
<input type="text" name="username" maxlength="50" required><br><br>

Password:<br>
<input type="password" name="password" required><br><br>

<button type="submit" name="register">Register</button>
</form>

<p><?php echo $message; ?></p>

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
<script>openModal();</script>
<?php endif; ?>

</body>
</html>