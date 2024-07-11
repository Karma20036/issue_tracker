<?php
$servername = "localhost";
$username = "sam";
$password = "root";
$dbname = "issue_tracker";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT id, password, role, active, company_name FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['active'] && password_verify($password, $row['password'])) {
        session_start();
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['role'] = $row['role'];
        $_SESSION['company_name'] = $row['company_name']; // Ensure company_name is set
        if ($row['role'] == 'Customer') {
            header("Location: customer_dashboard.php");
        } elseif ($row['role'] == 'Organization Admin') {
            header("Location: org_admin_dashboard.php");
        } elseif ($row['role'] == 'Global Admin') {
            header("Location: global_admin_dashboard.php");
        }
        exit();
    } else {
        echo $row['active'] ? "Invalid password" : "Your account is inactive. Please contact the administrator.";
    }
} else {
    echo "No user found with this email";
}

$conn->close();
?>
