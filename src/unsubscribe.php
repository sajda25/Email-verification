<?php
require_once 'functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email']) && !isset($_POST['verification_code'])) {
        // Handle unsubscribe email submission
        $email = filter_var($_POST['unsubscribe_email'], FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $code = generateVerificationCode();
            $_SESSION['verification_code'] = $code;
            $_SESSION['verification_email'] = $email;
            
            if (sendVerificationEmail($email, $code)) {
                $message = 'Verification code has been sent to your email.';
                $messageType = 'success';
            } else {
                $message = 'Failed to send verification code. Please try again.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid email address.';
            $messageType = 'error';
        }
    } elseif (isset($_POST['unsubscribe_email']) && isset($_POST['verification_code'])) {
        // Handle unsubscribe verification
        $email = filter_var($_POST['unsubscribe_email'], FILTER_SANITIZE_EMAIL);
        $code = filter_var($_POST['verification_code'], FILTER_SANITIZE_STRING);
        
        if (verifyCode($email, $code)) {
            if (unsubscribeEmail($email)) {
                $message = 'Successfully unsubscribed from XKCD comics.';
                $messageType = 'success';
            } else {
                $message = 'Failed to unsubscribe. Please try again.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid verification code.';
            $messageType = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unsubscribe from XKCD Comics</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        input[type="email"], input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
        }
        button {
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #c82333;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
        }
    </style>
</head>
<body>
    <h1>Unsubscribe from XKCD Comics</h1>
    
    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label for="unsubscribe_email">Email Address:</label>
            <input type="email" name="unsubscribe_email" id="unsubscribe_email" required>
        </div>
        
        <div class="form-group">
            <label for="verification_code">Verification Code:</label>
            <input type="text" name="verification_code" id="verification_code" maxlength="6" required>
        </div>
        
        <button type="submit" id="submit-unsubscribe">Unsubscribe</button>
    </form>
</body>
</html>
