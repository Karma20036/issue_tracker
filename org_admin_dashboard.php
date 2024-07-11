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

// Ensure company_name is set in session
if (!isset($_SESSION['company_name'])) {
    die("Company name not set in session.");
}

$company_name = $_SESSION['company_name'];

// Fetch organizations for the dropdown
$org_sql = "SELECT company_name FROM organizations";
$org_result = $conn->query($org_sql);

$organizations = [];
if ($org_result->num_rows > 0) {
    while ($row = $org_result->fetch_assoc()) {
        $organizations[] = $row['company_name'];
    }
}

// Fetch user details
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT first_name, company_name FROM users WHERE id = '$user_id'";
$user_result = $conn->query($user_sql);
$user_details = $user_result->fetch_assoc();
$first_name = $user_details['first_name'];
$company_name = $user_details['company_name'];


// Fetch users for the manage user section along with their ticket counts
$user_sql = "SELECT u.id, u.first_name, u.surname, u.email, u.role, u.active, COUNT(t.id) AS ticket_count 
             FROM users u 
             LEFT JOIN tickets t ON u.id = t.user_id 
             WHERE u.company_name = '$company_name' 
             GROUP BY u.id";
$user_result = $conn->query($user_sql);

// Fetch dashboard stats for the organization
$stats_sql = "
    SELECT 
        (SELECT COUNT(*) FROM users WHERE company_name = '$company_name' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS new_users,
        (SELECT COUNT(*) FROM users WHERE company_name = '$company_name') AS total_users,
        (SELECT COUNT(*) FROM tickets WHERE company_name = '$company_name' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)) AS new_tickets,
        (SELECT COUNT(*) FROM tickets WHERE company_name = '$company_name') AS total_tickets,
        (SELECT COUNT(*) FROM tickets WHERE company_name = '$company_name' AND status = 'Pending') AS pending_tickets,
        (SELECT COUNT(*) FROM tickets WHERE company_name = '$company_name' AND status = 'Assigned') AS assigned_tickets,
        (SELECT COUNT(*) FROM tickets WHERE company_name = '$company_name' AND status = 'Responded') AS responded_tickets,
        (SELECT COUNT(*) FROM tickets WHERE company_name = '$company_name' AND status = 'Resolved') AS resolved_tickets,
        (SELECT COUNT(*) FROM tickets WHERE company_name = '$company_name' AND status = 'Cancelled') AS cancelled_tickets
    ";
$stats_result = $conn->query($stats_sql);
$stats = $stats_result->fetch_assoc();

$avg_resolution_time_org_sql = "
    SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, resolved_at)) / 3600 AS avg_resolution_time
    FROM tickets
    WHERE company_name = '$company_name' AND status = 'Resolved'";
$avg_resolution_time_org_result = $conn->query($avg_resolution_time_org_sql);
$avg_resolution_time_org = $avg_resolution_time_org_result->fetch_assoc()['avg_resolution_time'] ?? 'N/A';

$avg_resolution_time_admin_sql = "
    SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, resolved_at)) / 3600 AS avg_resolution_time
    FROM tickets
    WHERE status = 'Resolved'";
$avg_resolution_time_admin_result = $conn->query($avg_resolution_time_admin_sql);
$avg_resolution_time_admin = $avg_resolution_time_admin_result->fetch_assoc()['avg_resolution_time'] ?? 'N/A';



// Fetch tickets for the organization
$pending_tickets_sql = "SELECT t.*, u.first_name AS raised_by_first_name, u.surname AS raised_by_surname
                        FROM tickets t
                        LEFT JOIN users u ON t.user_id = u.id
                        WHERE t.status = 'Pending' AND t.company_name = '$company_name'";
$pending_tickets_result = $conn->query($pending_tickets_sql);

$recent_tickets_sql = "SELECT t.*, u.first_name AS raised_by_first_name, u.surname AS raised_by_surname
                        FROM tickets t
                        LEFT JOIN users u ON t.user_id = u.id
                        WHERE t.status != 'Closed' AND t.company_name = '$company_name' 
                        ORDER BY t.created_at DESC
                        LIMIT 10";
$recent_tickets_result = $conn->query($recent_tickets_sql);

$all_tickets_sql = "SELECT t.*, u.first_name AS raised_by_first_name, u.surname AS raised_by_surname
                    FROM tickets t
                    LEFT JOIN users u ON t.user_id = u.id
                    WHERE t.company_name = '$company_name'";

$all_tickets_result = $conn->query($all_tickets_sql);

