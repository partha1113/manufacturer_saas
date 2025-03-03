<?php
$new_password = "test123"; // Replace with your desired password
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
echo "Hashed Password: " . $hashed_password;

$secretKey = bin2hex(random_bytes(32)); // Generates a 64-character hexadecimal string
echo "Secret Key: " .$secretKey;
?>
