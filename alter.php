<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h3>User Accounts</h3>
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

<script>
function openUpdateModal(id, firstName, surname, email, role) {
    document.getElementById('updateUserId').value = id;
    document.getElementById('updateFirstName').value = firstName;
    document.getElementById('updateSurname').value = surname;
    document.getElementById('updateEmail').value = email;
    document.getElementById('updateRole').value = role;
    document.getElementById('updateUserModal').style.display = 'block';
}

function closeUpdateModal() {
    document.getElementById('updateUserModal').style.display = 'none';
}

function openChangeRoleModal(id, role) {
    document.getElementById('changeRoleId').value = id;
    document.getElementById('changeRoleSelect').value = role;
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
</script>

<style>
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
.status-active {
    color: green;
}
.status-inactive {
    color: red;
}
.modal {
    display: none;
    position: fixed;
    z-index: 1;
    padding-top: 60px;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgb(0,0,0);
    background-color: rgba(0,0,0,0.4);
}
.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
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
</style>

</body>
</html>