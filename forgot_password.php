<?php
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

$email = $_POST['email'];
$mobile_number = $_POST['mobile_number'];

$sql = "SELECT id FROM users WHERE email='$email' AND mobile_number='$mobile_number'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Assuming only one user will match the email and mobile number
    $user = $result->fetch_assoc();
    $user_id = $user['id'];

    // Redirect to reset_password.html with user ID as a query parameter
    header("Location: reset_password.php?user_id=$user_id");
} else {
    echo "Email or mobile number is incorrect.";
}

$conn->close();
?>
