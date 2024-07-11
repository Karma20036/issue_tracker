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
// Fetch user details
$user_id = $_SESSION['user_id'];
$user_sql = "SELECT first_name, company_name FROM users WHERE id = '$user_id'";
$user_result = $conn->query($user_sql);
$user_details = $user_result->fetch_assoc();
$first_name = $user_details['first_name'];
$company_name = $user_details['company_name'];

// Fetch organizations for the dropdown
$org_sql = "SELECT company_name FROM organizations";
$org_result = $conn->query($org_sql);

$organizations = [];
if ($org_result->num_rows > 0) {
    while ($row = $org_result->fetch_assoc()) {
        $organizations[] = $row['company_name'];
    }
}

$customer_sql = "SELECT id, first_name, surname FROM users WHERE role = 'Customer'";
$customer_result = $conn->query($customer_sql);
$customers = [];
while ($row = $customer_result->fetch_assoc()) {
    $customers[] = $row;
}
// Fetch pending tickets
$pending_tickets_sql = "
    SELECT t.*, u.first_name, u.surname, u.company_name 
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    WHERE t.status = 'Pending'";
$pending_tickets_result = $conn->query($pending_tickets_sql);

// Fetch open tickets (status is either 'Pending' or 'Assigned')
$open_tickets_sql = "
    SELECT t.*, u.first_name, u.surname, u.company_name 
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    WHERE t.status IN ('Assigned', 'Responded')";
$open_tickets_result = $conn->query($open_tickets_sql);

// Fetch recent tickets (created within the last 7 days)
$recent_tickets_sql = "
    SELECT t.*, u.first_name, u.surname, u.company_name 
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    WHERE t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$recent_tickets_result = $conn->query($recent_tickets_sql);

// Fetch ticket history for global admin (all tickets)
$all_tickets_sql = "
    SELECT t.*, u.first_name, u.surname, u.company_name 
    FROM tickets t
    JOIN users u ON t.user_id = u.id";
$all_tickets_result = $conn->query($all_tickets_sql);

// Fetch cancelled tickets
$cancelled_tickets_sql = "
    SELECT t.*, u.first_name, u.surname, u.company_name 
    FROM tickets t
    JOIN users u ON t.user_id = u.id
    WHERE t.status = 'Cancelled'";
$cancelled_tickets_result = $conn->query($cancelled_tickets_sql);

// Fetch users for the manage user section
$user_sql = "SELECT id, first_name, surname, company_name, email, role, active FROM users";
$user_result = $conn->query($user_sql);

// Fetch Global Admins for the Assign Ticket modal
$global_admin_sql = "SELECT id, first_name, surname FROM users WHERE role = 'Global Admin'";
$global_admin_result = $conn->query($global_admin_sql);

$conn->close();
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

    <title>Global Admin Hub</title>
