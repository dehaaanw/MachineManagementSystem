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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $SupplierID = $_POST['SupplierID'];
    $SupplierName = $_POST['SupplierName'];

    // Check if the supplier name already exists
    $checkQuery = "SELECT * FROM supplierlist WHERE SupplierName = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $SupplierName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('The supplier name already exists. Data was not added!');</script>";
    } else {
        // Check for duplicate Supplier ID
        $checkIdQuery = "SELECT * FROM supplierlist WHERE SupplierID = ?";
        $stmt = $conn->prepare($checkIdQuery);
        $stmt->bind_param("s", $SupplierID);
        $stmt->execute();
        $idResult = $stmt->get_result();

        if ($idResult->num_rows > 0) {
            echo "<script>alert('Supplier ID already exists. Data was not added!');</script>";
        } else {
            // Insert new supplier into the table
            $insertQuery = "INSERT INTO supplierlist (SupplierID, SupplierName) VALUES (?, ?)";
            $stmt = $conn->prepare($insertQuery);
            $stmt->bind_param("ss", $SupplierID, $SupplierName);
            if ($stmt->execute()) {
                echo "<script>alert('Supplier added successfully!');</script>";
            } else {
                echo "<script>alert('Error adding supplier.');</script>";
            }
        }
    }
    $stmt->close();
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
            <script>
        function confirmSubmission(event) {
            if (!confirm("Are you sure you want to add a new supplier?")) {
                event.preventDefault();
            }
        }
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
                <a href="AdminEditMC.php" class="nav-link ">Edit MC ID & Rent Price</a>
                </li>
                 <li class="nav-item">
                <a href="AdminEditSupp.php" class="nav-link active">Edit Supplier List</a>
                </li>
                <li class="nav-item">
                 <a href="LogoutPage.php" class="nav-link " style="color: #ff4f4b;">Logout</a>
                 </li>
            </ul>
        </nav>
        <br/>
        <h2 class="col-md-100 mb-100" style="color:#3C4142; text-align:center;">Add New Supplier</h2>
    <form method="POST" onsubmit="confirmSubmission(event);">
    <div class="col-md-1 mb-3">
        <label for="SupplierID">Supplier ID:</label>
        <input type="text" id="SupplierID" name="SupplierID" class="form-control" value="ttsupp" required>
    </div>    
    <div class="col-md-3 mb-3">
        <label for="SupplierName">Supplier Name:</label>
        <input type="text" id="SupplierName" name="SupplierName" class="form-control" placeholder="Enter supplier name" required>
    </div>
    <div class="col md-1 mb-3 align-content-end">
        <button type="submit" class="btn btn-info">Add Supplier</button>
    </div>
    </form>
    </body>
</html>
<?php
// Close the database connection
$conn->close();
?>