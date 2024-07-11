<?php
header('Content-Type: application/json');

// Database connection settings
$servername = "localhost";
$username = "sam";
$password = "root";
$dbname = "issue_tracker";


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Check if the id parameter is provided
if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'User ID not provided']);
    $conn->close();
    exit();
}

$user_id = $_GET['id'];

// Prepare and execute the query to fetch revoke reason
$sql = "SELECT reason, revoked_by, revoked_at FROM revokes WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $revoke_data = $result->fetch_assoc();
    echo json_encode($revoke_data);
} else {
    echo json_encode(['error' => 'No revoke reason found for the provided user ID']);
}

$stmt->close();
$conn->close();
?>