// Fetch tickets for the organization
$cancelled_tickets_sql = "SELECT t.*, u.first_name AS raised_by_first_name, u.surname AS raised_by_surname
                        FROM tickets t
                        LEFT JOIN users u ON t.user_id = u.id
                        WHERE t.status = 'Cancelled' AND t.company_name = '$company_name'";
$cancelled_tickets_result = $conn->query($cancelled_tickets_sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Boxicons -->
    <link href='https://unpkg.com/boxicons@2.0.9/css/boxicons.min.css' rel='stylesheet'>
    <!-- My CSS -->
    <link rel="stylesheet" href="style.css">

    <title>Organization Admin Hub</title>
</head>
<body>
<style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        input {
            width: 50%;
            display: block;
            margin: 15px auto;
        }
        .status-active {
        color: green;
        }

        .status-inactive {
        color: red;
        }
        input, textarea, select {
            width: 50%;
            display: block;
            margin: 15px auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>

    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bxs-smile'></i>
            <span class="text">Organization Admin Hub</span>
        </a>
        <ul class="side-menu top">
            <li class="active">
                <a href="#stats" onclick="showSection('stats')">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard Stats</span>
                </a>
            </li>
            <li>
                <a href="#create-user" onclick="showSection('create-user')">
                    <i class='bx bxs-user-plus'></i>
                    <span class="text">Create New User</span>
                </a>
            </li>
            <li>
                <a href="#manage-users" onclick="showSection('manage-users')">
                    <i class='bx bxs-user-detail'></i>
                    <span class="text">Manage My Members</span>
                </a>
            </li>
            <li>
                <a href="#view-tickets" onclick="showSection('view-tickets')">
                    <i class='bx bxs-book'></i>
                    <span class="text">View My Organization's Tickets</span>
                </a>
            </li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="logout.php" class="logout">
                    <i class='bx bxs-log-out-circle'></i>
                    <span class="text">Logout</span>
                </a>
            </li>
        </ul>
    </section>
    <!-- SIDEBAR -->

    <!-- CONTENT -->
    <section id="content">
        <!-- NAVBAR -->
        <nav>
            <div class="welcome-message">
                <h2>Welcome, <?= $first_name ?> from <?= $company_name ?>!</h2>
            </div>
        </nav>
        <!-- NAVBAR -->

        <!-- MAIN -->
        <main>
            <div class="head-title">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                        <li>
                            <a href="#">Dashboard</a>
                        </li>
                        <li><i class='bx bx-chevron-right'></i></li>
                        <li>
                            <a class="active" href="#">Home</a>
                        </li>
                    </ul>
                </div>
            </div>


            <section id="stats" class="section" >
            <ul class="box-info">
                <li>
                    <i class='bx bxs-calendar-check'></i>
                    <span class="text">
                        <h3><?php echo $stats['new_users']; ?></h3>
                        <p>New Users</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-group'></i>
                    <span class="text">
                        <h3><?php echo $stats['total_users']; ?></h3>
                        <p>Total Users</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-file'></i>
                    <span class="text">
                        <h3><?php echo $stats['new_tickets']; ?></h3>
                        <p>New Tickets</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-folder-open'></i>
                    <span class="text">
                        <h3><?php echo $stats['total_tickets']; ?></h3>
                        <p>Total Tickets</p>
                    </span>
                </li>
            </ul>

            <div class="card">
                        <h3>Pending Tickets</h3>
                        <p><?php echo $stats['pending_tickets']; ?></p>
                    </div>
                    <div class="card">
                        <h3>Assigned Tickets</h3>
                        <p><?php echo $stats['assigned_tickets']; ?></p>
                    </div>
                    <div class="card">
                        <h3>Responded Tickets</h3>
                        <p><?php echo $stats['responded_tickets']; ?></p>
                    </div>
                    <div class="card">
                        <h3>Resolved Tickets</h3>
                        <p><?php echo $stats['resolved_tickets']; ?></p>
                    </div>
                    <div class="card">
                        <h3>Cancelled Tickets</h3>
                        <p><?php echo $stats['cancelled_tickets']; ?></p>
                    </div>
                    <div class="card">
                        <h3>Avg. Resolution Time (Org)</h3>
                        <p><?php echo $avg_resolution_time_org; ?> hours</p>
                    </div>
                    <div class="card">
                        <h3>Avg. Resolution Time (Admin)</h3>
                        <p><?php echo $avg_resolution_time_admin; ?> hours</p>
                    </div>
            </section>

            <section id="create-user" class="section" style="display:none;">
                <h3>Create New User</h3>
                <form id="createUserForm" action="create_user.php" method="POST" enctype="multipart/form-data">
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="surname" placeholder="Surname" required>
                    <input type="text" name="company_name" value="<?php echo $_SESSION['company_name']; ?>" readonly>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="text" name="mobile_number" placeholder="Mobile Number" required>
                    <button type="submit">Create User</button>
                </form>
            </section>
           
            <section id="manage-users" class="section" style="display:none;">
                <h3>Manage my Members</h3>
                <div id="manage-users-content">
                    <?php
                    if ($user_result->num_rows > 0) {
                        echo '<table>';
                        echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Ticket Count</th><th>Actions</th></tr>';
                        while ($row = $user_result->fetch_assoc()) {
                            $status = $row['active'] ? 'Active' : 'Inactive';
                            echo '<tr>';
                            echo '<td>' . $row['id'] . '</td>';
                            echo '<td>' . $row['first_name'] . ' ' . $row['surname'] . '</td>';
                            echo '<td>' . $row['email'] . '</td>';
                            echo '<td>' . $row['role'] . '</td>';
                            echo '<td class="' . ($row['active'] ? 'status-active' : 'status-inactive') . '">' . $status . '</td>';
                            echo '<td>' . $row['ticket_count'] . '</td>';
                            echo '<td>';
                            echo '<button onclick="openUpdateModal(' . $row['id'] . ', \'' . $row['first_name'] . '\', \'' . $row['surname'] . '\', \'' . $row['email'] . '\', \'' . $row['role'] . '\')">Update</button>';
                            echo '<button onclick="openChangeRoleModal(' . $row['id'] . ', \'' . $row['role'] . '\')">Change Role</button>';
                            if ($row['role'] !== 'Organization Admin') {
                                if ($row['active']) {
                                    echo '<button onclick="openRevokeAccessModal(' . $row['id'] . ')">Revoke Access</button>';
                                } else {
                                    echo '<button onclick="openGiveAccessModal(' . $row['id'] . ')">Give Access</button>';
                                }
                            } else {
                                echo 'N/A';
                            }
                            echo '</td>';
                            echo '</tr>';
                        }
                        echo '</table>';
                    } else {
                        echo 'No users found.';
                    }
                    ?>
                </div>
                </section>
                
            <!-- Update User Modal -->
            <div id="updateUserModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeUpdateModal()">&times;</span>
                    <h3>Update User Details</h3>
                    <form id="updateUserForm" action="org_update_user.php" method="POST">
                        <input type="hidden" name="id" id="updateUserId">
                        <input type="text" name="first_name" id="updateFirstName" placeholder="First Name" required>
                        <input type="text" name="surname" id="updateSurname" placeholder="Surname" required>
                        <input type="email" name="email" id="updateEmail" placeholder="Email Address" required>
                        <button type="submit">Update</button>
                    </form>
                </div>
            </div>

            <!-- Change Role Modal -->
            <div id="changeRoleModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeChangeRoleModal()">&times;</span>
                    <h3>Change User Role</h3>
                    <form id="changeRoleForm" action="org_change_role.php" method="POST">
                        <input type="hidden" name="id" id="changeRoleId">
                        <select name="role" id="changeRoleSelect" required>
                            <option value="Customer">Customer</option>
                            <option value="Organization Admin">Organization Admin</option>
                            <option value="Global Admin">Global Admin</option>
                        </select>
                        <button type="submit">Change Role</button>
                    </form>
                </div>
            </div>


            <!-- Revoke Access Modal -->
            <div id="revokeAccessModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeRevokeAccessModal()">&times;</span>
                    <h3>Revoke Access</h3>
                    <p>Are you sure you want to revoke access for this user?</p>
                    <form id="revokeAccessForm" action="org_revoke_access.php" method="POST">
                        <input type="hidden" name="id" id="revokeUserId">
                        <button type="submit">Yes, Revoke Access</button>
                        <button type="button" onclick="closeRevokeAccessModal()">Cancel</button>
                    </form>
                </div>
            </div>

            <!-- Give Access Modal -->
            <div id="giveAccessModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeGiveAccessModal()">&times;</span>
                    <h3>Give Access</h3>
                    <p>Are you sure you want to give access back to this user?</p>
                    <form id="giveAccessForm" action="org_give_access.php" method="POST">
                        <input type="hidden" name="id" id="giveUserId">
                        <button type="submit">Yes, Give Access</button>
                        <button type="button" onclick="closeGiveAccessModal()">Cancel</button>
                    </form>
                </div>
            </div>

            <section id="view-tickets" class="section" style="display:none;">
                <h3>View My Organization's Tickets</h3>
                <div>
                    <h4>Pending Tickets</h4>
                        <?php
                        if ($pending_tickets_result->num_rows > 0) {
                            echo '<table>';
                            echo '<tr><th>ID</th><th>Raised By</th><th>Category</th><th>Description</th><th>Status</th><th>Created At</th></tr>';
                            while ($row = $pending_tickets_result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $row['id'] . '</td>';
                                echo '<td>' . $row['raised_by_first_name'] . ' ' . $row['raised_by_surname'] . '</td>';
                                echo '<td>' . $row['category'] . '</td>';
                                echo '<td>' . $row['description'] . '</td>';
                                echo '<td>' . $row['status'] . '</td>';
                                echo '<td>' . $row['created_at'] . '</td>';
                                echo '</tr>';
                            }
                            echo '</table>';
                        } else {
                            echo 'No pending tickets found.';
                        }
                        ?>
                    </div>
                    <h4>Recent Tickets</h4>
                    <div id="recent-tickets-content">
                        <?php
                        if ($recent_tickets_result->num_rows > 0) {
                            echo '<table>';
                            echo '<tr><th>ID</th><th>Raised By</th><th>Category</th><th>Description</th><th>Status</th><th>Created At</th></tr>';
                            while ($row = $recent_tickets_result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $row['id'] . '</td>';
                                echo '<td>' . $row['raised_by_first_name'] . ' ' . $row['raised_by_surname'] . '</td>';
                                echo '<td>' . $row['category'] . '</td>';
                                echo '<td>' . $row['description'] . '</td>';
                                echo '<td>' . $row['status'] . '</td>';
                                echo '<td>' . $row['created_at'] . '</td>';
                                echo '</tr>';
                            }
                            echo '</table>';
                        } else {
                            echo 'No recent tickets found.';
                        }
                        ?>
                    </div>
                    <h4>Ticket History</h4>
                    <div id="all-tickets-content">
                        <?php
                        if ($all_tickets_result->num_rows > 0) {
                            echo '<table>';
                            echo '<tr><th>ID</th><th>Raised By</th><th>Category</th><th>Description</th><th>Status</th><th>Created At</th><th>Resolved At</th></tr>';
                            while ($row = $all_tickets_result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $row['id'] . '</td>';
                                echo '<td>' . $row['raised_by_first_name'] . ' ' . $row['raised_by_surname'] . '</td>';
                                echo '<td>' . $row['category'] . '</td>';
                                echo '<td>' . $row['description'] . '</td>';
                                echo '<td>' . $row['status'] . '</td>';
                                echo '<td>' . $row['created_at'] . '</td>';
                                echo '<td>' . ($row['resolved_at'] ? $row['resolved_at'] : 'N/A') . '</td>';
                                echo '</tr>';
                            }
                            echo '</table>';
                        } else {
                            echo 'No ticket history found.';
                        }
                        
                        ?>
                    </div>

                    <h4>Cancelled Tickets</h4>
        <div id="cancelled-tickets-content">
            <?php
            if ($cancelled_tickets_result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>ID</th><th>Category</th><th>Description</th><th>Status</th><th>Created At</th><th>Raised By</th><th>Organization</th><th>Actions</th></tr>';
                while ($row = $cancelled_tickets_result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['category'] . '</td>';
                    echo '<td>' . $row['description'] . '</td>';
                    echo '<td>' . $row['status'] . '</td>';
                    echo '<td>' . $row['created_at'] . '</td>';
                    echo '<td>' . $row['first_name'] . ' ' . $row['surname'] . '</td>';
                    echo '<td>' . $row['company_name'] . '</td>';
                    echo '<td>';
                    echo '<button onclick="openCancellationReasonModal(' . $row['id'] . ')">View Cancellation Reason</button>';
                    echo '</td>'; 
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo 'No cancelled tickets found.';
            }
            ?>
        </div>
                </div>
            </section>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script>
        function showSection(sectionId) {
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => {
            section.style.display = 'none';
        });
        document.getElementById(sectionId).style.display = 'block';
    }

        
    async function fetchDashboardStats() {
    const response = await fetch('get_dashboard_stats.php');
    const stats = await response.json();

    document.querySelector('.box-info .new-users').innerText = stats.new_users;
    document.querySelector('.box-info .total-users').innerText = stats.total_users;
    document.querySelector('.box-info .new-tickets').innerText = stats.new_tickets;
    document.querySelector('.box-info .total-tickets').innerText = stats.total_tickets;

    const ticketStats = document.getElementById('ticket-stats');
    ticketStats.innerHTML = `
        <tr>
            <td>Pending</td>
            <td>${stats.ticket_status_counts.Pending || 0}</td>
        </tr>
        <tr>
            <td>Assigned</td>
            <td>${stats.ticket_status_counts.Assigned || 0}</td>
        </tr>
        <tr>
            <td>Responded</td>
            <td>${stats.ticket_status_counts.Responded || 0}</td>
        </tr>
        <tr>
            <td>Resolved</td>
            <td>${stats.ticket_status_counts.Resolved || 0}</td>
        </tr>
        <tr>
            <td>Cancelled</td>
            <td>${stats.ticket_status_counts.Cancelled || 0}</td>
        </tr>
    `;

    document.getElementById('avg-resolution-time').innerText = stats.avg_time_resolution_org !== 'N/A' ? `${stats.avg_time_resolution_org.toFixed(2)} hours` : 'N/A';
}

document.addEventListener('DOMContentLoaded', () => {
    fetchDashboardStats();
});

        function openUpdateModal(id, firstName, surname, email, role) {
            document.getElementById('updateUserId').value = id;
            document.getElementById('updateFirstName').value = firstName;
            document.getElementById('updateSurname').value = surname;
            document.getElementById('updateEmail').value = email;
            document.getElementById('updateUserModal').style.display = 'block';
        }

        function closeUpdateModal() {
            document.getElementById('updateUserModal').style.display = 'none';
        }

        function openChangeRoleModal(id, currentRole) {
            document.getElementById('changeRoleId').value = id;
            document.getElementById('changeRoleSelect').value = currentRole;
            document.getElementById('changeRoleModal').style.display = 'block';
        }

        function closeChangeRoleModal() {
            document.getElementById('changeRoleModal').style.display = 'none';
        }

        function openRevokeAccessModal(id) {
            document.getElementById('revokeUserId').value = id;
            document.getElementById('revokeAccessModal').style.display = 'block';
        }

        function closeRevokeAccessModal() {
            document.getElementById('revokeAccessModal').style.display = 'none';
        }

        function openGiveAccessModal(id) {
            document.getElementById('giveUserId').value = id;
            document.getElementById('giveAccessModal').style.display = 'block';
        }

        function closeGiveAccessModal() {
            document.getElementById('giveAccessModal').style.display = 'none';
        }
        function openAssignModal(ticketId) {
            document.getElementById('assignTicketId').value = ticketId;
            document.getElementById('assignTicketModal').style.display = 'block';
        }

        function closeAssignModal() {
            document.getElementById('assignTicketModal').style.display = 'none';
        }

        function openRespondModal(ticketId) {
            document.getElementById('respondTicketId').value = ticketId;
            document.getElementById('respondTicketModal').style.display = 'block';
        }

        function closeRespondModal() {
            document.getElementById('respondTicketModal').style.display = 'none';
        }

        function openCloseModal(ticketId) {
            document.getElementById('closeTicketId').value = ticketId;
            document.getElementById('closeTicketModal').style.display = 'block';
        }

        function closeCloseModal() {
            document.getElementById('closeTicketModal').style.display = 'none';
        }

        async function fetchDashboardStats() {
            const response = await fetch('get_dashboard_stats.php');
            const stats = await response.json();

            document.getElementById('new-users').innerText = stats.new_users;
            document.getElementById('total-users').innerText = stats.total_users;
            document.getElementById('new-tickets').innerText = stats.new_tickets;
            document.getElementById('total-tickets').innerText = stats.total_tickets;

            const ticketStats = document.getElementById('ticket-stats');
            ticketStats.innerHTML = `
                <tr>
                    <td>Pending</td>
                    <td>${stats.ticket_status_counts.Pending || 0}</td>
                </tr>
                <tr>
                    <td>Assigned</td>
                    <td>${stats.ticket_status_counts.Assigned || 0}</td>
                </tr>
                <tr>
                    <td>Responded</td>
                    <td>${stats.ticket_status_counts.Responded || 0}</td>
                </tr>
                <tr>
                    <td>Resolved</td>
                    <td>${stats.ticket_status_counts.Resolved || 0}</td>
                </tr>
                <tr>
                    <td>Cancelled</td>
                    <td>${stats.ticket_status_counts.Cancelled || 0}</td>
                </tr>
            `;

            document.getElementById('avg-resolution-time-org').innerText = stats.avg_time_resolution_org;
            document.getElementById('avg-resolution-time-admin').innerText = stats.avg_time_resolution_admin;
            document.getElementById('global-admin-rating').innerText = stats.global_admin_rating_avg;
            document.getElementById('org-satisfaction-index').innerText = stats.org_satisfaction_index;
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchDashboardStats();
        });
    </script>
<?php
$conn->close();
?>

</body>
</html>
