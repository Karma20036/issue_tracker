<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$servername = "localhost";
$username = "sam";
$password = "root";
$dbname = "issue_tracker";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$ticket_id = $conn->real_escape_string($_POST['ticket_id']);
$category = $conn->real_escape_string($_POST['category']);
$description = $conn->real_escape_string($_POST['description']);
$user_id = $_SESSION['user_id'];

// Check if a new attachment was uploaded
$attachment = null;
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    $attachment = $upload_dir . basename($_FILES['attachment']['name']);
    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment)) {
        die("Failed to upload attachment.");
    }
}

// Update ticket details
$sql = "UPDATE tickets SET category='$category', description='$description'";

if ($attachment) {
    $sql .= ", attachment='$attachment'";
}

$sql .= " WHERE id='$ticket_id' AND user_id='$user_id'";

if ($conn->query($sql) === TRUE) {
    echo "Ticket updated successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
header('Location: customer_dashboard.php');
exit();
?>
