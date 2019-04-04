<?php

/**
 * Description of LPTerminalServices
 * @package application.modules.launchpad.models
 * @author 
 */
require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPTerminalServices extends LPModel {

    private static $_instance = null;
    public $_pdoconn;

    private function __construct() {
        $connstring = LPConfig::app()->params["db1"]["connectionString"];
        $username = LPConfig::app()->params["db1"]["username"];
        $password = LPConfig::app()->params["db1"]["password"];
        $this->_pdoconn = $this->setpdoconn($connstring, $username, $password);
    }

    /**
     * Get instance of LPTerminalServices
     * @return LPTerminalServices 
     */
    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new LPTerminalServices();
        return self::$_instance;
    }

    public function checkTerminalServicesSession($terminalCode, $option) {
        if ($option == 0) {
            $query = "SELECT tss.TerminalID,tss.ServiceID,tss.ServicePassword,tss.HashedServicePassword,rs.UserMode,rs.ServiceGroupID "
                    . "FROM terminals ts "
                    . "INNER JOIN terminalservices tss ON ts.TerminalID = tss.TerminalID "
                    . "INNER JOIN ref_services rs ON tss.ServiceID = rs.ServiceID "
                    . "WHERE tss.Status = 1 "
                    . "AND ts.TerminalCode IN ('$terminalCode','$terminalCode" . "VIP') "
                    . "GROUP BY ts.TerminalID";
        } else {
            $query = "SELECT tss.TerminalID, tss.ServiceID,tss.ServicePassword "
                    . "FROM terminals ts "
                    . "INNER JOIN terminalservices tss ON ts.TerminalID = tss.TerminalID "
                    . "INNER JOIN ref_services rs ON tss.ServiceID = rs.ServiceID "
                    . "WHERE tss.Status = 1 "
                    . "AND ts.TerminalCode IN ('$terminalCode','$terminalCode" . "VIP') "
                    . "AND rs.UserMode <> 1 "
                    . "AND rs.ServiceGroupID <> 4 "
                    . "GROUP BY ts.TerminalID";
        }

        $rqst = $this->_pdoconn->prepare($query);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            $this->logerror("File: launchpad.models.LPTerminalServices, Message: Can't get terminal sessions");
        }

        return $result;
    }

    public function countMappedCasino($terminalID, $terminalIDVIP, $option) {
        if ($option == 0) {
            $query = "SELECT TerminalID, COUNT(ServiceID) as Count "
                    . "FROM terminalservices "
                    . "WHERE TerminalID IN($terminalID,$terminalIDVIP) "
                    . "AND Status = 1 "
                    . "GROUP BY TerminalID";
        } else {
            $query = "SELECT t.TerminalID, COUNT(rs.ServiceID) as Count "
                    . "FROM terminalservices rs "
                    . "INNER JOIN terminals t ON t.TerminalID=rs.TerminalID "
                    . "WHERE t.TerminalCode IN ('$terminalID','$terminalID" . "VIP') "
                    . "AND rs.Status=1 "
                    . "GROUP BY t.TerminalID";
        }

        $rqst = $this->_pdoconn->prepare($query);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            $this->logerror("File: launchpad.models.LPTerminalServices, Message: Can't get count of mapped casino");
        }

        return $result[0];
    }

    public function getTerminalUserMode($terminalCode) {
        $query = "SELECT rs.UserMode,rs.ServiceID, rs.Code, s.SiteClassificationID as SiteClassID, ts.TerminalType FROM terminalservices tss "
                . "INNER JOIN terminals ts ON ts.TerminalID = tss.TerminalID "
                . "INNER JOIN ref_services rs ON tss.ServiceID = rs.ServiceID "
                . "INNER JOIN sites s ON ts.SiteID = s.SiteID "
                . "WHERE ts.TerminalCode = :terminalCode AND tss.STATUS=1;";

        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminalCode', $terminalCode);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            $this->logerror("File: launchpad.models.LPServiceTerminals, Message: Can't get terminal service ID");
        }
        return $result[0];
    }

    public function getTerminalSiteClassification($terminalCode) {
        $query = "SELECT ss.SiteClassificationID FROM terminals ts "
                . "INNER JOIN sites ss ON ts.SiteID = ss.SiteID "
                . "WHERE ts.TerminalCode = :terminalCode;";

        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':terminalCode', $terminalCode);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            $this->logerror("File: launchpad.models.LPServiceTerminals, Message: Can't get terminal service ID");
        }
        return $result[0];
    }

    public function countServices($terminalCode) {
        /* $query = "SELECT count(*) as Count FROM terminalservices
          INNER JOIN (
          SELECT terminalservices.TerminalID FROM terminalservices
          INNER JOIN terminals ON terminals.TerminalID = terminalservices.TerminalID
          WHERE terminals.TerminalCode = :terminalCode
          GROUP BY terminalservices.TerminalID HAVING COUNT(terminalservices.TerminalID) > 1
          )temp ON terminalservices.TerminalID = temp.TerminalID
          WHERE Status = 1;"; */

        $query = "SELECT COUNT(terminalservices.ServiceID) as Count FROM terminalservices " .
                "INNER JOIN terminals ON terminals.TerminalID = terminalservices.TerminalID " .
                "WHERE terminals.TerminalCode IN ('$terminalCode','$terminalCode" . "VIP') AND terminalservices.Status = 1 " .
                "GROUP BY terminalservices.ServiceID;";

        $rqst = $this->_pdoconn->prepare($query);
        //$rqst->bindParam(':terminalCode',$terminalCode);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            $this->logerror("File: launchpad.models.LPServiceTerminals, Message: Can't get terminal service ID");
        }
        return $result[0];
    }

    public function getCurrentCasino($terminalCode) {

        $query = "SELECT GROUP_CONCAT(ServiceID SEPARATOR ', ') as ServiceID FROM terminalservices ts
                INNER JOIN terminals t ON t.TerminalID = ts.TerminalID 
                WHERE t.TerminalCode IN ('" . $terminalCode . "') AND ts.Status = 1";

        $rqst = $this->_pdoconn->prepare($query);
        //$rqst->bindParam(':terminalCode',$terminalCode);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            $this->logerror("File: launchpad.models.LPServiceTerminals, Message: Can't get terminal service ID");
        }
        return $result[0];
    }

}
