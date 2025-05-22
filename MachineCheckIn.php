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
        <script>
            function lock(){
            document.getElementById("txt").readOnly=true;
        };
            function update(){
                var x = document.getElementById("txt").value;
                if(x==''){
                    alert("Please Scan the Barcode");
                }else{
                    window.location.href="scan_b.php?x="+x;
                }
            };
            function save(){
                var x=document.getElementById("txt").value;
                if(x==''){
                    alert("Please Scan the Barcode");
                }else{
                    window.location.href="movement_path.php?x="+x;
                }
            }
        
        </script>
        <meta charset="utf-8">
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark navbar-expand-sm">
           <a href="MachineUtilization.php"><img src="maslogo.png" alt="Logo" style="width:70px; margin: .5rem 2rem;" ></a>
            <button class="navbar-toggler" data-toggler="collapse" data-target="#menu"></button>
            <ul class="navbar-nav">
                <li class="nav-item">
                <a href="MachineCheckIn.php" class="nav-link">Machine Check-In</a>
                </li>
                 <li class="nav-item">
                <a href="UpdateMachine.php" class="nav-link ">Update Machine</a>
                </li>
                 <li class="nav-item">
                <a href="ViewModule.php" class="nav-link " >View/Edit Location</a>
                </li>
                 <li class="nav-item">
                <div href="abc.html" class="nav-link " onmouseover="this.style.cursor='not-allowed';" onmouseout="this.style.cursor='default';">View Machine</div>
                </li>
                 <li class="nav-item">
                <a href="CheckOutPage.php" class="nav-link ">Machine Check-Out</a>
                </li>
                 <li class="nav-item">
                <a href="LogoutPage.php" class="nav-link " style="color: #ff4f4b;">Logout</a>
                </li>
            </ul>
        </nav>
        <br/>
        <div class="main-text">
        <h1 class="centered-text">Select the Machine Type.</h1><br/>
        <div class="btn-container">
            <a href="MatrixOwned.php" class="btn" style="color:123499"><b>Matrix Owned</b></a>
            <a href="RentalMachine.php" class="btn" style="color:123499"><b>Rental Machine</b></a>
            <a href="OnLoanIn.php" class="btn" style="color:123499"><b>On-Loan Machine IN</b></a>
            <!--<a href="OnLoanOut.html" class="btn" style="color:123499"><b>On-Loan Machine OUT</b></a>-->
        </div>
        </div>
        <div class="footer"> &copy Matrix Autonomation 2024 </div>
    </body>
</html>