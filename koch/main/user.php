<?php
session_start();
include_once '../component/header.php';
include_once '../component/menubar.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Get user information
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$user_role = $_SESSION['user_role'] ?? 'user';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i>
                        <?php echo _('User Profile'); ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center">
                                <img src="../img/user/avatar.png" class="rounded-circle" alt="User Avatar" width="150">
                                <h4 class="mt-3"><?php echo htmlspecialchars($username); ?></h4>
                                <p class="text-muted"><?php echo ucfirst(htmlspecialchars($user_role)); ?></p>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <form id="userProfileForm">
                                <div class="form-group">
                                    <label for="username"><?php echo _('Username'); ?></label>
                                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($username); ?>" readonly>
                                </div>
                                <div class="form-group">
                                    <label for="email"><?php echo _('Email'); ?></label>
                                    <input type="email" class="form-control" id="email" placeholder="<?php echo _('Enter your email'); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="phone"><?php echo _('Phone'); ?></label>
                                    <input type="tel" class="form-control" id="phone" placeholder="<?php echo _('Enter your phone number'); ?>">
                                </div>
                                <div class="form-group">
                                    <label for="company"><?php echo _('Company'); ?></label>
                                    <input type="text" class="form-control" id="company" placeholder="<?php echo _('Enter your company name'); ?>">
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    <?php echo _('Update Profile'); ?>
                                </button>
                                <a href="logout.php" class="btn btn-danger ml-2">
                                    <i class="fas fa-sign-out-alt"></i>
                                    <?php echo _('Logout'); ?>
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-history"></i>
                        <?php echo _('Recent Activity'); ?>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><?php echo _('Date'); ?></th>
                                    <th><?php echo _('Activity'); ?></th>
                                    <th><?php echo _('Status'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>2024-03-23 10:30</td>
                                    <td><?php echo _('Quotation Request'); ?></td>
                                    <td><span class="badge badge-success"><?php echo _('Completed'); ?></span></td>
                                </tr>
                                <tr>
                                    <td>2024-03-22 15:45</td>
                                    <td><?php echo _('Product Inquiry'); ?></td>
                                    <td><span class="badge badge-info"><?php echo _('In Progress'); ?></span></td>
                                </tr>
                                <tr>
                                    <td>2024-03-20 09:15</td>
                                    <td><?php echo _('Contact Form'); ?></td>
                                    <td><span class="badge badge-success"><?php echo _('Completed'); ?></span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#userProfileForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '../api/update_profile.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                alert('<?php echo _('Profile updated successfully!'); ?>');
            },
            error: function() {
                alert('<?php echo _('Error updating profile. Please try again.'); ?>');
            }
        });
    });
});
</script>

<?php include_once '../component/footer.php'; ?>
