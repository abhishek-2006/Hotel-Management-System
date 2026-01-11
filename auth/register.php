<?php 
// Define project root for consistent pathing
$PROJECT_ROOT = '/Hotel%20Management%20system';
include('../includes/config.php');

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: {$PROJECT_ROOT}/user/dashboard.php");
    exit;
}

$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join The Citadel Retreat | Register</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= $PROJECT_ROOT ?>/assets/favicon.ico">
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/styles.css"/>
    
    <style>
        :root {
            --color-brand: #0077b6;
            --color-brand-dark: #023e8a;
            --color-text-dark: #1d3557;
            --color-text-light: #4a4a4a;
            --color-bg-auth: #f8f9fa;
            --shadow-lg: 0 15px 35px rgba(0,0,0,0.1);
            --border-radius: 12px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #cfdef3 0%, #e0eafc 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px; /* Extra padding for scroll room */
            overflow-y: auto; 
        }

        .auth-wrapper {
            width: 100%;
            max-width: 500px;
            margin: auto;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 25px;
        }

        .logo-container img {
            max-width: 160px;
            height: auto;
        }

        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .auth-header h2 {
            font-family: 'Playfair Display', serif;
            color: var(--color-text-dark);
            font-size: 1.85rem;
            margin-bottom: 8px;
        }

        .auth-header p {
            color: var(--color-text-light);
            font-size: 0.95rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--color-text-dark);
        }

        .form-control-wrapper {
            position: relative;
        }

        .form-control-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--color-brand);
            font-size: 0.9rem;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px 12px 42px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--color-brand);
            box-shadow: 0 0 0 4px rgba(0, 119, 182, 0.1);
        }

        .btn-register {
            width: 100%;
            padding: 14px;
            background: var(--color-brand);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
            letter-spacing: 1px;
        }

        .btn-register:hover {
            background: var(--color-brand-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 119, 182, 0.3);
        }

        .auth-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9rem;
            color: var(--color-text-light);
        }

        .auth-footer a {
            color: var(--color-brand);
            text-decoration: none;
            font-weight: 700;
        }

        .back-home {
            display: inline-block;
            margin-top: 15px;
            font-size: 0.85rem;
            color: var(--color-text-light);
            text-decoration: none;
            transition: color 0.3s;
        }

        .back-home:hover {
            color: var(--color-brand);
        }

        .alert {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            text-align: center;
            background: #ffe5e5;
            color: #d63031;
            border: 1px solid #ffcccc;
        }
    </style>
</head>
<body>

<div class="auth-wrapper">
    <div class="auth-card">
        <!-- Logo Section -->
        <div class="logo-container">
            <a href="<?= $PROJECT_ROOT ?>/index.php">
                <img src="<?= $PROJECT_ROOT ?>/assets/images/logo.png" alt="Citadel Retreat Logo" onerror="this.style.display='none'">
                <?php if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $PROJECT_ROOT . '/assets/images/logo.png')): ?>
                    <h1 style="font-family: 'Playfair Display', serif; color: var(--color-brand); font-size: 1.4rem;">THE CITADEL</h1>
                <?php endif; ?>
            </a>
        </div>

        <div class="auth-header">
            <h2>Join The Citadel</h2>
            <p>Create an account for exclusive member benefits</p>
        </div>

        <?php if ($error_message): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="<?= $PROJECT_ROOT ?>/bookings/auth_process.php" method="POST">
            <input type="hidden" name="action" value="register">

            <div class="form-grid">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <div class="form-control-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" id="full_name" name="full_name" class="form-control" placeholder="John Doe" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="form-control-wrapper">
                        <i class="fas fa-envelope"></i>
                        <input type="email" id="email" name="email" class="form-control" placeholder="john@example.com" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <div class="form-control-wrapper">
                        <i class="fas fa-phone"></i>
                        <input type="tel" id="phone" name="phone" class="form-control" placeholder="+91 98765 43210" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Create Password</label>
                    <div class="form-control-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-register">CREATE ACCOUNT</button>
        </form>

        <div class="auth-footer">
            <p>Already a member? <a href="login.php">Sign in here</a></p>
            <a href="<?= $PROJECT_ROOT ?>/index.php" class="back-home">
                <i class="fas fa-arrow-left"></i> Back to Homepage
            </a>
        </div>
    </div>
</div>

</body>
</html>