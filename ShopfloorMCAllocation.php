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
$mysqli = new mysqli("localhost", "gciwxkmy_matrixeng", "Active@2024", "gciwxkmy_WPESQ");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}   

// Get filter dates
$startDate = $_POST['start_date'] ?? null;
$endDate   = $_POST['end_date']   ?? null;
$dataRows  = [];
$totalRentPaid   = 0;
$totalRentalLoss = 0;

// Fetch rental data for pie chart
function fetchRentalData($mysqli) {
    $q = "SELECT mc_status, COUNT(*) as count FROM mc_database WHERE mc_type='Rental Machine' GROUP BY mc_status";
    $r = $mysqli->query($q);
    $d = [];
    while($row = $r->fetch_assoc()) {
        $d[$row['mc_status']] = $row['count'];
    }
    return ['Production' => $d['Production'] ?? 0, 'Machine Yard' => $d['Machine Yard'] ?? 0];
}
$rentalData = fetchRentalData($mysqli);

if ($startDate && $endDate) {
    $startDT = new DateTime($startDate);
    $endDT   = new DateTime($endDate);

    // --- Active machines ---
    $sqlActive = "SELECT id,m_name,movement,rent,supplier,model,brand,mc_serial,in_date
                   FROM mc_database
                   WHERE mc_type='Rental Machine'";
    $resA = $mysqli->query($sqlActive);
    while ($r = $resA->fetch_assoc()) {
        $inDT = new DateTime($r['in_date']);
        // include if in_date <= endDate
        if ($inDT > $endDT) continue;
        // determine rent period start
        $calcStart = ($inDT < $startDT) ? $startDT : $inDT;
        // rent runs until endDate
        $calcEnd = $endDT;
        $days = $calcStart->diff($calcEnd)->days;
        $rent = (float)str_replace(',', '', $r['rent']);
        $rentPaid = ($rent/30)*$days;
        $movement_data = $r['movement'];

        // downtime unchanged
        // Calculate downtime based on movement log from mc_database
        if (!empty($movement_data)) {
            $lines = explode("\n", $movement_data);
            $locations = [];
            foreach ($lines as $line) {
                preg_match('/^(\d{4}-\d{2}-\d{2}) \d{2}:\d{2}:\d{2} - (.+)$/', $line, $matches);
                if ($matches) {
                    $date = $matches[1];
                    $location = trim($matches[2]);
                    $locations[$date] = $location;
                }
            }
            $downtime = 0;
            $dates = array_keys($locations);
            sort($dates);
            $start_loc_date = null;
            foreach ($dates as $date) {
                if ($locations[$date] === 'Machine Yard') {
                    if ($start_loc_date === null) {
                        $start_loc_date = $date;
                    }
                } else {
                    if ($start_loc_date !== null) {
                        $intervalStart = max(new DateTime($startDate), new DateTime($start_loc_date));
                        $intervalEnd = min(new DateTime($endDate), new DateTime($date));
                        if ($intervalStart <= $intervalEnd) {
                            $downtime += $intervalStart->diff($intervalEnd)->days;
                        }
                        $start_loc_date = null;
                    }
                }
            }
            if (!empty($dates)) {
                $last_date = end($dates);
                if (isset($locations[$last_date]) && $locations[$last_date] === 'Machine Yard' && $start_loc_date !== null) {
                    $intervalStart = max(new DateTime($startDate), new DateTime($start_loc_date));
                    $intervalEnd = min(new DateTime(date('Y-m-d')), new DateTime($endDate));
                    if ($intervalStart <= $intervalEnd) {
                        $downtime += $intervalStart->diff($intervalEnd)->days;
                    }
                }
            }
        } else {
            $downtime = 0;
        }

        // include if any charge
        if ($days>0 || $downtime>0) {
            $dataRows[] = [
                'id' => $r['id'],
                'm_name' => $r['m_name'],
                'supplier' => $r['supplier'],
                'model' => $r['model'],
                'mc_serial' => $r['mc_serial'],
                'brand' => $r['brand'],
                'in_date' => $r['in_date'],
                'start_date' => $calcStart->format('Y-m-d'),
                'end_date'   => $calcEnd->format('Y-m-d'),
                'downtime'   => $downtime,
                'rent_paid'  => number_format($rentPaid,2,'.',','),
                'rental_loss'=> number_format(($rent/30)*$downtime,2,'.',','),
                'rent_raw'   => $rentPaid,
                'loss_raw'   => ($rent/30)*$downtime
            ];
            $totalRentPaid   += $rentPaid;
            $totalRentalLoss += ($rent/30)*$downtime;
        }
    }

    // --- Exited machines ---
    $sqlExit = "SELECT id,m_name,movement,rent,supplier,model,brand,mc_serial,in_date,actual_out_date
                FROM out_machines
                WHERE mc_type='Rental Machine'";
    $resE = $mysqli->query($sqlExit);
    while ($r = $resE->fetch_assoc()) {
        $inDT  = new DateTime($r['in_date']);
        $outDT = new DateTime($r['actual_out_date']);
        if ($inDT > $endDT) continue;
        // include if actual_out_date >= startDate
        if ($outDT < $startDT) continue;
        // determine rent period start
        $calcStart = ($inDT > $startDT && $inDT <= $endDT) ? $inDT : $startDT;
        // determine rent period end
        $calcEnd = ($outDT < $endDT) ? $outDT : $endDT;
        $days = $calcStart->diff($calcEnd)->days;
        $rent = (float)str_replace(',', '', $r['rent']);
        $rentPaid = ($rent/30)*$days;
        // downtime unchanged

        // Calculate downtime based on movement log from mc_database
        if (!empty($movement_data)) {
            $lines = explode("\n", $movement_data);
            $locations = [];
            foreach ($lines as $line) {
                preg_match('/^(\d{4}-\d{2}-\d{2}) \d{2}:\d{2}:\d{2} - (.+)$/', $line, $matches);
                if ($matches) {
                    $date = $matches[1];
                    $location = trim($matches[2]);
                    $locations[$date] = $location;
                }
            }
            $downtime = 0;
            $dates = array_keys($locations);
            sort($dates);
            $start_loc_date = null;
            foreach ($dates as $date) {
                if ($locations[$date] === 'Machine Yard') {
                    if ($start_loc_date === null) {
                        $start_loc_date = $date;
                    }
                } else {
                    if ($start_loc_date !== null) {
                        $intervalStart = max(new DateTime($startDate), new DateTime($start_loc_date));
                        $intervalEnd = min(new DateTime($endDate), new DateTime($date));
                        if ($intervalStart <= $intervalEnd) {
                            $downtime += $intervalStart->diff($intervalEnd)->days;
                        }
                        $start_loc_date = null;
                    }
                }
            }
            if (!empty($dates)) {
                $last_date = end($dates);
                if (isset($locations[$last_date]) && $locations[$last_date] === 'Machine Yard' && $start_loc_date !== null) {
                    $intervalStart = max(new DateTime($startDate), new DateTime($start_loc_date));
                    $intervalEnd = min(new DateTime(date('Y-m-d')), new DateTime($endDate));
                    if ($intervalStart <= $intervalEnd) {
                        $downtime += $intervalStart->diff($intervalEnd)->days;
                    }
                }
            }
        } else {
            $downtime = 0;
        }

        if ($days>0 || $downtime>0) {
            $dataRows[] = [
                'id' => $r['id'],
                'm_name' => $r['m_name'],
                'supplier' => $r['supplier'],
                'model' => $r['model'],
                'mc_serial' => $r['mc_serial'],
                'brand' => $r['brand'],
                'in_date' => $r['in_date'],
                'start_date' => $calcStart->format('Y-m-d'),
                'end_date'   => $calcEnd->format('Y-m-d'),
                'downtime'   => $downtime,
                'rent_paid'  => number_format($rentPaid,2,'.',','),
                'rental_loss'=> number_format(($rent/30)*$downtime,2,'.',','),
                'rent_raw'   => $rentPaid,
                'loss_raw'   => ($rent/30)*$downtime
            ];
            $totalRentPaid   += $rentPaid;
            $totalRentalLoss += ($rent/30)*$downtime;
        }
    }
    // Unique suppliers
    $suppliers = array_unique(array_column($dataRows, 'supplier'));
    sort($suppliers);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>TexTrack Machine Allocation Invoice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="bootstrap.css">
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="jquery-3.2.1.slim.min.js"></script>
    <script src="bootstrap.bundle.min.js"></script>
    <script src="xlsx.full.min.js"></script>
    <style>
        .invoice {
            border: 1px solid #000;
            padding: 20px;
            max-width: 1300px;
            margin: auto;
            font-family: Georgia, 'Times New Roman', Times, serif;
            position: relative;
        }
        .invoice::before {
            content: "Confidential";
            font-size: 100px;
            color: rgba(0, 0, 0, 0.1);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
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
        .details h4 {
            margin: 5px 0;
        }
        .summary {
            margin-top: 20px;
            border-top: 1px solid #000;
            padding-top: 10px;
        }
        .summary h3 span {
            color: red;
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
    <script type="text/javascript">
        // Load Google Charts library and draw the pie chart
        google.charts.load('current', {'packages': ['corechart']});
        google.charts.setOnLoadCallback(drawRentalPieChart);
        function drawRentalPieChart() {
            const data = google.visualization.arrayToDataTable([
                ['Status', 'Count'],
                ['Production', <?= $rentalData['Production'] ?>],
                ['Machine Yard', <?= $rentalData['Machine Yard'] ?>]
            ]);
            const options = {
                pieSliceText: 'label',
                legend: { position: 'none' },
                chartArea: { width: '80%', height: '80%' },
                slices: {
                    0: { color: '#00FF00', offset: 0.05 },
                    1: { color: '#FF0000', offset: 0.05 }
                },
                fontSize: 14,
                titleTextStyle: { fontSize: 18, bold: true },
                pieSliceTextStyle: { fontSize: 14, bold: true }
            };
            const chart = new google.visualization.PieChart(document.getElementById('rentalChart'));
            chart.draw(data, options);
            const legendContainer = document.getElementById('rentalChartLegend');
            legendContainer.innerHTML = `
                <div style="text-align: center; margin-top: 10px;">
                    <span style="color: #00FF00;">● Production: <?= $rentalData['Production'] ?></span> |
                    <span style="color: #FF0000;">● Machine Yard: <?= $rentalData['Machine Yard'] ?></span>
                </div>
            `;
        }
        function applyCombinedFilter() {
        var sup = $('#supplierFilterDropdown').val();
        var rows = $('#invoiceTable tbody tr');
        var totalRent = 0, totalLoss = 0;
        rows.each(function() {
            var row = $(this);
            var s = row.attr('data-supplier');
            var dt = parseFloat(row.attr('data-downtime'));
            var rent = parseFloat(row.attr('data-rent_raw'));
            var loss = parseFloat(row.attr('data-loss_raw'));
            var show = true;
            if (sup && s !== sup) show = false;
            if (downtimeFilterActive && dt <= 0) show = false;
            row.toggle(show);
            if (show) { totalRent += rent; totalLoss += loss; }
        });
        $('#supplierTotals').html(
            '<center><h1>Total Rent Paid: Rs ' + totalRent.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' | Total Loss: Rs ' + totalLoss.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</h1></center>'
        );
        }

        var downtimeFilterActive = false;
        function toggleDowntimeFilter() {
            downtimeFilterActive = !downtimeFilterActive;
            $('#toggleDowntimeBtn').text(downtimeFilterActive ? 'Show All Machines' : 'Only show machines with downtimes');
            applyCombinedFilter();
        }

        function toggleSummary() {
            $('#summaryDiv').toggle();
            $('#invoiceTable, #controls, #rentalChart').toggle();
        }
                // Convert to USD
        function convertToUSD() {
            var rate = parseFloat($('#usdRate').val());
        if (rate > 0) {
            var rent = parseFloat($('#summaryDiv').data('rent_raw'));
            var loss = parseFloat($('#summaryDiv').data('loss_raw'));
            $('#rentUsd').text('USD ' + (rent / rate).toFixed(2));
            $('#lossUsd').text('USD ' + (loss / rate).toFixed(2));
        }
    }
    function exportInvoiceExcel() {
        var wb = XLSX.utils.table_to_book(document.getElementById('invoiceTable'), { sheet: 'Invoice' });
        var wbout = XLSX.write(wb, { bookType: 'xlsx', type: 'binary' });
        function s2ab(s) { var buf = new ArrayBuffer(s.length); var view = new Uint8Array(buf); for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF; return buf; }
        var blob = new Blob([s2ab(wbout)], { type: 'application/octet-stream' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'Invoice.xlsx';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    $(document).ready(function() {
        applyCombinedFilter();
    });
    </script>
</head>
<body>
    <nav class="navbar navbar-dark bg-dark navbar-expand-sm">
        <a href="CostingForCurrentMachines.php">
            <img src="maslogo.png" alt="Logo" style="width:70px; margin: .5rem 2rem;">
        </a>
        <button class="navbar-toggler" data-toggler="collapse" data-target="#menu"></button>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="CostingForCurrentMachines.php" class="nav-link">Allocation Per Machine</a>
            </li>
            <li class="nav-item">
                <a href="ShopfloorMCAllocation.php" class="nav-link active">Shopfloor Machine Allocation</a>
            </li>
            <li class="nav-item">
                <a href="MCComparison.php" class="nav-link">Machine Comparison</a>
            </li>
            <li class="nav-item">
                <a href="LogoutPage.php" class="nav-link" style="color: #ff4f4b;">Logout</a>
            </li>
        </ul>
    </nav>
    <h1 style="text-align:center; color:#3C4142;">Shopfloor<span style="color:#ff4f4b;"> Rental Machine </span>Allocation</h1>
    <form method="POST">
    <div class="col-md-2 mb-3">   
        Start Date: <input type="date" name="start_date" class="form-control" required>
    </div>
    <div class="col-md-2 mb-3">   
        End Date:   <input type="date" name="end_date" class="form-control" required>
    </div>
        <button type="submit" class="btn btn-info">Submit</button>
    </form>
    <div class="invoice">
    <div class="invoice-header">
                <h2>TexTrack</h2>
                <h1>Shopfloor Rental Machine Utilization Invoice</h1>
                <p><strong>Date:</strong> <?= date("Y-m-d") ?></p>
    </div>
    <?php if (!empty($dataRows)): ?>
  <div id="rentalChart" style="width:400px; height:400px; margin:auto;"></div>
  <div id="rentalChartLegend" style="text-align:center; margin-bottom:1rem;"></div>
  <div class="details">
        <h4><strong>Start Date:</strong> <?= $startDate ?></h4>
        <h4><strong>End Date:</strong> <?= $endDate ?></h4>
    </div>
  <center>
  <div>
    <select id="supplierFilterDropdown" class="form-control w-auto d-inline mr-2" onchange="applyCombinedFilter()">
      <option value="">All Suppliers</option>
      <?php foreach ($suppliers as $s): ?>
        <option><?=htmlspecialchars($s)?></option>
      <?php endforeach; ?>
    </select>
    <button id="toggleDowntimeBtn" class="btn btn-warning mr-2" onclick="toggleDowntimeFilter()">Only show machines with downtimes</button>
    <button class="btn btn-secondary mr-2" onclick="toggleSummary()">Check for Summary</button>
    <input id="usdRate" type="number" class="form-control w-auto d-inline mr-2" placeholder="USD Rate">
    <button class="btn btn-primary mr-2" onclick="convertToUSD()">OK</button>
    <button class="btn btn-success" onclick="exportInvoiceExcel()">Export Excel</button>
    <button onclick="window.print()" class="btn btn-info">Print Invoice</button>
    </div>
</center>
  <div id="summaryDiv" class="summary" style="display:none;" data-rent_raw="<?=$totalRentPaid?>" data-loss_raw="<?=$totalRentalLoss?>">
  <div id="rentalChart" style="width:400px; height:400px; margin:auto;"></div>
  <div id="rentalChartLegend" style="text-align:center; margin-bottom:1rem;"></div>
    <h3>Total Rent Paid: Rs <?=number_format($totalRentPaid,2,'.',',')?> <span id="rentUsd"></span></h3>
    <h3>Total Rental Loss: Rs <?=number_format($totalRentalLoss,2,'.',',')?> <span id="lossUsd"></span></h3>
  </div>
  <div id="supplierTotals" class="my-2"></div>
  <table id="invoiceTable" border="1" style="font-family: 'Courier New', Courier, monospace; margin: auto;">
    <thead>
      <tr>
        <th>ID</th>
        <th>Name</th>
        <th>Supplier</th>
        <th>Model</th>
        <th>Serial</th>
        <th>Brand</th>
        <th>Start Date</th>
        <th>End Date</th>
        <th>Days Yard</th>
        <th>Rent Paid</th>
        <th>Loss</th>
    </tr>
    </thead>
    <tbody>
      <?php foreach ($dataRows as $r): ?>
        <tr 
        data-supplier="<?=htmlspecialchars($r['supplier'])?>" 
        data-downtime="<?=$r['downtime']?>" 
        data-rent_raw="<?=$r['rent_raw']?>" 
        data-loss_raw="<?=$r['loss_raw']?>">
          <td><?=$r['id']?></td>
          <td><?=$r['m_name']?></td>
          <td><?=$r['supplier']?></td>
          <td><?=$r['model']?></td>
          <td><?=$r['mc_serial']?></td>
          <td><?=$r['brand']?></td>
          <td><?=$r['start_date']?></td>
          <td><?=$r['end_date']?></td>
          <td><?=$r['downtime']?></td>
          <td><?=$r['rent_paid']?></td>
          <td><?=$r['rental_loss']?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div class="images">
    <img src="Autonomation.png" alt="Autonomation">
    <img src="TexTrackLogo.png" alt="TexTrack Logo">
        </div>
        <p style="font-size: small; text-align: center;">
            This is an Auto-Generated Document from TexTrack System by Matrix Autonomation.
        </p>
      </div>
<?php endif; ?>
</div>
</body>
</html>
<?php $mysqli->close(); ?>
