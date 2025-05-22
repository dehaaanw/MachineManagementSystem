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

// Fetch data for pie charts
function fetchData($conn, $filterType = null) {
    $condition = $filterType ? "WHERE mc_type = '$filterType'" : "";
    $query = "SELECT mc_status, COUNT(*) as count FROM mc_database $condition GROUP BY mc_status";
    $result = $conn->query($query);

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[$row['mc_status']] = $row['count'];
    }

    return [
        'Production' => $data['Production'] ?? 0,
        'Machine Yard' => $data['Machine Yard'] ?? 0
    ];
}

$allData = fetchData($conn);
$matrixOwnedData = fetchData($conn, 'Matrix Owned Machine');
$rentalData = fetchData($conn, 'Rental Machine');
$onLoanData = fetchData($conn, 'On Loan In Machines');
?>
<html>
<head>
    <style>
        body {
            background-image: url(Background.jpg);
            background-repeat: no-repeat;
            background-attachment: fixed;
            background-size: cover;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
            /* Header container styles */
        .header-container {
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            padding: 0px;
            background-color: rgba(255,255,255,0.8);
        }
        /* Button styling */
        .home-btn {
            position: absolute;
            left: 20px;
            padding: 8px 16px;
            background-color:rgb(255, 255, 255);
            color: black;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            font-family: Century Gothic;
        }
        .home-btn:hover {
            background-color:rgb(0, 0, 0);
            color: white;
        }
        th, td {
            padding: 1px;
            text-align: left;
            border: 1px solid black;
        }

        thead th {
            background-color: #708090;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        th {
            padding: 8px;
            text-align: left;
            background-color: #708090;
            position: sticky;
            top: 0;
            z-index: 1;
            border: 1px solid black;
        }
        tr:nth-child(odd) {
            background-color: #BEBEBE;
        }
        tr:nth-child(even) {
            background-color: #D3D3D3;
        }
        .chart-container {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 10px;
            padding: 20px;
        }
        .chart-box {
            flex: 1 1 22%;
            max-width: 22%;
            min-width: 200px;
        }
        canvas {
            width: 100% !important;
            height: auto !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        function fetchData(mc_type, mc_status) {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'fetch_data.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    const dataTable = document.getElementById('dataTable').getElementsByTagName('tbody')[0];
                    dataTable.innerHTML = xhr.responseText;
                }
            };
            xhr.send(`mc_type=${encodeURIComponent(mc_type)}&mc_status=${encodeURIComponent(mc_status)}`);
        }

        function createPieChart(ctxId, data, labels, title, mc_type) {
            const ctx = document.getElementById(ctxId).getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: ['#00FF00', '#FF0000']
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: title,
                            font: {
                                size: 24,
                                weight: 'bold'
                            }
                        }
                    },
                    onClick: (event, elements) => {
                        if (elements.length > 0) {
                            const index = elements[0].index;
                            const status = labels[index];
                            fetchData(mc_type, status);
                        }
                    }
                }
            });
        }

        window.onload = function() {
            const allData = <?= json_encode(array_values($allData)) ?>;
            const matrixData = <?= json_encode(array_values($matrixOwnedData)) ?>;
            const rentalData = <?= json_encode(array_values($rentalData)) ?>;
            const loanData = <?= json_encode(array_values($onLoanData)) ?>;

            const labels = ['Production', 'Machine Yard'];

            createPieChart('allChart', allData, labels, 'All Machines', '');
            createPieChart('matrixChart', matrixData, labels, 'Matrix Owned Machines', 'Matrix Owned Machine');
            createPieChart('rentalChart', rentalData, labels, 'Rental Machines', 'Rental Machine');
            createPieChart('loanChart', loanData, labels, 'On Loan In Machines', 'On Loan In Machines');
        };
    </script>
</head>
<body>
    <div class="header-container">
        <a href="MachineCheckIn.php" class="home-btn"><b>Back to Check-In</b></a>
        <h1 style="color:rgb(2, 19, 117); text-align:center; font-size:60px; font-family:Century Gothic;"><div style="color:rgba(2, 19, 117, 0.45); font-size:45px;"> Matrix Engineering </div>Sewing Machine Utilization</h1>
    </div>
    <div class="chart-container">
        <div class="chart-box"><canvas id="allChart"></canvas></div>
        <div class="chart-box"><canvas id="matrixChart"></canvas></div>
        <div class="chart-box"><canvas id="rentalChart"></canvas></div>
        <div class="chart-box"><canvas id="loanChart"></canvas></div>
    </div>
    <h2>Data Table</h2>
    <table id="dataTable">
        <thead>
            <tr>
                <th>Machine Type</th>
                <th>Machine Name</th>
                <th>Brand</th>
                <th>Model</th>
                <th>ID</th>
                <th>Size</th>
                <th>Machine Serial</th>
                <th>Control Box No.</th>
                <th>Machine Status</th>
                <th>Current Location</th>
                <th>Machine In Date</th>
                <th>Planned Out Date</th>
                <th>Supplier</th>
                <th>Duration</th>
                <th>Rent</th>
                <th>IN Gate Pass</th>
                <th>OUT Gate Pass</th>
                <th>Invoice</th>
                <th>Remarks</th>
            </tr>
        </thead>
        <tbody>
            <!-- Data will be dynamically added here -->
        </tbody>
    </table>
</body>
</html>

<?php $conn->close(); ?>
