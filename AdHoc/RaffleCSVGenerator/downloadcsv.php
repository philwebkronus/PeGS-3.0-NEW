<?php

ini_set('display_errors', false);
ini_set('log_errors', false);
ini_set('memory_limit', '-1');
date_default_timezone_set("Asia/Taipei");

include 'PDOhandler.php';
include 'config/config.php';
$PDO = new PDOhandler($svrname, $dbType, $dbPort, $dbName, $dbUsername, $dbPassword);

function array2csv(array &$array) {
    if (count($array) == 0) {
        return null;
    }
    ob_start();
    $df = fopen("php://output", 'w');
    fputcsv($df, array_keys(reset($array)));
    foreach ($array as $row) {
        fputcsv($df, $row);
    }
    fclose($df);
    return ob_get_clean();
}

function download_send_headers($filename) {
// disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

// force download  
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

// disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

//Get Player Points
$getPlayerVvPoints = $PDO->getPlayerVvPoints();
if (count($getPlayerVvPoints) > 0) {
    // Logs : Success Get Points
    $title = "[GET PLAYER POINTS:]";
    $message = "Success getting player points.";
    $PDO->InsertLogs($title, $message);

    $counter = 1;

    foreach ($getPlayerVvPoints as $playerRaffle) {
        $RaffleEntry = $playerRaffle['VvRaffleEntry'];

        for ($x = 1; $x <= $RaffleEntry; $x++) {
            $list['ID'] = $counter;
            $list['MID'] = $playerRaffle['MID'];
            $list['CardNumber'] = $playerRaffle['CardNumber'];

            $counter = $counter + 1;
            $result[] = $list;
        }
    }

    // Logs : Fail Get Points
    $title = "[CSV GENERATING:]";
    $message = "Generating csv file.";
    $PDO->InsertLogs($title, $message);

    download_send_headers("Habanero_Raffle_Data_" . date("Y-m-d") . ".csv");
    echo array2csv($result);
} else {
    // Logs : Fail Get Points
    $title = "[GET PLAYER POINTS:]";
    $message = "Fail getting player points.";
    $PDO->InsertLogs($title, $message);
}
?>