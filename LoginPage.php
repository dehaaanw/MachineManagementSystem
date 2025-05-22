<?php
// Set a custom session save path
session_save_path(__DIR__ . '/tmp_sessions');
if (!file_exists(__DIR__ . '/tmp_sessions')) {
    mkdir(__DIR__ . '/tmp_sessions', 0777, true);
}

session_start();

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    if ($_SESSION['username'] === 'recorder') {
        header("Location: MachineCheckIn.php");
        exit();
    } elseif ($_SESSION['username'] === 'admin') {
        header("Location: AdminEditMC.php");
        exit();
    } elseif ($_SESSION['username'] === 'observer') {
        header("Location: CostingForCurrentMachines.php");
        exit();
    } 
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === 'recorder' && $password === 'matrixeng') {
        $_SESSION['username'] = 'recorder';
        header("Location: MachineCheckIn.php");
        exit();
    } elseif ($username === 'admin' && $password === 'MASMatrixAuto2025') {
        $_SESSION['username'] = 'admin';
        header("Location: AdminEditMC.php");
        exit();
    } elseif ($username === 'observer' && $password === 'MASMatrix@123') {
        $_SESSION['username'] = 'observer';
        header("Location: CostingForCurrentMachines.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        body{
            background-image: url(Background.jpg);
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: cover;
        }
    </style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link rel="stylesheet" href="bootstrap.css">
        <script src="jquery-3.2.1.slim.min.js"></script>
        <script src="bootstrap.bundle.min.js"></script>
    <title>Login</title>
</head>
<body>
    <center>
    <div style="margin-top:15%;">
    <img src="TexTrackLogo.png" style="width:20%;">
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <form method="POST" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br>
        <button type="submit" class="btn btn-info">Login</button>
    </form>
    </div>
    </center>
</body>
</html>