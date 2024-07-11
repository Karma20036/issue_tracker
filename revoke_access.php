<?php
session_start();

// Check if the user is logged in and is a Global Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Global Admin') {
    header('Location: login.html');
    exit();
}

$servername = "localhost";
$username = "sam";
$password = "root";
$dbname = "issue_tracker";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sanitize input
$id = $conn->real_escape_string($_POST['id']);
$reason = $conn->real_escape_string($_POST['reason']); // Assume reason is passed from the form
$revoked_by = $_SESSION['user_id'];
$revoked_at = date('Y-m-d H:i:s');

// Begin transaction
$conn->begin_transaction();

try {
    // Revoke user access by setting active to FALSE
    $sql_update_user = "UPDATE users SET active = FALSE WHERE id = '$id'";
    if (!$conn->query($sql_update_user)) {
        throw new Exception("Error updating users table: " . $conn->error);
    }

    // Insert into revokes table
    $sql_insert_revoke = "INSERT INTO revokes (user_id, reason, revoked_by, revoked_at) VALUES ('$id', '$reason', '$revoked_by', '$revoked_at')";
    if (!$conn->query($sql_insert_revoke)) {
        throw new Exception("Error inserting into revokes table: " . $conn->error);
    }

    // Commit transaction
    $conn->commit();
    echo "User access revoked successfully";
} catch (Exception $e) {
    // Rollback transaction in case of error
    $conn->rollback();
    echo "Failed to revoke user access: " . $e->getMessage();
}

$conn->close();
header('Location: global_admin_dashboard.php');
exit();
?>
