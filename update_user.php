<?php
session_start();

// Check if the user is logged in and is a Global Admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['Global Admin', 'Organization Admin'])) {
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
$first_name = $conn->real_escape_string($_POST['first_name']);
$surname = $conn->real_escape_string($_POST['surname']);
$email = $conn->real_escape_string($_POST['email']);

// Update user details
$sql = "UPDATE users SET first_name='$first_name', surname='$surname', email='$email' WHERE id='$id'";

if ($conn->query($sql) === TRUE) {
    echo "User details updated successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
header('Location: global_admin_dashboard.php');
exit();
?>
