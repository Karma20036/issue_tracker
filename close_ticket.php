<?php
session_start();

// Check if the user is logged in and is a Global Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Global Admin') {
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
$reason = $_POST['reason'];

// Prepare SQL statement to close the ticket
$sql = "UPDATE tickets SET status = 'Resolved', resolved_at = NOW() WHERE id = ?";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

// Bind parameters and execute the statement
$stmt->bind_param("i", $ticket_id);

if ($stmt->execute()) {
    // Insert the closing reason into the ticket_responses table
    $admin_id = $_SESSION['user_id'];
    $response = "Ticket closed. Reason: " . $reason;
    $attachment = '';

    $insert_response_sql = "INSERT INTO ticket_responses (ticket_id, admin_id, response, attachment, created_at) VALUES (?, ?, ?, ?, NOW())";
    $insert_stmt = $conn->prepare($insert_response_sql);
    $insert_stmt->bind_param("iiss", $ticket_id, $admin_id, $response, $attachment);
    $insert_stmt->execute();
    $insert_stmt->close();

    // Redirect to global admin dashboard on successful ticket closure
    header('Location: global_admin_dashboard.php');
    exit();
} else {
    echo "Error: " . $stmt->error;
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
