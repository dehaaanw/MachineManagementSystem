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
$servername = "localhost";
$username = "gciwxkmy_matrixeng";
$password = "Active@2024";
$dbname = "gciwxkmy_WPESQ";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables for displaying results
$day_nos = 0;
$rent_paid = 0;
$formatted_data = "";
$locations = "";
$downtime = 0;
$rent = 0;
$locations = [];

$stmt = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    // Fetch the relevant row from the table
    $query = "SELECT m_name, brand, model, id, supplier, in_date, rent, movement FROM mc_database WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $in_date = $row['in_date'];
        $m_name = $row['m_name'];
        $brand = $row['brand'];
        $model = $row['model'];
        $id = $row['id'];
        $supplier = $row['supplier'];
        $rent = (float)str_replace(',', '', $row['rent']); 
        $movement_data = $row['movement'];

        // Calculate the number of days (day_nos) from in_date to today
        $in_date_obj = new DateTime($in_date);
        $today_date_obj = new DateTime(); // Today's date
        $interval = $in_date_obj->diff($today_date_obj);
        $day_nos = $interval->days;

        // Calculate rent_paid
        $rent_paid = round(($rent / 30) * $day_nos, 2);

        $lines = explode("\n", $movement_data); // Split by newline
        $locations = [];

        foreach ($lines as $line) {
            preg_match('/^(\d{4}-\d{2}-\d{2}) \d{2}:\d{2}:\d{2} - (.+)$/', $line, $matches);
            if ($matches) {
                $date = $matches[1];
                $location = trim($matches[2]);
                $locations[$date] = $location; // Overwrite to keep the last location of the day
            }
        }

        // Calculate downtime
        $downtime = 0;
        $dates = array_keys($locations);
        sort($dates);
        
        $start_date = null;
        $today_date = date('Y-m-d'); // Current date
        
        foreach ($dates as $i => $date) {
            if ($locations[$date] === 'Machine Yard') {
                if ($start_date === null) {
                    $start_date = $date; // Start of downtime period
                }
            } else {
                if ($start_date !== null) {
                    // Calculate downtime for the period when the machine was in "Machine Yard"
                    $downtime += (strtotime($date) - strtotime($start_date)) / (60 * 60 * 24);
                    $start_date = null; // Reset after downtime calculation
                }
            }
        }
        
        // Handle ongoing "Machine Yard" period if it extends to the last date or today
        $last_date = end($dates);
        if ($locations[$last_date] === 'Machine Yard') {
            $downtime += (strtotime($today_date) - strtotime($start_date ?? $last_date)) / (60 * 60 * 24) + 1;
        }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap.css">
    <script src="jquery-3.2.1.slim.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="bootstrap.bundle.min.js"></script>
    <style>
        .invoice {
            border: 1px solid #000;
            padding: 20px;
            max-width: 1000px;
            margin: auto;
            font-family:Georgia, 'Times New Roman', Times, serif;
        }
        .invoice::before {
            content: "Confidential";
            font-size: 100px;
            color: rgba(0, 0, 0, 0.1);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg); /* Rotating the watermark text diagonally */
            z-index: -1;
            white-space: nowrap;
        }
        .invoice-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .invoice-header h1 {
            margin: 0;
        }
        .details {
            margin-bottom: 20px;
        }
        .details p {
            margin: 5px 0;
        }
        .summary {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .summary h3 span {
            color: red; /* Efficiency in red */
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .invoice, .invoice * {
                visibility: visible;
            }
            .invoice {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
            }
        }
        .images {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .images img {
            width: 60px;
            height: 40px;
            margin: 0 10px;
        }
    </style>
    <title>Check Machine Allocation</title>
</head>
<body>
<nav class="navbar navbar-dark bg-dark navbar-expand-sm">
           <a href="CostingForCurrentMachines.php"><img src="maslogo.png" alt="Logo" style="width:70px; margin: .5rem 2rem;" ></a>
            <button class="navbar-toggler" data-toggler="collapse" data-target="#menu"></button>
            <ul class="navbar-nav">
                <li class="nav-item">
                <a href="CostingForCurrentMachines.php" class="nav-link active">Allocation Per Machine</a>
                </li>
                 <li class="nav-item">
                <a href="ShopfloorMCAllocation.php" class="nav-link ">Shopfloor Machine Allocation</a>
                </li>
                <li class="nav-item">
                <a href="MCComparison.php" class="nav-link ">Machine Comparison</a>
                </li>
                <li class="nav-item">
                 <a href="LogoutPage.php" class="nav-link " style="color: #ff4f4b;">Logout</a>
                 </li>
            </ul>
        </nav>
    <h1 style="text-align:center; color:#3C4142;">Check <span style="color:#ff4f4b;">Machine Allocation</span></h1>
    <form method="POST" action="">
    <div class="col-md-2 mb-3">
        <label for="id">Enter Machine ID:</label>
        <input type="text" id="id" name="id" class="form-control" required>
    </div>
    <div class="col md-1 mb-3 align-content-end">
        <button type="submit" class="btn btn-info">Check Machine Allocation</button>
    </div>
    </form> 

    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($result) && $result->num_rows > 0): ?>
    <div class="invoice">
        <div class="invoice-header">
        <h2> TexTrack </h2>
        <h1>Machine Allocation Invoice</h1>
        <p><strong>Date:</strong> <?= date("Y-m-d") ?></p>
        </div>
        <div class="details">
        <p><strong>Machine Name:</strong> <?= $m_name ?></p>
        <p><strong>Machine ID:</strong> <?= $id ?></p>
        <p><strong>Supplier:</strong> <?= $supplier ?></p>
        <p><strong>Model:</strong> <?= $model ?></p>
        <p><strong>Brand:</strong> <?= $brand ?></p>
        <p><strong>In Date:</strong> <?= $in_date ?></p>
        <?php endif; ?>
    `   </div>
    <div class="summary">
    <?php
        // Display movement summary and downtime
        echo "<h3>Machine Movement Summary:</h3>";
        foreach ($locations as $date => $location) {
            $next_date_index = array_search($date, $dates) + 1;
            $next_date = $dates[$next_date_index] ?? null;
        
            if ($next_date) {
                echo "From $date to $next_date - $location.<br>";
            } else {
                echo "$date - Present - $location.<br>";
            }
        }
        $roundeddowntime = round($downtime);
        $roundedrent = number_format($rent_paid,2);
        $rentlost = number_format(($rent/30) * $roundeddowntime,2);
        $efficiency = $day_nos > 0 ? number_format((($day_nos - $roundeddowntime) / $day_nos) * 100, 2) : 0;
        echo "<br><br><h3>No of Days Rented: $day_nos days</h3>";
        echo "<h3>Rent Paid: Rs. $roundedrent</h3>";
        echo "<h3>Downtime at Machine Yard: $roundeddowntime days</h3>";
        echo "<h3>Rent Lost due to Machine Downtime: <span><u> Rs. $rentlost </u></span></h3>";
        echo "<h3>Machine Efficiency (Rough Value): <u>$efficiency % </u></h3>";
    // Only close $stmt if it was set (not null)
    if ($stmt !== null) {
        $stmt->close();
    }
    ?>
    </div>
    <div class="images">
        <img src="Autonomation.png" alt="Image 1">
        <img src="TexTrackLogo.png" alt="Image 2">
    </div>
    <br>
    <p style="font-size: small; text-align:center;">This is an Auto-Generated Document from TexTrack System by Matrix Autonomation.</p>
    </div>
    <center>
    <div class="col md-1 mb-3 align-content-end">
        <button onclick="window.print()" class="btn btn-info">Print Invoice</button>
    </div>
    </center>
</body>
</html>
<?php $conn->close(); ?>