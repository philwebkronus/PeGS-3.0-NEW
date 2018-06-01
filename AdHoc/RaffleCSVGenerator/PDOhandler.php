<?php

include 'config/config.php';

class PDOhandler {

    public $_connection;

    public function __construct($srvrname, $dbType, $dbPort, $dbName, $dbUname, $dbPass) {
        $servername = $srvrname;
        $DatabaseType = $dbType;
        $port = $dbPort;
        $dbname = $dbName;
        $username = $dbUname;
        $password = $dbPass;

        $this->_connection = new PDO("$DatabaseType:host=$servername;port=$port;dbname=$dbname", $username, $password);
        $this->_connection->setAttribute(PDO:: ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($this->_connection)
            return true;
        else
            return false;
    }

    public function getPlayerVvPoints() {
        $query = $this->_connection->prepare('SELECT MID, CardNumber, VvRaffleEntry FROM loyaltydb.membercards WHERE Status = 1 AND VvRaffleEntry > 0');

        if ($query->execute()) {
            $result = $query->fetchAll();
        } else {
            $result = 0;
        }

        return $result;
    }

    public function UpdatePlayerPointsData($divisibleBy) {
        try {
            $remarks = "UpdatePlayerPointsData";
            $query = $this->_connection->prepare('UPDATE loyaltydb.membercards  SET VvPreviousPoints = VvCompPoints,  VvRaffleEntry =  FLOOR(VvCompPoints/' . $divisibleBy . '), VvCompPoints = 0 WHERE VvCompPoints >= ' . $divisibleBy . ' AND Status = 1;');

            if ($query->execute()) {
                if ($query->rowCount() > 0) {
                    $result = true;
                } else {
                    $result = false;
                }
            } else {
                $result = false;
            }

            return $result;
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

    //function for inserting logs 
    public function InsertLogs($title, $errormessage) {
        date_default_timezone_set("Asia/Taipei");
        $rootPath = '/var/www/membership.egamescasino.ph/RaffleCSVGenerator/log';
        $logfile = $rootPath . date('mdY') . '.txt';

        $today = date("Y-m-d H:i:s");

        if (!is_dir($rootPath)) {
            mkdir($rootPath);
        }

        $txt = "[" . $today . "]" . " " . $title . "  " . $errormessage;
        if (file_exists($logfile)) {
            $fh = fopen($logfile, 'a');
            fwrite($fh, $txt . "\r\n");
        } else {
            $fh = fopen($logfile, 'w');
            fwrite($fh, $txt . "\r\n");
        }
        fclose($fh);
    }

}
