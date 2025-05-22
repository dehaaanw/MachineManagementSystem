<?php
if (isset($_GET['ids'])  && isset($_GET['mc_serials']) && isset($_GET['suppliers']) && isset($_GET['in_dates'])) {
    // Retrieve and sanitize input data
    $ids = explode(',', htmlspecialchars($_GET['ids']));
    $mc_serials = explode(',', htmlspecialchars($_GET['mc_serials']));
    $suppliers = explode(',', htmlspecialchars($_GET['suppliers']));
    $in_dates = explode(',', htmlspecialchars($_GET['in_dates']));
    
    // Use only the first item from each array
    $id = $ids[0];
    $mc_serial = $mc_serials[0];
    $supplier = $suppliers[0];
    $in_date = $in_dates[0];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Barcode Printing</title>
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
            const ids = <?php echo json_encode($ids); ?>;
            document.querySelectorAll('.barcode-section').forEach(function(container, index) {
                JsBarcode(container.querySelector('canvas'), ids[index], {
                    format: "CODE128",
                    displayValue: false,
                    width: 1,
                    height: 18
                });
            });
            window.print();
        };
    </script>
    </head>
<body>
    <center>
    <?php
    // Loop through each machine ID and name to display their barcodes and additional details
    for ($i = 0; $i < count($ids); $i++) {
        $id = $ids[$i];
        $mc_serial = $mc_serials[$i];
        $supplier = $suppliers[$i];
        $in_date = $in_dates[$i];
        $barcodeUrl = "https://barcode.tec-it.com/barcode.ashx?data=" . urlencode($id) . "&code=Code128&dpi=96";
    ?>
    <div class="barcode-section">
    <div class = "details"><?php echo "$mc_serial | $supplier | $in_date"; ?></div>
    <canvas></canvas>
    <div class="details"><?php echo "$id"; ?></div>
    </div>
    <?php
    }
    ?>
    </center>
</body>
</html>
<?php
} else {
    echo "Required parameters not provided.";
}
?>
