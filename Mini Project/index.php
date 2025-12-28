<?php
// index.php — small launcher that sends logged-in users to the journal page
require 'config.php';
/** @var mysqli $mysqli */

session_start();

// if not logged in, go to public home
if (!isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit;
}

// optional: you could keep a lightweight dashboard here. For now redirect to journal page.
header('Location: journal_page.php');
exit;
