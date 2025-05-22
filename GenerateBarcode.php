<?php
if (isset($_GET['id'])  && isset($_GET['mc_serial']) && isset($_GET['supplier']) && isset($_GET['in_date'])) {
    // Retrieve and sanitize input data
    $id = $_GET['id'];
    $mc_serial = $_GET['mc_serial'];
    $supplier = $_GET['supplier'];
    $in_date = $_GET['in_date'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode Generation</title>
    <style>
                .barcode-section {
                    width: 70mm;
                    height: 22mm;
                    box-sizing: border-box;
                    text-align: center;
                    display: flex;
                    flex-direction: column;
                    justify-content: flex-start;
                    margin-bottom: 3mm;
                }

                .details {
                    display: flex;
                    flex-direction: column;
                    justify-content: center;
                    align-items: center;
                    margin-bottom: 0;
                    font-weight: bold; /* Make text bold */
                }

                .details div {
                    white-space: nowrap;
                    font-size: 3.5vw; /* Responsive font size */
                    line-height: 1;
                    margin: 0;
                }

                .barcode-section canvas {
                    width: 100%;
                    height: 10mm; /* Half the previous height */
                    margin: 0;
                    padding: 0;
                }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <script>
        window.onload = function() {
            const id = "<?php echo $id; ?>";
            const canvas = document.querySelector('.barcode-section canvas');
            JsBarcode(canvas, id, {
                format: "CODE128",
                displayValue: false,
                width: 1,
                height: 18
            });
            window.print();
        };
    </script>
</head>
<body>
    <center>
        <div class="barcode-section">
            <div class="details"><?php echo "$mc_serial | $supplier | $in_date"; ?></div>
            <canvas></canvas>
            <div class="details"><?php echo "$id"; ?></div>
        </div>
    </center>
</body>
</html>
<?php
} else {
    // If any of the required parameters (id, mc_serial, supplier, in_date) are not provided
    echo "Required parameters not provided.";
}
?>