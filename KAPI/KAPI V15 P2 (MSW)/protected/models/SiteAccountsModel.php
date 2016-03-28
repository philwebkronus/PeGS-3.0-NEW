<?php

/**
 * Date Created 11 11, 11 6:36:28 PM <pre />
 * Date Modified 10/12/12
 * Description of SiteAccountsModel
 * @author Bryan Salazar
 * @author Edson Perez
 */
class SiteAccountsModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new SiteAccountsModel();
        return self::$_instance;
    }
    
    /**FOR REMOVAL
     *
     * @param type $siteid
     * @param type $bgiOwner
     * @return type 
     */
//    public function getSiteGroup($siteid, $bgiOwner)
//    {
//        $sql = "SELECT COUNT(SiteID) as ctrbgi FROM sites WHERE OwnerAID = :owner_id AND SiteID = :site_id";
//        $param = array(':owner_id'=>$bgiOwner,':site_id'=>$siteid);
//        $command = $this->_connection->createCommand($sql);
//        $result = $command->queryRow(true, $param);
//        return $result['ctrbgi'];
//    }
    
    public function getVirtualCashier($siteid){
        $sql = "SELECT sa.AID FROM siteaccounts sa 
                INNER JOIN accounts a ON a.AID = sa.AID 
                WHERE AccountTypeID = 15 AND sa.SiteID = :site_id 
                AND sa.Status = 1 AND a.Status = 1";
        $param = array(':site_id'=>$siteid);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if($result == ''){
            return false;
        }
        return $result['AID'];
    }
    
    public function getAIDByAccountTypeIDAndTerminalID($accountTypeID, $terminalID) {
        $sql = 'SELECT a.AID FROM accounts a
                    INNER JOIN ref_accounttypes rat ON rat.AccountTypeID = a.AccountTypeID
                    INNER JOIN siteaccounts sa ON sa.AID = a.AID
                    INNER JOIN sites s ON s.SiteID = sa.SiteID
                    INNER JOIN terminals t ON t.SiteID = s.SiteID
                    WHERE a.AccountTypeID = :account_type_id
                    AND t.TerminalID = :terminal_id';
        $param = array(":account_type_id" => $accountTypeID, ":terminal_id" => $terminalID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if (!empty($result)) {
            return $result['AID'];
        } else {
            return 0;
        }
    }
}