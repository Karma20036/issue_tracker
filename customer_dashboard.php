<?php
session_start();

// Check if the user is logged in and is a Customer
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Customer') {
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

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_sql = "SELECT first_name, company_name FROM users WHERE id = '$user_id'";
$user_result = $conn->query($user_sql);
$user_details = $user_result->fetch_assoc();
$first_name = $user_details['first_name'];
$company_name = $user_details['company_name'];

// Fetch categories from database
$sql = "SELECT id, name FROM categories";
$result = $conn->query($sql);

// Fetch the list of Global Admins
$global_admin_sql = "SELECT id, first_name, surname FROM users WHERE role = 'Global Admin'";
$global_admin_result = $conn->query($global_admin_sql);
$global_admins = [];
while ($row = $global_admin_result->fetch_assoc()) {
    $global_admins[] = $row;
}

// Fetch pending tickets
$pending_tickets_sql = "SELECT * FROM tickets WHERE user_id = '$user_id' AND status = 'Pending'";
$pending_tickets_result = $conn->query($pending_tickets_sql);

// Fetch recent tickets (created within the last 7 days)
$recent_tickets_sql = "SELECT * FROM tickets WHERE user_id = '$user_id' AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
$recent_tickets_result = $conn->query($recent_tickets_sql);

// Fetch all tickets (ticket history)
$all_tickets_sql = "SELECT * FROM tickets WHERE user_id = '$user_id'";
$all_tickets_result = $conn->query($all_tickets_sql);

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

    <title>Customer Dashboard</title>
