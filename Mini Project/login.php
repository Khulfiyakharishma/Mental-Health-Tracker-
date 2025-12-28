<?php
// login.php
require 'config.php';

/** @var mysqli $mysqli */  // Helps Intelephense know $mysqli is a mysqli object

session_start(); // Start session before using $_SESSION

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure values are strings and not null
    $email = (string) trim($_POST['email'] ?? '');
    $password = (string) ($_POST['password'] ?? '');

    // Prepare statement safely
    $stmt = $mysqli->prepare("SELECT id, name, password FROM users WHERE email = ?");
    if (!$stmt) {
        die('Database error: ' . htmlspecialchars($mysqli->error));
    }

    // Bind parameter and execute
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();

    // Check if email exists
    if ($stmt->num_rows === 0) {
        $stmt->close();
        die('Invalid credentials. <a href="home.php">Back</a>');
    }

    // Fetch user data
    $stmt->bind_result($id, $name, $hash);
    $stmt->fetch();

    // Verify password
    if (password_verify($password, $hash)) {
        $_SESSION['user_id'] = $id;
        $_SESSION['user_name'] = $name;

        // Redirect to dashboard after successful login
        header('Location: index.php');
        exit;
    } else {
        echo "Invalid password. <a href='home.php'>Back</a>";
    }

    $stmt->close();
} else {
    // Redirect to home if accessed without form submission
    header('Location: home.php');
    exit;
}
?>
