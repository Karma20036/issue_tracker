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

$company_name = $_POST['company_name'];
$physical_address = $_POST['physical_address'];
$postal_address = $_POST['postal_address'];
$email = $_POST['email'];
$mobile_number = $_POST['mobile_number'];

// Handle logo upload
$target_dir = "uploads/";
$logo = $_FILES['logo']['name'];
$target_file = $target_dir . basename($logo);
$imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
$uploadOk = 1;

// Check if image file is an actual image or fake image
$check = getimagesize($_FILES['logo']['tmp_name']);
if ($check !== false) {
    $uploadOk = 1;
} else {
    echo "File is not an image.";
    $uploadOk = 0;
}

// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists.";
    $uploadOk = 0;
}

// Check file size
if ($_FILES['logo']['size'] > 500000) { // 500KB limit
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}

// Allow certain file formats
if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}

// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// If everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES['logo']['tmp_name'], $target_file)) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO organizations (company_name, physical_address, postal_address, email, mobile_number, logo) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $company_name, $physical_address, $postal_address, $email, $mobile_number, $target_file);

        if ($stmt->execute()) {
            echo "New organization created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
       
    } else {
        echo "Sorry, there was an error uploading your file.";
    }
}

$conn->close();
?>
