<?php

/**
 * Description of LPTerminalSessions
 * @package application.modules.launchpad.models
 * @author Bryan Salazar
 */
require_once '../models/LPModel.php';
require_once '../models/LPConfig.php';

class LPTerminalSessions extends LPModel {

    /**
     *
     * @var LPTerminalSessions 
     */
    private static $_instance = null;
    public $_pdoconn;

    private function __construct() {
        $connstring = LPConfig::app()->params["db1"]["connectionString"];
        $username = LPConfig::app()->params["db1"]["username"];
        $password = LPConfig::app()->params["db1"]["password"];

        $this->_pdoconn = $this->setpdoconn($connstring, $username, $password);
    }

    /**
     * Get instance of LPTerminalSessions
     * @return LPTerminalSessions 
     */
    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new LPTerminalSessions();
        return self::$_instance;
    }

    public function checkSession($terminalCode) {

        $query = "SELECT ts.TerminalID, tss.UserMode, tss.ServiceID "
                . "FROM terminalsessions tss "
                . "INNER JOIN terminals ts ON tss.TerminalID = ts.TerminalID "
                . "INNER JOIN ref_services a ON tss.ServiceID = a.ServiceID "
                . "WHERE ts.TerminalCode IN ('$terminalCode','$terminalCode" . "VIP') ";

        $rqst = $this->_pdoconn->prepare($query);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        if (!$result) {
            $this->logerror("File: launchpad.models.LPTerminalSessions, Message: [checksession] Can't get terminal session");
        }
        return $result[0];
    }

    public function checkIfTerminalSessionLobby($terminalCode, $serviceID) {
        $query = "SELECT COUNT(ts.TerminalID) as Counter, tss.ServiceID as TsServiceID, tss.*, ts.*, a.*, t.* "
                . "FROM terminalsessions tss "
                . "INNER JOIN terminals ts ON tss.TerminalID = ts.TerminalID "
                . "INNER JOIN ref_services a ON tss.ServiceID = a.ServiceID "
                . "INNER JOIN terminalservices t ON ts.TerminalID = t.TerminalID "
                . "WHERE t.ServiceID = $serviceID AND ts.Status = 1 AND  ts.TerminalCode IN ('$terminalCode','$terminalCode" . "VIP') ";


        $rqst = $this->_pdoconn->prepare($query);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            $this->logerror("File: launchpad.models.LPTerminalSessions, Message: [Lobby] Can't get terminal session");
        }
        return $result[0];
    }

    public function checkExistingEwalletSession($terminalCode, $option) {

        if ($option == 0) {
            $query = "SELECT * "
                    . "FROM terminalsessions tss "
                    . "INNER JOIN terminals ts ON tss.TerminalID = ts.TerminalID "
                    . "INNER JOIN ref_services a ON tss.ServiceID = a.ServiceID "
                    . "WHERE ts.TerminalCode IN ('$terminalCode','$terminalCode" . "VIP')";
        } else {
            $query = "SELECT tss.LoyaltyCardNumber, tss.ServiceID,tss.UBServiceLogin,tss.UBServicePassword,tss.UBHashedServicePassword, tss.MID, tss.TransactionSummaryID, "
                    . "a.UserMode, a.ServiceGroupID "
                    . "FROM terminalsessions tss "
                    . "INNER JOIN terminals ts ON tss.TerminalID = ts.TerminalID "
                    . "INNER JOIN ref_services a ON tss.ServiceID = a.ServiceID "
                    . "WHERE ts.TerminalCode IN ('$terminalCode','$terminalCode" . "VIP') "
                    . "AND a.UserMode = 1 "
                    . "AND a.ServiceGroupID = 4 ";
        }

        $rqst = $this->_pdoconn->prepare($query);
        try {
            $rqst->execute();
            $result = $rqst->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->logerror("File: launchpad.models.LPTerminalSessions, Message: Can't get existing e-wallet session");
        }

        if (!empty($result)) {
            $result = $result[0];
        } else {
            $result = false;
        }

        return $result;
    }

    public function checkIsCardSession($ubCard) {

        $query = "SELECT * "
                . "FROM terminalsessions tss "
                . "INNER JOIN terminals ts ON tss.TerminalID = ts.TerminalID "
                . "INNER JOIN ref_services a ON tss.ServiceID = a.ServiceID "
                . "AND a.UserMode = 1 "
                . "AND a.ServiceGroupID = 4 "
                . "AND tss.LoyaltyCardNumber =:ubCard";

        $rqst = $this->_pdoconn->prepare($query);
        $rqst->bindParam(':ubCard', $ubCard);
        $rqst->execute();
        $result = $rqst->fetchAll(PDO::FETCH_ASSOC);

        if (!$result) {
            $this->logerror("File: launchpad.models.LPTerminalSession, Message: Can't get card terminal session");
        }
        return $result[0];
    }

}

