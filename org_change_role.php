<?php
session_start();

// Check if the user is logged in and is an Organization Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Organization Admin') {
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

$id = $_POST['id'];
$role = $_POST['role'];

// Update user role in the database
$sql = "UPDATE users SET role = ? WHERE id = ? AND company_name = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sds", $role, $id, $_SESSION['company_name']);

if ($stmt->execute()) {
    header('Location: org_admin_dashboard.php');
} else {
    echo "Error updating record: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
