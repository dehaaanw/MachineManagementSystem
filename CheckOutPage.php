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
            body{
                background-image: url(Background.jpg);
                background-repeat: no-repeat;
                background-attachment: fixed;
                background-size: cover;
                margin : 0;
            }
            table {
            width: 100%;
            table-layout: fixed;
        }
        th, td {
            padding: 1px;
            text-align: center;
            border: 1px solid #ccc; /* Horizontal and vertical borders */
        }
        td{
            font-size: 13px;
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
            font-size: 14px;
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
        <script src="xlsx.full.min.js"></script>
        <script>
            
            // Function to get and return the current date
            function getCurrentDate() {
                return new Date().toLocaleDateString();
            }
            // Function to update the div element with the current date
            function updateCurrentDate() {
                document.getElementById("current_date").innerText = getCurrentDate();
            }
            // Call the updateCurrentDate function when the page loads
            window.onload = updateCurrentDate;

            function submitForm(event) {
            event.preventDefault(); // Prevent default form submission
            var formData = new FormData(document.getElementById("copy-form"));
            
            // Send form data to the server using AJAX
            fetch("OutMachineTable.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // Display alert with the response from the server
                alert(data);
            })
            .catch(error => {
                console.error("Error:", error);
            });
        }
        //Function to download the Excel sheet
        function downloadExcel() {
            // Select the table
            var table = document.getElementById('machinesTable');
            
            // Convert the table to a workbook
            var workbook = XLSX.utils.table_to_book(table, { sheet: "Sheet1" });
            
            // Create a binary string representation of the workbook
            var wbout = XLSX.write(workbook, { bookType: 'xlsx', type: 'binary' });
            
            // Function to convert the binary string to an octet stream
            function s2ab(s) {
                var buf = new ArrayBuffer(s.length);
                var view = new Uint8Array(buf);
                for (var i = 0; i < s.length; i++) {
                    view[i] = s.charCodeAt(i) & 0xFF;
                }
                return buf;
            }
            
            // Create a Blob object from the octet stream and trigger the download
            var blob = new Blob([s2ab(wbout)], { type: "application/octet-stream" });
            var link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'Plant IN Machines.xlsx';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        document.addEventListener("DOMContentLoaded", function () {
                populateFilters();
            });

            function populateFilters() {
                var table = document.getElementById("machinesTable");
                var headers = table.getElementsByTagName("th");
                for (let i = 0; i < headers.length; i++) {
                    if (headers[i].getElementsByTagName("select").length > 0) {
                        var uniqueValues = getUniqueColumnValues(i);
                        addOptionsToFilter(headers[i].getElementsByTagName("select")[0], uniqueValues);
                    }
                }
            }

            function getUniqueColumnValues(columnIndex) {
                var table = document.getElementById("machinesTable");
                var tr = table.getElementsByTagName("tr");
                var uniqueValues = new Set();
                for (let i = 1; i < tr.length; i++) {
                    var td = tr[i].getElementsByTagName("td")[columnIndex];
                    if (td) {
                        uniqueValues.add(td.textContent || td.innerText);
                    }
                }
                return Array.from(uniqueValues).sort();
            }

            function addOptionsToFilter(selectElement, values) {
                values.forEach(value => {
                    var option = document.createElement("option");
                    option.value = value;
                    option.text = value;
                    selectElement.add(option);
                });
            }

            function filterTable(columnIndex, selectId) {
                var select, filter, table, tr, td, i, txtValue;
                select = document.getElementById(selectId);
                filter = select.value.toUpperCase();
                table = document.getElementById("machinesTable");
                tr = table.getElementsByTagName("tr");

                for (i = 1; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td")[columnIndex];
                    if (td) {
                        txtValue = td.textContent || td.innerText;
                        if (filter === "" || txtValue.toUpperCase() === filter) {
                            tr[i].style.display = "";
                        } else {
                            tr[i].style.display = "none";
                        }
                    }
                }
            }
        </script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <meta charset="utf-8">
    </head>
    <body>
        <nav class="navbar navbar-dark bg-dark navbar-expand-sm">
            <a href="MachineUtilization.php"><img src="maslogo.png" alt="Logo" style="width:70px; margin: .5rem 2rem;" ></a>
             <button class="navbar-toggler" data-toggler="collapse" data-target="#menu"></button>
             <ul class="navbar-nav">
                 <li class="nav-item">
                 <a href="MachineCheckIn.php" class="nav-link">Machine Check-In</a>
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
                 <a href="CheckOutPage.php" class="nav-link active">Machine Check-Out</a>
                 </li>
                  <li class="nav-item">
                 <a href="LogoutPage.php" class="nav-link " style="color: #ff4f4b;">Logout</a>
                 </li>
             </ul>
         </nav>
         <br/>
         <h1 style="text-align:center; color:#3C4142;"><span style="color:#ff4f4b;">Check-Out</span> Machine.</h1>
         <!--<form action="OutMachineTable.php" method="post" id="copy-form">-->
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-row">
                <h2 class="col-md-2 mb-3">Today's Date is:</h2>
                <h2 class="col-md-2 mb-3" id="current_date" style="color: #313dbe;">      
                </h2>
            </div>
            <div class="form-row">
                <div class="col-md-2 mb-3">
                    <label for="valitxt03">ID Code <span style="color:#ff4f4b;"><b>*</b></span></label>
                    <input type="text" class="form-control" name="id" id="valitxt03" placeholder="ID Code" required autofocus>
                </div>
            </div>
            <div class="col md-1 mb-3 align-content-end">
                <input type="submit" name="save" class="btn btn-info" value="Check-Out Machine">
                <a href="OutMachineTable.php" class="btn" style="background-color:lightgray;"> Out Machine Table </a>
            </div>
        </form>
        <input type = "button" onclick="downloadExcel()" class="btn btn-info" value="Download Excel">
        <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                // Retrieve idCode from the form submission
                $id = $_POST['id'];
                
                date_default_timezone_set('Asia/Kolkata');
                // Get the current date
                $actual_out_date = date("Y-m-d");

                // Connect to the database
                $servername = "localhost"; // Change this if your database is hosted elsewhere
                $username = "gciwxkmy_matrixeng"; // Change this to your MySQL username
                $password = "Active@2024"; // Change this to your MySQL password
                $dbname = "gciwxkmy_WPESQ"; // Change this to your MySQL database name
                
                $conn = new mysqli($servername, $username, $password, $dbname);
            
                // Check connection
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }

                // Check if the idCode already exists in the 'out_machines' table
                $sqlCheckOutMachines = "SELECT id FROM out_machines WHERE id = ?";
                $stmtCheckOutMachines = $conn->prepare($sqlCheckOutMachines);
                $stmtCheckOutMachines->bind_param("s", $id);
                $stmtCheckOutMachines->execute();
                $resultCheckOutMachines = $stmtCheckOutMachines->get_result();
                if ($resultCheckOutMachines->num_rows > 0) {
                    die("Error: ID Code already exists in 'out_machines' table");
                    /*echo '<script language="javascript">';
                    echo 'alert("ID Code already exists in out_machines table")';
                    echo '</script>';*/
                } else
                $stmtCheckOutMachines->close();

                // Check if the idCode exists in the current table
                $sqlCheckCurrentTable = "SELECT id FROM mc_database WHERE id = ?";
                $stmtCheckCurrentTable = $conn->prepare($sqlCheckCurrentTable);
                $stmtCheckCurrentTable->bind_param("s", $id);
                $stmtCheckCurrentTable->execute();
                $resultCheckCurrentTable = $stmtCheckCurrentTable->get_result();
                if ($resultCheckCurrentTable->num_rows === 0) {
                    die("Error: ID Code does not exist in current table");
                    /*echo '<script language="javascript">';
                    echo 'alert("ID Code does not exist in the current table")';
                    echo '</script>';*/
                } else
                $stmtCheckCurrentTable->close();

                // Begin a transaction
                $conn->begin_transaction();

                // Copy the row to the 'out_machines' table
                $sqlCopy = "INSERT INTO out_machines SELECT *, ? FROM mc_database WHERE id = ?";
                $stmtCopy = $conn->prepare($sqlCopy);
                $stmtCopy->bind_param("ss", $actual_out_date , $id);

                // Execute the copy operation
                if ($stmtCopy->execute() === TRUE) {
                    // Delete the row from the current table
                    $sqlDelete = "DELETE FROM mc_database WHERE id = ?";
                    $stmtDelete = $conn->prepare($sqlDelete);
                    $stmtDelete->bind_param("s", $id);

                    // Execute the delete operation
                    if ($stmtDelete->execute() === TRUE) {
                        // Commit the transaction
                        $conn->commit();
                        echo '<script type="text/javascript">window.onload = function () { alert("Machine removed from the current table successfully"); }</script>';
                        //echo "Machine removed from the current table successfully";
                    } else {
                        // Rollback the transaction if delete operation fails
                        $conn->rollback();
                        echo '<script type="text/javascript">window.onload = function () { alert(Error deleting record: . $conn->error); }</script>';
                        echo "Error deleting record: " . $conn->error;
                    }
                } else {
                    // Rollback the transaction if copy operation fails
                    $conn->rollback();
                    echo "Error copying record: " . $conn->error;
                }

                // Close connections
                $stmtCopy->close();
                $stmtDelete->close();
                $conn->close();
            }
        ?>
        <div class="form-row">
        <div class="col-md-2 mb-3">
        <input type="text" id="machineName" placeholder="Search by Machine Name..." class="form-control">
        </div>
        <div class="col-md-2 mb-3">
        <input type="text" id="machineId" placeholder="Search by Machine ID..." class="form-control">
        </div>
        <div class="col-md-2 mb-3">
        <input type="text" id="machineSupplier" placeholder="Search by Machine Supplier..." class="form-control">
        </div>
        <div class="col-md-2 mb-3">
        <input type="text" id="machineLocation" placeholder="Search by Machine Location..." class="form-control">
        </div>
        <div class="col-md-2 mb-3">
        <input type="text" id="machineSerial" placeholder="Search by Machine Serial..." class="form-control">
        </div>
        <div class="col-md-2 mb-3">
        <input type="text" id="machinegPass" placeholder="Search by IN Gate Pass..." class="form-control">
        </div>
        </div>
        <h2 class="col-md-100 mb-100" style="color:#3C4142; text-align:center;">Currently Rented Machines</h2>
        <table class="" id="machinesTable">
        <tr>
                <th>
                    Machine Type
                    <br>
                    <select id="filter_mc_type" onchange="filterTable(0, 'filter_mc_type')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Name
                    <br>
                    <select id="filter_m_name" onchange="filterTable(1, 'filter_m_name')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Brand
                    <br>
                    <select id="filter_brand" onchange="filterTable(2, 'filter_brand')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Model
                    <br>
                    <select id="filter_model" onchange="filterTable(3, 'filter_model')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    ID
                    <br>
                    <select id="filter_id" onchange="filterTable(4, 'filter_id')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Size
                    <br>
                    <select id="filter_size" onchange="filterTable(5, 'filter_size')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Serial Number
                    <br>
                    <select id="filter_mc_serial" onchange="filterTable(6, 'filter_mc_serial')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Control Box Number
                    <br>
                    <select id="filter_c_box" onchange="filterTable(7, 'filter_c_box')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Machine Status
                    <br>
                    <select id="filter_mc_status" onchange="filterTable(8, 'filter_mc_status')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Location
                    <br>
                    <select id="filter_current_location" onchange="filterTable(9, 'filter_current_location')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Machine In Date
                    <br>
                    <select id="filter_in_date" onchange="filterTable(10, 'filter_in_date')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Proposed Out Date
                    <br>
                    <select id="filter_pout_date" onchange="filterTable(11, 'filter_pout_date')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Supplier
                    <br>
                    <select id="filter_supplier" onchange="filterTable(12, 'filter_supplier')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Duration
                    <br>
                    <select id="filter_duration" onchange="filterTable(13, 'filter_duration')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Rent
                    <br>
                    <select id="filter_rent" onchange="filterTable(14, 'filter_rent')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    IN Gate Pass
                    <br>
                    <select id="filter_in_gpass" onchange="filterTable(15, 'filter_in_gpass')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>
                    Remarks
                    <br>
                    <select id="filter_remarks" onchange="filterTable(18, 'filter_remarks')">
                        <option value="">All</option>
                    </select>
                </th>
                <th>Print Barcode</th>
                <th>Edit</th>
                <th>
                <button class="bulk-print-button" onclick="bulkPrint()">Bulk Print</button>
                </th>
            </tr>
        <?php

        // Connect to the database
        $servername = "localhost"; // Change this if your database is hosted elsewhere
        $username = "gciwxkmy_matrixeng"; // Change this to your MySQL username
        $password = "Active@2024"; // Change this to your MySQL password
        $dbname = "gciwxkmy_WPESQ"; // Change this to your MySQL database name
        
        $conn = new mysqli($servername, $username, $password, $dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Fetch data from 'out_machines' table
        $sqlOutMachines = "SELECT * FROM mc_database ORDER BY in_date DESC";
        $resultOutMachines = $conn->query($sqlOutMachines);

        if ($resultOutMachines->num_rows > 0) {
            while ($row = $resultOutMachines->fetch_assoc()) {
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
                echo "<td>" . $row["remarks"] . "</td>";
                //Print barcode column
                echo "<td><a href='GenerateBarcode.php?id=" . $row['id'] . "&mc_serial=" . urlencode($row['mc_serial']) . "&supplier=" . urlencode($row['supplier']) . "&in_date=" . urlencode($row['in_date']) . "' target='_blank'>Print Barcode</a></td>";
                echo "<td><button onclick='openModal(".json_encode($row).")'>Edit</button></td>";
                echo "<td><input type='checkbox' class='machine-checkbox' data-id='{$row['id']}' data-serial='{$row['mc_serial']}' data-supplier='{$row['supplier']}' data-indate='{$row['in_date']}'></td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No records found</td></tr>";
        }
        ?>
        </table>

        <script>
        function bulkPrint() {
        // Collect the machine IDs of selected checkboxes
        var selectedIds = [];
        var selectedSerials =[];
        var selectedSuppliers = [];
        var selectedInDates = [];
        var checkboxes = document.querySelectorAll('.machine-checkbox:checked');
        
        checkboxes.forEach(function(checkbox) {
            selectedIds.push(checkbox.getAttribute('data-id'));
            selectedSerials.push(checkbox.getAttribute('data-serial'));
            selectedSuppliers.push(checkbox.getAttribute('data-supplier'));
            selectedInDates.push(checkbox.getAttribute('data-indate'));

        });

        if (selectedIds.length > 0) {
            // Open a new window to print all selected barcodes
            window.open("GenerateBarcode_Bulk.php?ids=" + encodeURIComponent(selectedIds.join(','))
            + "&mc_serials=" + encodeURIComponent(selectedSerials.join(','))+ 
            "&suppliers=" + encodeURIComponent(selectedSuppliers.join(',')) + 
            "&in_dates=" + encodeURIComponent(selectedInDates.join(',')), 
            "_blank");
        } else {
            alert("Please select at least one machine to print.");
        }
        }
        </script>

        <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Edit Machine Data</h2>
            <form id="editForm">
                <input type="hidden" id="editId" name="id">
                <label for="editMachineType">Machine Type</label><br>
                <select name="mc_type" id ="editMachineType"> 
                    <option value="Matrix Owned Machine">Matrix Owned Machine</option>
                    <option value="Rental Machine">Rental Machine</option>
                    <option value="On Loan In Machines">On Loan In Machines</option>
                    <option value="On Loan Out Machine">On Loan Out Machine</option>
                </select><br><br>
                <label for="editMachineName">Machine Name</label><br>
                <input type="text" id="editMachineName" name="m_name"><br><br>
                <label for="editMachineBrand">Brand</label><br>
                <input type="text" id="editMachineBrand" name="brand"><br><br>
                <label for="editMachineModel">Model</label><br>
                <input type="text" id="editMachineModel" name="model"><br><br>
                <label for="editMachineSize">Size</label><br>
                <input type="text" id="editMachineSize" name="size"><br><br>
                <label for="editMachineSerialNo">Serial No</label><br>
                <input type="text" id="editMachineSerialNo" name="mc_serial"><br><br>
                <label for="editMachineCBox">Control Box No</label><br>
                <input type="text" id="editMachineCBox" name="c_box"><br><br>
                <label for="editMachineStatus">Status</label><br>
                <select name="mc_status" id ="editMachineStatus"> 
                    <option value="Production">Production</option>
                    <option value="Breakdown">Breakdown</option>
                    <option value="Machine Yard">Machine Yard</option>
                    <option value="On-Loan OUT">On-Loan OUT</option>
                </select><br><br>
                <label for="editMachineCurrentLocation">Location</label><br>
                <select name="current_location" id="editMachineCurrentLocation">
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
                </select><br><br>
                <label for="editMachineInDate">In Date</label><br>
                <input type="date" id="editMachineInDate" name="in_date"><br><br>
                <label for="editMachinePOutDate">Proposed Out Date</label><br>
                <input type="date" id="editMachinePOutDate" name="pout_date"><br><br>
                <label for="editMachineSupplier">Supplier</label><br>
                <select id="editMachineSupplier" name="supplier">
                <option value="MAS">MAS</option>
                            <option value="MAS Intimo">MAS Intimo</option>
                            <option value="MAS Thurulie">MAS Thurulie</option>
                            <option value="MAS Active">MAS Active</option>
                            <option value="MAS Methliya">MAS Methliya</option>
                            <option value="MAS Unichela">MAS Unichela</option>
                            <option value="MAS Matrix">MAS Matrix</option>
                            <option value="MAS Kreeda">MAS Kreeda</option>
                            <option value="MAS Synergy">MAS Synergy</option>
                            <option value="MAS Contourline">MAS Contourline</option>
                            <option value="MAS Asialine">MAS Asialine</option>
                            <option value="MAS Shadeline">MAS Shadeline</option>
                            <option value="MAS Shadowline">MAS Shadowline</option>
                            <option value="MAS Casualine">MAS Casualine</option>
                            <option value="Dayarathne Holdings (PVT) LTD">Dayarathne Holdings (PVT) LTD</option>
                            <option value="New Dayarathne & Company">New Dayarathne & Company</option>
                            <option value="Able Technologies">Able Technologies</option>
                            <option value="Needle Technologies">Needle Technologies</option>
                            <option value="Vision Machine">Vision Machine</option>
                            <option value="Tiyaan International">Tiyaan International</option>
                            <option value="Orange Sewing Solutions(PVT) LTD">Orange Sewing Solutions(PVT) LTD</option>
                            <option value="Linea Clothing">Linea Clothing</option>
                            <option value="Yess Trading">Yess Trading</option>
                            <option value="Singer Sri Lanka PLC">Singer Sri Lanka PLC</option>
                            <option value="Ceylon Prestige Knitwear">Ceylon Prestige Knitwear</option>
                            <option value="Eastman Suppliers">Eastman Suppliers</option>
                            <option value="Ceylon Knitwear (PVT) LTD">Ceylon Knitwear (PVT) LTD</option>
                            <option value="Perfect Technologies">Perfect Technologies</option>
                            <option value="Chanuthi Enterprises">Chanuthi Enterprises</option>
                            <option value="Nui Mishin Enterprises">Nui Mishin Enterprises</option>
                            <option value="Seybi Trading">Seybi Trading</option>
                            <option value="Navoda Embroidery Designs">Navoda Embroidery Designs</option>
                            <option value="Sithila Machine Center">Sithila Machine Center</option>
                            <option value="Moonlanka Machinery">Moonlanka Machinery</option>
                            <option value="Ruwin">Ruwin</option>
                            <option value="A.K.I">A.K.I</option>
                            <option value="New Aruna Juki">New Aruna Juki</option>
                            <option value="Rovenka Holdings">Rovenka Holdings</option>
                            <option value="Abhisheka Holdings(PVT)LTD">Abhisheka Holdings(PVT)LTD</option>
                            <option value="RMD Technologies">RMD Technologies</option>
                            <option value="Priyantha Enterprise">Priyantha Enterprise</option>
                            <option value="Global Apparel">Global Apparel</option>
                            <option value="Global Apparel Solution">Global Apparel Solution</option>
                            <option value="YMC">YMC</option>
                            <option value="Nulmishine">Nulmishine</option>
                            <option value="Priyasons Sewing Technology">Priyasons Sewing Technology</option>
                            <option value="Bimsara Machinery">Bimsara Machinery</option>
                            <option value="Methul Lanka">Methul Lanka</option>
                            <option value="Orient Sewing Machines">Orient Sewing Machines</option>
                            <option value="Ran Win International (PVT) LTD">Ran Win International (PVT) LTD</option>
                            <option value="Right Engineers">Right Engineers</option>
                            <option value="Tisva Sewing Technology">Tisva Sewing Technology</option>
                            <option value="Ardmel Manufacturing">Ardmel Manufacturing</option>
                            <option value="Chandana Machine Center">Chandana Machine Center</option>
                            <option value="CJ Apparel Solution">CJ Apparel Solution</option>
                            <option value="Dilshan Enterprise">Dilshan Enterprise</option>
                            <option value="KHB Associates">KHB Associates</option>
                            <option value="Lesandu Apparel Solution">Lesandu Apparel Solution</option>
                            <option value="Mindika Enterprises">Mindika Enterprises</option>
                            <option value="Mithma Enterprises">Mithma Enterprises</option>
                            <option value="Matrix Autonomation">Matrix Autonomation</option>
                </select><br><br>
                <label for="editMachineDuration">Duration</label><br>
                <select name="duration" id="editMachineDuration">
                    <option value="" selected>Select Duration</option>
                    <option value="3">3</option> 
                    <option value="6">6</option>
                </select><br><br>
                <label for="editMachineRent">Rent</label><br>
                <input type="text" id="editMachineRent" name="rent"><br><br>
                <label for="editMachineInGPass">In Gate Pass</label><br>
                <input type="text" id="editMachineInGPass" name="in_gpass"><br><br>
                <label for="editMachineOutGPass">Out Gate Pass</label><br>
                <input type="text" id="editMachineOutGPass" name="out_gpass"><br><br>
                <label for="editMachineInvoice">Invoice</label><br>
                <input type="longtext" id="editMachineInvoice" name="invoice"><br><br>
                <label for="editMachineRemarks">Remarks</label><br>
                <input type="text" id="editMachineRemarks" name="remarks"><br><br>
                <button type="button" onclick="saveChanges()">Save</button>
            </form>
            </div>
        </div>
        <script>
            function openModal(row) {
            console.log('Opening modal with row data:', row); // Debugging log
            
            document.getElementById('editId').value = row.id;
            document.getElementById('editMachineType').value = row.mc_type;
            document.getElementById('editMachineName').value = row.m_name;
            document.getElementById('editMachineBrand').value = row.brand;
            document.getElementById('editMachineModel').value = row.model;
            document.getElementById('editMachineSize').value = row.size;
            document.getElementById('editMachineSerialNo').value = row.mc_serial;
            document.getElementById('editMachineCBox').value = row.c_box;
            document.getElementById('editMachineStatus').value = row.mc_status;
            document.getElementById('editMachineCurrentLocation').value = row.current_location;
            document.getElementById('editMachineInDate').value = row.in_date;
            document.getElementById('editMachinePOutDate').value = row.pout_date;
            document.getElementById('editMachineSupplier').value = row.supplier;
            document.getElementById('editMachineDuration').value = row.duration;
            document.getElementById('editMachineRent').value = row.rent;
            document.getElementById('editMachineInGPass').value = row.in_gpass;
            document.getElementById('editMachineOutGPass').value = row.out_gpass;
            document.getElementById('editMachineInvoice').value = row.invoice;
            document.getElementById('editMachineRemarks').value = row.remarks;
            var modal = document.getElementById('editModal');
            if (modal) {
                modal.style.display = "block";
                console.log('Modal set to display: block'); // Debugging log
            } else {
                console.log('Modal not found in the DOM'); // Debugging log
            }
        }

        function closeModal() {
            document.getElementById('editModal').style.display = "none";
        }
        function saveChanges() {
            var id = document.getElementById('editId').value;
            var mc_type = document.getElementById('editMachineType').value;
            var m_name = document.getElementById('editMachineName').value;
            var brand = document.getElementById('editMachineBrand').value;
            var model = document.getElementById('editMachineModel').value;
            var size = document.getElementById('editMachineSize').value;
            var mc_serial = document.getElementById('editMachineSerialNo').value;
            var c_box = document.getElementById('editMachineCBox').value;
            var mc_status = document.getElementById('editMachineStatus').value;
            var current_location = document.getElementById('editMachineCurrentLocation').value;
            var in_date = document.getElementById('editMachineInDate').value;
            var pout_date = document.getElementById('editMachinePOutDate').value;
            var supplier = document.getElementById('editMachineSupplier').value;
            var duration = document.getElementById('editMachineDuration').value;
            var rent = document.getElementById('editMachineRent').value;
            var in_gpass = document.getElementById('editMachineInGPass').value;
            var out_gpass = document.getElementById('editMachineOutGPass').value;
            var invoice = document.getElementById('editMachineInvoice').value;
            var remarks = document.getElementById('editMachineRemarks').value;

            console.log('Saving changes:', {
                id: id,
                mc_type: mc_type,
                m_name: m_name,
                brand: brand,
                model: model,
                size: size,
                mc_serial: mc_serial,
                c_box: c_box,
                mc_status: mc_status,
                current_location: current_location,
                in_date: in_date,
                pout_date: pout_date,
                supplier: supplier,
                duration: duration,
                rent: rent,
                in_gpass: in_gpass,
                out_gpass: out_gpass,
                invoice: invoice,
                remarks: remarks,
            }); // Debugging log

            var xhr = new XMLHttpRequest();
            xhr.open("POST", "update_machine.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function () {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    console.log('Response from server:', xhr.responseText); // Debugging log
                    location.reload();
                }
            };
            var data = "id=" + id + 
            "&mc_type=" + mc_type + 
            "&m_name=" + m_name + 
            "&brand=" + brand +
            "&model=" + model +
            "&size=" + size +
            "&mc_serial=" + mc_serial +
            "&c_box=" + c_box +
            "&mc_status=" + mc_status +
            "&current_location=" + current_location +
            "&in_date=" + in_date +
            "&pout_date=" + pout_date +
            "&supplier=" + supplier +
            "&duration=" + duration +
            "&rent=" + rent +
            "&in_gpass=" + in_gpass +
            "&out_gpass=" + out_gpass +
            "&invoice=" + invoice +
            "&remarks=" + remarks;
            xhr.send(data);
        }
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
            document.getElementById("machineLocation").addEventListener("input", function() {
                var input, filter, table, tr, td, i, txtValue;
                input = document.getElementById("machineLocation");
                filter = input.value.toUpperCase();
                table = document.getElementById("machinesTable");
                tr = table.getElementsByTagName("tr");

                for (i = 1; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td")[9]; // Column index 1 for Brand
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
            document.getElementById("machineSerial").addEventListener("input", function() {
                var input, filter, table, tr, td, i, txtValue;
                input = document.getElementById("machineSerial");
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
            document.getElementById("machinegPass").addEventListener("input", function() {
                var input, filter, table, tr, td, i, txtValue;
                input = document.getElementById("machinegPass");
                filter = input.value.toUpperCase();
                table = document.getElementById("machinesTable");
                tr = table.getElementsByTagName("tr");

                for (i = 1; i < tr.length; i++) {
                    td = tr[i].getElementsByTagName("td")[15]; // Column index 1 for Brand
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

        </script>
    </body>
</html>
<?php
$conn->close();
?>