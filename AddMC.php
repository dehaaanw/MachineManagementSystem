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
// Database connection settings
$servername = "localhost";
$username = "gciwxkmy_matrixeng";
$password = "Active@2024";
$database = "gciwxkmy_WPESQ";

// Create a connection
$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $SupplierName = $_POST['SupplierName'];
    $MachineName = $_POST['MachineName'];
    $brand = $_POST['brand'];
    $Model = $_POST['Model'];
    $MachineCode = $_POST['MachineCode'];
    $Price = $_POST['Price'];

    // Insert data into machinelist table
    $insertQuery = "INSERT INTO machinelist1 (id, SupplierName, MachineName, brand, Model, MachineCode, Price) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertQuery);
    $stmt->bind_param("sssssss", $id, $SupplierName, $MachineName, $brand, $Model, $MachineCode, $Price);

    if ($stmt->execute()) {
        echo "<script>alert('Data Entered Successfully.');</script>";
    } else {
        echo "<script>alert('Error entering data.');</script>";
    }
    $stmt->close();
}

// Fetch supplier names from supplierlist table
$supplierListQuery = "SELECT SupplierName FROM supplierlist";
$supplierResult = $conn->query($supplierListQuery);

$conn->close();
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
        <h2 class="col-md-100 mb-100" style="color:#3C4142; text-align:center;">Add New Machine & Price Data</h2>
    
        <form method="POST" class="needs-validation col-md-12 align">
        <div class="col-md-3 mb-3">
        <label for="SupplierName">Supplier Name:</label><br>
        <select id="SupplierName" name="SupplierName" class="form-control" required>
            <option value="" disabled selected>Select Supplier</option>
            <?php
            // Populate dropdown with supplier names
            if ($supplierResult->num_rows > 0) {
                while ($row = $supplierResult->fetch_assoc()) {
                    echo "<option value='" . htmlspecialchars($row['SupplierName'], ENT_QUOTES) . "'>" . htmlspecialchars($row['SupplierName'], ENT_QUOTES) . "</option>";
                }
            }
            ?>
        </select>
        </div>
        <br>
        <div class="col-md-3 mb-3">
        <label for="id">ID:</label><br>
        <input type="text" id="id" name="id" class="form-control" placeholder="ID" required><br>
        </div>
        <div class="col-md-3 mb-3">
        <label for="MachineName">Machine Name:</label><br>
        <input type="text" id="MachineName" name="MachineName" class="form-control" placeholder="Machine Name" required><br>
        </div>
        <div class="col-md-3 mb-3">
        <label for="brand">Brand :</label><br>
        <input type="text" id="brand" name="brand" class="form-control" placeholder="Brand Name" required><br>
        </div>
        <div class="col-md-3 mb-3">
        <label for="Model">Model:</label><br>
        <input type="text" id="Model" name="Model" class="form-control" placeholder="Model Name" required><br>
        </div>
        <div class="col-md-3 mb-3">
        <label for="MachineCode">Machine Code:</label><br>
        <input type="text" id="MachineCode" name="MachineCode" placeholder="Add a space to the end" class="form-control" required><br>
        </div>
        <div class="col-md-3 mb-3">
        <label for="Price">Price:</label><br>
        <input type="number" id="Price" name="Price" min="0" step="0.01" placeholder="Add '.00' to the end" class="form-control"><br>
        </div>
        <button type="submit" class="btn btn-info">Submit</button>
    </form>

    </body>
</html>