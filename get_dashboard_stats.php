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

// New Users
$new_users_query = "SELECT COUNT(id) AS new_users FROM users WHERE DATE(created_at) = CURDATE()";
$new_users_result = $conn->query($new_users_query);
$new_users = $new_users_result->fetch_assoc()['new_users'];

// Total Users
$total_users_query = "SELECT COUNT(id) AS total_users FROM users";
$total_users_result = $conn->query($total_users_query);
$total_users = $total_users_result->fetch_assoc()['total_users'];

// New Tickets
$new_tickets_query = "SELECT COUNT(id) AS new_tickets FROM tickets WHERE DATE(created_at) = CURDATE()";
$new_tickets_result = $conn->query($new_tickets_query);
$new_tickets = $new_tickets_result->fetch_assoc()['new_tickets'];

// Total Tickets
$total_tickets_query = "SELECT COUNT(id) AS total_tickets FROM tickets";
$total_tickets_result = $conn->query($total_tickets_query);
$total_tickets = $total_tickets_result->fetch_assoc()['total_tickets'];

// Tickets by Status
$status_query = "SELECT status, COUNT(id) AS count FROM tickets GROUP BY status";
$status_result = $conn->query($status_query);
$ticket_status_counts = [];
while ($row = $status_result->fetch_assoc()) {
    $ticket_status_counts[$row['status']] = $row['count'];
}

// Tickets by User
$tickets_by_user_query = "SELECT assigned_to, COUNT(id) AS count FROM tickets GROUP BY assigned_to";
$tickets_by_user_result = $conn->query($tickets_by_user_query);
$ticket_user_counts = [];
while ($row = $tickets_by_user_result->fetch_assoc()) {
    $ticket_user_counts[$row['assigned_to']] = $row['count'];
}

// Tickets by Organization
$tickets_by_org_query = "SELECT company_name, COUNT(id) AS count FROM tickets GROUP BY company_name";
$tickets_by_org_result = $conn->query($tickets_by_org_query);
$ticket_org_counts = [];
while ($row = $tickets_by_org_result->fetch_assoc()) {
    $ticket_org_counts[$row['company_name']] = $row['count'];
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

// Global Admin Rating Average
$global_admin_rating_query = "SELECT AVG(rating) AS global_admin_rating_avg FROM admin_ratings WHERE admin_role = 'Global Admin'";
$global_admin_rating_result = $conn->query($global_admin_rating_query);
$global_admin_rating_avg = $global_admin_rating_result->fetch_assoc()['global_admin_rating_avg'];

// Organization Satisfaction Index
$org_satisfaction_query = "SELECT users.company_name, AVG(admin_ratings.rating) AS satisfaction_index FROM admin_ratings JOIN users ON admin_ratings.admin_id = users.id WHERE users.role = 'Organization Admin' GROUP BY users.company_name";
$org_satisfaction_result = $conn->query($org_satisfaction_query);
$org_satisfaction_index = [];
while ($row = $org_satisfaction_result->fetch_assoc()) {
    $org_satisfaction_index[$row['company_name']] = $row['satisfaction_index'];
}

$conn->close();

$response = [
    'new_users' => $new_users,
    'total_users' => $total_users,
    'new_tickets' => $new_tickets,
    'total_tickets' => $total_tickets,
    'ticket_status_counts' => $ticket_status_counts,
    'ticket_user_counts' => $ticket_user_counts,
    'ticket_org_counts' => $ticket_org_counts,
    'avg_time_resolution_org' => $avg_time_resolution_org,
    'avg_time_resolution_admin' => $avg_time_resolution_admin,
    'global_admin_rating_avg' => $global_admin_rating_avg,
    'org_satisfaction_index' => $org_satisfaction_index,
];

echo json_encode($response);
?>
