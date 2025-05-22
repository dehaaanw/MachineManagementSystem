<?php
// Connect to the database
$servername = "localhost"; // Change this if your database is hosted elsewhere
$username = "gciwxkmy_matrixeng"; // Change this to your MySQL username
$password = "Active@2024"; // Change this to your MySQL password
$dbname = "gciwxkmy_WPESQ"; // Change this to your MySQL database name
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update data in database
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $mc_type = $_POST['mc_type'];
    $m_name = $_POST['m_name'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $size = $_POST['size'];
    $mc_serial = $_POST['mc_serial'];
    $c_box = $_POST['c_box'];
    $mc_status = $_POST['mc_status'];
    $current_location = $_POST['current_location'];
    $in_date = $_POST['in_date'];
    $pout_date = $_POST['pout_date'];
    $supplier = $_POST['supplier'];
    $duration = $_POST['duration'];
    $rent = $_POST['rent'];
    $in_gpass = $_POST['in_gpass'];
    $out_gpass = $_POST['out_gpass'];
    $invoice = $_POST['invoice'];
    $remarks = $_POST['remarks'];

    $sql = "UPDATE mc_database SET 
    mc_type=?, 
    m_name=?, 
    brand=?, 
    model=?, 
    size=?, 
    mc_serial=?, 
    c_box=?, 
    mc_status=?, 
    current_location=?, 
    in_date=?, 
    pout_date=?, 
    supplier=?, 
    duration=?, 
    rent=?, 
    in_gpass=?, 
    out_gpass=?, 
    invoice=?,     
    remarks=?
    WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssssssssssssss", $mc_type, $m_name, $brand, $model, $size, $mc_serial, $c_box, $mc_status, $current_location, $in_date, $pout_date, $supplier, $duration, $rent, $in_gpass, $out_gpass, $invoice, $remarks, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Success";
    } else {
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
