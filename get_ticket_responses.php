<?php
session_start();
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

if (isset($_GET['ticket_id'])) {
    $ticket_id = intval($_GET['ticket_id']);

    $sql = "SELECT r.*, u.first_name, u.surname FROM responses r JOIN users u ON r.admin_id = u.id WHERE r.ticket_id = $ticket_id";
    $result = $conn->query($sql);

    $responses = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $responses[] = [
                'user' => $row['first_name'] . ' ' . $row['surname'],
                'message' => $row['response'],
                'created_at' => $row['created_at']
            ];
        }
    }

    echo json_encode($responses);
} else {
    echo json_encode(['error' => 'Ticket ID not provided']);
}

$conn->close();
?>
