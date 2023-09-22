<?php

// Check login state
require("../res/php/session.php");
start_session();
require("../res/php/checkLogin.php");
if (!checkLogin()) header("Location: https://account.noten-app.de");

// Get config
require("../config.php");

// DB Connection
$con = mysqli_connect(
    config_db_host,
    config_db_user,
    config_db_password,
    config_db_name
);
if (mysqli_connect_errno()) exit("Error with the Database");

// Get years
if ($stmt = $con->prepare("SELECT id, name FROM " . config_table_name_school_years . " WHERE owner = ?")) {
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
    <link rel="stylesheet" href="./style.css">
    <link rel="stylesheet" href="./overlays.css">
</head>

<body>
    <nav>
        <a href="/" class="nav-link">
            <div class="navbar_icon">
                <i class="fas fa-home"></i>
            </div>
        </a>
        <div class="nav-link">
            <div class="homework_sidebutton homework_button-theme" id="theme-icon" onclick="cycleTheme();">
                <div>
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </div>
            </div>
        </div>
    </nav>
    <div class="overlays" id="overlay_container">
        <div class="overlay" id="overlay_account">
            <h1 class="overlay-title">Account</h1>
            <div class="dropdown_container container_item">
                <?php
                if (isset($_SESSION['user_email'])) $mail_link = "edit";
                else $mail_link = "add";
                ?>
                <div class="dropdown_container-name" onclick="location.assign('https:\/\/accounttools.noten-app.de/email/<?= $mail_link ?>/')">
                    <?php
                    if (isset($_SESSION['user_email'])) echo "Change Email";
                    else echo "Add Email";
                    ?>
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-at"></i>
                </div>
            </div>
            <!-- <div class="dropdown_container container_item">
                <div class="dropdown_container-name">
                    Change Username
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-user"></i>
                </div>
            </div> -->
            <div class="dropdown_container container_item" onclick="changePW()">
                <div class="dropdown_container-name">
                    Change Password
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-key"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="location.assign('https:\/\/account.noten-app.de/logout');">
                <div class="dropdown_container-name">
                    Logout
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </div>
            </div>
        </div>
        <div class="overlay" id="overlay_credits">
            <h1 class="overlay-title">Credits</h1>
            <p>Open Source software used:</p>
            <div class="dropdown_container container_item" onclick="window.open('https:\/\/fontawesome.com/', '_blank');">
                <div class="dropdown_container-name">
                    Fontawesome Icons
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="window.open('http:\/\/fpdf.org', '_blank');">
                <div class="dropdown_container-name">
                    FPDF
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="window.open('https:\/\/github.com/PHPMailer/PHPMailer/', '_blank');">
                <div class="dropdown_container-name">
                    PHPMailer
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="window.open('https:\/\/themesjs.de/', '_blank');">
                <div class="dropdown_container-name">
                    Themes.js
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="window.open('https:\/\/chartjs.org/', '_blank');">
                <div class="dropdown_container-name">
                    Chart.js
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
                </div>
            </div>
        </div>
        <div class="overlay" id="overlay_delete">
            <h1 class="overlay-title">Delete Grades</h1>
            <p>Are you sure, you want to delete all your grades?</p>
            <p style="color: red;">You cannot undo this!</p>
            <p>We recommend exporting all your grades before deleting them</p>
            <div class="dropdown_container container_item" id="delete_grades_button" onclick="location.assign('delete_grades.php');">
                <div class="dropdown_container-name">
                    Delete all grades
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
            </div>
        </div>
        <div class="overlay" id="overlay_warn_system">
            <h1 class="overlay-title">Warning</h1>
            <p>You have grades in your Account that end in .5!</p>
            <p>Because the Grade-Point system has +Grades (.66) and -Grades (.33), .5-Values cant be converted!</p>
            <p>These grades are not supported by the system and will therefore look weird.</p>
            <p>You can fix this by changing the grades or deleting them.</p>
            <p>You also could revert back to the old System with Grades! Then all your Grades will be automatically restored from the now converted Grade-Points.</p>
        </div>
        <div class="overlay" id="overlay_schoolyears">
            <h1 class="overlay-title">School-Years</h1>
            <div class="dropdown_container container_item">
                <div class="dropdown_container-name" onclick="location.assign('add-year')">
                    Add School-Year
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-calendar-plus"></i>
                </div>
            </div>
            <?php
            foreach ($school_years as $year) {
                echo '<div class="dropdown_container container_item"';
                echo 'onclick="loadYear(\'' . $year["id"] . '\');"';
                if ($_SESSION["setting_years"] == $year["id"]) echo 'style="background-color: var(--background3-color);"';
                echo '><div class="dropdown_container-name">';
                echo "<span>" . htmlspecialchars($year["name"]) . "</span>";
                echo '</div></div>';
            }
            ?>
        </div>
    </div>
    <main id="main">
        <div class="group_container" id="account-settings" onclick="open_overlay('overlay_account');">
            <div class="account-greeting">
                <span id="account_greeting-naa">Naaa,</span>
                <span id="account_greeting-name"><?= $_SESSION["user_name"] ?></span>
            </div>
            <div class="account-icon">
                <i class="fas fa-user"></i>
            </div>
        </div>
        <span class="container-title">Grading</span>
        <div class="group_container" id="grade-settings">
            <!-- <div class="container_item">
                <div class="button_divider">
                    <div class="button_divider-button1 button_divider-button_active">
                        Grades<br>1,0 - 6,0
                    </div>
                    <div class="button_divider-button2">
                        Points<br>0 - 15
                    </div>
                </div>
            </div> -->
            <div class="container_item">
                Decimals (Rounding)
                <div class="button_divider">
                    <div <?php if ($_SESSION["setting_rounding"] == 0) echo 'class="button_divider-button_active" '; ?>onclick="setRounding(0);">
                        No<br>
                        2
                    </div>
                    <div <?php if ($_SESSION["setting_rounding"] == 1) echo 'class="button_divider-button_active" '; ?>onclick="setRounding(1);">
                        One<br>
                        1,7
                    </div>
                    <div <?php if ($_SESSION["setting_rounding"] == 2) echo 'class="button_divider-button_active" '; ?>onclick="setRounding(2);">
                        Two<br>1,72
                    </div>
                </div>
            </div>
            <div class="container_item">
                System
                <div class="button_divider" id="system_container">
                    <div <?php if ($_SESSION["setting_system"] == "noten") echo 'class="button_divider-button_active" '; ?> onclick="setSystem('noten');">
                        Noten
                    </div>
                    <div <?php if ($_SESSION["setting_system"] == "punkte") echo 'class="button_divider-button_active" '; ?> onclick="setSystem('punkte');">
                        Punkte
                    </div>
                </div>
            </div>
        </div>
        <span class="container-title">Sorting settings</span>
        <div class="group_container" id="grade-settings">
            <div class="container_item">
                Classes
                <div class="button_divider">
                    <div <?php if ($_SESSION["setting_sorting"] == "average") echo 'class="button_divider-button_active" '; ?>onclick="setSorting('average');">
                        Average
                    </div>
                    <div <?php if ($_SESSION["setting_sorting"] == "alphabet") echo 'class="button_divider-button_active" '; ?>onclick="setSorting('alphabet');">
                        Alphabet
                    </div>
                    <div <?php if ($_SESSION["setting_sorting"] == "lastuse") echo 'class="button_divider-button_active" '; ?>onclick="setSorting('lastuse');">
                        Last use
                    </div>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="open_overlay('overlay_schoolyears');">
                <div class="dropdown_container-name">
                    School-Years
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
        </div>
        <!-- <span class="container-title">Location-based settings</span>
        <div class="group_container" id="location-settings">
            <div class="dropdown_container container_item">
                <div class="dropdown_container-name">
                    Language
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <div class="dropdown_container container_item">
                <div class="dropdown_container-name">
                    Region
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
            <div class="dropdown_container container_item">
                <div class="dropdown_container-name">
                    Off-days
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-chevron-down"></i>
                </div>
            </div>
        </div> -->
        <span class="container-title">Advanced settings</span>
        <div class="group_container" id="location-settings">
            <div class="dropdown_container container_item" onclick="window.open('./export_grades.php', '_blank')">
                <div class="dropdown_container-name">
                    Export grades
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-file-arrow-down"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="open_overlay('overlay_delete');">
                <div class="dropdown_container-name">
                    Delete grades
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-trash-can"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="window.open('https:\/\/noten-app.de/changelogs/', '_blank')">
                <div class="dropdown_container-name">
                    About this version
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>
        <span class="container-title">More Help</span>
        <div class="group_container" id="location-settings">
            <div class="dropdown_container container_item" onclick="window.open('https:\/\/noten-app.de', '_blank')">
                <div class="dropdown_container-name">
                    Website
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fa-solid fa-chevron-right"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="window.open('https:\/\/noten-app.de/help/', '_blank')">
                <div class="dropdown_container-name">
                    Help & FAQ
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="window.open('https:\/\/noten-app.de/support/', '_blank')">
                <div class="dropdown_container-name">
                    Support
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="open_overlay('overlay_credits');">
                <div class="dropdown_container-name">
                    Credits
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="window.open('https:\/\/noten-app.de/legal/agb/', '_blank')">
                <div class="dropdown_container-name">
                    Terms of Service
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
            <div class="dropdown_container container_item" onclick="window.open('https:\/\/noten-app.de/legal/datenschutz/', '_blank')">
                <div class="dropdown_container-name">
                    Privacy policy
                </div>
                <div class="dropdown_container-dropdown_icon">
                    <i class="fas fa-chevron-right"></i>
                </div>
            </div>
        </div>
        <footer>
            <div class="footer_container">
                <p>Made with ❤️ in Germany.</p>
                <p><?php echo config_version_name; ?><?php if ($_SESSION["beta_tester"] == 1) echo " Beta" ?></p>
            </div>
        </footer>
    </main>
    <script src="https://assets.noten-app.de/js/jquery/jquery-3.6.1.min.js"></script>
    <script src="https://assets.noten-app.de/js/themes/themes.js"></script>
    <script src="overlays.js"></script>
    <script src="rounding.js"></script>
    <script src="sorting.js"></script>
    <script src="grade_system.js"></script>
    <script src="school_years.js"></script>
</body>

</html>