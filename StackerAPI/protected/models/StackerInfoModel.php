<?php

/**
 * @description of StackerInfoModel
 * @author jshernandez <jshernandez@philweb.com.ph>
 * @datecreated 11 11, 13 1:11:44 PM
 */
class StackerInfoModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new TerminalsModel();
        return self::$_instance;
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $stackerTagID
     * @return object
     */
    public function isStackerTagIDExists($stackerTagID) {
        $sql = "SELECT COUNT(StackerInfoID) ctrStackerInfoID FROM stackerinfo WHERE StackerTagID = :stacker_tag_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':stacker_tag_id' => $stackerTagID));
        $result = $command->queryRow();

        if (isset($result['ctrStackerInfoID'])) {
            return $result['ctrStackerInfoID'];
        } else {
            return 0;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $serialNumber
     * @return object
     */
    public function isSerialNumberExists($serialNumber) {
        $sql = "SELECT COUNT(StackerInfoID) ctrStackerInfoID FROM stackerinfo WHERE SerialNumber = :serial_number";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':serial_number' => $serialNumber));
        $result = $command->queryRow();

        if (isset($result['ctrStackerInfoID'])) {
            return $result['ctrStackerInfoID'];
        } else {
            return 0;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $terminalName
     * @return object
     */
    public function isTerminalExists($terminalName) {
        $sql = "SELECT COUNT(StackerInfoID) ctrStackerInfoID FROM stackerinfo WHERE TerminalName = :terminal_name";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':terminal_name' => $terminalName));
        $result = $command->queryRow();

        if (isset($result['ctrStackerInfoID'])) {
            return $result['ctrStackerInfoID'];
        } else {
            return 0;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $terminalName
     * @return object
     */
    public function checkIfTerminalIsActive($terminalName) {
        $sql = "SELECT COUNT(StackerInfoID) ctrStackerInfoID FROM stackerinfo WHERE TerminalName = :terminal_name AND (Status = 0 OR Status = 1)";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':terminal_name' => $terminalName));
        $result = $command->queryRow();

        if (isset($result['ctrStackerInfoID'])) {
            return $result['ctrStackerInfoID'];
        } else {
            return 0;
        }
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $terminalName
     * @return object
     */
    public function checkIfStackerTagIDIsActive($stackerTagID) {
        $sql = "SELECT COUNT(StackerInfoID) ctrStackerInfoID FROM stackerinfo WHERE StackerTagID = :stacker_tag_id AND (Status = 0 OR Status = 1)";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':stacker_tag_id' => $stackerTagID));
        $result = $command->queryRow();

        if (isset($result['ctrStackerInfoID'])) {
            return $result['ctrStackerInfoID'];
        } else {
            return 0;
        }
    }
    
    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $terminalName
     * @return object
     */
    public function checkIfSerialNumberIsActive($serialNumber) {
        $sql = "SELECT COUNT(StackerInfoID) ctrStackerInfoID FROM stackerinfo WHERE SerialNumber = :serial_number AND (Status = 0 OR Status = 1)";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':serial_number' => $serialNumber));
        $result = $command->queryRow();

        if (isset($result['ctrStackerInfoID'])) {
            return $result['ctrStackerInfoID'];
        } else {
            return 0;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $terminalName
     * @param string $serialNumber
     * @return object
     */
    public function checkIfTerminalAndSerialMatched($terminalName, $serialNumber) {
        $sql = "SELECT StackerInfoID FROM stackerinfo WHERE TerminalName = :terminal_name AND SerialNumber = :serial_number";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':terminal_name' => $terminalName, ':serial_number' => $serialNumber));
        $result = $command->queryRow();

        if (isset($result['StackerInfoID'])) {
            return $result['StackerInfoID'];
        } else {
            return 0;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $terminalName
     * @param string $serialNumber
     * @return object
     */
    public function checkTerminalStatus($terminalName, $serialNumber) {
        $sql = "SELECT Status FROM stackerinfo WHERE TerminalName = :terminal_name AND SerialNumber = :serial_number";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':terminal_name' => $terminalName, ':serial_number' => $serialNumber));
        $result = $command->queryRow();

        if (isset($result['Status'])) {
            return $result['Status'];
        } else {
            return 0;
        }
    }

    /**
     * @author JunJun S. Hernandez
     * @datecreated 11/11/13
     * @param string $terminalName
     * @param string $serialNumber
     * @return object
     */
    public function getStackerInfoIDByTerminalName($terminalName) {
        $sql = "SELECT StackerInfoID FROM stackerinfo WHERE TerminalName = :terminal_name";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':terminal_name' => $terminalName));
        $result = $command->queryRow();

        if (isset($result['StackerInfoID'])) {
            return $result['StackerInfoID'];
        } else {
            return 0;
        }
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param int $stackerInfoID
     * @param int $status
     * @return boolean success|failed
     */
    public function updateStackerInfo($stackerInfoID, $status) {
        $beginTrans = $this->_connection->beginTransaction();

        try {

            $sql = "UPDATE stackerinfo SET Status = :status WHERE StackerInfoID = :stacker_info_id";

            $param = array(':stacker_info_id' => $stackerInfoID, ':status' => $status);

            $command = $this->_connection->createCommand($sql);

            $command->bindValues($param);

            $command->execute();

            try {
                $beginTrans->commit();
                return 1;
            } catch (PDOException $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param string $stackerTagID
     * @param string $serialNumber
     * @return array
     */
    public function getStackerInfoByStackerTagAndSerial($stackerTagID) {
        $sql = "SELECT TerminalName, Status, SerialNumber FROM stackerinfo WHERE StackerTagID = :stacker_tag_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':stacker_tag_id' => $stackerTagID));
        $result = $command->queryRow();

        if (!empty($result)) {
            return $result;
        } else {
            return 0;
        }
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param string $stackerTagID
     * @param string $serialNumber
     * @param string $terminalName
     * @return array
     */
    public function getStackerInfoIDByData($stackerTagID, $serialNumber, $terminalName) {
        $sql = "SELECT StackerInfoID FROM stackerinfo WHERE StackerTagID = :stacker_tag_id AND SerialNumber = :serial_number";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':stacker_tag_id' => $stackerTagID, ':serial_number' => $serialNumber));
        $result = $command->queryRow();

        if (!empty($result)) {
            return $result;
        } else {
            return 0;
        }
    }
    
    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param string $stackerTagID
     * @param string $serialNumber
     * @param string $terminalName
     * @return array
     */
    public function getStackerInfoByStackerInfoID($stackerInfoID) {
        $sql = "SELECT StackerTagID, SerialName FROM stackerinfo WHERE StackerInfoID = :stacker_info_id";
        $command = $this->_connection->createCommand($sql);
        $command->bindValues(array(':stacker_info_id' => $stackerInfoID));
        $result = $command->queryRow();

        if (!empty($result)) {
            return $result;
        } else {
            return 0;
        }
    }

    /**
     * @Author JunJun S. Hernandez
     * @DateCreated 11/11/13
     * @param string $stackerTagID
     * @param string $serialNumber
     * @param int $status
     * @param string $terminalName
     * @return array
     */
    public function addStackerInfo($stackerTagID, $serialNumber, $status, $terminalName) {
        $beginTrans = $this->_connection->beginTransaction();

        try {

            $sql = "INSERT INTO stackerinfo(StackerTagID, SerialNumber, Status, TerminalName, DateCreated) VALUES(:stacker_tag_id, :serial_number, :status, :terminal_name, NOW(6))";

            $param = array(':stacker_tag_id' => $stackerTagID, ':serial_number' => $serialNumber, ':status' => $status, ':terminal_name' => $terminalName);

            $command = $this->_connection->createCommand($sql);

            $command->bindValues($param);

            $command->execute();
            $logID = $this->_connection->getLastInsertID();

            try {
                $beginTrans->commit();
                return $logID;
            } catch (PDOException $e) {
                $beginTrans->rollback();
                Utilities::log($e->getMessage());
                return 0;
            }
        } catch (Exception $e) {
            $beginTrans->rollback();
            Utilities::log($e->getMessage());
            return 0;
        }
    }

}
