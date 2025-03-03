<?php
$file_path = __DIR__ . "/../debug_test.txt";
$log_message = "Test file writing on " . date('Y-m-d H:i:s') . "\n";

if (file_put_contents($file_path, $log_message, FILE_APPEND)) {
    echo "✅ File written successfully: " . $file_path;
} else {
    echo "❌ Failed to write file";
}
echo 'filename  ' . bin2hex(random_bytes(32));

?>

