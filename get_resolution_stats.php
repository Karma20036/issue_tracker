<?php
session_start();

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

// Average Time to Resolution by Organization
$avg_time_resolution_org_query = "SELECT company_name, AVG(TIMESTAMPDIFF(SECOND, created_at, resolved_at)) AS avg_resolution_time FROM tickets WHERE resolved_at IS NOT NULL GROUP BY company_name";
$avg_time_resolution_org_result = $conn->query($avg_time_resolution_org_query);
$avg_time_resolution_org = [];
while ($row = $avg_time_resolution_org_result->fetch_assoc()) {
    $avg_time_resolution_org[$row['company_name']] = $row['avg_resolution_time'];
}

// Average Time to Resolution by Assigned Admin
$avg_time_resolution_admin_query = "SELECT assigned_to, AVG(TIMESTAMPDIFF(SECOND, created_at, resolved_at)) AS avg_resolution_time FROM tickets WHERE resolved_at IS NOT NULL GROUP BY assigned_to";
$avg_time_resolution_admin_result = $conn->query($avg_time_resolution_admin_query);
$avg_time_resolution_admin = [];
while ($row = $avg_time_resolution_admin_result->fetch_assoc()) {
    $avg_time_resolution_admin[$row['assigned_to']] = $row['avg_resolution_time'];
}

$conn->close();

$response = [
    'avg_time_resolution_org' => $avg_time_resolution_org,
    'avg_time_resolution_admin' => $avg_time_resolution_admin,
];

echo json_encode($response);
?>
