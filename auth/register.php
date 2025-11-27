<?php 
$PROJECT_ROOT = '/Hotel%20Management%20system';
include('../includes/header.php'); 
include('../includes/config.php'); 
error_reporting(E_ALL);

if (isset($_SESSION['user_id'])) {
    header('Location: ../user/dashboard.php');
    exit;
}

// Placeholder for error message display
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); 
}
?>

<div class="auth-page">
    <div class="auth-container">
        <div class="auth-header">
            <h2>Join HotelBooking</h2>
            <p>Create your account in seconds to start booking.</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert alert-danger" style="color: #dc3545; border: 1px solid #dc3545; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
                <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="<?= $PROJECT_ROOT ?>/bookings/auth_process.php" method="POST">
            <input type="hidden" name="action" value="register">
            
            <div class="form-group form-control-icon">
                <label for="full_name">Full Name</label>
                <i class="fas fa-user"></i>
                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Your Full Name" required>
            </div>

            <div class="form-group form-control-icon">
                <label for="email">Email Address</label>
                <i class="fas fa-envelope"></i>
                <input type="email" id="email" name="email" class="form-control" placeholder="your.email@example.com" required>
            </div>

            <div class="form-group form-control-icon">
                <label for="phone">Phone Number</label>
                <i class="fas fa-phone"></i>
                <input type="tel" id="phone" name="phone" class="form-control" placeholder="+91 XXXX XXXX" required>
            </div>

            <div class="form-group form-control-icon">
                <label for="password">Choose Password</label>
                <i class="fas fa-lock"></i>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-action btn-auth-submit">
                Register Account
            </button>
        </form>

        <div class="auth-footer">
            <p>
                Already have an account? 
                <a href="login.php">Log in here.</a>
            </p>
        </div>
    </div>
</div>

<?php include('../includes/footer.php'); ?>