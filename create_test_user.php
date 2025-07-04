<?php
// Generate password hash for test user
$password = 'TestPass123!';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password hash for 'TestPass123!': $hash\n";
echo "Hash length: " . strlen($hash) . "\n";

// Test verification
if (password_verify($password, $hash)) {
    echo "Password verification: SUCCESS\n";
} else {
    echo "Password verification: FAILED\n";
}
?>
