<?php 
session_start(); 
include 'db_connect.php'; 
 
if (!isset($_SESSION['userid']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 
'super_admin')) { 
    header("Location: login.php"); 
    exit; 
} 
 
if (isset($_GET['toggle_user'])) { 
    $user_id = intval($_GET['toggle_user']); 
    $admin_id = $_SESSION['userid']; 
     
    $result = $conn->query("SELECT is_active, role FROM user WHERE userid = $user_id"); 
    if ($result->num_rows > 0) { 
        $user = $result->fetch_assoc(); 
         
        if ($user['role'] == 'super_admin' && $_SESSION['role'] != 'super_admin') { 
            $_SESSION['error_message'] = "You don't have permission to modify super admins."; 
            header("Location: admin_users.php"); 
            exit; 
        } 
         
        $new_status = $user['is_active'] ? 0 : 1; 
         
        if ($conn->query("UPDATE user SET is_active = $new_status WHERE userid = $user_id")) 
{ 
            $action = $new_status ? 'unblocked' : 'blocked'; 
            $conn->query("INSERT INTO admin_actions (admin_id, action_type, target_id, 
description)  
                         VALUES ($admin_id, 'user_block', $user_id, 'User $action by admin')"); 
             
            $_SESSION['success_message'] = "User status updated successfully."; 
        } else { 
            $_SESSION['error_message'] = "Error updating user status: " . $conn->error; 
        } 
    } 
    header("Location: admin_users.php"); 
    exit; 
} 

if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);
    $admin_id = $_SESSION['userid'];

    $result = $conn->query("SELECT role FROM user WHERE userid = $user_id");
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if ($user['role'] == 'super_admin' && $_SESSION['role'] != 'super_admin') {
            $_SESSION['error_message'] = "You don't have permission to delete super admins.";
            header("Location: admin_users.php");
            exit;
        }

        if ($user_id == $_SESSION['userid']) {
            $_SESSION['error_message'] = "You cannot delete your own account.";
            header("Location: admin_users.php");
            exit;
        }

        $conn->query("DELETE FROM property WHERE userid = $user_id");

        if ($conn->query("DELETE FROM user WHERE userid = $user_id")) {
            $conn->query("INSERT INTO admin_actions (admin_id, action_type, target_id, description) VALUES ($admin_id, 'user_delete', $user_id, 'User hard deleted by admin')");

            $_SESSION['success_message'] = "User and associated properties permanently deleted.";
        } else {
            $_SESSION['error_message'] = "Error deleting user: " . $conn->error;
        }
    }
    header("Location: admin_users.php");
    exit;
}

 
$users = $conn->query("SELECT * FROM user WHERE is_deleted = 0 ORDER BY created_at DESC"); 
?> 
<!DOCTYPE html> 
<html lang="en"> 
<head> 
    <meta charset="UTF-8"> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Manage Users | Admin Panel</title> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> 
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"> 
    <link rel="stylesheet" href="style/admin_user.css">
