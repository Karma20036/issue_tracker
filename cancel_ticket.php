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
$reason = $conn->real_escape_string($_POST['reason']);
$user_id = $_SESSION['user_id'];

$sql = "UPDATE tickets SET status='Cancelled', cancellation_reason='$reason' WHERE id='$ticket_id' AND user_id='$user_id'";

if ($conn->query($sql) === TRUE) {
    echo "Ticket cancelled successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
header('Location: customer_dashboard.php');
exit();
?>
