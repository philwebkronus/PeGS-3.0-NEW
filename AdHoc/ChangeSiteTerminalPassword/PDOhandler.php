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

    public function UpdateTerminalServices($TerminalCode, $serviceID, $PlainPassword, $HashedPasswword) {
        try {
            $remarks = "ChangeSiteTerminalPasswordWebTool";
            $query = $this->_connection->prepare('UPDATE terminalservices ts 
                                        INNER JOIN terminals t ON t.TerminalID = ts.TerminalID
                                        SET ts.ServicePassword = "' . $PlainPassword . '" , ts.HashedServicePassword = "' . $HashedPasswword . '" , Remarks = "' . $remarks . '" 
                                        WHERE TerminalCode IN ("' . $TerminalCode . '") AND ServiceID = "' . $serviceID . '"');

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
        $query = $this->_connection->prepare('SELECT * FROM ref_services WHERE Status = 1');
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

        $rootPath = '/var/www/ChangeSiteTerminalPassword/log/';
        $logfile = $rootPath . 'runtime.txt';

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

}
