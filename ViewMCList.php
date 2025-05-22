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

// SQL query to fetch data from the suppliers table
$sql = "SELECT id, SupplierName, MachineName, brand, Model, MachineCode, Price FROM machinelist1";
$result = $conn->query($sql);
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
            table {
            width: 50%;
            border-collapse: collapse;
            }
            th, td {
                padding: 1px;
                text-align: left;
                border: 1px solid #ccc; /* Horizontal and vertical borders */
                padding: 8px;
            }
            th {
                padding: 8px; 
                text-align: left; 
                border: 1px solid #ccc; 
                background-color: #708090; 
                position: sticky; 
                top: 0; 
                z-index: 1;
                border: 1px solid black ;
            }
            tr:nth-child(odd) {
                background-color: #BEBEBE; /* Lightest blue */
            }
            tr:nth-child(even) {
                background-color: #D3D3D3; /* Light blue */
            }
            .modal {
                display: none;
                position: fixed;
                z-index: 1;
                padding-top: 60px;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgb(0,0,0);
                background-color: rgba(0,0,0,0.4);
            }
            .modal-content {
                background-color: #fefefe;
                margin: 5% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 40%;
                max-height: 80%; /* Limit max height of modal content */
                overflow-y: auto; /* Enable vertical scrolling */
            }
            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
            }
            .close:hover,
            .close:focus {
                color: black;
                text-decoration: none;
                cursor: pointer;
            }
            .bulk-print-button{
                cursor: pointer;
                background-color: #4CAF50;
                color: white;
                border: none;
                font-size: 16px;    
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
        <h2 class="col-md-100 mb-100" style="color:#3C4142; text-align:center;">Machine List</h2>
        <center>
        <table>
            <thead>
                <tr>
                    <th>Supplier Name</th>
                    <th>Machine Name</th>
                    <th> Brand </th>
                    <th>Model</th>
                    <th>Machine Code</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    // Output data of each row
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . $row['SupplierName'] . "</td>
                                <td>" . $row['MachineName'] . "</td>
                                <td>" . $row['brand']. "</td>
                                <td>" . $row['Model'] . "</td>
                                <td>" . $row['MachineCode'] . "</td>
                                <td>" . $row['Price'] . "</td>
                            </tr>";
                    }
                } else {
                    echo "<tr><td colspan='2'>No data found</td></tr>";
                }
                ?>
            </tbody>
        </table>
        </center>
    </body>
</html>
<?php
// Close the database connection
$conn->close();
?>