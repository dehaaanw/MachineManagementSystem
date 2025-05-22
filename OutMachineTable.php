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
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
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
        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #ccc;
        }
        th {
            background-color: #708090;
            border: 1px solid black;
            position: sticky;
            top: 0;
            z-index: 1;
        }
        tr:nth-child(odd) { background-color: #BEBEBE; }
        tr:nth-child(even) { background-color: #D3D3D3; }
        @media print {
            /* Hide all non-printable elements */
            body * { visibility: hidden; }
            /* Show printable area */
            #printableArea, #printableArea * { visibility: visible; }
            #printableArea { position: absolute; top: 0; left: 0; width: 100%; }
            /* Hide specific columns: Size(6), Machine Status(9), Location(10), Proposed Out Date(12), Duration(14) */
            #printableArea table th:nth-child(6),
            #printableArea table td:nth-child(6),
            #printableArea table th:nth-child(9),
            #printableArea table td:nth-child(9),
            #printableArea table th:nth-child(10),
            #printableArea table td:nth-child(10),
            #printableArea table th:nth-child(12),
            #printableArea table td:nth-child(12),
            #printableArea table th:nth-child(14),
            #printableArea table td:nth-child(14),
            #printableArea table th:nth-child(18),
            #printableArea table td:nth-child(18) {
                display: none;
            }
        }
    </style>
    <script src="jquery-3.2.1.slim.min.js"></script>
    <script src="bootstrap.bundle.min.js"></script>
    <script src="xlsx.full.min.js"></script>
    <script>
        function downloadExcel() {
            var table = document.getElementById('machinesTable');
            var workbook = XLSX.utils.table_to_book(table, { sheet: 'Sheet1' });
            var wbout = XLSX.write(workbook, { bookType: 'xlsx', type: 'binary' });
            function s2ab(s) {
                var buf = new ArrayBuffer(s.length);
                var view = new Uint8Array(buf);
                for (var i = 0; i < s.length; i++) view[i] = s.charCodeAt(i) & 0xFF;
                return buf;
            }
            var blob = new Blob([s2ab(wbout)], { type: 'application/octet-stream' });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Plant_OUT_Machines.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        function filterByDate() {
            var sel = document.getElementById('outDateFilter');
            var filter = sel.value;
            var table = document.getElementById('machinesTable');
            var tr = table.getElementsByTagName('tr');
            for (var i = 1; i < tr.length; i++) {
                var td = tr[i].getElementsByTagName('td')[19]; // Actual Out Date index
                if (!td) continue;
                var txt = td.textContent || td.innerText;
                tr[i].style.display = (filter === '' || txt === filter) ? '' : 'none';
            }
        }
        function printTable() {
            window.print();
        }
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="stylesheet" href="bootstrap.css">
</head>
<body>
<nav class="navbar navbar-dark bg-dark navbar-expand-sm">
    <a href="MachineUtilization.php"><img src="maslogo.png" alt="Logo" style="width:70px; margin: .5rem 2rem;"></a>
    <ul class="navbar-nav">
        <li class="nav-item"><a href="MachineCheckIn.php" class="nav-link">Machine Check-In</a></li>
        <li class="nav-item"><a href="UpdateMachine.php" class="nav-link">Update Machine</a></li>
        <li class="nav-item"><a href="ViewModule.php" class="nav-link">View/Edit Location</a></li>
        <li class="nav-item"><div class="nav-link disabled" style="cursor:not-allowed;">View Machine</div></li>
        <li class="nav-item"><a href="CheckOutPage.php" class="nav-link active">Machine Check-Out</a></li>
        <li class="nav-item"><a href="LogoutPage.php" class="nav-link" style="color:#ff4f4b;">Logout</a></li>
    </ul>
</nav>
<br/>
<h1 style="text-align:center; color:#3C4142;"><span style="color:#ff4f4b;">OUT</span> Machines.</h1>
<div class="form-row" style="margin: 0 1rem 1rem;">
    <a href="CheckOutPage.php" class="btn btn-secondary">Back to Check-Out Page</a>
    <input type="text" id="machineName" placeholder="Search by Machine Name..." class="form-control ml-2" style="width:200px;">
    <input type="text" id="machineId" placeholder="Search by Machine ID..." class="form-control ml-2" style="width:200px;">
    <input type="text" id="serialNo" placeholder="Search by Serial No..." class="form-control ml-2" style="width:200px;">
    <input type="text" id="machineSupplier" placeholder="Search by Supplier" class="form-control ml-2" style="width:200px;">
    <button onclick="downloadExcel()" class="btn btn-info ml-2">Download Excel</button>
    <?php
    $conn = new mysqli('localhost','gciwxkmy_matrixeng','Active@2024','gciwxkmy_WPESQ');
    $dates = [];
    $res = $conn->query("SELECT DISTINCT actual_out_date FROM out_machines ORDER BY actual_out_date DESC");
    while($d = $res->fetch_assoc()) $dates[] = $d['actual_out_date'];
    $conn->close();
    ?>
    <select id="outDateFilter" onchange="filterByDate()" class="form-control ml-2" style="width:180px;">
        <option value="">All Dates</option>
        <?php foreach($dates as $dt): ?>
            <option value="<?= htmlspecialchars($dt) ?>"><?= htmlspecialchars($dt) ?></option>
        <?php endforeach; ?>
    </select>
    <button onclick="printTable()" class="btn btn-warning ml-2">Print PDF</button>
</div>
<div id="printableArea" style="margin: 0 1rem;">
    <table id="machinesTable">
        <tr>
            <th>Machine Type</th><th>Name</th><th>Brand</th><th>Model</th><th>ID</th>
            <th>Size</th><th>Serial Number</th><th>Control Box Number</th><th>Machine Status</th><th>Location</th>
            <th>Machine In Date</th><th>Proposed Out Date</th><th>Supplier</th><th>Duration</th><th>Rent</th>
            <th>IN Gate Pass</th><th>OUT Gate Pass</th><th>Invoice</th><th>Remarks</th><th>Actual Out Date</th>
        </tr>
        <?php
        $conn = new mysqli('localhost','gciwxkmy_matrixeng','Active@2024','gciwxkmy_WPESQ');
        if($conn->connect_error) die('Connection failed');
        $sql = "SELECT
            mc_type AS 'Machine Type',
            m_name AS 'Name',
            brand AS 'Brand',
            model AS 'Model',
            id AS 'ID',
            size AS 'Size',
            mc_serial AS 'Serial Number',
            c_box AS 'Control Box Number',
            mc_status AS 'Machine Status',
            current_location AS 'Location',
            in_date AS 'Machine In Date',
            pout_date AS 'Proposed Out Date',
            supplier AS 'Supplier',
            duration AS 'Duration',
            rent AS 'Rent',
            in_gpass AS 'IN Gate Pass',
            out_gpass AS 'OUT Gate Pass',
            invoice AS 'Invoice',
            remarks AS 'Remarks',
            actual_out_date AS 'Actual Out Date'
        FROM out_machines
        ORDER BY actual_out_date DESC";
        $res = $conn->query($sql);
        if($res->num_rows > 0) {
            while($row = $res->fetch_assoc()) {
                echo '<tr>';
                foreach($row as $cell) {
                    echo '<td>' . htmlspecialchars($cell) . '</td>';
                }
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="20">No records found</td></tr>';
        }
        $conn->close();
        ?>
    </table>
</div>
    <script>
            document.getElementById("machineName").addEventListener("input", function() {
            var input, filter, table, tr, td, i, txtValue;
            input = document.getElementById("machineName");
            filter = input.value.toUpperCase();
            table = document.getElementById("machinesTable");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                td = tr[i].getElementsByTagName("td")[1]; // Column index 1 for Brand
                if (td) {
                    txtValue = td.textContent || td.innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }
            }
        });
            document.getElementById("machineId").addEventListener("input", function() {
                var input, filter, table, tr, td, i, txtValue;
                input = document.getElementById("machineId");
                filter = input.value.toUpperCase();
                table = document.getElementById("machinesTable");
                tr = table.getElementsByTagName("tr");

                for (i = 1; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td")[4]; // Column index 1 for Brand
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            });
            document.getElementById("serialNo").addEventListener("input", function() {
                var input, filter, table, tr, td, i, txtValue;
                input = document.getElementById("serialNo");
                filter = input.value.toUpperCase();
                table = document.getElementById("machinesTable");
                tr = table.getElementsByTagName("tr");

                for (i = 1; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td")[6]; // Column index 1 for Brand
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            });
            document.getElementById("machineSupplier").addEventListener("input", function() {
                var input, filter, table, tr, td, i, txtValue;
                input = document.getElementById("machineSupplier");
                filter = input.value.toUpperCase();
                table = document.getElementById("machinesTable");
                tr = table.getElementsByTagName("tr");

                for (i = 1; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td")[12]; // Column index 1 for Brand
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            });
            function addFilter(inputId, colIndex) {
                document.getElementById(inputId).addEventListener('input', function() {
                    var filter = this.value.toUpperCase();
                    var tr = document.getElementById('machinesTable').getElementsByTagName('tr');
                    for (var i = 1; i < tr.length; i++) {
                        var td = tr[i].getElementsByTagName('td')[colIndex];
                        tr[i].style.display = (td && td.textContent.toUpperCase().indexOf(filter) > -1) ? '' : 'none';
                    }
                });
            }
            addFilter('machineName', 1);
            addFilter('machineId', 4);
            addFilter('serialNo', 6);
            addFilter('machineSupplier', 12);
        </script>
    </body>
</html>