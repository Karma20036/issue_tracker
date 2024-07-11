<?php
session_start();

// Check if the user is logged in and is a Customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
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

$ticket_id = $_POST['ticket_id'];
$user_id = $_SESSION['user_id'];  // Assuming user_id is stored in session
$resolved = $_POST['resolved'];
$satisfaction = $_POST['service_rating']; // Corrected from 'satisfaction'
$comments = $_POST['comments'];

$sql = "INSERT INTO feedback (ticket_id, user_id, resolved, satisfaction, comments, created_at) VALUES (?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iisss", $ticket_id, $user_id, $resolved, $satisfaction, $comments);

if ($stmt->execute()) {
    header('Location: customer_dashboard.php');
    exit();
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$stmt->close();
$conn->close();
?>
