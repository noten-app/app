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
    $config["db"]["credentials"]["host"],
    $config["db"]["credentials"]["user"],
    $config["db"]["credentials"]["password"],
    $config["db"]["credentials"]["name"]
);
if (mysqli_connect_errno()) exit("Error with the Database");

// Get years
if ($stmt = $con->prepare("SELECT id, name FROM " . $config["db"]["tables"]["years"] . " WHERE owner = ?")) {
    $stmt->bind_param("s", $_SESSION["user_id"]);
    $stmt->execute();
    $stmt->bind_result($year, $name);
    $school_years = [];
    while ($stmt->fetch()) {
        array_push($school_years, ["id" => $year, "name" => $name]);
    }
    $stmt->close();
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
    <title>Settings | Noten-App</title>
    <link rel="icon" type="image/x-icon" href="/res/img/favicon.ico" />
    <link rel="stylesheet" href="/res/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="/res/fontawesome/css/solid.min.css">
    <link rel="stylesheet" href="/res/css/fonts.css">
    <link rel="stylesheet" href="/res/css/main.css">
    <link rel="stylesheet" href="/res/css/navbar.css">
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="../overlays.css">
</head>

<body>
    <nav>
        <a href="/" class="nav-link">
            <div class="navbar_icon">
                <i class="fas fa-home"></i>
            </div>
        </a>
        <a href="/settings/" class="nav-link">
            <div class="navbar_icon">
                <i class="fa-solid fa-arrow-left"></i>
            </div>
        </a>
    </nav>
    <div class="overlays" id="overlay_container">
        <div class="overlay" id="overlay_schoolyears">
            <h1 class="overlay-title">School-Years</h1>
            <?php
            foreach ($school_years as $year) {
                echo '<div class="dropdown_container container_item"';
                echo 'onclick="loadYear(\'' . $year["id"] . '\');"';
                if ($_SESSION["setting_year"] == $year["id"]) echo 'style="background-color: var(--background3-color);"';
                echo '><div class="dropdown_container-name">';
                echo "<span>" . htmlspecialchars($year["name"]) . "</span>";
                echo '</div></div>';
            }
            ?>
        </div>
    </div>
    <main id="main">
        <div class="group_container" id="current-year" onclick="open_overlay('overlay_schoolyears');">
            <div class="current-year">
                <span id="current-year-title">Current School Year</span><br>
                <span id="current-year-name"><?php foreach ($school_years as $year) if ($_SESSION["setting_year"] == $year["id"]) echo htmlspecialchars($year["name"]); ?></span>
            </div>
            <div class="calendar-icon">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>
        <div class="group_container" id="year-delete">
            <div class="year-name">
                <span id="year-name-title">New School Year</span><br>
                <input type="text" id="year-name-input" placeholder="Name" maxlength="20">
            </div>
        </div>
        <div id="submit_button">Update Year</div>
        <div id="delete_button">Delete this Year</div>
        <div class="group_container" id="info">
            <p>Warning: When you click on delete, all subjects, grades and homework entries for the current chosen year will be deleted.</p>
        </div>
        <div id="nextyear" style="display:none;visibility:hidden;" hidden>
            <?php
            foreach ($school_years as $year) if ($_SESSION["setting_year"] != $year["id"]) {
                echo htmlspecialchars($year["id"]);
                break;
            }
            ?></div>
        <footer>
            <div class="footer_container">
                <p>Made with ❤️ in Germany.</p>
                <p><?php echo $config["version"]["name"]; ?><?php if ($_SESSION["beta_tester"] == 1) echo " Beta" ?></p>
            </div>
        </footer>
    </main>
    <script src="https://assets.noten-app.de/js/jquery/jquery-3.6.1.min.js"></script>
    <script src="https://assets.noten-app.de/js/themes/themes.js"></script>
    <script src="../overlays.js"></script>
    <script src="../school_years.js"></script>
    <script src="script.js"></script>
    <?php if ($config["tracking"]["matomo"]["on"]) echo ($config["tracking"]["matomo"]["code"]); ?>
</body>

</html>