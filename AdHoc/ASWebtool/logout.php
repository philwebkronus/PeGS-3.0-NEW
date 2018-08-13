<?php

ini_set('display_errors', true);
ini_set('log_errors', true);

include 'config/config.php';
include 'PDOhandler.php';

$PDO = new PDOhandler($svrname, $dbType, $dbPort, $dbName, $dbUsername, $dbPassword);

if ($_SERVER['REMOTE_ADDR']) {
    if (in_array(htmlspecialchars(trim($_SERVER['REMOTE_ADDR'])), $IPrestriction)) {
        session_start();
        if (!empty($_SESSION)) {
            session_destroy();
        }

        $title = "[Logout]";
        $errormessage = "Username : " . $_SESSION['loggedin'] . " | IP Address " . $_SERVER['REMOTE_ADDR'] . " | User Successfuly Logout!.";
        $PDO->InsertLogs($title, $errormessage);

        $_SESSION['loggedin'] = null;
        $_SESSION['IP_Adddress'] = null;

        header("Location: login.php");
        die();
    } else {
        header("Location: forbidden.php");
        die();
    }
}

