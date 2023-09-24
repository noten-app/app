<?php

// Check login state
require($_SERVER["DOCUMENT_ROOT"] . "/res/php/session.php");
start_session();
require($_SERVER["DOCUMENT_ROOT"] . "/res/php/checkLogin.php");
if (!checkLogin()) header("Location: https://account.noten-app.de");

// Get config
require($_SERVER["DOCUMENT_ROOT"] . "/config.php");

// DB Connection
$con = mysqli_connect(
    config_db_host,
    config_db_user,
    config_db_password,
    config_db_name
);
if (mysqli_connect_errno()) die("Error with the Database");

// Get input
$class = $_POST["class"];
$type = $_POST["type"];
$date_due = $_POST["date_due"];
$task = $_POST["task"];

// Check if necessary input is given
if (!isset($class) || strlen($class) == 0) die("missing-class");
if (!isset($type)) die("missing-type");
if (!($type == "b" || $type == "v" || $type == "w" || $type == "o")) die("invalid-type");
if (!isset($date_due) || strlen($date_due) == 0) die("missing-date_due");
if (!isset($task) || strlen($task) == 0) die("missing-task");
if (strlen($task) > 75) die("too-long-task");

// Encode task
$task = htmlentities($task);

// Create given-date
$date_given = date("Y-m-d");

// Add class to DB and get inserted ID
if ($stmt = $con->prepare('INSERT INTO ' . config_table_name_homework . ' (user_id, class, given, deadline, text, type, year) VALUES (?, ?, ?, ?, ?, ?, ?)')) {
    $stmt->bind_param('sisssss', $_SESSION["user_id"], $class, $date_given, $date_due, $task, $type, $_SESSION["setting_years"]);
    $stmt->execute();
    $stmt->close();
    exit("success");
} else die("Error with the Database");

// DB Con close
$con->close();
