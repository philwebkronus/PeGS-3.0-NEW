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

    public function getSites() {
        $query = $this->_connection->prepare('SELECT * FROM sites WHERE SiteID NOT IN (0) ORDER BY SiteCode');

        if ($query->execute()) {
            $res = $query->fetchAll();
            foreach ($res as $single) {
                $result[] = $single;
            }
        } else {
            $result = "Error!";
        }

        return $result;
    }

    public function getActiveSites() {
        $query = $this->_connection->prepare('SELECT * FROM sites WHERE SiteID NOT IN (0) AND Status = 1 ORDER BY SiteCode');

        if ($query->execute()) {
            $res = $query->fetchAll();
            foreach ($res as $single) {
                $result[] = $single;
            }
        } else {
            $result = "Error!";
        }

        return $result;
    }

    public function getTerminals($SiteID) {
        $query = $this->_connection->prepare('SELECT * FROM terminals WHERE SiteID  = ' . $SiteID . ' AND isVIP = 0');

        if ($query->execute()) {
            $res = $query->fetchAll();
            if (count($res) > 0) {
                foreach ($res as $single) {
                    $result[] = $single;
                }
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getTerminalID($terminalCode) {
        $query = $this->_connection->prepare('SELECT * FROM terminals WHERE TerminalCode = "' . $terminalCode . '"');

        if ($query->execute()) {
            $res = $query->fetchAll();
            if (count($res) > 0) {
                $result = $res[0];
            } else {
                $result = false;
            }
        } else {
            $result = "Error!";
        }

        return $result;
    }

    public function checkTerminalServices($terminalID) {
        $query = $this->_connection->prepare('SELECT * FROM terminalservices WHERE TerminalID  = "' . $terminalID . '" AND ServiceID = 25');

        if ($query->execute()) {
            $res = $query->fetchAll();
            if (count($res) > 0) {
                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = "Error!";
        }

        return $result;
    }

    public function getGeneratedPasswordBatchID() {
        $query = $this->_connection->prepare('SELECT GeneratedPasswordBatchID FROM generatedpasswordbatch
                        WHERE SiteID IS NULL 
                        AND DateUsed IS NULL AND Status = 0 
                        LIMIT 1');


        if ($query->execute()) {
            $res = $query->fetchAll();
            if (count($res) > 0) {
                $result = $res[0];
            } else {
                $result = false;
            }
        } else {
            $result = "Error!";
        }

        return $result;
    }

    public function getOldGeneratedPasswordBatchID($SiteID) {
        $query = $this->_connection->prepare('SELECT GeneratedPasswordBatchID FROM generatedpasswordbatch 
                     WHERE SiteID = "' . $SiteID . '" AND Status = 1 AND DateUsed IS NOT NULL LIMIT 1');


        if ($query->execute()) {
            $res = $query->fetchAll();
            if (count($res) > 0) {
                $result = $res[0];
            } else {
                $result = false;
            }
        } else {
            $result = "Error!";
        }

        return $result;
    }

    public function getServiceGroupID($serviceID) {
        $query = $this->_connection->prepare('
                SELECT rsg.ServiceGroupID FROM ref_services rs
                INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                WHERE rs.ServiceID = ' . $serviceID);


        if ($query->execute()) {
            $res = $query->fetchAll();
            if (count($res) > 0) {
                $result = $res[0];
            } else {
                $result = false;
            }
        } else {
            $result = "Error!";
        }

        return $result;
    }

    public function getServicePassword($terminalid, $serviceID) {
        $query = $this->_connection->prepare('SELECT ServicePassword FROM terminalservices WHERE TerminalID = "' . $terminalid . '" AND ServiceID = ' . $serviceID);
        if ($query->execute()) {
            $res = $query->fetchAll();

            if (count($res) > 0) {
                $result = $res[0];
            } else {
                $result = false;
            }
        } else {
            $result = "Error!";
        }

        return $result;
    }

    public function getGeneratedPasswordPool($serviceGroupID) {
        $query = $this->_connection->prepare('SELECT DISTINCT PlainPassword, EncryptedPassword FROM generatedpasswordpool WHERE ServiceGroupID = "' . $serviceGroupID . '" ORDER BY RAND() LIMIT 1');

        if ($query->execute()) {
            $res = $query->fetchAll();
            if (count($res) > 0) {
                $result = $res[0];
            } else {
                $result = false;
            }
        } else {
            $result = "Error!";
        }

        return $result;
    }

    public function gethabaneroGeneratedPasswordPool($GeneratedPasswordBatchID) {
        $query = $this->_connection->prepare('SELECT * FROM generatedpasswordpool WHERE GeneratedPasswordBatchID = "' . $GeneratedPasswordBatchID . '" AND ServiceGroupID = 6');

        if ($query->execute()) {
            $result = $query->fetchAll();
        } else {
            $result = "Error!";
        }

        return $result[0];
    }

    public function UpdateTerminalServices($TerminalCode, $serviceID, $PlainPassword, $HashedPasswword) {
        try {
            $remarks = "ChangeSiteTerminalPasswordWebTool";
            $query = $this->_connection->prepare('UPDATE terminalservices ts 
                                        INNER JOIN terminals t ON t.TerminalID = ts.TerminalID
                                        SET ts.ServicePassword = "' . $PlainPassword . '" , ts.HashedServicePassword = "' . $HashedPasswword . '" , Remarks = "' . $remarks . '" 
                                        WHERE TerminalCode = "' . $TerminalCode . '" AND ServiceID = "' . $serviceID . '"');

            if ($query->execute()) {
                return 1;
            } else {
                $result = 0;
            }

            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function UpdateGeneratedPaswordBatch($GeneratedPasswordBatchID, $serviceGroupID, $siteID) {
        $this->_connection->beginTransaction();
        $getOldGeneratedPasswordBatchID = $this->getOldGeneratedPasswordBatchID($siteID);
        if ($getOldGeneratedPasswordBatchID > 0) {
            $OldGeneratedPasswordBatchID = $getOldGeneratedPasswordBatchID[0];

            try {
                $query = $this->_connection->prepare('UPDATE generatedpasswordbatch SET Status = 2 WHERE SiteID = "' . $siteID . '" AND GeneratedPasswordBatchID = "' . $OldGeneratedPasswordBatchID . '" AND Status = 1');
                $query->execute();
                $isupdated1 = $this->rowCount();
                try {
                    $query = $this->_connection->prepare('
                        UPDATE generatedpasswordbatch SET Status = 1, DateUsed = NOW(6), SiteID = "' . $siteID . '"
                            WHERE Status = 0 AND SiteID IS NULL AND DateUsed IS NULL AND GeneratedPasswordBatchID = "' . $GeneratedPasswordBatchID . '"
                            ');
                    $query->execute();
                    $isupdated2 = $this->rowCount();
                    try {
                        if ($isupdated1 > 0 && $isupdated2 > 0) {
                            $this->_connection->commit();
                            return 1;
                        } else
                            return 0;
                    } catch (PDOException $e) {
                        $this->_connection->rollBack();
                        return 0;
                    }
                } catch (Exception $ex) {
                    $this->_connection->rollBack();
                    return 0;
                }
            } catch (Exception $ex) {
                $this->_connection->rollBack();
                return 0;
            }
        }
    }

    public function getActiveCasinos() {
        $query = $this->_connection->prepare('SELECT * FROM ref_services WHERE Status = 1 AND UserMode = 0');
        if ($query->execute()) {
            $res = $query->fetchAll();

            if (count($res) > 0) {
                $result = $res;
            } else {
                $result = false;
            }
        } else {
            $result = "Error!";
        }

        return $result;
    }

    //function for inserting logs 
    public function InsertLogs($title, $errormessage) {
        date_default_timezone_set("Asia/Taipei");
        $rootPath = '/var/www/ASWebtool/log/ChangePassword/';
//        $rootPath = 'C:/xampp1/htdocs/ASWebtool/log/ChangePassword/';
        $logfile = $rootPath . date('mdY') . '.txt';

        $today = date("Y-m-d H:i:s");

        if (!is_dir($rootPath)) {
            mkdir($rootPath);
        }

        $txt = "[" . $today . "]" . " " . $title . " | " . $errormessage;
        if (file_exists($logfile)) {
            $fh = fopen($logfile, 'a');
            fwrite($fh, $txt . "\r\n");
        } else {
            $fh = fopen($logfile, 'w');
            fwrite($fh, $txt . "\r\n");
        }
        fclose($fh);
    }

    //function for inserting logs 
    public function InsertLogsHabanero($title, $errormessage) {
        date_default_timezone_set("Asia/Taipei");

        $rootPath = '/var/www/ASWebtool/log/HabaneroAccountCreation/';
//        $rootPath = 'C:/xampp1/htdocs/ASWebtool/log/HabaneroAccountCreation/';
        $logfile = $rootPath . date('mdY') . '.txt';

        $today = date("Y-m-d H:i:s");

        if (!is_dir($rootPath)) {
            mkdir($rootPath);
        }

        $txt = "[" . $today . "]" . " " . $title . " | " . $errormessage;
        if (file_exists($logfile)) {
            $fh = fopen($logfile, 'a');
            fwrite($fh, $txt . "\r\n");
        } else {
            $fh = fopen($logfile, 'w');
            fwrite($fh, $txt . "\r\n");
        }
        fclose($fh);
    }

    //function for inserting logs 
    public function InsertLogsHabaneroUserbased($title, $errormessage) {
        date_default_timezone_set("Asia/Taipei");

        $rootPath = '/var/www/ASWebtool/log/HabaneroUserbasedAccountCreation/';
//        $rootPath = 'C:/xampp1/htdocs/ASWebtool/log/HabaneroUserbasedAccountCreation/';
        $logfile = $rootPath . date('mdY') . '.txt';

        $today = date("Y-m-d H:i:s");

        if (!is_dir($rootPath)) {
            mkdir($rootPath);
        }

        $txt = "[" . $today . "]" . " " . $title . " | " . $errormessage;
        if (file_exists($logfile)) {
            $fh = fopen($logfile, 'a');
            fwrite($fh, $txt . "\r\n");
        } else {
            $fh = fopen($logfile, 'w');
            fwrite($fh, $txt . "\r\n");
        }
        fclose($fh);
    }

    public function checkTerminalServicesIfHasTopaz() {
        $query = $this->_connection->prepare('SELECT * FROM terminalservices WHERE ServiceID = 22 ORDER BY TerminalID');

        if ($query->execute()) {
            $res = $query->fetchAll();
            foreach ($res as $single) {
                $result[] = $single;
            }
        } else {
            $result = false;
        }

        return $result;
    }

    public function checkTerminalServicesIfHasHabanero($terminalID) {
        $query = $this->_connection->prepare('SELECT * FROM terminalservices WHERE TerminalID  = "' . $terminalID . '" AND ServiceID = 25');

        if ($query->execute()) {
            $res = $query->fetchAll();
            if (count($res) > 0) {
                $result = true;
            } else {
                $result = false;
            }
        } else {
            $result = "Error!";
        }

        return $result;
    }

    public function getNewGeneratedPasswordBatchID() {
        $query = $this->_connection->prepare('SELECT * FROM generatedpasswordbatch WHERE Status = 0 ORDER BY GeneratedPasswordBatchID DESC LIMIT 1');

        if ($query->execute()) {
            $result = $query->fetchAll();
        } else {
            $result = "Error!";
        }

        return $result[0];
    }

    public function UpdateNewGeneratedPasswordBatchID($SiteID, $GeneratedpasswordBatchID) {
        try {
            $remarks = "ChangeSiteTerminalPasswordWebTool";
            $query = $this->_connection->prepare('UPDATE generatedpasswordbatch
                                        SET Status = 1 , SiteID = "' . $SiteID . '" , DateUsed = NOW(6)
                                        WHERE GeneratedPasswordBatchID = "' . $GeneratedpasswordBatchID . '" ORDER BY DateUsed DESC');

            if ($query->execute()) {
                return 1;
            } else {
                $result = 0;
            }

            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function getHabaneroGeneratedPasswordBatchID($SiteID) {
        $query = $this->_connection->prepare('SELECT * FROM generatedpasswordbatch WHERE SiteID = "' . $SiteID . '" AND Status = 1 ORDER BY GeneratedPasswordBatchID DESC LIMIT 1');

        if ($query->execute()) {
            $result = $query->fetchAll();
        } else {
            $result = "Error!";
        }

        return $result[0];
    }

    public function getHabanerpGeneratedPasswordPool($GeneratedPasswordBatchID) {
        $query = $this->_connection->prepare('SELECT * FROM generatedpasswordpool WHERE GeneratedPasswordBatchID = "' . $GeneratedPasswordBatchID . '" AND ServiceGroupID = 6');

        if ($query->execute()) {
            $result = $query->fetchAll();
        } else {
            $result = "Error!";
        }

        return $result[0];
    }

    public function InsertTerminalServices($TerminalID, $PlainPassword, $HashedPasswword) {
        date_default_timezone_set("Asia/Taipei");
        $today = date("Y-m-d H:i:s");
        try {
            $query = $this->_connection->prepare('INSERT INTO terminalservices
            (TerminalID,
            ServiceID,
            ServicePassword,
            HashedServicePassword,
            Status,
            isCreated,
            DateCreated)
            VALUES
            (
            "' . $TerminalID . '",
            "25",
            "' . $PlainPassword . '",
            "' . $HashedPasswword . '",
            "0",
            "1",
            "' . $today . '"
            )');


            if ($query->execute()) {
                return true;
                $this->_connection->beginTransaction();
                $this->_connection->commit();
            } else {
                $result = "Error!";
            }

            return $result;
        } catch (Exception $ex) {
            echo "<pre>";
            echo $ex->getMessage();
            $this->_connection->rollBack();
        }
    }

    public function getHabaneroAccountsForCreation($Count) {
        $query = $this->_connection->prepare('SELECT * FROM membership.tempmemberservices where ServiceID = 29 AND Status = 1  AND  OptionID1 IS NULL ORDER BY MID ASC LIMIT ' . $Count);

        if ($query->execute()) {
            $res = $query->fetchAll();
            foreach ($res as $single) {
                $result[] = $single;
            }
        } else {
            $result = 0;
        }
        return $result;
    }

    public function updateOptionID($MID) {
        try {
            $query = $this->_connection->prepare('UPDATE membership.tempmemberservices
                                        SET OptionID1 = 1 WHERE MID = "' . $MID . '" AND ServiceID = 29 AND Status = 1  AND  OptionID1 IS NULL');

            if ($query->execute()) {
                return true;
            } else {
                $result = false;
            }

            return $result;
        } catch (Exception $ex) {
            $result = false;
        }
    }

}
