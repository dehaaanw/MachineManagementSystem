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

<?php
// Define initial variables
$machine_id = $dropdown1 = $dropdown2 = "";

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get input values
    $machine_id = $_POST['machine_id'];
    $dropdown1 = $_POST['dropdown1'];
    $dropdown2 = $_POST['dropdown2'];
}
?><html>
    <head>
        <style>
            body{
                background-image: url(Background.jpg);
                background-repeat: no-repeat;
                background-attachment: fixed;
                background-size: cover;
            }
        </style>
        <script>
            function handleDropdownChange() {
            var dropdown1 = document.getElementById("dropdown1").value;
            var dropdown2 = document.getElementById("dropdown2");
            var options = dropdown2.options;

            if (dropdown1 === "Machine Yard" || dropdown1 === "Breakdown") {
                dropdown2.style.display = "none";
                for (var i = 0; i < options.length; i++) {
                    if (options[i].value === dropdown1) {
                        dropdown2.selectedIndex = i;
                        break;
                    }
                }
            } else if (dropdown1 === "Production") {
                dropdown2.style.display = "inline";
            }
        }

        window.onload = function() {
            handleDropdownChange();
        };
        </script>
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
                <a href="MachineCheckIn.php" class="nav-link ">Machine Check-In</a>
                </li>
                 <li class="nav-item">
                <a href="UpdateMachine.php" class="nav-link active">Update Machine</a>
                </li>
                 <li class="nav-item">
                <a href="ViewModule.php" class="nav-link ">View/Edit Location</a>
                </li>
                 <li class="nav-item">
                <div href="abc.html" class="nav-link " onmouseover="this.style.cursor='not-allowed';" onmouseout="this.style.cursor='default';">View Machine</div>
                </li>
                 <li class="nav-item">
                <a href="CheckOutPage.php" class="nav-link ">Machine Check-Out</a>
                </li>
                 <li class="nav-item">
                <a href="LogoutPage.php" class="nav-link "  style="color: #ff4f4b;">Logout</a>
                </li>
            </ul>
        </nav>
        Update Machine Location <br/>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
        <div class="form-row">
            <div class="col-md-2 mb-3">
                <input type="text" class="form-control" name="machine_id" id="machine_id" placeholder="ID Code" required autofocus>
            </div>
        </div>
        <div class="col-md-2 mb-3">
            <label for="valitxt07">Status</label>
                <select name="dropdown1" id ="dropdown1" class="form-control" onchange="handleDropdownChange()"> 
                    <option value="Machine Yard" <?php if ($dropdown1 == "Machine Yard") echo "selected"; ?>>Machine Yard</option>
                    <option value="Production" <?php if ($dropdown1 == "Production") echo "selected"; ?>>Production</option>
                    <option value="Breakdown" <?php if ($dropdown1 == "Breakdown") echo "selected"; ?>>Breakdown</option>
                </select>
        </div>
        <div class="form-row col-8 mb-4">
            <div class="col-md-3">
                <select class="form-control" id="dropdown2" name="dropdown2">
                    <option value="Module 1" <?php if ($dropdown2 == "Module 1") echo "selected"; ?>>Module 01</option>
                    <option value="Module 2" <?php if ($dropdown2 == "Module 2") echo "selected"; ?>>Module 02</option>
                    <option value="Module 3" <?php if ($dropdown2 == "Module 3") echo "selected"; ?>>Module 03</option>
                    <option value="Module 4" <?php if ($dropdown2 == "Module 4") echo "selected"; ?>>Module 04</option>
                    <option value="Module 5" <?php if ($dropdown2 == "Module 5") echo "selected"; ?>>Module 05</option>
                    <option value="Module 6" <?php if ($dropdown2 == "Module 6") echo "selected"; ?>>Module 06</option>
                    <option value="Module 7" <?php if ($dropdown2 == "Module 7") echo "selected"; ?>>Module 07</option>
                    <option value="Module 8" <?php if ($dropdown2 == "Module 8") echo "selected"; ?>>Module 08</option>
                    <option value="Module 9" <?php if ($dropdown2 == "Module 9") echo "selected"; ?>>Module 09</option>
                    <option value="Module 10" <?php if ($dropdown2 == "Module 10") echo "selected"; ?>>Module 10</option>
                    <option value="Module 11" <?php if ($dropdown2 == "Module 11") echo "selected"; ?>>Module 11</option>
                    <option value="Module 12" <?php if ($dropdown2 == "Module 12") echo "selected"; ?>>Module 12</option>
                    <option value="Module 13" <?php if ($dropdown2 == "Module 13") echo "selected"; ?>>Module 13</option>
                    <option value="Module 14" <?php if ($dropdown2 == "Module 14") echo "selected"; ?>>Module14</option>
                    <option value="Module 15" <?php if ($dropdown2 == "Module 15") echo "selected"; ?>>Module 15</option>
                    <option value="Module 16" <?php if ($dropdown2 == "Module 16") echo "selected"; ?>>Module 16</option>
                    <option value="Module 17" <?php if ($dropdown2 == "Module 17") echo "selected"; ?>>Module 17</option>
                    <option value="Module 18" <?php if ($dropdown2 == "Module 18") echo "selected"; ?>>Module 18</option>
                    <option value="Module 19" <?php if ($dropdown2 == "Module 19") echo "selected"; ?>>Module 19</option>
                    <option value="Module 20" <?php if ($dropdown2 == "Module 20") echo "selected"; ?>>Module 20</option>
                    <option value="Sample Line" <?php if ($dropdown2 == "Sample Line") echo "selected"; ?>>Sample Line</option>
                    <option value="Pilot Module" <?php if ($dropdown2 == "Pilot Module") echo "selected"; ?>>Pilot Module</option>
                    <option value="Training Module" <?php if ($dropdown2 == "Training Module") echo "selected"; ?>>Training Module</option>
                    <option value="FCDC Alternative" <?php if ($dropdown2 == "FCDC Alternative") echo "selected"; ?>>FCDC Alternative</option>
                    <option value="Preset Module" <?php if ($dropdown2 == "Preset Module") echo "selected"; ?>>Preset Module</option>
                    <option value="Machine Yard" <?php if ($dropdown2 == "Machine Yard") echo "selected"; ?>>Machine Yard</option>
                    <option value="Breakdown" <?php if ($dropdown2 == "Breakdown") echo "selected"; ?>>Breakdown</option>
                </select>
            </div>
        </div>
        <div class="col-md-1">
            <input type="submit" class="form-control btn btn-info">
        </div>
        </form>
        <?php
            // Connect to database
            $servername = "localhost"; // Change this if your database is hosted elsewhere
            $username = "gciwxkmy_matrixeng"; // Change this to your MySQL username
            $password = "Active@2024"; // Change this to your MySQL password
            $dbname = "gciwxkmy_WPESQ"; // Change this to your MySQL database name

            $conn = new mysqli($servername, $username, $password, $dbname);

            // Check connection
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Get input values
                $machine_id = $_POST['machine_id'];
                $dropdown1 = $_POST['dropdown1'];
                $dropdown2 = $_POST['dropdown2'];
                date_default_timezone_set('Asia/Colombo');
                $current_time = date('Y-m-d H:i:s'); // Get current date time
                
                // Update database record
                $sql = "UPDATE mc_database 
                        SET current_location = '$dropdown2', 
                            mc_status = '$dropdown1', 
                            movement = CONCAT(movement, '\n', '$current_time - $dropdown2') 
                        WHERE id = '$machine_id'";
                
                if ($conn->query($sql) === TRUE) {
                    if ($conn->affected_rows > 0) {
                        echo "<h5>Machine location updated successfully.</h5>";
                    } else {
                        echo "<h2><center>No machine available!!!!</center></h2>";
                    }
                } else {
                    echo "Error updating record: " . $conn->error;
                }
            }            
            
            // Close connection
            $conn->close();
        ?>
        <div class="footer"> &copy Matrix Autonomation 2024 </div>
    </body>
</html>