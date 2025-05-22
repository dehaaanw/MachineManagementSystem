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
// Database connection
$host = "localhost";
$user = "gciwxkmy_matrixeng";
$password = "Active@2024"; // Replace with your DB password
$dbname = "gciwxkmy_WPESQ";

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'fetch_next_id') {  // New handler for next ID suggestion
        $baseId = $_POST['baseId']; // e.g. "AP-DL"
        if ($baseId) {
            // This query finds the row where id starts with the base and a space, then orders by the numeric part
            $query = "SELECT id FROM (
                    SELECT id FROM mc_database WHERE id LIKE CONCAT(?, ' %')
                    UNION ALL
                    SELECT id FROM out_machines WHERE id LIKE CONCAT(?, ' %')
                ) AS combined
                ORDER BY CAST(SUBSTRING_INDEX(id, ' ', -1) AS UNSIGNED) DESC
                LIMIT 1";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ss", $baseId, $baseId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                // Extract the numeric part and increment it
                $lastId = $row['id']; // e.g. "AP-DL 433"
                $parts = explode(" ", $lastId);
                $lastNum = (int) end($parts);
                $nextNum = $lastNum + 1;
            } else {
                $nextNum = 1; // No previous entry exists, so start with 1
            }
            $suggestion = $baseId . " " . $nextNum;
            echo json_encode([$suggestion]);
        }
        exit;
    }   
}

