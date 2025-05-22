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
// Connect to the database
$mysqli = new mysqli("localhost", "gciwxkmy_matrixeng", "Active@2024", "gciwxkmy_WPESQ");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Retrieve only the two machine types and include the 'model' column
$query = "SELECT id, mc_type, m_name, model, mc_serial, rent, mc_status, current_location
          FROM mc_database 
          WHERE mc_type IN ('Matrix Owned Machine', 'Rental Machine')";
$result = $mysqli->query($query);

$groups = [];
while ($row = $result->fetch_assoc()) {
    // Group rows by a composite key: mc_name and model
    $groupKey = $row['m_name'] . '|' . $row['model'];
    if (!isset($groups[$groupKey])) {
        $groups[$groupKey] = [];
    }
    $groups[$groupKey][] = $row;
}

$mysqli->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Machine Comparison</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap CSS (Using CDN) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="bootstrap.css">
    <script src="jquery-3.2.1.slim.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="bootstrap.bundle.min.js"></script>
    <style>
        /* Custom styling */
        body {
            background-color: #f8f9fa;
        }
        h1 {
            margin-top: 30px;
            margin-bottom: 30px;
            text-align: center;
            color: #343a40;
        }
        .group-container {
            margin-bottom: 40px;
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 2px 4px rgba(0,0,0,0.1);
        }
        .machine-card {
            margin-bottom: 20px;
            transition: transform 0.2s ease-in-out;
        }
        .machine-card:hover {
            transform: scale(1.03);
        }
        .card-header {
            font-weight: bold;
            background-color: #007bff;
            color: #fff;
        }
        .machine-yard {
            background-color: #dc3545 !important;
            color: white;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark navbar-expand-sm">
           <a href="CostingForCurrentMachines.php"><img src="maslogo.png" alt="Logo" style="width:70px; margin: .5rem 2rem;" ></a>
            <button class="navbar-toggler" data-toggler="collapse" data-target="#menu"></button>
            <ul class="navbar-nav">
                <li class="nav-item">
                <a href="CostingForCurrentMachines.php" class="nav-link">Allocation Per Machine</a>
                </li>
                 <li class="nav-item">
                <a href="ShopfloorMCAllocation.php" class="nav-link ">Shopfloor Machine Allocation</a>
                </li>
                <li class="nav-item">
                <a href="MCComparison.php" class="nav-link active">Machine Comparison</a>
                </li>
                <li class="nav-item">
                 <a href="LogoutPage.php" class="nav-link " style="color: #ff4f4b;">Logout</a>
                 </li>
            </ul>
        </nav>
    <h1 style="text-align:center; color:#3C4142;">Matrix Owned <span style="color:#ff4f4b;">vs</span> Rental Machine</h1>
<div class="container">
    <?php foreach ($groups as $groupKey => $records): ?>
        <?php
        // Extract mc_name and model from the composite group key
        list($mcName, $model) = explode('|', $groupKey);
        
        // Check if this group has at least one record of each machine type.
        $hasMatrixOwned = false;
        $hasRental = false;
        foreach ($records as $record) {
            if ($record['mc_type'] === 'Matrix Owned Machine') {
                $hasMatrixOwned = true;
            }
            if ($record['mc_type'] === 'Rental Machine') {
                $hasRental = true;
            }
        }
        // Only display groups where both types exist.
        if (!($hasMatrixOwned && $hasRental)) {
            continue;
        }
        ?>
        <div class="group-container">
            <h3 class="text-center"><?php echo htmlspecialchars($mcName); ?> - Model: <?php echo htmlspecialchars($model); ?></h3>
            <div class="row">
                <?php foreach ($records as $record): ?>
                    <?php if ($record['mc_type'] === 'Matrix Owned Machine' || $record['mc_type'] === 'Rental Machine'): ?>
                        <?php
                        // Determine if the card should be red based on mc_status
                        $cardClass = ($record['mc_status'] === 'Machine Yard') ? 'machine-yard' : '';
                        ?>
                        <div class="col-md-6">
                            <div class="card machine-card <?php echo $cardClass; ?>">
                                <div class="card-header text-center">
                                    <?php echo htmlspecialchars($record['mc_type']); ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title text-center"><?php echo htmlspecialchars($record['m_name']); ?></h5>
                                    <p class="card-text">
                                        <strong>ID:</strong> <?php echo htmlspecialchars($record['id']); ?><br>
                                        <strong>Model:</strong> <?php echo htmlspecialchars($record['model']); ?><br>
                                        <strong>Serial No:</strong> <?php echo htmlspecialchars($record['mc_serial']); ?><br>
                                        <strong>Price:</strong> Rs.<?php echo htmlspecialchars($record['rent']); ?><br>
                                        <strong>Status:</strong> <?php echo htmlspecialchars($record['mc_status']); ?><br>
                                        <strong>Location:</strong> <?php echo htmlspecialchars($record['current_location']); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<!-- Bootstrap JS and dependencies (Using CDN) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>