</head> 
<body> 
    <header> 
        <div class="container header-container"> 
            <a href="admin_dashboard.php" class="logo"> 
                <img src="icons/denwhite.svg" alt="DEN LOGO"> 
                <span class="logo-text">Admin Panel</span> 
            </a> 
            <nav> 
                <ul> 
                    <li><a href="admin_dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li> 
                    <li><a href="admin_properties.php"><i class="fas fa-home"></i> Properties</a></li> 
                    <li><a href="admin_users.php"><i class="fas fa-users"></i> Users</a></li> 
                    <?php if ($_SESSION['role'] == 'super_admin'): ?> 
                        <li><a href="admin_management.php"><i class="fas fa-user-shield"></i> Admins</a></li> 
                    <?php endif; ?> 
                    <li><a href="home.php"><i class="fas fa-home"></i> Public Site</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li> 
                </ul> 
            </nav> 
        </div> 
    </header> 
 
    <main class="container dashboard-container"> 
        <div class="dashboard-header"> 
            <h1 class="section-title">Manage Users</h1> 
            <p>View and manage all user accounts on the platform</p> 
        </div> 
 
        <?php if (isset($_SESSION['error_message'])): ?> 
            <div class="alert alert-danger"> 
                <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error_message']; ?> 
                <?php unset($_SESSION['error_message']); ?> 
            </div> 
        <?php endif; ?> 
 
        <?php if (isset($_SESSION['success_message'])): ?> 
            <div class="alert alert-success"> 
                <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success_message']; ?> 
                <?php unset($_SESSION['success_message']); ?> 
            </div> 
        <?php endif; ?> 
 
        <table class="admin-table"> 
            <thead> 
                <tr> 
                    <th>User</th> 
                    <th>Email</th> 
                    <th>Role</th> 
                    <th>Status</th> 
                    <th>Joined</th> 
                    <th>Actions</th> 
                </tr> 
            </thead> 
            <tbody> 
                <?php while ($user = $users->fetch_assoc()): ?> 
                <tr> 
                    <td> 
                        <div style="display: flex; align-items: center;"> 
                            <?php if (!empty($user['profile_image'])): ?> 
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($user['profile_image']); ?>" class="user-avatar" alt="User Avatar"> 
                            <?php else: ?> 
                                <div class="avatar-placeholder"> 
                                    <i class="fas fa-user"></i> 
                                </div> 
                            <?php endif; ?> 
                            <?php echo htmlspecialchars($user['name']); ?> 
                        </div> 
                    </td> 
                    <td><?php echo htmlspecialchars($user['email']); ?></td> 
                    <td> 
                        <span class="role-<?php echo str_replace('_', '-', $user['role']); ?>"> 
                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?> 
                        </span> 
                    </td> 
                    <td> 
                        <?php if ($user['is_active']): ?> 
                            <span class="status-active">Active</span> 
                        <?php else: ?> 
                            <span class="status-inactive">Blocked</span> 
                        <?php endif; ?> 
                    </td> 
                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td> 
                    <td> 
                        <div style="display: flex; flex-wrap: wrap;"> 
                            <?php if ($user['userid'] != $_SESSION['userid']): ?> 
                                <button class="btn btn-<?php echo $user['is_active'] ? 'danger' : 'success'; 
                                ?> btn-sm toggle-user-btn"  
                                        data-userid="<?php echo $user['userid']; ?>" 
                                        data-action="<?php echo $user['is_active'] ? 'block' : 'unblock'; ?>" 
                                        data-username="<?php echo htmlspecialchars($user['name']); ?>"> 
                                    <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>  
                                    <?php echo $user['is_active'] ? 'Block' : 'Unblock'; ?> 
                                </button> 
                                <button class="btn btn-warning btn-sm delete-user-btn"  
                                        data-userid="<?php echo $user['userid']; ?>" 
                                        data-username="<?php echo htmlspecialchars($user['name']); ?>"> 
                                    <i class="fas fa-trash-alt"></i> Delete 
                                </button> 
                            <?php endif; ?> 
                        </div> 
                    </td> 
                </tr> 
                <?php endwhile; ?> 
            </tbody> 
        </table> 
    </main> 
 
    <div class="modal-overlay" id="toggleUserModal"> 
        <div class="modal-content"> 
            <div class="modal-header"> 
                <h2><i class="fas fa-exclamation-triangle"></i> <span id="modalActionText">Block</span> User</h2> 
            </div> 
            <div class="modal-body"> 
                <p>Are you sure you want to <span id="actionText">block</span> <strong id="usernameText">[username]</strong>?</p> 
                <p>This action will <span id="actionEffectText">prevent the user from logging in, posting properties, or making any changes to their account</span>.</p> 
                <p><strong>This action can be reversed at any time.</strong></p> 
            </div> 
            <div class="modal-footer"> 
                <button class="modal-btn modal-btn-cancel" id="cancelToggleUser">Cancel</button> 
                <a href="#" class="modal-btn modal-btn-confirm" id="confirmToggleUser"> 
                    <i class="fas fa-check"></i> Confirm 
                </a> 
            </div> 
        </div> 
    </div> 
 
    <div class="modal-overlay" id="deleteUserModal"> 
        <div class="modal-content"> 
            <div class="modal-header"> 
                <h2><i class="fas fa-exclamation-triangle"></i> Delete User</h2> 
            </div> 
            <div class="modal-body"> 
                <p>Are you sure you want to permanently delete <strong id="deleteUsernameText">[username]</strong>?</p> 
                <p>This action will:</p> 
                <ul style="text-align: left; margin-left: 20px; margin-bottom: 15px;"> 
                    <li>Remove all user data from the system</li> 
                    <li>Delete any properties posted by this user</li> 
                    <li>Prevent the user from accessing their account</li> 
                </ul> 
                <p><strong>This action cannot be undone!</strong></p> 
            </div> 
            <div class="modal-footer"> 
                <button class="modal-btn modal-btn-cancel" id="cancelDeleteUser">Cancel</button> 
                <a href="#" class="modal-btn modal-btn-confirm" id="confirmDeleteUser"> 
                    <i class="fas fa-trash-alt"></i> Delete Permanently 
                </a> 
            </div> 
        </div> 
    </div> 
 
    <footer> 
        <div class="container"> 
            <div class="footer-container"> 
                <div class="footer-about"> 
                    <div class="footer-logo"> 
                        <img src="icons/denwhite.svg" alt="DEN LOGO"> 
                        <span class="footer-logo-text"></span> 
                    </div> 
                    <p>Your trusted partner in finding the perfect property. We connect buyers and sellers with the best real estate opportunities.</p> 
                    <div class="social-links"> 
                        <a href="#"><i class="fab fa-facebook-f"></i></a> 
                        <a href="#"><i class="fab fa-twitter"></i></a> 
                        <a href="#"><i class="fab fa-instagram"></i></a> 
                        <a href="#"><i class="fab fa-whatsapp"></i></a> 
                    </div> 
                </div> 
                <div class="footer-links"> 
                    <h3>Quick Links</h3> 
                    <ul> 
                        <li><a href="home.php">Public Site</a></li> 
                        <li><a href="admin_dashboard.php">Dashboard</a></li> 
                        <li><a href="admin_properties.php">Properties</a></li> 
                        <li><a href="admin_users.php">Users</a></li> 
                    </ul> 
                </div> 
                <div class="footer-contact"> 
                    <h3>Contact Us</h3> 
                    <p><i class="fas fa-phone"></i> +964 (751) 202-2694</p> 
                    <p><i class="fas fa-envelope"></i> info@duhokestatenavigator.com</p> 
                    <p><i class="fas fa-clock"></i> Mon-Fri: 9AM - 6PM</p> 
                </div> 
            </div> 
            <div class="copyright"> 
                <p>&copy; <?php echo date('Y'); ?> Duhok Estate Navigator. All rights reserved.</p> 
            </div> 
        </div> 
    </footer> 
 
    <script> 
        const toggleUserModal = document.getElementById('toggleUserModal'); 
        const toggleUserBtns = document.querySelectorAll('.toggle-user-btn'); 
        const cancelToggleUserBtn = document.getElementById('cancelToggleUser'); 
        const confirmToggleUserBtn = document.getElementById('confirmToggleUser'); 
        const modalActionText = document.getElementById('modalActionText'); 
        const actionText = document.getElementById('actionText'); 
        const actionEffectText = document.getElementById('actionEffectText'); 
        const usernameText = document.getElementById('usernameText'); 
         
        let currentUserIdToToggle = null; 
        let currentAction = null; 
         
        toggleUserBtns.forEach(btn => { 
            btn.addEventListener('click', (e) => { 
                e.preventDefault(); 
                currentUserIdToToggle = btn.getAttribute('data-userid'); 
                currentAction = btn.getAttribute('data-action'); 
                const username = btn.getAttribute('data-username'); 
                 
                if (currentAction === 'block') { 
                    modalActionText.textContent = 'Block'; 
                    actionText.textContent = 'block'; 
                    actionEffectText.textContent = 'prevent the user from logging in, posting properties, or making any changes to their account'; 
                    confirmToggleUserBtn.className = 'modal-btn modal-btn-confirm'; 
                    confirmToggleUserBtn.innerHTML = '<i class="fas fa-ban"></i> Block User'; 
                } else { 
                    modalActionText.textContent = 'Unblock'; 
                    actionText.textContent = 'unblock'; 
                    actionEffectText.textContent = 'allow the user to log in and access all features again'; 
                    confirmToggleUserBtn.className = 'modal-btn modal-btn-confirm'; 
                    confirmToggleUserBtn.innerHTML = '<i class="fas fa-check"></i> Unblock User'; 
                } 
                 
                usernameText.textContent = username; 
                toggleUserModal.classList.add('active'); 
            }); 
        }); 
         
        cancelToggleUserBtn.addEventListener('click', () => { 
            toggleUserModal.classList.remove('active'); 
            currentUserIdToToggle = null; 
            currentAction = null; 
        }); 
         
        confirmToggleUserBtn.addEventListener('click', (e) => { 
            e.preventDefault(); 
            if (currentUserIdToToggle) { 
                window.location.href = `admin_users.php?toggle_user=${currentUserIdToToggle}`; 
            } 
        }); 
         
        const deleteUserModal = document.getElementById('deleteUserModal'); 
        const deleteUserBtns = document.querySelectorAll('.delete-user-btn'); 
        const cancelDeleteUserBtn = document.getElementById('cancelDeleteUser'); 
        const confirmDeleteUserBtn = document.getElementById('confirmDeleteUser'); 
        const deleteUsernameText = document.getElementById('deleteUsernameText'); 
         
        let currentUserIdToDelete = null; 
         
        deleteUserBtns.forEach(btn => { 
            btn.addEventListener('click', (e) => { 
                e.preventDefault(); 
                currentUserIdToDelete = btn.getAttribute('data-userid'); 
                const username = btn.getAttribute('data-username'); 
                 
                deleteUsernameText.textContent = username; 
                deleteUserModal.classList.add('active'); 
            }); 
        }); 
         
        cancelDeleteUserBtn.addEventListener('click', () => { 
            deleteUserModal.classList.remove('active'); 
            currentUserIdToDelete = null; 
        }); 
         
        confirmDeleteUserBtn.addEventListener('click', (e) => { 
            e.preventDefault(); 
            if (currentUserIdToDelete) { 
                window.location.href = `admin_users.php?delete_user=${currentUserIdToDelete}`; 
            } 
        }); 
         
        window.addEventListener('click', (e) => { 
            if (e.target === toggleUserModal) { 
                toggleUserModal.classList.remove('active'); 
                currentUserIdToToggle = null; 
                currentAction = null; 
            } 
             
            if (e.target === deleteUserModal) { 
                deleteUserModal.classList.remove('active'); 
                currentUserIdToDelete = null; 
            } 
        }); 
    </script> 
</body> 
</html> 
<?php $conn->close(); ?> 