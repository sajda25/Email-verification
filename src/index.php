<?php
session_start();
require_once 'functions.php';

$message = '';
$messageType = '';
$showVerificationForm = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['email']) && !isset($_POST['verification_code'])) {
        // Handle email submission
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            $_SESSION['verification_code'] = $code;
            $_SESSION['verification_email'] = $email;
            
            if (sendVerificationEmail($email, $code)) {
                $message = 'Verification code has been sent to your email.';
                $messageType = 'success';
                $showVerificationForm = true;
            } else {
                $message = 'Failed to send verification code. Please try again.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid email address.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['email']) && isset($_POST['verification_code'])) {
        // Handle verification code submission
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        $code = filter_var($_POST['verification_code'], FILTER_SANITIZE_STRING);
        
        if (verifyCode($email, $code)) {
            if (registerEmail($email)) {
                $message = 'Email successfully registered! You will receive XKCD comics daily.';
                $messageType = 'success';
                session_destroy(); // Clear session after successful registration
            } else {
                $message = 'Failed to register email. Please try again.';
                $messageType = 'error';
                $showVerificationForm = true;
            }
        } else {
            $message = 'Invalid verification code.';
            $messageType = 'error';
            $showVerificationForm = true;
        }
    }
}

// Show verification form if we have a pending verification
if (isset($_SESSION['verification_code']) && isset($_SESSION['verification_email'])) {
    $showVerificationForm = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XKCD Comic Subscription</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color:rgb(23, 99, 27);
        }
        .message {
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
            font-size: 16px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
        .section {
            margin: 20px 0;
            padding: 25px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        h2 {
            color: #444;
            margin-top: 0;
        }
        label {
            font-weight: bold;
            color: #555;
        }
    </style>
</head>
<body>
    <h1>XKCD Comic Subscription</h1>
    
    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="section">
        <h2>Subscribe to XKCD Comics</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" name="email" id="email" required placeholder="Enter your email address">
            </div>
            <button type="submit" id="submit-email">Submit</button>
        </form>
    </div>

    <?php if ($showVerificationForm): ?>
    <div class="section">
        <h2>Enter Verification Code</h2>
        <form method="POST" action="">
            <div class="form-group">
                <label for="verification_code">Verification Code:</label>
                <input type="text" name="verification_code" id="verification_code" maxlength="6" required placeholder="Enter the 6-digit code">
                <input type="hidden" name="email" value="<?php echo isset($_SESSION['verification_email']) ? htmlspecialchars($_SESSION['verification_email']) : ''; ?>">
            </div>
            <button type="submit" id="submit-verification">Verify</button>
        </form>
    </div>
    <?php endif; ?>
</body>
</html>
