<?php
session_start();

// Check if the user is logged in and is a Global Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Global Admin') {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Unauthorized"]);
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

$sql = "SELECT
            COUNT(CASE WHEN status = 'Pending' THEN 1 END) AS pending,
            COUNT(CASE WHEN status = 'Assigned' THEN 1 END) AS assigned,
            COUNT(CASE WHEN status = 'Responded' THEN 1 END) AS responded,
            COUNT(CASE WHEN status = 'Resolved' THEN 1 END) AS resolved,
            COUNT(CASE WHEN status = 'Cancelled' THEN 1 END) AS cancelled
        FROM tickets";
$result = $conn->query($sql);

$stats = $result->fetch_assoc();
$conn->close();

header('Content-Type: application/json');
echo json_encode($stats);
?>
