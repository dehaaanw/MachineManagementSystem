<?php
        $servername = "localhost"; // Change this if your database is hosted elsewhere
        $username = "gciwxkmy_matrixeng"; // Change this to your MySQL username
        $password = "Active@2024"; // Change this to your MySQL password
        $dbname = "gciwxkmy_WPESQ"; // Change this to your MySQL database name

        error_log("Received parameters: " . json_encode($_POST));
        // Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the parameters sent via POST request
$mc_type = isset($_POST['mc_type']) ? $_POST['mc_type'] : '';
$mc_status = isset($_POST['mc_status']) ? $_POST['mc_status'] : '';

// Build the WHERE clause dynamically based on the provided parameters
$whereClause = [];
if ($mc_type) {
    $whereClause[] = "mc_type = '" . $conn->real_escape_string($mc_type) . "'";
}
if ($mc_status) {
    $whereClause[] = "mc_status = '" . $conn->real_escape_string($mc_status) . "'";
}

$whereSql = count($whereClause) > 0 ? "WHERE " . implode(" AND ", $whereClause) : "";

// Fetch filtered data
$query = "SELECT mc_type, m_name, brand, model, id, size, mc_serial, c_box, mc_status, current_location, in_date, pout_date, supplier, duration, rent, in_gpass, out_gpass, invoice, remarks FROM mc_database $whereSql";
$result = $conn->query($query);


// Check if the query executed successfully
if ($result === false) {
    die("Query failed: " . $conn->error);
}

// Check if any results were returned
if ($result->num_rows > 0) {
    // Start outputting table rows
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['mc_type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['m_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['brand']) . "</td>";
        echo "<td>" . htmlspecialchars($row['model']) . "</td>";
        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['size']) . "</td>";
        echo "<td>" . htmlspecialchars($row['mc_serial']) . "</td>";
        echo "<td>" . htmlspecialchars($row['c_box']) . "</td>";
        echo "<td>" . htmlspecialchars($row['mc_status']) . "</td>";
        echo "<td>" . htmlspecialchars($row['current_location']) . "</td>";
        echo "<td>" . htmlspecialchars($row['in_date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['pout_date']) . "</td>";
        echo "<td>" . htmlspecialchars($row['supplier']) . "</td>";
        echo "<td>" . htmlspecialchars($row['duration']) . "</td>";
        echo "<td>" . htmlspecialchars($row['rent']) . "</td>";
        echo "<td>" . htmlspecialchars($row['in_gpass']) . "</td>";
        echo "<td>" . htmlspecialchars($row['out_gpass']) . "</td>";
        echo "<td>" . htmlspecialchars($row['invoice']) . "</td>";
        echo "<td>" . htmlspecialchars($row['remarks']) . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3'>No data found</td></tr>";
}

// Close the database connection
$conn->close();
?>
