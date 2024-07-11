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

$company_options = "";

// Fetch company names
$sql = "SELECT company_name FROM organizations";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $company_options .= "<option value=\"" . $row['company_name'] . "\">" . $row['company_name'] . "</option>";
    }
} else {
    $company_options = "<option value=\"\">No companies available</option>";
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $surname = $_POST['surname'];
    $company_name = $_POST['company_name'];
    $email = $_POST['email'];
    $mobile_number = $_POST['mobile_number'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if the organization exists
    $org_check_sql = "SELECT company_name FROM organizations WHERE company_name='$company_name'";
    $org_check_result = $conn->query($org_check_sql);

    if ($org_check_result->num_rows > 0) {
        $sql = "INSERT INTO users (first_name, surname, company_name, email, mobile_number, password, role)
                VALUES ('$first_name', '$surname', '$company_name', '$email', '$mobile_number', '$password', '$role')";

        if ($conn->query($sql) === TRUE) {
            $message = "New user created successfully";
        } else {
            $message = "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        $message = "Organization does not exist.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="styles.css">
    <script defer src="scripts.js"></script>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h2>Sign Up</h2>
            <?php if (isset($message)): ?>
                <p><?php echo $message; ?></p>
            <?php endif; ?>
            <form id="signupForm" action="signup.php" method="POST">
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="surname" placeholder="Surname" required>
                <select name="company_name" required>
                    <option value="">Select Company</option>
                    <?php echo $company_options; ?>
                </select>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="text" name="mobile_number" placeholder="Mobile Number" required>
                <input type="password" name="password" placeholder="Password" required>
                <select name="role" required>
                    <option value="Customer" selected>Customer</option>
                    <option value="Organization Admin" disabled>Organization Admin</option>
                    <option value="Global Admin" disabled>Global Admin</option>
                </select>
                <button type="submit">Sign Up</button>
            </form>
            <p>Already have an account? <a href="login.html">Log in</a></p>
        </div>
    </div>
</body>
</html>
