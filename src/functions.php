<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $envVars = parse_ini_file($envFile);
    foreach ($envVars as $key => $value) {
        putenv("$key=$value");
    }
}

/**
 * Generate a 6-digit numeric verification code.
 */
function generateVerificationCode(): string {
    return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
}

/**
 * Verify if the provided code matches the sent code.
 */
function verifyCode(string $email, string $code): bool {
    if (!isset($_SESSION['verification_code']) || !isset($_SESSION['verification_email'])) {
        return false;
    }
    
    return $_SESSION['verification_code'] === $code && 
           $_SESSION['verification_email'] === $email;
}

/**
 * Send a verification code to an email.
 */
function sendVerificationEmail(string $email, string $code): bool {
    $subject = "Your Verification Code";
    
    $message = "
    <html>
    <head>
        <title>Verification Code</title>
    </head>
    <body>
        <p>Your verification code is: <strong>{$code}</strong></p>
    </body>
    </html>
    ";
    
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@example.com" . "\r\n";
    
    // Log the attempt to send email
    error_log("Attempting to send verification code to: " . $email);
    error_log("Verification code: " . $code);
    
    $result = mail($email, $subject, $message, $headers);
    
    // Log the result
    if ($result) {
        error_log("Email sent successfully to: " . $email);
    } else {
        error_log("Failed to send email to: " . $email);
        error_log("PHP mail() error: " . error_get_last()['message']);
    }
    
    return $result;
}

/**
 * Register my  email by storing it in a file.
 */
function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    
    // Checking if email already exists
    if (file_exists($file)) {
        $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (in_array($email, $emails)) {
            return false;
        }
    }
    
    return file_put_contents($file, $email . PHP_EOL, FILE_APPEND) !== false;
}

/**
 * Unsubscribe my  email by removing it from the list.
 */
function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';
    
    if (!file_exists($file)) {
        return false;
    }
    
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $emails = array_filter($emails, function($line) use ($email) {
        return trim($line) !== $email;
    });
    
    return file_put_contents($file, implode(PHP_EOL, $emails) . PHP_EOL) !== false;
}

/**
 * Fetching random XKCD comic and format data as HTML.
 */
function fetchAndFormatXKCDData(): string {
    // Get total number of comics
    $response = file_get_contents('https://xkcd.com/info.0.json');
    if ($response === false) {
        return "Error fetching XKCD data";
    }
    
    $data = json_decode($response, true);
    $totalComics = $data['num'];
    
    // Getting random comic
    $randomNum = random_int(1, $totalComics);
    $comicResponse = file_get_contents("https://xkcd.com/$randomNum/info.0.json");
    if ($comicResponse === false) {
        return "Error fetching random comic";
    }
    
    $comicData = json_decode($comicResponse, true);
    
    return "
    <html>
    <head>
        <title>XKCD Comic</title>
    </head>
    <body>
        <h2>XKCD Comic</h2>
        <img src='{$comicData['img']}' alt='{$comicData['alt']}'>
        <p><a href='#' id='unsubscribe-button'>Unsubscribe</a></p>
    </body>
    </html>
    ";
}

/**
 * Send the formatted XKCD updates to registered emails.
 */
function sendXKCDUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    
    if (!file_exists($file)) {
        return;
    }
    
    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $comicHtml = fetchAndFormatXKCDData();
    
    $subject = "Your XKCD Comic";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: no-reply@example.com" . "\r\n";
    
    foreach ($emails as $email) {
        mail($email, $subject, $comicHtml, $headers);
    }
}
