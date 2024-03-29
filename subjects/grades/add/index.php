<?php

// Check if subject url-parameter is given
if (!isset($_GET["subject"])) header("Location: /subjects");
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

// DB Connection
$con = mysqli_connect(
    $config["db"]["credentials"]["host"],
    $config["db"]["credentials"]["user"],
    $config["db"]["credentials"]["password"],
    $config["db"]["credentials"]["name"]
);
if (mysqli_connect_errno()) exit("Error with the Database");

// Get subject
if ($stmt = $con->prepare('SELECT name, color, user_id, last_used, weight_exam, weight_oral, weight_other FROM subjects WHERE id = ?')) {
    $stmt->bind_param('s', $subject_id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($subject_name, $subject_color, $user_id, $last_used, $weight_exam, $weight_oral, $weight_other);
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

// DB Con close
$con->close();
?>

<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add grade - <?= $subject_name ?> | Noten-App</title>
    <link rel="stylesheet" href="/res/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="/res/fontawesome/css/solid.min.css">
    <link rel="stylesheet" href="/res/css/fonts.css">
    <link rel="stylesheet" href="/res/css/main.css">
    <link rel="stylesheet" href="/res/css/navbar.css">
    <link rel="stylesheet" href="./style.css">
    <?php if (isset($_SESSION["setting_system"]) && $_SESSION["setting_system"] == "punkte") echo '<link rel="stylesheet" href="./points.css">';
    else echo '<link rel="stylesheet" href="./grades.css">' ?>
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
        <a href="/subjects/grades/?subject=<?= $subject_id ?>" class="nav-link">
            <div class="navbar_icon">
                <i class="fa-solid fa-arrow-left"></i>
            </div>
        </a>
    </nav>
    <main id="main">
        <div class="subject_title">
            <h1 style="color: #<?= $subject_color ?>"><?= $subject_name ?></h1>
        </div>
        <div class="subject-main_content">
            <div class="type">
                <div class="type-title">
                    Type
                </div>
                <div class="type-container">
                    <div class="type_k" id="type_k">Exam</div>
                    <div class="type_m" id="type_m">Verbal</div>
                    <div class="type_t" id="type_t">Test</div>
                    <div class="type_s" id="type_s">Other</div>
                </div>
            </div>
            <div class="grade">
                <div class="grade-title">
                    Grade
                </div>
                <div class="grade-container_1-6">
                    <?php
                    if (isset($_SESSION["setting_system"]) && $_SESSION["setting_system"] == "punkte") {
                        echo '<div class="gr1" onclick="openModifiers(15)">15-13</div>
                                <div class="gr2" onclick="openModifiers(12)">12-10</div>
                                <div class="gr3" onclick="openModifiers(9)">9-7</div>
                                <div class="gr4" onclick="openModifiers(6)">6-4</div>
                                <div class="gr5" onclick="openModifiers(3)">3-1</div>
                                <div class="gr6" onclick="openModifiers(0)">0</div>';
                    } else echo '<div class="gr1">1</div>
                                <div class="gr2">2</div>
                                <div class="gr3">3</div>
                                <div class="gr4">4</div>
                                <div class="gr5">5</div>
                                <div class="gr6">6</div>';

                    ?>
                </div>
                <div class="grade-modifier_container">
                    <?php
                    if (isset($_SESSION["setting_system"]) && $_SESSION["setting_system"] == "punkte") {
                        echo '<div class="gr-full" onclick="modify(0)"><span id="gr-full_points"></span></div>
                        <div class="gr-minusone" onclick="modify(1)"><span id="gr-minusone_points"></span></div>
                        <div class="gr-minustwo" onclick="modify(2)"><span id="gr-minustwo_points"></span></div>';
                    } else echo '<div class="gr-full"><span id="gr-full_grade"></span></div>
                        <div class="gr-025"><span id="gr-025_grade"></span></div>
                        <div class="gr-050"><span id="gr-050_grade"></span></div>
                        <div class="gr-075"><span id="gr-075_grade"></span></div>';
                    ?>
                    <i class="fa-solid fa-rotate-left" onclick="resetGradeModifier();"></i>
                </div>
            </div>
            <div class="note">
                <div class="note-title">
                    Note
                </div>
                <div class="note-container">
                    <input type="text" id="note-input" maxlength="25">
                </div>
            </div>
            <div class="date">
                <div class="date-title">
                    Date
                </div>
                <div class="date-input">
                    <input type="date" id="date_input-input" value="<?= date("Y-m-d", time()) ?>">
                </div>
            </div>
        </div>
        <div class="grade_add">
            <div>Add new grade <i class="fas fa-plus"></i></div>
        </div>
        <div id="subject_id" style="display: none;"><?= $subject_id ?></div>
    </main>
    <script src="https://assets.noten-app.de/js/jquery/jquery-3.6.1.min.js"></script>
    <script src="https://assets.noten-app.de/js/themes/themes.js"></script>
    <script src="./choose-type.js"></script>
    <?php
    if (isset($_SESSION["setting_system"]) && $_SESSION["setting_system"] == "punkte") echo '<script src="./choose-points.js"></script>';
    else echo '<script src="./choose-grade.js"></script>';
    ?>
    <script src="./add-grade.js"></script>
    <?php if ($config["tracking"]["matomo"]["on"]) echo ($config["tracking"]["matomo"]["code"]); ?>
</body>

</html>