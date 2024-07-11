<?php
session_start();

// Check if the user is logged in and is a Global Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Global Admin') {
    header('Location: login.html');
    exit();
}

// Check if category name is set and not empty
if (isset($_POST['categoryName']) && !empty($_POST['categoryName'])) {
    $categoryName = $_POST['categoryName'];

    // Database connection (example, adjust with your connection method)
    $servername = "localhost";
    $username = "sam";
    $password = "root";
    $dbname = "issue_tracker";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Prepare SQL statement to insert category into the database
    $sql = "INSERT INTO categories (name) VALUES (?)";
    $stmt = $conn->prepare($sql);

    // Bind parameter and execute statement
    $stmt->bind_param("s", $categoryName);

    if ($stmt->execute()) {
        echo "Category added successfully.";
    } else {
        echo "Error adding category: " . $stmt->error;
    }

    // Close statement and database connection
    $stmt->close();
    $conn->close();
} else {
    echo "Category name is required.";
}
?>
