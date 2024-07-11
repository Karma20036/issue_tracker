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

$first_name = $conn->real_escape_string($_POST['first_name']);
$surname = $conn->real_escape_string($_POST['surname']);
$email = $conn->real_escape_string($_POST['email']);
$mobile_number = $conn->real_escape_string($_POST['mobile_number']);
$password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT);
$company_name = $_SESSION['company_name'];

$sql = "INSERT INTO users (first_name, surname, email, mobile_number, password, company_name, role, active)
        VALUES ('$first_name', '$surname', '$email', '$mobile_number', '$password', '$company_name', 'Customer', TRUE)";

if ($conn->query($sql) === TRUE) {
    echo "New user created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
header('Location: org_admin_dashboard.php');
exit();
?>
