// add at top of monthly_tracker.php after session_start()
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['__send_alert']) && !empty($friend_phone)) {
    $message = "Alert: " . ($db_name ?? $_SESSION['user_name']) . " may need support. Please check on them.";
    $sms = send_sms($friend_phone, $message);
    if ($sms !== false) {
        $f = $mysqli->real_escape_string($friend_phone);
        $d = $mysqli->real_escape_string($message);
        $mysqli->query("INSERT INTO alerts (user_id, alert_type, sent_to, details) VALUES ({$user_id}, 'manual_alert', '{$f}', '{$d}')");
    }
    header('Location: monthly_tracker.php'); exit;
}