</head>
<body>

    <!-- SIDEBAR -->
    <section id="sidebar">
        <a href="#" class="brand">
            <i class='bx bxs-smile'></i>
            <span class="text">Customer Dashboard</span>
        </a>
        <ul class="side-menu top">
            <li class="active">
                <a href="#raise-ticket" onclick="showSection('raise-ticket')">
                    <i class='bx bxs-bookmark-plus'></i>
                    <span class="text">Raise a New Ticket</span>
                </a>
            </li>
            <li>
                <a href="#view-tickets" onclick="showSection('view-tickets')">
                    <i class='bx bxs-book'></i>
                    <span class="text">View My Tickets</span>
                </a>
            </li>
            <li>
                <a href="#update-ticket" onclick="showSection('update-ticket')">
                    <i class='bx bxs-edit'></i>
                    <span class="text">Update a Ticket</span>
                </a>
            </li>
            <li>
                <a href="#cancel-ticket" onclick="showSection('cancel-ticket')">
                    <i class='bx bxs-x-circle'></i>
                    <span class="text">Cancel a Ticket</span>
                </a>
            </li>
            <li>
                <a href="#ticket-feedback" onclick="showSection('ticket-feedback')">
                    <i class='bx bxs-check-circle'></i>
                    <span class="text">Ticket Feedback</span>
                </a>
            </li>
            <li>
                <a href="#chat-section" onclick="showSection('chat-section')">
                    <i class='bx bxs-message'></i>
                    <span class="text">Chat with Global Admin</span>
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
                 <!-- Welcome Message -->
            <div class="welcome-message">
                <h2>Welcome, <?= $first_name ?> from <?= $company_name ?>!</h2>
            </div>
        </nav>
       
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

            

            <section id="raise-ticket" class="section">
                <h3>Raise a New Ticket</h3>
                <form id="raiseTicketForm" action="raise_ticket.php" method="POST" enctype="multipart/form-data">
                    <select name="category" required>
                        <?php
                    // Populate dropdown with categories
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                        }
                    } else {
                        echo '<option value="">No categories found</option>';
                    }
                        ?>
                    <textarea name="description" placeholder="Description" required></textarea>
                    <input type="file" name="attachment">
                    <button type="submit">Raise Ticket</button>
                </form>
            </section>

            <section id="view-tickets" class="section" style="display:none;">
                <h3>View My Tickets</h3>
                <div>
                    <h4>Pending Tickets</h4>
                    <div id="pending-tickets-content">
                        <?php
                        if ($pending_tickets_result->num_rows > 0) {
                            echo '<table>';
                            echo '<tr><th>ID</th><th>Category</th><th>Description</th><th>Status</th><th>Created At</th></tr>';
                            while ($row = $pending_tickets_result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $row['id'] . '</td>';
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
                            echo '<tr><th>ID</th><th>Category</th><th>Description</th><th>Status</th><th>Created At</th></tr>';
                            while ($row = $recent_tickets_result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $row['id'] . '</td>';
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
                            echo '<tr><th>ID</th><th>Category</th><th>Description</th><th>Status</th><th>Created At</th><th>Resolved At</th><th>Responses</th></tr>';
                            while ($row = $all_tickets_result->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>' . $row['id'] . '</td>';
                                echo '<td>' . $row['category'] . '</td>';
                                echo '<td>' . $row['description'] . '</td>';
                                echo '<td>' . $row['status'] . '</td>';
                                echo '<td>' . $row['created_at'] . '</td>';
                                echo '<td>' . ($row['resolved_at'] ? $row['resolved_at'] : 'N/A') . '</td>';
                                echo '<td><button onclick="openResponseModal(' . $row['id'] . ')">View Responses</button></td>';
                                echo '</tr>';
                            }
                            echo '</table>';
                        } else {
                            echo 'No ticket history found.';
                        }
                        ?>
                    </div>
                </div>
            </section>

            <!-- Response Modal -->
            <div id="responseModal" class="modal" style="display:none;">
                <div class="modal-content">
                    <span class="close" onclick="closeResponseModal()">&times;</span>
                    <h3>Ticket Responses</h3>
                    <div id="responseContent"></div>
                </div>
            </div>

            <section id="update-ticket" class="section" style="display:none;">
                <h3>Update a Ticket</h3>
                <form id="fetchUpdateTicketForm" method="POST">
                    <input type="text" name="ticket_id" placeholder="Enter Ticket ID" required>
                    <button type="button" onclick="fetchTicketDetails('update')">Fetch Ticket</button>
                </form>
                <div id="updateTicketDetails" style="display:none;">
                    <h4>Ticket Details</h4>
                    <p id="updateTicketInfo"></p>
                </div>
                <form id="updateTicketForm" action="update_ticket.php" method="POST" enctype="multipart/form-data" style="display:none;">
                    <input type="hidden" name="ticket_id" id="updateTicketId">
                    <input type="text" name="category" id="updateCategory" placeholder="Issue Category" required>
                    <textarea name="description" id="updateDescription" placeholder="Description" required></textarea>
                    <input type="file" name="attachment">
                    <button type="submit">Update Ticket</button>
                </form>
            </section>

            <section id="cancel-ticket" class="section" style="display:none;">
                <h3>Cancel a Ticket</h3>
                <form id="fetchCancelTicketForm" method="POST">
                    <input type="text" name="ticket_id" placeholder="Enter Ticket ID" required>
                    <button type="button" onclick="fetchTicketDetails('cancel')">Fetch Ticket</button>
                </form>
                <div id="cancelTicketDetails" style="display:none;">
                    <h4>Ticket Details</h4>
                    <p id="cancelTicketInfo"></p>
                </div>
                <form id="cancelTicketForm" action="cancel_ticket.php" method="POST" style="display:none;">
                    <input type="hidden" name="ticket_id" id="cancelTicketId">
                    <textarea name="reason" placeholder="Reason for cancellation" required></textarea>
                    <button type="submit">Cancel Ticket</button>
                </form>
            </section>
             <!-- Chat Interface -->
            <section id="chat-section" class="section" style="display:none;">
                <h3>Chat with Global Admin</h3>
                <select id="globalAdminSelect">
                    <?php foreach ($global_admins as $admin): ?>
                        <option value="<?= $admin['id'] ?>"><?= $admin['first_name'] . ' ' . $admin['surname'] ?></option>
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


            <section id="ticket-feedback" class="section" style="display:none;">
                <h3>Ticket Feedback</h3>
                <form id="fetchCloseTicketForm" method="POST">
                    <input type="text" name="ticket_id" placeholder="Enter Ticket ID" required>
                    <button type="button" onclick="fetchTicketDetails('close')">Fetch Ticket</button>
                </form>
                <div id="closeTicketDetails" style="display:none;">
                    <h4>Ticket Details</h4>
                    <p id="closeTicketInfo"></p>
                </div>
                <form id="closeTicketForm" action="submit_feedback.php" method="POST" style="display:none;">
                    <input type="hidden" name="ticket_id" id="closeTicketId">
                    <p>Was your issue resolved?</p>
                    <select name="resolved" id="resolved" required>
                        <option value="Yes">Yes</option>
                        <option value="No">No</option>
                    </select>
                    <p>Are you happy with the service?</p>
                    <select name="service_rating" id="service_rating" required>
                        <option value="Excellent">Excellent</option>
                        <option value="Good">Good</option>
                        <option value="Poor">Poor</option>
                    </select>
                    <textarea name="comments" id="comments" placeholder="Comments"></textarea>
                    <button type="submit">Submit Feedback</button>
                </form>
            </section>
        </main>
        <!-- MAIN -->
    </section>
    <!-- CONTENT -->

    <script src="script.js"></script>
    <script>
    function showSection(sectionId) {
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => {
            section.style.display = 'none';
        });
        document.getElementById(sectionId).style.display = 'block';
    }

    async function fetchTicketDetails(action) {
        const ticketId = document.querySelector(`#fetch${capitalizeFirstLetter(action)}TicketForm input[name='ticket_id']`).value;
        const response = await fetch(`get_ticket_details.php?ticket_id=${ticketId}`);
        const ticket = await response.json();

        if (ticket.error) {
            alert(ticket.error);
            return;
        }

        let ticketInfo = `ID: ${ticket.id}<br>Category: ${ticket.category}<br>Description: ${ticket.description}<br>Status: ${ticket.status}<br>Created At: ${ticket.created_at}`;
        if (ticket.resolved_at) {
            ticketInfo += `<br>Resolved At: ${ticket.resolved_at}`;
        }

        if (action === 'update') {
            document.getElementById('updateTicketId').value = ticket.id;
            document.getElementById('updateCategory').value = ticket.category;
            document.getElementById('updateDescription').value = ticket.description;
            document.getElementById('updateTicketDetails').style.display = 'block';
            document.getElementById('updateTicketInfo').innerHTML = ticketInfo;
            document.getElementById('updateTicketForm').style.display = 'block';
        } else if (action === 'cancel') {
            document.getElementById('cancelTicketId').value = ticket.id;
            document.getElementById('cancelTicketDetails').style.display = 'block';
            document.getElementById('cancelTicketInfo').innerHTML = ticketInfo;
            document.getElementById('cancelTicketForm').style.display = 'block';
        } else if (action === 'close') {
            document.getElementById('closeTicketId').value = ticket.id;
            document.getElementById('closeTicketDetails').style.display = 'block';
            document.getElementById('closeTicketInfo').innerHTML = ticketInfo;
            document.getElementById('closeTicketForm').style.display = 'block';
        }
    }
    
    document.addEventListener('DOMContentLoaded', () => {
    const globalAdminSelect = document.getElementById('globalAdminSelect');
    const messageForm = document.getElementById('messageForm');
    const recipientIdInput = document.getElementById('recipientId');
    const chatBox = document.getElementById('chatBox');
    const messageInput = document.getElementById('messageInput');

    // Set up event listener for global admin selection
    globalAdminSelect.addEventListener('change', () => {
        recipientIdInput.value = globalAdminSelect.value;
        fetchMessages();
    });

    // Set up event listener for message form submission
    messageForm.addEventListener('submit', (e) => {
        e.preventDefault();
        sendMessage();
    });

    // Initialize chat with the first admin
    globalAdminSelect.dispatchEvent(new Event('change'));

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
async function openResponseModal(ticketId) {
    try {
        const response = await fetch(`get_ticket_responses.php?ticket_id=${ticketId}`);
        const responses = await response.json();

        const responseContent = document.getElementById('responseContent');
        responseContent.innerHTML = '';

        if (responses.error) {
            responseContent.innerHTML = '<p>' + responses.error + '</p>';
        } else if (responses.length > 0) {
            responses.forEach(response => {
                const responseDiv = document.createElement('div');
                responseDiv.classList.add('response');
                responseDiv.innerHTML = `<p><strong>${response.user}</strong>: ${response.message}</p><p><em>${response.created_at}</em></p>`;
                responseContent.appendChild(responseDiv);
            });
        } else {
            responseContent.innerHTML = '<p>No responses found.</p>';
        }

        document.getElementById('responseModal').style.display = 'block';
    } catch (error) {
        console.error('Error fetching responses:', error);
        alert('An error occurred while fetching responses. Please try again later.');
    }
}
function closeResponseModal() {
       document.getElementById('responseModal').style.display = 'none';
   }

    function capitalizeFirstLetter(string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    }
</script>

    <style>
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

    </style>

<!--Start of Tawk.to Script-->
<!--Start of Tawk.to Script-->
<script type="text/javascript">
var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
(function(){
var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
s1.async=true;
s1.src='https://embed.tawk.to/668fc0347a36f5aaec9717d6/1i2godk4h';
s1.charset='UTF-8';
s1.setAttribute('crossorigin','*');
s0.parentNode.insertBefore(s1,s0);
})();
</script>
<!--End of Tawk.to Script-->
</body>
</html>
