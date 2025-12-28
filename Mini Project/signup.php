<?php
// signup.php
session_start();
require 'config.php'; // make sure config.php sets up $mysqli (mysqli object) and handles errors

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit;
}

// collect and sanitize
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$dob = $_POST['dob'] ?? null;
$gender = $_POST['gender'] ?? null;
$interests = trim($_POST['interests'] ?? '');
$friend_phone = trim($_POST['friend_phone'] ?? '');
$signup_answers = trim($_POST['signup_answers'] ?? '');

if (!$name || !$email || !$password) {
    // better to redirect back with error message in production; keeping simple here
    die('Please fill required fields. <a href="signup_view.php">Go back</a>');
}

// basic email check
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die('Invalid email address. <a href="signup_view.php">Go back</a>');
}

// check existing
$stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
if (!$stmt) {
    die('DB error: ' . $mysqli->error);
}
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    die('Email already registered. <a href="login.php">Login</a>');
}
$stmt->close();

// hash password
$hash = password_hash($password, PASSWORD_DEFAULT);

// Insert (store empty strings if null to avoid bind trouble)
$dob_param = $dob ?: '';
$gender_param = $gender ?: '';
$interests_param = $interests ?: '';
$friend_phone_param = $friend_phone ?: '';
$signup_answers_param = $signup_answers ?: '';

$ins = $mysqli->prepare("INSERT INTO users (name,email,password,dob,gender,interests,friend_phone,signup_answers) VALUES (?,?,?,?,?,?,?,?)");
if (!$ins) {
    die('DB prepare error: ' . $mysqli->error);
}
$ins->bind_param('ssssssss', $name, $email, $hash, $dob_param, $gender_param, $interests_param, $friend_phone_param, $signup_answers_param);
if ($ins->execute()) {
    // set session and redirect to index or home
    $_SESSION['user_id'] = $ins->insert_id;
    $_SESSION['user_name'] = $name;
    $ins->close();
    header('Location: index.php'); // change to home.php if that's your landing page
    exit;
} else {
    echo "Signup failed: " . $mysqli->error . ' <a href="signup_view.php">Try again</a>';
    $ins->close();
}
