<?php
session_start();

// Check if the user is logged in and is a Global Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Global Admin') {
    header('Location: login.html');
    exit();
}

// Check if category ID is set and not empty
if (isset($_POST['categoryId']) && !empty($_POST['categoryId'])) {
    $categoryId = $_POST['categoryId'];

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

    // Prepare SQL statement to delete category from the database
    $sql = "DELETE FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);

    // Bind parameter and execute statement
    $stmt->bind_param("i", $categoryId);

    if ($stmt->execute()) {
        echo "Category deleted successfully.";
    } else {
        echo "Error deleting category: " . $stmt->error;
    }

    // Close statement and database connection
    $stmt->close();
    $conn->close();
} else {
    echo "Category ID is required.";
}
?>
