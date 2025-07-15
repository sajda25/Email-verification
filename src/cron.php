<?php
require_once 'functions.php';



// Setting error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);


$logFile = __DIR__ . '/cron.log';

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

try {
    // Sending XKCD updates to all registered subscribers
    sendXKCDUpdatesToSubscribers();
    logMessage("Successfully sent XKCD comics to all subscribers");
} catch (Exception $e) {
    logMessage("Error: " . $e->getMessage());
}
