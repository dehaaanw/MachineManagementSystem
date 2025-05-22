<?php
$file = __DIR__ . '/tmp_sessions/test.txt';
if (file_put_contents($file, "Testing write access")) {
    echo "Directory is writable.";
} else {
    echo "Directory is not writable.";
}
?>