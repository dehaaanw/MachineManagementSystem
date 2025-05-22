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
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 1 Jan 2000 00:00:00 GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
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
            width: 100%;
            border-collapse: collapse;
            }
            th, td {
                padding: 8px;
                text-align: left;
                border: 1px solid #ccc; /* Horizontal and vertical borders */
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
            function showSecondDropdown(){
            var firstDropdown = document.getElementById("first-dropdown");
            var secondDropdown = document.getElementById("second-dropdown");
            if(firstDropdown.value=="Production"){
                secondDropdown.style.display="contents";
            } else {
                secondDropdown.style.display="none";
            }
        }
            function displayUpdateList(){
            var displayedTable = document.getElementById("displayedTable");
            var displayQueries = document.getElementById("displayQueries");
            if(displayedTable.value!=" "){
                displayQueries.style.display="contents";
            } else {
                displayQueries.style.display="none";
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
                <a href="MachineCheckIn.php" class="nav-link ">Machine Check-In</a>
                </li>
                 <li class="nav-item">
                <a href="UpdateMachine.php" class="nav-link ">Update Machine</a>
                </li>
                 <li class="nav-item">
                <a href="ViewModule.php" class="nav-link active">View/Edit Location</a>
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
        <br/>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?t=' . time(); ?>" method="get">
        <div class="col-md-2 mb-3">
                    <label for="valitxt01">Machine ID</label>
                    <input type="text" class="form-control" name="id" id="id" placeholder="Machine ID" required autofocus>
        </div>
            <input type="submit" class="btn btn-info" value="Search" onsubmit="displayUpdateList()"> <!--START FROM THINS POINT ONWARDS. When submit button is clicked only the dropdowns are supposed to be shown-->
        <div id="displayedTable">
    <?php
    if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
            // Connect to the database
        $servername = "localhost"; // Change this if your database is hosted elsewhere
        $username = "gciwxkmy_matrixeng"; // Change this to your MySQL username
        $password = "Active@2024"; // Change this to your MySQL password
        $dbname = "gciwxkmy_WPESQ"; // Change this to your MySQL database name
        $conn = new mysqli($servername, $username, $password, $dbname);
        $conn->set_charset("utf8");

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Retrieve machine ID from the query string
        $id = $_GET['id'];

        // Prepare SQL statement to search for the machine ID
        $sql = "SELECT * FROM mc_database WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Display the results
        if ($result->num_rows > 0) {
            echo "<h2>Machine ID: $id </h2>";
            echo "<table border='1'>";
            echo "<tr>
            <th>Machine Type</th>
            <th>Machine Name</th>
            <th>Brand</th>
            <th>Model</th>
            <th>ID Code</th>
            <th>Size</th>
            <th>Serial No</th>
            <th>Control Box No</th>
            <th>Status</th>
            <th>Current Location</th>
            <th>In Date</th>
            <th>Proposed Out Date</th>
            <th>Supplier</th>
            <th>Duration</th>
            <th>Rent</th>
            <th>IN Gate Pass</th>
            <th>OUT Gate Pass</th>
            <th>Invoice</th>
            <th>Remarks</th>
            </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["mc_type"] . "</td>";
                echo "<td>" . $row["m_name"] . "</td>";
                echo "<td>" . $row["brand"] . "</td>";
                echo "<td>" . $row["model"] . "</td>";
                echo "<td>" . $row["id"] . "</td>";
                echo "<td>" . $row["size"] . "</td>";
                echo "<td>" . $row["mc_serial"] . "</td>";
                echo "<td>" . $row["c_box"] . "</td>";
                echo "<td>" . $row["mc_status"] . "</td>";
                echo "<td>" . $row["current_location"] . "</td>";
                echo "<td>" . $row["in_date"] . "</td>";
                echo "<td>" . $row["pout_date"] . "</td>";
                echo "<td>" . $row["supplier"] . "</td>";
                echo "<td>" . $row["duration"] . "</td>";
                echo "<td>" . $row["rent"] . "</td>";
                echo "<td>" . $row["in_gpass"] . "</td>";
                echo "<td>" . $row["out_gpass"] . "</td>";
                echo "<td>" . $row["invoice"] . "</td>";
                echo "<td>" . $row["remarks"] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "No results found for ID: $id";
        }

        // Close connection
        $stmt->close();
        $conn->close();
    }
    ?>
    </div>
    <br/>       
 </form>
        <div class="footer"> &copy Matrix Autonomation 2024 </div>
    </body>
</html>