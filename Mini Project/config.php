<?php
// config.php
session_start();

$db_host = 'localhost';
$db_name = 'mental_tracker';
$db_user = 'root';
$db_pass = ''; // set your password

// Optional Twilio settings (for SMS alerts) - fill if you want SMS
$TWILIO_SID = '';      // e.g., "ACxxxx"
$TWILIO_TOKEN = '';    // e.g., "your_auth_token"
$TWILIO_FROM = '';     // Twilio phone number "+1xxx" or your gateway number

// Create mysqli connection
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) {
    die("DB connect failed: " . $mysqli->connect_error);
}

// helper: send SMS using Twilio REST API (uses curl)
// If you don't want/use Twilio, remove or change this function.
function send_sms($to, $message) {
    global $TWILIO_SID, $TWILIO_TOKEN, $TWILIO_FROM;
    if (empty($TWILIO_SID) || empty($TWILIO_TOKEN) || empty($TWILIO_FROM)) {
        return false; // Twilio not configured
    }

    $url = "https://api.twilio.com/2010-04-01/Accounts/{$TWILIO_SID}/Messages.json";
    $data = http_build_query([
        'To' => $to,
        'From' => $TWILIO_FROM,
        'Body' => $message
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_USERPWD, $TWILIO_SID . ':' . $TWILIO_TOKEN);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);
    if ($err) return false;
    return $response;
}
