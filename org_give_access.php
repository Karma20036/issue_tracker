<?php
session_start();

// Check if the user is logged in and is an Organization Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Organization Admin') {
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

$id = $conn->real_escape_string($_POST['id']);
$company_name = $_SESSION['company_name'];

// Ensure the user being given access belongs to the same company
$check_sql = "SELECT company_name FROM users WHERE id='$id'";
$check_result = $conn->query($check_sql);
if ($check_result->num_rows > 0) {
    $row = $check_result->fetch_assoc();
    if ($row['company_name'] === $company_name) {
        $sql = "UPDATE users SET active = TRUE WHERE id = '$id'";
        if ($conn->query($sql) === TRUE) {
            echo "User access granted successfully";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        echo "You cannot give access to this user.";
    }
} else {
    echo "User not found.";
}

$conn->close();
header('Location: org_admin_dashboard.php');
exit();
?>