// Fetch supplier names for the dropdown
$supplierQuery = "SELECT SupplierName FROM supplierlist";
$supplierResult = $conn->query($supplierQuery);
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
            .dropdown-menu {
                width: 100%;
                max-height: 200px;
                overflow-y: auto;
            }
            .dropdown-container {
            position: relative;
            width: 570px;
            }
            .dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 150px;
            border: 1px solid #ccc;
            border-top: none;
            background-color: #fff;
            overflow-y: auto;
            z-index: 1000;
            display: none;
            }
            .dropdown div {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            }
            .dropdown div:hover {
            background-color: #f0f0f0;
            }
            .dropdown div:last-child {
            border-bottom: none;
            }   
        </style>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
        <link rel="stylesheet" href="bootstrap.css">
        <script src="jquery-3.2.1.slim.min.js"></script>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="bootstrap.bundle.min.js"></script>
        <meta charset="utf-8">
        <script>
            function showSecondDropdown(){
            var firstDropdown = document.getElementById("first-dropdown");
            var secondDropdown = document.getElementById("second-dropdown");
            if(firstDropdown.value=="Production"){
                secondDropdown.style.display="contents";
            } else {
                secondDropdown.style.display="none";
            }
            }
            // Build a Bootstrap dropdown from a suggestions array
            function buildDropdown(suggestions) {
                let dropdown = '<div class="dropdown-menu show">';
                suggestions.forEach(item => {
                    dropdown += `<a class="dropdown-item" href="#">${item}</a>`;
                });
                dropdown += '</div>';
                return dropdown;
            }
            // Binds a static dropdown behavior for a textbox
            function bindStaticDropdown(selector, suggestionContainer, optionsArray) {
                $(selector).on('input focus', function () {
                    const inputVal = $(this).val().toLowerCase();
                    const filtered = optionsArray.filter(item => item.toLowerCase().includes(inputVal));
                    const dropdown = buildDropdown(filtered);
                    $(suggestionContainer).html(dropdown);
                    
                    // When a suggestion is clicked, update the field
                    $('.dropdown-item').click(function (e) {
                        e.preventDefault();
                        $(selector).val($(this).text());
                        $(suggestionContainer).empty();
                    });
                });
            }
            
            $(document).ready(function () {
                // Static suggestion arrays
                const machineNameOptions = ["Auto Super Linking Machine", 
                "Band Knife Machine", 
                "Bar tack Machine", 
                "Blind Hem Machine", 
                "Bottle iron","Button Attacher Machine",
                "Button Hole Machine",
                "Button Wrapping Machine",
                "Dial Linking Machine",
                "Die Cut Machine",
                "Double Needle Machine",
                "Flat Lock Binding Machine",
                "Flat Lock Cylinder Bed Machine",
                "Flat Lock Cylinder Bed Rubber Puller",
                "Flat Lock Flat Bed Machine",
                "Flat Lock Hem Cutter Machine",
                "Flat Lock Riger Machine",
                "Flat Lock Small Cylinder Bed Machine",
                "Flat Lock machine",
                "Flat Seam Machine",
                "Heat Seal Machine",
                "Overlock Machine",
                "PLK Machine",
                "Pattern Sewer Machine",
                "Pullout Seam Machine",
                "Single Needle Machine",
                "Upsteam Bed",
                "Vacuum Bed"];
                const brandOptions = [
                    "ABLE",
                    "BROTHER",
                    "DONG SHENG",
                    "Exacter",
                    "Flying Yang",
                    "HI-TECH",
                    "HIGH SPEED",
                    "Hikari",
                    "Impress",
                    "JUKI",
                    "MATIX",
                    "MegaSew",
                    "Mentasti",
                    "NOKI",
                    "Neurotek",
                    "Pegasus",
                    "SHUNFA",
                    "Sigma",
                    "Shing Ling",
                    "Silver Stars",
                    "TREASURE",
                    "Suprem",
                    "Yamato"
                ];
                
                // Bind static dropdowns to MachineName, Brand, and Model textboxes
                bindStaticDropdown("#MachineName", "#MachineNameSuggestions", machineNameOptions);
                bindStaticDropdown("#brand", "#brandSuggestions", brandOptions);

                // Bind event for MachineCode (ID) suggestions
                $('#MachineCode').on('input focus', function () {
                    const baseId = $(this).val(); // e.g. "AP-DL"
                    if (baseId) {
                        $.ajax({
                            url: 'RentalMachine.php',
                            type: 'POST',
                            data: { action: 'fetch_next_id', baseId: baseId },
                            success: function (data) {
                                const suggestions = JSON.parse(data);
                                const dropdown = buildDropdown(suggestions);
                                $('#MachineCodeSuggestions').html(dropdown);
                                $('.dropdown-item').click(function (e) {
                                    e.preventDefault();
                                    $('#MachineCode').val($(this).text());
                                    $('#MachineCodeSuggestions').empty();
                                });
                            }
                        });
                    }
                });
        });
        </script>
    </head>
    <body>
         <nav class="navbar navbar-dark bg-dark navbar-expand-sm">
           <a href="MachineUtilization.php"><img src="maslogo.png" alt="Logo" style="width:70px; margin: .5rem 2rem;" ></a>
            <button class="navbar-toggler" data-toggler="collapse" data-target="#menu"></button>
            <ul class="navbar-nav">
                <li class="nav-item">
                <a href="MachineCheckIn.php" class="nav-link active">Machine Check-In</a>
                </li>
                 <li class="nav-item">
                <a href="UpdateMachine.php" class="nav-link ">Update Machine</a>
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
                <a href="LogoutPage.php" class="nav-link " style="color: #ff4f4b;">Logout</a>
                </li>
            </ul>
        </nav>
        <br/>
        <h1 style="text-align:center; color:#3C4142;">Register <span style="color:#ff4f4b;">Rental</span> Machine.</h1>
        <center>

        <form method="POST" class="needs-validation col-md-12 align">
        
        <div class="form-row">
        <div class="col-md-2 mb-3">
        <label for="SupplierName">Supplier Name</label><br>
        <select id="SupplierName" name="SupplierName" class="form-control" required>
            <option value="">Select Supplier</option>
            <?php
            if ($supplierResult->num_rows > 0) {
                while ($row = $supplierResult->fetch_assoc()) {
                    echo "<option value='{$row['SupplierName']}'>{$row['SupplierName']}</option>";
                }
            }
            ?>
        </select>
        </div>
        <br>
        <div class="col-md-2 mb-3">
        <label for="MachineName">Machine Name</label><br>
        <input type="text" id="MachineName" name="MachineName" class="form-control" autocomplete="off" placeholder="Machine Name">
        <div id="MachineNameSuggestions" class="suggestions-wrapper"></div>
        </div>
        <div class="col-md-2 mb-3">
        <label for="valitxt02">Brand</label>
        <input type="text" class="form-control" name="brand" id="brand" placeholder="Brand" autocomplete="off">
        <div id="brandSuggestions" class="suggestions-wrapper"></div>
        </div>
        <div class="col-md-2 mb-3">
        <label for="Model">Model</label><br>
        <input type="text" id="Model" name="Model" class="form-control" autocomplete="off" placeholder="Model">
        <div id="ModelSuggestions" class="suggestions-wrapper"></div>
        </div>
        <div class="col-md-2 mb-3">
        <label for="Price">Price</label><br>
        <input type="text" id="Price" class="form-control" name="Price" placeholder="Rent Price"><br>
        </div>
        </div>
        <div class="form-row">
        <div class="col-md-2 mb-3">   
        <label for="MachineCode">ID Code</label><br>
        <input type="text" id="MachineCode" class="form-control" name="MachineCode" placeholder="ID Code"><br>
            <div id="MachineCodeSuggestions"></div>
        </div>
        <div class="col-md-2 mb-3">
                    <label for="valitxt04">Size</label>
                    <input type="text" class="form-control" name="size" id="valitxt04" placeholder="Size" >
                </div>
                <div class="col-md-2 mb-3">
                    <label for="valitxt05">Serial No</label>
                    <input type="text" class="form-control" name="mc_serial" id="valitxt05" placeholder="Serial No" >
                </div>
                <div class="col-md-2 mb-3">
                    <label for="valitxt06">Control Box No</label>
                    <input type="text" class="form-control" name="c_box" id="valitxt06" placeholder="Control Box No" >
                </div>  
                <div class="col-md-2 mb-3">
                    <label for="valitxt07">Status</label>
                        <select name="mc_status" id ="first-dropdown" class="form-control" onchange="showSecondDropdown()"> 
                            <option value="" selected>Select Status</option>
                            <option value="Production">Production</option>
                            <option value="Breakdown">Breakdown</option>
                            <option value="Machine Yard">Machine Yard</option>
                            <option value="On-Loan OUT">On-Loan OUT</option>
                        </select>
                </div>
                <div id="second-dropdown" style="display:none;">
                <div class="col-md-2 mb-3">
                    <label for="valitxt08">Location</label>
                        <select name="current_location" class="form-control">
                            <option value="" selected>Select Location</option>
                            <option value="Module 1">Module 01</option>
                            <option value="Module 2">Module 02</option>
                            <option value="Module 3">Module 03</option>
                            <option value="Module 4">Module 04</option>
                            <option value="Module 5">Module 05</option>
                            <option value="Module 6">Module 06</option>
                            <option value="Module 7">Module 07</option>
                            <option value="Module 8">Module 08</option>
                            <option value="Module 9">Module 09</option>
                            <option value="Module 10">Module 10</option>
                            <option value="Module 11">Module 11</option>
                            <option value="Module 12">Module 12</option>
                            <option value="Module 13">Module 13</option>
                            <option value="Module 14">Module14</option>
                            <option value="Module 15">Module 15</option>
                            <option value="Module 16">Module 16</option>
                            <option value="Module 17">Module 17</option>
                            <option value="Module 18">Module 18</option>
                            <option value="Module 19">Module 19</option>
                            <option value="Module 20">Module 20</option>
                            <option value="Sample Line">Sample Line</option>
                            <option value="Pilot Module">Pilot Module</option>
                            <option value="Machine Yard">Machine Yard</option>
                            <option value="Training Module">Training Module</option>
                            <option value="FCDC Alternative">FCDC Alternative</option>
                            <option value="Preset Module">Preset Module</option>
                        </select>
                    </div>
                </div>
        </div>
            <div class="form-row">
                <div class="col-md-2 mb-2">
                    <label for="valitxt09">In Date</label>
                    <input type="date" class="form-control" name="in_date" id="valitxt09" placeholder="In Date" >
                </div>
                <div class="col-md-2 mb-2">
                    <label for="valitxt10">Proposed Out Date</label>
                    <input type="date" class="form-control" name="pout_date" id="valitxt10" placeholder="Out Date" >
                </div>
                <div class="col-md-2 mb-2">
                    <label for="valitxt12">Duration</label>
                        <select name="duration" class="form-control">
                            <option value="" selected>Select Duration</option>
                            <option value="3">3</option> 
                            <option value="6">6</option>
                        </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label for="valitxt14">IN Gate Pass</label>
                    <input type="number" class="form-control" name="in_gpass" placeholder="IN Gate Pass">
                </div>
        </div>
        <div class="form-row">
                <div class="col-md-2 mb-2" style="display:none;">
                    <select name="mc_type">
                        <option value="Rental Machine" selected>On Loan IN</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="valitxt15">Remarks</label>
                    <textarea name="remarks" cols="50" rows="3" id="valitxt15" placeholder="Remarks"></textarea>
                </div>
        </div>
        <div class="col md-1 mb-3 align-content-end">
        <button type="submit" class="btn btn-info" name="submitForm">Submit</button>
        </div>
        </form>
        </center>

        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {

        // Database connection parameters
        $servername = "localhost"; // Change this if your database is hosted elsewhere
        $username = "gciwxkmy_matrixeng"; // Change this to your MySQL username
        $password = "Active@2024"; // Change this to your MySQL password
        $dbname = "gciwxkmy_WPESQ"; // Change this to your MySQL database name
        //$servername = "localhost";
        //$username = "gciwxkmy_matrixeng";
        //$password = "Active@2024";
        //$dbname = "gciwxkmy_WPESQ";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Set parameters and execute
        $mc_type = isset($_POST['mc_type']) ? $_POST['mc_type'] : '';
        $m_name = isset($_POST['MachineName']) ? $_POST['MachineName'] : '';
        $brand = isset($_POST['brand']) ? $_POST['brand'] : '';
        $model = isset($_POST['Model']) ? $_POST['Model'] : '';
        $id = isset($_POST['MachineCode']) ? $_POST['MachineCode'] : '';
        $size = isset($_POST['size']) ? $_POST['size'] : '';
        $mc_serial = isset($_POST['mc_serial']) ? $_POST['mc_serial'] : '';
        $c_box = isset($_POST['c_box']) ? $_POST['c_box'] : '';
        $mc_status = isset($_POST['mc_status']) ? $_POST['mc_status'] : '';
        $current_location = isset($_POST['current_location']) ? $_POST['current_location'] : '';
        $in_date = isset($_POST['in_date']) ? $_POST['in_date'] : '';
        $pout_date = isset($_POST['pout_date']) ? $_POST['pout_date'] : '';
        $supplier = isset($_POST['SupplierName']) ? $_POST['SupplierName'] : '';
        $duration = isset($_POST['duration']) ? $_POST['duration'] : '';
        $rent = isset($_POST['Price']) ? $_POST['Price'] : '';
        $in_gpass = isset($_POST['in_gpass']) ? $_POST['in_gpass'] : '';
        $out_gpass = isset($_POST['out_gpass']) ? $_POST['out_gpass'] : '';
        $invoice = isset($_POST['invoice']) ? $_POST['invoice'] : '';
        $remarks = isset($_POST['remarks']) ? $_POST['remarks'] : '';

        date_default_timezone_set('Asia/Colombo');
        $current_time = date('Y-m-d H:i:s'); // Get current date time
        $movement = "$current_time - $mc_status";

            // Check if the ID already exists
        $checkQuery = "SELECT id FROM mc_database WHERE id = ?";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("s", $id);
        $checkStmt->execute();
        $checkStmt->store_result();
        
        if ($checkStmt->num_rows > 0) {
            // ID already exists
            echo '<script>alert("ID already exists!");</script>';
        } else {
        // Prepare and bind SQL statement
        $stmt = $conn->prepare("INSERT INTO mc_database (mc_type,m_name,brand,model,id,size,mc_serial,c_box,mc_status,current_location,in_date,pout_date,supplier,duration,rent,in_gpass,out_gpass,invoice,remarks,movement) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");

        if (!$stmt) {
            die("Error preparing statement: " . $conn->error);
        }

        $stmt->bind_param("ssssssssssssssssssss",$mc_type, $m_name, $brand, $model, $id, $size, $mc_serial, $c_box, $mc_status, $current_location, $in_date, $pout_date, $supplier, $duration, $rent, $in_gpass, $out_gpass, $invoice, $remarks,$movement);

        if ($stmt->execute()) {
            // Instead of a simple alert, display a confirm dialog via JavaScript
            echo "<script>
                if(confirm('Machine Entered Successfully. Generate Barcode?')) {
                    window.location.href = 'GenerateBarcode.php?id=" . urlencode($id) . "&mc_serial=" . urlencode($mc_serial) . "&supplier=" . urlencode($supplier) . "&in_date=" . urlencode($in_date) . "';
                } else {
                    window.location.href = 'RentalMachine.php';
                }
            </script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $checkStmt->close();
    $conn->close();
    }
    
    ?>
        <div class="footer"> &copy Matrix Autonomation 2024 </div>
    </body>
</html>