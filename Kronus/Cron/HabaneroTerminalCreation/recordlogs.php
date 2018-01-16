<?php

//function for inserting logs 
function InsertLogs($title, $message) {
    date_default_timezone_set("Asia/Taipei");

    $rootPath = '/var/www/kronus.cron/HabaneroTerminalCreation/Logs/';
    $logfile = $rootPath . 'Logs.txt';

    $today = date("Y-m-d H:i:s");

    if (!is_dir($rootPath)) {
        mkdir($rootPath);
    }

    $txt = "[" . $today . "]" . " " . $title . " " . $message;
    if (file_exists($logfile)) {
        $fh = fopen($logfile, 'a');
        fwrite($fh, $txt . "\r\n");
    } else {
        $fh = fopen($logfile, 'w');
        fwrite($fh, $txt . "\r\n");
    }
    fclose($fh);
}

?>
