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

// Query to get all users
$sql = "SELECT id, first_name, surname, email, role FROM users";
$result = $conn->query($sql);

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$conn->close();

header('Content-Type: application/json');
echo json_encode($users);
?>
