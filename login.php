<?php

$conn = mysqli_connect("localhost", "root", "", "practice_db");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$message = "";
$showLoginPopup = false;

if (isset($_POST['register'])) {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, password) 
            VALUES ('$username', '$hashed_password')";

    if (mysqli_query($conn, $sql)) {
        $message = "Registration Successful!";
        $showLoginPopup = true; // trigger popup
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}


if (isset($_POST['login'])) {

    $username = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {

        $row = mysqli_fetch_assoc($result);
        $stored_hash = $row['password'];

        if (password_verify($password, $stored_hash)) {
            $message = "Login Successful!";
        } else {
            $message = "Wrong Password!";
            $showLoginPopup = true; // reopen popup
        }

    } else {
        $message = "Username not found!";
        $showLoginPopup = true; // reopen popup
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>PHP Login System</title>

<style>
body {
    font-family: Arial;
}

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
<script>
    openModal();
</script>
<?php endif; ?>

</body>
</html>