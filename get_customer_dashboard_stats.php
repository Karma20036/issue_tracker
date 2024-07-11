<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$servername = "localhost";
$username = "sam";
$password = "root";
$dbname = "issue_tracker";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Connection failed']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Get pending tickets count
$pending_sql = "SELECT COUNT(*) as count FROM tickets WHERE user_id = '$user_id' AND status = 'Pending'";
$pending_result = $conn->query($pending_sql);
$pending_tickets = $pending_result->fetch_assoc()['count'];

// Get total tickets count
$total_sql = "SELECT COUNT(*) as count FROM tickets WHERE user_id = '$user_id'";
$total_result = $conn->query($total_sql);
$total_tickets = $total_result->fetch_assoc()['count'];

// Get resolved tickets count
$resolved_sql = "SELECT COUNT(*) as count FROM tickets WHERE user_id = '$user_id' AND status = 'Resolved'";
$resolved_result = $conn->query($resolved_sql);
$resolved_tickets = $resolved_result->fetch_assoc()['count'];

// Get recent tickets
$recent_sql = "SELECT id, title, status, created_at FROM tickets WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 5";
$recent_result = $conn->query($recent_sql);
$recent_tickets = [];
while ($row = $recent_result->fetch_assoc()) {
    $recent_tickets[] = $row;
}

$stats = [
    'pending_tickets' => $pending_tickets,
    'total_tickets' => $total_tickets,
    'resolved_tickets' => $resolved_tickets,
    'recent_tickets' => $recent_tickets
];

$conn->close();

header('Content-Type: application/json');
echo json_encode($stats);
?>