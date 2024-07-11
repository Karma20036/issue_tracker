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

$user_id = $_SESSION['user_id'];
$category = $conn->real_escape_string($_POST['category']);
$description = $conn->real_escape_string($_POST['description']);
$company_name = $_SESSION['company_name'];

$attachment = null;
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = 'uploads/';
    $attachment = $upload_dir . basename($_FILES['attachment']['name']);
    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $attachment)) {
        die("Failed to upload attachment.");
    }
}

$sql = "INSERT INTO tickets (user_id, company_name, category, description, attachment, status, created_at)
        VALUES ('$user_id', '$company_name', '$category', '$description', '$attachment', 'Pending', NOW())";

if ($conn->query($sql) === TRUE) {
    echo "New ticket raised successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
header('Location: customer_dashboard.php');
exit();
?>
