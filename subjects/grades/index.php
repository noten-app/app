<?php

// Check if subject url-parameter is given
if (!isset($_GET["subject"])) header("Location: /grades");
$subject_id = htmlspecialchars($_GET["subject"]);
// Check if subject is a-z or 0-9
if (!preg_match("/^[a-z0-9]*$/", $subject_id)) header("Location: /subjects");

// Check login state
require($_SERVER["DOCUMENT_ROOT"] . "/res/php/session.php");
start_session();
require($_SERVER["DOCUMENT_ROOT"] . "/res/php/checkLogin.php");
if (!checkLogin()) header("Location: https://account.noten-app.de");

// Get config
require($_SERVER["DOCUMENT_ROOT"] . "/config.php");

// Get point system transformer
require($_SERVER["DOCUMENT_ROOT"] . "/res/php/point-system.php");

// DB Connection
$con = mysqli_connect(
    $config["db"]["credentials"]["host"],
    $config["db"]["credentials"]["user"],
    $config["db"]["credentials"]["password"],
    $config["db"]["credentials"]["name"]
);
if (mysqli_connect_errno()) exit("Error with the Database");

// Get subject
if ($stmt = $con->prepare('SELECT name, color, user_id, last_used, weight_exam, weight_oral, weight_test, weight_other FROM ' . $config["db"]["tables"]["subjects"] . ' WHERE id = ?')) {
    $stmt->bind_param('s', $subject_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($subject_name, $subject_color, $user_id, $last_used, $weight_exam, $weight_oral, $weight_test, $weight_other);
    $stmt->fetch();
    if ($user_id !== $_SESSION["user_id"]) {
        $name = "";
        $color = "";
        $user_id = "";
        $last_used = "";
        exit("ERROR2");
    }
    $stmt->close();
} else {
    exit("ERROR1");
}

// Get grades
$grades = array();
if ($stmt = $con->prepare('SELECT id, user_id, subject, note, type, date, grade FROM grades WHERE subject = ?')) {
    $stmt->bind_param('s', $subject_id);
    $stmt->execute();
    $result = $stmt->get_result();
    foreach ($result as $row) {
        array_push($grades, $row);
    }
    if ($user_id !== $_SESSION["user_id"]) {
        $name = "";
        $user_id = "";
        $id = "";
        $subject = "";
        $note = "";
        $date = "";
        $grade = "";
        exit("ERROR2");
    }
    $stmt->close();
} else {
    exit("ERROR1");
}

// 
// Calculate average
// 
$num_of_k = 0;
$num_of_m = 0;
$num_of_t = 0;
$num_of_s = 0;
foreach ($grades as $grade) {
    if ($grade["type"] == "k") $num_of_k++;
    if ($grade["type"] == "m") $num_of_m++;
    if ($grade["type"] == "t") $num_of_t++;
    if ($grade["type"] == "s") $num_of_s++;
}
// Calculate the sum of all weights
if ($weight_test == "1exam" || $weight_test == "") $weight_sum = $weight_exam + $weight_oral + $weight_other;
else $weight_sum = $weight_exam + $weight_oral + $weight_test + $weight_other;
// Calculate average (not tests)
$weight_otherum = 0.0;
$weight_sum = 0.0;
foreach ($grades as $grade) {
    switch ($grade["type"]) {
        case "k":
            $weight_otherum += $grade["grade"] * $weight_exam;
            $weight_sum += $weight_exam;
            break;
        case "m":
            $weight_otherum += $grade["grade"] * $weight_oral;
            $weight_sum += $weight_oral;
            break;
        case "s":
            $weight_otherum += $grade["grade"] * $weight_other;
            $weight_sum += $weight_other;
            break;
    }
}
// Add tests to average
if ($num_of_t != 0) {
    if ($weight_test == "1exam" || $weight_test == "") {
        // Get average of tests and add it to the average as one k
        $test_sum = 0;
        $test_count = 0;
        foreach ($grades as $grade) {
            if ($grade["type"] == "t") {
                $test_sum += $grade["grade"];
                $test_count++;
            }
        }
        $test_avg = $test_sum / $test_count;
        $weight_otherum += $test_avg * $weight_exam;
    } else {
        // Add each test to the average with weight weight_test
        foreach ($grades as $grade) {
            if ($grade["type"] == "t") {
                $weight_otherum += $grade["grade"] * $weight_test;
            }
        }
    }
}
if (!($weight_otherum == 0 || $weight_sum == 0)) {
    // Calculate average
    $average = $weight_otherum / $weight_sum;
    // Insert average into subject
    if ($stmt = $con->prepare('UPDATE subjects SET average = ? WHERE id = ?')) {
        $stmt->bind_param('ss', $average, $subject_id);
        $stmt->execute();
        $stmt->close();
    } else {
        exit("ERROR1");
    }
}
// If no grades but average is given -> delete average
if ($num_of_k + $num_of_m + $num_of_t + $num_of_s == 0) {
    if ($stmt = $con->prepare('UPDATE subjects SET average = 0 WHERE id = ?')) {
        $stmt->bind_param('s', $subject_id);
        $stmt->execute();
        $stmt->close();
    } else {
        exit("ERROR1");
    }
}

// DB Con close
$con->close();
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $subject_name ?> | Noten-App</title>
    <link rel="stylesheet" href="/res/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="/res/fontawesome/css/solid.min.css">
    <link rel="stylesheet" href="/res/css/fonts.css">
    <link rel="stylesheet" href="/res/css/main.css">
    <link rel="stylesheet" href="/res/css/navbar.css">
    <link rel="stylesheet" href="./style.css">
    <link rel="apple-touch-icon" sizes="180x180" href="/res/img/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/res/img/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/res/img/favicon-16x16.png">
    <link rel="mask-icon" href="/res/img/safari-pinned-tab.svg" color="#eb660e">
    <link rel="shortcut icon" href="/res/img/favicon.ico">
    <meta name="apple-mobile-web-app-title" content="Noten-App">
    <meta name="application-name" content="Noten-App">
    <meta name="msapplication-TileColor" content="#282c36">
    <meta name="msapplication-TileImage" content="/res/img/mstile-144x144.png">
    <meta name="theme-color" content="#ffffff">
    <link rel="manifest" href="/app.webmanifest">
    <meta name="msapplication-config" content="/browserconfig.xml">
</head>

<body>
    <nav>
        <a href="/" class="nav-link">
            <div class="navbar_icon">
                <i class="fas fa-home"></i>
            </div>
        </a>
        <a href=".." class="nav-link">
            <div class="navbar_icon">
                <i class="fa-solid fa-arrow-left"></i>
            </div>
        </a>
    </nav>
    <main id="main">
        <div class="subject_title">
            <h1><?= $subject_name ?></h1>
        </div>
        <div class="subject_edit">
            <i id="view_toggle" class="fa-solid fa-chart-simple" onclick="toggle_stats_view()"></i>
            <i class="fas fa-cog" onclick="location.assign('/subjects/edit/?subject=<?= $subject_id ?>')"></i>
        </div>
        <div class="subject-main_content">
            <div class="gradelist">
                <?php
                foreach ($grades as $grade) {
                    echo '<div class="grade_entry" onclick="location.assign(\'/subjects/grades/edit/?grade=' . $grade["id"] . '\')">';
                    echo '<div class="grade">';
                    if (systemRun("punkte")) echo (calcToPoints(true, $grade["grade"]));
                    else echo $grade["grade"];
                    echo '</div><div class="weight_testype">';
                    switch (strtolower($grade["type"])) {
                        case "k":
                            echo "Exam";
                            break;
                        case "m":
                            echo "Verbal";
                            break;
                        case "t":
                            echo "Test";
                            break;
                        case "s":
                            echo "Other";
                            break;
                        default:
                            echo "Unspecified";
                            break;
                    }
                    echo '</div><div class="grade_date">';
                    echo date("d.m.Y", strtotime($grade["date"]));
                    echo '</div></div>';
                }
                if (count($grades) == 0) echo '<div class="nogrades">No grades yet</div>';
                ?>
            </div>
            <div class="statistics"></div>
        </div>
        <div class="grade_add" onclick="location.assign('/subjects/grades/add/?subject=<?= $subject_id ?>')">
            <div>Add grade <i class="fas fa-plus"></i></div>
        </div>
    </main>
    <script src="https://assets.noten-app.de/js/themes/themes.js"></script>
    <script src="/res/js/point-system.js"></script>
    <script src="./view-cycler.js"></script>
    <?php if ($config["tracking"]["matomo"]["on"]) echo ($config["tracking"]["matomo"]["code"]); ?>
</body>

</html>