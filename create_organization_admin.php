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

// Sanitize input
$first_name = $conn->real_escape_string($_POST['first_name']);
$surname = $conn->real_escape_string($_POST['surname']);
$company_name = $conn->real_escape_string($_POST['company_name']);
$email = $conn->real_escape_string($_POST['email']);
$mobile_number = $conn->real_escape_string($_POST['mobile_number']);
$password = password_hash('defaultpassword', PASSWORD_DEFAULT); // Use a default password and recommend changing it later

// Check if email already exists
$sql = "SELECT email FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "Email already exists. Please use a different email.";
} else {
    // Insert new organization admin into the users table
    $sql = "INSERT INTO users (first_name, surname, company_name, email, mobile_number, password, role)
            VALUES ('$first_name', '$surname', '$company_name', '$email', '$mobile_number', '$password', 'Organization Admin')";

    if ($conn->query($sql) === TRUE) {
        echo "New organization admin created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
