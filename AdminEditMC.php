<?php
session_save_path(__DIR__ . '/tmp_sessions'); // Use the custom directory for sessions
session_start();
// Check if the user is logged in
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    // Redirect to LoginPage.php if the user is not logged in
    header("Location: LoginPage.php");
    exit();
}
?>

<html>
    <head>
        <style>
            body{
                background-image: url(Background.jpg);
                background-repeat: no-repeat;
                background-attachment: fixed;
                background-size: cover;
            }
        </style>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link rel="stylesheet" href="bootstrap.css">
        <script src="jquery-3.2.1.slim.min.js"></script>
        <script src="bootstrap.bundle.min.js"></script>
        <meta charset="utf-8">
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark navbar-expand-sm">
           <a href="MachineUtilization.php"><img src="maslogo.png" alt="Logo" style="width:70px; margin: .5rem 2rem;" ></a>
            <button class="navbar-toggler" data-toggler="collapse" data-target="#menu"></button>
            <ul class="navbar-nav">
                <li class="nav-item">
                <a href="AdminEditMC.php" class="nav-link active">Edit MC ID & Rent Price</a>
                </li>
                 <li class="nav-item">
                <a href="AdminEditSupp.php" class="nav-link ">Edit Supplier List</a>
                </li>
                <li class="nav-item">
                 <a href="LogoutPage.php" class="nav-link " style="color: #ff4f4b;">Logout</a>
                 </li>
            </ul>
        </nav>
        <br/>
        <div class="main-text">
        <h1 class="centered-text">Edit Machine ID & Rental Price</h1><br/>
        <div class="btn-container">
            <a href="ViewMCList.php" class="btn" style="color:123499"><b>View Machine List</b></a>
            <a href="AddMC.php" class="btn" style="color:123499"><b>Add Machine Data</b></a>
            <!--<a href="OnLoanOut.html" class="btn" style="color:123499"><b>On-Loan Machine OUT</b></a>-->
        </div>
        </div>
        <div class="footer"> &copy Matrix Autonomation 2024 </div>
    </body>
</html>