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

$admin_id = $_SESSION['user_id'];
$ticket_id = $_POST['ticket_id'];
$response = $_POST['response'];
$attachment = '';

// Handle file upload
if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES['attachment']['name']);
    
    // Move uploaded file to the target directory
    if (move_uploaded_file($_FILES['attachment']['tmp_name'], $target_file)) {
        $attachment = basename($_FILES['attachment']['name']);
    } else {
        echo "Sorry, there was an error uploading your file.";
        exit();
    }
}

// Prepare SQL statement using prepared statements to prevent SQL injection
$sql = "INSERT INTO responses (ticket_id, admin_id, response, attachment, created_at) VALUES (?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    echo "Error preparing statement: " . $conn->error;
    exit();
}

// Bind parameters and execute the statement
$stmt->bind_param("iiss", $ticket_id, $admin_id, $response, $attachment);

if ($stmt->execute()) {
    // Update ticket status to "Responded"
    $update_ticket_sql = "UPDATE tickets SET status = 'Responded' WHERE id = ?";
    $update_stmt = $conn->prepare($update_ticket_sql);
    $update_stmt->bind_param("i", $ticket_id);
    $update_stmt->execute();
    $update_stmt->close();

    // Redirect to global admin dashboard on successful response
    header('Location: global_admin_dashboard.php');
    exit();
} else {
    echo "Error: " . $stmt->error;
}

// Close statement and connection
$stmt->close();
$conn->close();
?>
