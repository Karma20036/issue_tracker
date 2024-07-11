const allSideMenu = document.querySelectorAll('#sidebar .side-menu.top li a');

allSideMenu.forEach(item => {
    const li = item.parentElement;

    item.addEventListener('click', function () {
        allSideMenu.forEach(i => {
            i.parentElement.classList.remove('active');
        })
        li.classList.add('active');
    })
});

// TOGGLE SIDEBAR
const menuBar = document.querySelector('#content nav .bx.bx-menu');
const sidebar = document.getElementById('sidebar');

menuBar.addEventListener('click', function () {
    sidebar.classList.toggle('hide');
});

const searchButton = document.querySelector('#content nav form .form-input button');
const searchButtonIcon = document.querySelector('#content nav form .form-input button .bx');
const searchForm = document.querySelector('#content nav form');

searchButton.addEventListener('click', function (e) {
    if (window.innerWidth < 576) {
        e.preventDefault();
        searchForm.classList.toggle('show');
        if (searchForm.classList.contains('show')) {
            searchButtonIcon.classList.replace('bx-search', 'bx-x');
        } else {
            searchButtonIcon.classList.replace('bx-x', 'bx-search');
        }
    }
});

if (window.innerWidth < 768) {
    sidebar.classList.add('hide');
} else if (window.innerWidth > 576) {
    searchButtonIcon.classList.replace('bx-x', 'bx-search');
    searchForm.classList.remove('show');
}

window.addEventListener('resize', function () {
    if (this.innerWidth > 576) {
        searchButtonIcon.classList.replace('bx-x', 'bx-search');
        searchForm.classList.remove('show');
    }
});

const switchMode = document.getElementById('switch-mode');

switchMode.addEventListener('change', function () {
    if (this.checked) {
        document.body.classList.add('dark');
    } else {
        document.body.classList.remove('dark');
    }
});

// Fetch and populate data for the dashboard stats and other sections
document.addEventListener('DOMContentLoaded', () => {
    fetchDashboardStats();
    fetchTicketStats();
    fetchResolutionStats();
    fetchManageUsers();
    fetchTickets();

    async function fetchDashboardStats() {
        const response = await fetch('get_dashboard_stats.php');
        const stats = await response.json();

        document.getElementById('new-users').innerText = stats.new_users;
        document.getElementById('total-users').innerText = stats.total_users;
        document.getElementById('new-tickets').innerText = stats.new_tickets;
        document.getElementById('total-tickets').innerText = stats.total_tickets;
    }

    async function fetchTicketStats() {
        const response = await fetch('get_ticket_stats.php');
        const stats = await response.json();

        const ticketStats = document.getElementById('ticket-stats');
        ticketStats.innerHTML = `
            <tr>
                <td>Pending</td>
                <td>${stats.pending}</td>
            </tr>
            <tr>
                <td>Assigned</td>
                <td>${stats.assigned}</td>
            </tr>
            <tr>
                <td>Responded</td>
                <td>${stats.responded}</td>
            </tr>
            <tr>
                <td>Resolved</td>
                <td>${stats.resolved}</td>
            </tr>
            <tr>
                <td>Cancelled</td>
                <td>${stats.cancelled}</td>
            </tr>
        `;
    }

    async function fetchResolutionStats() {
        const response = await fetch('get_resolution_stats.php');
        const stats = await response.json();

        document.getElementById('avg-resolution-time-org').innerText = stats.avg_resolution_time_org;
        document.getElementById('avg-resolution-time-admin').innerText = stats.avg_resolution_time_admin;
        document.getElementById('global-admin-rating').innerText = stats.global_admin_rating;
        document.getElementById('org-satisfaction-index').innerText = stats.org_satisfaction_index;
    }

    async function fetchManageUsers() {
        const response = await fetch('get_manage_users.php');
        const users = await response.json();

        let userContent = '<table><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>';
        users.forEach(user => {
            userContent += `
                <tr>
                    <td>${user.id}</td>
                    <td>${user.first_name} ${user.surname}</td>
                    <td>${user.email}</td>
                    <td>${user.role}</td>
                    <td>
                        <button onclick="openUpdateModal(${user.id}, '${user.first_name}', '${user.surname}', '${user.email}', '${user.role}')">Update</button>
                        <button onclick="openChangeRoleModal(${user.id}, '${user.role}')">Change Role</button>
                        <button onclick="openRevokeAccessModal(${user.id})">Revoke Access</button>
                    </td>
                </tr>
            `;
        });
        userContent += '</table>';
        document.getElementById('manage-users-content').innerHTML = userContent;
    }

    async function fetchTickets() {
        const response = await fetch('get_tickets.php');
        const tickets = await response.json();

        let ticketContent = '<table><tr><th>ID</th><th>Status</th><th>User</th><th>Organization</th><th>Actions</th></tr>';
        tickets.forEach(ticket => {
            ticketContent += `
                <tr>
                    <td>${ticket.id}</td>
                    <td>${ticket.status}</td>
                    <td>${ticket.user_name}</td>
                    <td>${ticket.organization_name}</td>
                    <td>
                        <button onclick="assignTicket(${ticket.id})">Assign</button>
                        <button onclick="respondTicket(${ticket.id})">Respond</button>
                        <button onclick="closeTicket(${ticket.id})">Close</button>
                    </td>
                </tr>
            `;
        });
        ticketContent += '</table>';
        document.getElementById('view-tickets-content').innerHTML = ticketContent;
    }
});

window.updateUser = function (userId) {
    openUpdateModal(userId);
};

window.manageAccessLevel = function (userId) {
    openChangeRoleModal(userId);
};

window.revokeAccess = function (userId) {
    if (confirm('Are you sure you want to revoke access for this user?')) {
        document.getElementById('revokeUserId').value = userId;
        document.getElementById('revokeAccessForm').submit();
    }
};

window.assignTicket = function (ticketId) {
    // Implement assign ticket functionality
};

window.respondTicket = function (ticketId) {
    // Implement respond to ticket functionality
};

window.closeTicket = function (ticketId) {
    // Implement close ticket functionality
};

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("createOrgBtn").addEventListener("click", function() {
        document.getElementById("createOrgForm").submit();
    });
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
 
function previewLogo(input) {
    var file = input.files[0];
    var reader = new FileReader();

    reader.onload = function(e) {
        document.getElementById('logo-preview').src = e.target.result;
        document.getElementById('logo-preview').style.display = 'block';
    };

    reader.readAsDataURL(file);
    }