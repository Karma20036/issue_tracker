<?php
session_start();

// Check if the user is logged in and is an Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Global Admin') {
    echo json_encode(['error' => 'Unauthorized access.']);
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
    echo json_encode(['error' => 'Connection failed: ' . $conn->connect_error]);
    exit();
}

$ticket_id = isset($_GET['ticket_id']) ? intval($_GET['ticket_id']) : 0;

$sql = "SELECT user_id, resolved, satisfaction, comments, created_at FROM feedback WHERE ticket_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $ticket_id);
$stmt->execute();
$result = $stmt->get_result();

$feedback = [];
while ($row = $result->fetch_assoc()) {
    $feedback[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($feedback);
?>
