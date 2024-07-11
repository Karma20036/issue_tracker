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

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_id = $_POST['ticket_id'];
    $admin_id = $_POST['admin_id'];

    // Update the ticket with the assigned admin
    $sql = "UPDATE tickets SET assigned_admin = ?, status = 'Assigned' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $admin_id, $ticket_id);

    if ($stmt->execute()) {
        echo "Ticket assigned successfully.";
        // Redirect back to the dashboard or appropriate page
        header('Location: global_admin_dashboard.php');
        exit();
    } else {
        echo "Error assigning ticket: " . $conn->error;
    }

    $stmt->close();
}

$conn->close();
?>