</head>
<body>

    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bxs-smile'></i>
            <span class="text">Global Admin Hub</span>
        </a>
        <ul class="side-menu top">
            <li class="active">
                <a href="#stats" onclick="showSection('stats')">
                    <i class='bx bxs-dashboard'></i>
                    <span class="text">Dashboard Stats</span>
                </a>
            </li>
            <li>
                <a href="#create-org" onclick="showSection('create-org')">
                    <i class='bx bxs-building-house'></i>
                    <span class="text">Create Organization</span>
                </a>
            </li>
            <li>
                <a href="#create-org-admin" onclick="showSection('create-org-admin')">
                    <i class='bx bxs-user-plus'></i>
                    <span class="text">Create Organization Admin</span>
                </a>
            </li>
            <li>
                <a href="#manage-users" onclick="showSection('manage-users')">
                    <i class='bx bxs-user-detail'></i>
                    <span class="text">Manage User Accounts</span>
                </a>
            </li>
            <li>
                <a href="#view-tickets" onclick="showSection('view-tickets')">
                    <i class='bx bxs-book'></i>
                    <span class="text">View All Tickets</span>
                </a>
            </li>
            <li>
                <a href="#chat-section" onclick="showSection('chat-section')">
                    <i class='bx bxs-check-circle'></i>
                    <span class="text">Chat with Customer</span>
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
        <section id="stats" class="section" >

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
        
            <ul class="box-info">
                <li>
                    <i class='bx bxs-calendar-check'></i>
                    <span class="text">
                        <h3 id="new-users">0</h3>
                        <p>New Users</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-group'></i>
                    <span class="text">
                        <h3 id="total-users">0</h3>
                        <p>Total Users</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-file'></i>
                    <span class="text">
                        <h3 id="new-tickets">0</h3>
                        <p>New Tickets</p>
                    </span>
                </li>
                <li>
                    <i class='bx bxs-folder-open'></i>
                    <span class="text">
                        <h3 id="total-tickets">0</h3>
                        <p>Total Tickets</p>
                    </span>
                </li>
            </ul>

            <div class="table-data">
                <div class="order">
                    <div class="head">
                        <h3>Ticket Stats</h3>
                    </div>
                    <table>
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Count</th>
                            </tr>
                        </thead>
                        <tbody id="ticket-stats">
                            <!-- Populate with JavaScript -->
                        </tbody>
                    </table>
                </div>
                <div class="todo">
                    <div class="head">
                        <h3>Resolution Stats</h3>
                    </div>
                    <ul class="todo-list">
                        <li>
                            <p>Average Time to Resolution by Organization</p>
                            <span id="avg-resolution-time-org">N/A</span>
                        </li>
                        <li>
                            <p>Average Time to Resolution by Assigned Admin</p>
                            <span id="avg-resolution-time-admin">N/A</span>
                        </li>
                        <li>
                            <p>Global Admin Rating Average</p>
                            <span id="global-admin-rating">N/A</span>
                        </li>
                        <li>
                            <p>Organization Satisfaction Index</p>
                            <span id="org-satisfaction-index">N/A</span>
                        </li>
                    </ul>
                </div>
            </div>
            </section>

            <section id="create-org" class="section" style="display:none;">
            <h3>Create Organization</h3>
            <form id="createOrgForm" action="create_organization.php" method="POST" enctype="multipart/form-data">
                <input type="text" name="company_name" placeholder="Company Name" required>
                <input type="text" name="physical_address" placeholder="Physical Address" required>
                <input type="text" name="postal_address" placeholder="Postal Address" required>
                <input type="email" name="email" placeholder="Email Address" required>
                <input type="text" name="mobile_number" placeholder="Mobile Number" required>
                <!-- Styled file input -->
                <label for="logo" class="file-input-label">
                    Upload Logo
                    <input type="file" id="logo" name="logo" width="100" height="100">
                </label>
                <button type="submit" id="createOrgBtn">Create Organization</button>
            </form>
            </section>

            <section id="create-org-admin" class="section" style="display:none;">
                <h3>Create Organization Admin</h3>
                <form id="createOrgAdminForm" action="create_org_admin.php" method="POST">
                    <input type="text" name="first_name" placeholder="First Name" required>
                    <input type="text" name="surname" placeholder="Surname" required>
                    <select name="company_name" required>
                        <?php
                        foreach ($organizations as $company) {
                            echo "<option value=\"$company\">$company</option>";
                        }
                        ?>
                    </select>
                    <input type="email" name="email" placeholder="Email Address" required>
                    <input type="text" name="mobile_number" placeholder="Mobile Number" required>
                    <button type="submit">Create Organization Admin</button>
                </form>
            </section>
    <section id="manage-users" class="section" style="display: none;">       
    <h3>User Accounts</h3>
    <div class="user-list">
        <?php
        if ($user_result->num_rows > 0) {
            while ($row = $user_result->fetch_assoc()) {
                $status = $row['active'] ? 'Active' : 'Inactive';
                echo '<div class="user-card">';
                echo '<p><strong>ID:</strong> ' . $row['id'] . '</p>';
                echo '<p><strong>Name:</strong> ' . $row['first_name'] . ' ' . $row['surname'] . '</p>';
                echo '<p><strong>Company:</strong> ' . $row['company_name'] . '</p>';
                echo '<p><strong>Email:</strong> ' . $row['email'] . '</p>';
                echo '<p><strong>Role:</strong> ' . $row['role'] . '</p>';
                echo '<p><strong>Status:</strong> <span class="' . ($row['active'] ? 'status-active' : 'status-inactive') . '">' . $status . '</span></p>';
                echo '<div class="actions">';
                echo '<button onclick="openUpdateModal(' . $row['id'] . ', \'' . $row['first_name'] . '\', \'' . $row['surname'] . '\', \'' . $row['email'] . '\', \'' . $row['role'] . '\')">Update</button>';
                echo '<button onclick="openChangeRoleModal(' . $row['id'] . ', \'' . $row['role'] . '\')">Change Role</button>';
                if ($row['role'] !== 'Global Admin') {
                    if ($row['active']) {
                        echo '<button onclick="openRevokeAccessModal(' . $row['id'] . ')">Revoke Access</button>';
                    } else {
                        echo '<button onclick="openGiveAccessModal(' . $row['id'] . ')">Give Access</button>';
                        echo '<button onclick="openViewRevokeReasonModal(' . $row['id'] . ')">View Revoke Reason</button>';
                    }
                } else {
                    echo 'N/A';
                }
                echo '</div>';
                echo '</div>';
            }
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
        <form id="updateUserForm" action="update_user.php" method="POST">
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
        <form id="changeRoleForm" action="change_role.php" method="POST">
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
        <form id="revokeAccessForm" action="revoke_access.php" method="POST">
            <input type="hidden" name="id" id="revokeUserId">
            <textarea name="reason" placeholder="Enter reason for revoking access" required></textarea>
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
        <form id="giveAccessForm" action="give_access.php" method="POST">
            <input type="hidden" name="id" id="giveUserId">
            <button type="submit">Yes, Give Access</button>
            <button type="button" onclick="closeGiveAccessModal()">Cancel</button>
        </form>
    </div>
</div>

<!-- View Revoke Reason Modal -->
<div id="viewRevokeReasonModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeViewRevokeReasonModal()">&times;</span>
        <h3>Revoke Reason</h3>
        <div id="revokeReasonContent"></div>
    </div>
</div>

<section id="view-tickets" class="section" style="display:none;">
    <h3>View all Tickets</h3>
    <div class="button-container">
        <button class="button" onclick="openAddCategoryModal()">+Add New Category</button>
        <button class="button delete-button" onclick="openDeleteCategoryModal()">-Delete Category</button>
    </div>
    <div>
        <h4>Pending Tickets</h4>
        <div id="pending-tickets-content">
            <?php
            if ($pending_tickets_result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>ID</th><th>Category</th><th>Description</th><th>Status</th><th>Created At</th><th>Raised By</th><th>Organization</th><th>Actions</th></tr>';
                while ($row = $pending_tickets_result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['category'] . '</td>';
                    echo '<td>' . $row['description'] . '</td>';
                    echo '<td>' . $row['status'] . '</td>';
                    echo '<td>' . $row['created_at'] . '</td>';
                    echo '<td>' . $row['first_name'] . ' ' . $row['surname'] . '</td>';
                    echo '<td>' . $row['company_name'] . '</td>';
                    echo '<td>';
                    echo '<button onclick="openAssignModal(' . $row['id'] . ')">Assign</button>';
                    echo '<button onclick="openRespondModal(' . $row['id'] . ')">Respond</button>';
                    echo '<button onclick="openCloseModal(' . $row['id'] . ')">Close</button>';
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo 'No pending tickets found.';
            }
            ?>
        </div>

        <h4>Open Tickets</h4>
        <div id="open-tickets-content">
            <?php
            if ($open_tickets_result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>ID</th><th>Category</th><th>Description</th><th>Status</th><th>Created At</th><th>Raised By</th><th>Organization</th><th>Actions</th></tr>';
                while ($row = $open_tickets_result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['category'] . '</td>';
                    echo '<td>' . $row['description'] . '</td>';
                    echo '<td>' . $row['status'] . '</td>';
                    echo '<td>' . $row['created_at'] . '</td>';
                    echo '<td>' . $row['first_name'] . ' ' . $row['surname'] . '</td>';
                    echo '<td>' . $row['company_name'] . '</td>';
                    echo '<td>';
                    if ($row['status'] == 'Assigned') {
                        echo '<button onclick="openRespondModal(' . $row['id'] . ')">Respond</button>';
                        echo '<button onclick="openCloseModal(' . $row['id'] . ')">Close</button>';
                    } elseif ($row['status'] == 'Responded') {
                        echo '<button onclick="openCloseModal(' . $row['id'] . ')">Close</button>';
                    }
                    echo '</td>';
                    echo '</tr>';
                }
                echo '</table>';
            } else {
                echo 'No open tickets found.';
            }
            ?>
        </div>

        <h4>Recent Tickets</h4>
        <div id="recent-tickets-content">
            <?php
            if ($recent_tickets_result->num_rows > 0) {
                echo '<table>';
                echo '<tr><th>ID</th><th>Category</th><th>Description</th><th>Status</th><th>Created At</th><th>Raised By</th><th>Organization</th></tr>';
                while ($row = $recent_tickets_result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['category'] . '</td>';
                    echo '<td>' . $row['description'] . '</td>';
                    echo '<td>' . $row['status'] . '</td>';
                    echo '<td>' . $row['created_at'] . '</td>';
                    echo '<td>' . $row['first_name'] . ' ' . $row['surname'] . '</td>';
                    echo '<td>' . $row['company_name'] . '</td>';
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
                echo '<tr><th>ID</th><th>Category</th><th>Description</th><th>Status</th><th>Created At</th><th>Resolved At</th><th>Raised By</th><th>Organization</th><th>Feedback</th></tr>';
                while ($row = $all_tickets_result->fetch_assoc()) {
                    echo '<tr>';
                    echo '<td>' . $row['id'] . '</td>';
                    echo '<td>' . $row['category'] . '</td>';
                    echo '<td>' . $row['description'] . '</td>';
                    echo '<td>' . $row['status'] . '</td>';
                    echo '<td>' . $row['created_at'] . '</td>';
                    echo '<td>' . ($row['resolved_at'] ? $row['resolved_at'] : 'N/A') . '</td>';
                    echo '<td>' . $row['first_name'] . ' ' . $row['surname'] . '</td>';
                    echo '<td>' . $row['company_name'] . '</td>';
                    echo '<td>';
                    echo '<button onclick="openFeedbackModal(' . $row['id'] . ')">View user feedback</button>';
                    echo '</td>';
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
<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeAddCategoryModal()">&times;</span>
        <h2>Add New Category</h2>
        <form id="addCategoryForm">
            <label for="categoryName">Category Name:</label>
            <input type="text" id="categoryName" name="categoryName" required>
            <button type="submit">Add Category</button>
        </form>
    </div>
</div>
<!-- Delete Category Modal -->
<div id="deleteCategoryModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDeleteCategoryModal()">&times;</span>
        <h2>Delete Category</h2>
        <form id="deleteCategoryForm">
            <label for="categorySelect">Select Category:</label>
            <select id="categorySelect" name="categorySelect" required>
                <!-- Options will be populated dynamically -->
            </select>
            <button type="submit">Delete Category</button>
        </form>
    </div>
</div>

<div id="cancellationReasonModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeCancellationReasonModal()">&times;</span>
        <h2>Cancellation Reason</h2>
        <p id="cancellation-reason-text"></p>
    </div>
</div>

            <!-- Assign Ticket Modal -->
            <div id="assignTicketModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeAssignModal()">&times;</span>
                    <h3>Assign Ticket</h3>
                    <form id="assignTicketForm" action="assign_ticket.php" method="POST">
                        <input type="hidden" name="ticket_id" id="assignTicketId">
                        <select name="admin_id" required>
                            <?php
                            while ($row = $global_admin_result->fetch_assoc()) {
                                echo '<option value="' . $row['id'] . '">' . $row['first_name'] . ' ' . $row['surname'] . '</option>';
                            }
                            ?>
                        </select>
                        <button type="submit">Assign Ticket</button>
                    </form>
                </div>
            </div>

            <!-- Respond to Ticket Modal -->
            <div id="respondTicketModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeRespondModal()">&times;</span>
                    <h3>Respond to Ticket</h3>
                    <form id="respondTicketForm" action="respond_ticket.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="ticket_id" id="respondTicketId">
                        <textarea name="response" placeholder="Your response" required></textarea>
                        <input type="file" name="attachment">
                        <button type="submit">Respond</button>
                    </form>
                </div>
            </div>

            <!-- Close Ticket Modal -->
            <div id="closeTicketModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeCloseModal()">&times;</span>
                    <h3>Close Ticket</h3>
                    <p>Are you sure you want to close this ticket?</p>
                    <form id="closeTicketForm" action="close_ticket.php" method="POST">
                        <input type="hidden" name="ticket_id" id="closeTicketId">
                        <textarea name="reason" placeholder="Reason for closing" required></textarea>
                        <button type="submit">Close Ticket</button>
                    </form>
                </div>
            </div>

            <!-- Feedback Modal -->
            <div id="feedbackModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeFeedbackModal()">&times;</span>
                    <h4>Feedback Details</h4>
                    <div id="feedbackContent">
                        <!-- Feedback details will be loaded here -->
                    </div>
                </div>
            </div>

                    <!-- Chat Interface -->
            <section id="chat-section" class="section" style="display:none;">
                <h3>Chat with Customer</h3>
                <select id="customerSelect">
                    <?php foreach ($customers as $customer): ?>
                        <option value="<?= $customer['id'] ?>"><?= $customer['first_name'] . ' ' . $customer['surname'] ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="chat-container">
                    <div class="chat-box" id="chatBox"></div>
                    <form id="messageForm">
                        <input type="hidden" name="recipient_id" id="recipientId">
                        <textarea name="message" id="messageInput" placeholder="Type a message" required></textarea>
                        <button type="submit">Send</button>
                    </form>
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


function openAddCategoryModal() {
    document.getElementById('addCategoryModal').style.display = 'block';
}

function closeAddCategoryModal() {
    document.getElementById('addCategoryModal').style.display = 'none';
}

function openDeleteCategoryModal() {
    fetch('get_categories.php')
    .then(response => response.json())
    .then(data => {
        const categorySelect = document.getElementById('categorySelect');
        categorySelect.innerHTML = '';
        data.categories.forEach(category => {
            const option = document.createElement('option');
            option.value = category.id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });
        document.getElementById('deleteCategoryModal').style.display = 'block';
    })
    .catch(error => console.error('Error:', error));
}

function closeDeleteCategoryModal() {
    document.getElementById('deleteCategoryModal').style.display = 'none';
}

document.getElementById('addCategoryForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const categoryName = document.getElementById('categoryName').value;

    fetch('add_category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `categoryName=${categoryName}`
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        closeAddCategoryModal();
    })
    .catch(error => console.error('Error:', error));
});

document.getElementById('deleteCategoryForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const categoryId = document.getElementById('categorySelect').value;

    fetch('delete_category.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `categoryId=${categoryId}`
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        closeDeleteCategoryModal();
    })
    .catch(error => console.error('Error:', error));
});
    function openCancellationReasonModal(ticketId) {
    // Fetch the cancellation reason from the server using AJAX or fetch API
    fetch(`get_cancellation_reason.php?ticket_id=${ticketId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('cancellation-reason-text').innerText = data;
            document.getElementById('cancellationReasonModal').style.display = 'block';
        })
        .catch(error => console.error('Error fetching cancellation reason:', error));
}

function closeCancellationReasonModal() {
    document.getElementById('cancellationReasonModal').style.display = 'none';
}

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
        function openViewRevokeReasonModal(id) {
    // Fetch the reason, who revoked and when from the server using AJAX
    fetch('get_revoke_reason.php?id=' + id)
        .then(response => response.json())
        .then(data => {
            document.getElementById('revokeReasonContent').innerHTML = `
                <p><strong>Reason:</strong> ${data.reason}</p>
                <p><strong>Revoked By:</strong> ${data.revoked_by}</p>
                <p><strong>Date:</strong> ${data.revoked_at}</p>
            `;
            document.getElementById('viewRevokeReasonModal').style.display = 'block';
        });
}

function closeViewRevokeReasonModal() {
    document.getElementById('viewRevokeReasonModal').style.display = 'none';
}

        function openFeedbackModal(ticketId) {
    document.getElementById('feedbackContent').innerHTML = 'Loading...';
    document.getElementById('feedbackModal').style.display = 'block';

    fetch('get_feedback.php?ticket_id=' + ticketId)
        .then(response => response.json())
        .then(data => {
            let feedbackContent = '';
            if (data.error) {
                feedbackContent = `<p>${data.error}</p>`;
            } else if (data.length > 0) {
                data.forEach(feedback => {
                    feedbackContent += `
                        <div class="feedback-item">
                            <p><strong>User ID ${feedback.user_id}:</strong></p>
                            <p>Resolved: ${feedback.resolved ? 'Yes' : 'No'}</p>
                            <p>Satisfaction: ${feedback.satisfaction}</p>
                            <p>Comments: ${feedback.comments}</p>
                            <p><small>${feedback.created_at}</small></p>
                        </div>
                        <hr>
                    `;
                });
            } else {
                feedbackContent = '<p>No feedback available for this ticket.</p>';
            }
            document.getElementById('feedbackContent').innerHTML = feedbackContent;
        })
        .catch(error => {
            document.getElementById('feedbackContent').innerHTML = 'Error loading feedback.';
            console.error('Error:', error);
        });
        }

        function closeFeedbackModal() {
            document.getElementById('feedbackModal').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const customerSelect = document.getElementById('customerSelect');
            const messageForm = document.getElementById('messageForm');
            const recipientIdInput = document.getElementById('recipientId');
            const chatBox = document.getElementById('chatBox');
            const messageInput = document.getElementById('messageInput');

            // Set up event listener for customer selection
            customerSelect.addEventListener('change', () => {
                recipientIdInput.value = customerSelect.value;
                fetchMessages();
            });

            // Set up event listener for message form submission
            messageForm.addEventListener('submit', (e) => {
                e.preventDefault();
                sendMessage();
            });

            // Initialize chat with the first customer
            customerSelect.dispatchEvent(new Event('change'));

            function fetchMessages() {
                const recipientId = recipientIdInput.value;
                if (recipientId) {
                    fetch(`get_messages.php?other_user_id=${recipientId}`)
                        .then(response => response.json())
                        .then(messages => {
                            chatBox.innerHTML = '';

                            messages.forEach(message => {
                                const messageDiv = document.createElement('div');
                                messageDiv.classList.add('message');

                                if (message.sender_id == <?= $_SESSION['user_id']; ?>) {
                                    messageDiv.classList.add('sender');
                                } else {
                                    messageDiv.classList.add('recipient');
                                }

                                messageDiv.innerText = message.message;
                                chatBox.appendChild(messageDiv);
                            });

                            chatBox.scrollTop = chatBox.scrollHeight;
                        });
                }
            }

            function sendMessage() {
                const formData = new FormData(messageForm);
                fetch('send_message.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        fetchMessages();
                        messageInput.value = '';
                    } else {
                        alert('Error sending message');
                    }
                });
            }
        });

        async function fetchDashboardStats() {
    try {
        const response = await fetch('get_dashboard_stats.php');
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        const stats = await response.json();
        console.log(stats); // Add this line to check the structure

        document.getElementById('new-users').innerText = stats.new_users || 0;
        document.getElementById('total-users').innerText = stats.total_users || 0;
        document.getElementById('new-tickets').innerText = stats.new_tickets || 0;
        document.getElementById('total-tickets').innerText = stats.total_tickets || 0;

        const ticketStats = document.getElementById('ticket-stats');
        const ticketStatusCounts = stats.ticket_status_counts || {};
        ticketStats.innerHTML = `
            <tr>
                <td>Pending</td>
                <td>${ticketStatusCounts.Pending || 0}</td>
            </tr>
            <tr>
                <td>Assigned</td>
                <td>${ticketStatusCounts.Assigned || 0}</td>
            </tr>
            <tr>
                <td>Responded</td>
                <td>${ticketStatusCounts.Responded || 0}</td>
            </tr>
            <tr>
                <td>Resolved</td>
                <td>${ticketStatusCounts.Resolved || 0}</td>
            </tr>
            <tr>
                <td>Cancelled</td>
                <td>${ticketStatusCounts.Cancelled || 0}</td>
        `;

        const avgResolutionTimeOrg = stats.avg_time_resolution_org;
        const avgResolutionTimeAdmin = stats.avg_time_resolution_admin;
        const orgSatisfactionIndex = stats.org_satisfaction_index;

        document.getElementById('avg-resolution-time-org').innerText = formatObject(avgResolutionTimeOrg);
        document.getElementById('avg-resolution-time-admin').innerText = formatObject(avgResolutionTimeAdmin);
        document.getElementById('global-admin-rating').innerText = stats.global_admin_rating_avg || 0;
        document.getElementById('org-satisfaction-index').innerText = formatObject(orgSatisfactionIndex);
    } catch (error) {
        console.error('Failed to fetch dashboard stats:', error);
    }
}

function formatObject(obj) {
    if (typeof obj === 'object' && obj !== null) {
        return Object.entries(obj).map(([key, value]) => `${key}: ${value}`).join(', ');
    }
    return obj || 0;
}

document.addEventListener('DOMContentLoaded', () => {
    fetchDashboardStats();
});

    </script>
    <style>
        /* Modal Styles */
            .modal {
            display: none;
            position: fixed;
            z-index: 1;
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

        .feedback-item {
            margin-bottom: 10px;
        }

        .feedback-item p {
            margin: 5px 0;
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

        .status-active {
            color: green;
        }

        .status-inactive {
            color: red;
        }
        .user-card {
    border: 1px solid #ddd;
    padding: 15px;
    margin-bottom: 10px;
}
.user-card p {
    margin: 5px 0;
}
.actions {
    margin-top: 10px;
}

        .chat-container {
    width: 400px;
    margin: 0 auto;
    border: 1px solid #ccc;
    padding: 10px;
    display: flex;
    flex-direction: column;
    height: 500px;
}

.chat-box {
    flex: 1;
    overflow-y: auto;
    border: 1px solid #ccc;
    padding: 10px;
    margin-bottom: 10px;
}

.message {
    padding: 5px;
    margin: 5px 0;
}

.sender {
    text-align: right;
    background-color: #d1ffd1;
}

.recipient {
    text-align: left;
    background-color: #ffd1d1;
}

form {
    display: flex;
}

textarea {
    flex: 1;
    padding: 10px;
}

button {
    padding: 10px;
    
}
.button {
    cursor: pointer;
    background-color: rgb(43, 174, 226);
    width: 4cm;
    height: 50px;
    font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
    font-weight: 100px;
    margin-right: 10px;
}

.delete-button {
    background-color: rgb(226, 43, 43); /* Red background for delete button */
}

.button-container {
    display: flex;
    justify-content: center; /* Center the buttons horizontally */
    margin-bottom: 10px;
}



        .file-input-label {
            display: inline-block;
            padding: 10px 15px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
            cursor: pointer;
            width: 300px; 
            height: 150px;;
            text-align: center;
        }

    </style>
</body>
</html