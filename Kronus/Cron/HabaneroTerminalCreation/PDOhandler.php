<?php

/*
 * @description: Batch Terminal Creation for habanero
 * @date Created January 04, 2018 14:51 PM <pre />
 * @author Jonathan Concepcion <jvconcepcion@philweb.com.ph> & Ronald Patrick Santos <rjsantos@philweb.com.ph>
 */

class PDOhandler {

    public $_connection;

    public function __construct() {
        $servername = '172.16.116.17';
        $DatabaseType = 'mysql';
        $port = '3307';
        $dbname = 'npos';
        $username = 'pegsconn';
        $password = 'pegsconnpass';

        $this->_connection = new PDO("$DatabaseType:host=$servername;port=$port;dbname=$dbname", $username, $password);
        $this->_connection->setAttribute(PDO:: ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        if ($this->_connection)
            return true;
        else
            return false;
    }

    public function getSites() {
        $query = $this->_connection->prepare('SELECT * FROM sites WHERE SiteID NOT IN (0)');

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

    public function getTerminalDetails($terminalID) {
        $query = $this->_connection->prepare('SELECT * FROM terminals WHERE terminalID  = ' . $terminalID);

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
                                        WHERE GeneratedPasswordBatchID = "' . $GeneratedpasswordBatchID . '"');

            if ($query->execute()) {
                return 1;
            } else {
                $result = 0;
            }

            return $result;
        } catch (Exception $ex) {
            
        }
    }

    public function getGeneratedPasswordBatchID($SiteID) {
        $query = $this->_connection->prepare('SELECT * FROM generatedpasswordbatch WHERE SiteID = "' . $SiteID . '" AND Status = 1 ORDER BY GeneratedPasswordBatchID DESC LIMIT 1');

        if ($query->execute()) {
            $result = $query->fetchAll();
        } else {
            $result = "Error!";
        }

        return $result[0];
    }

    public function getGeneratedPasswordPool($GeneratedPasswordBatchID) {
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

}

