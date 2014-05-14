<?php

/**
 * @description of AccountsModel
 * @author JunJun S. Hernandez <jshernandez@philweb.com.ph>
 * @datecreated 02/21/14
 */
class AccountsModel {

    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection2 = Yii::app()->db2;
    }

    public static function model() {
        if (self::$_instance == null)
            self::$_instance = new AccountsModel();
        return self::$_instance;
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
        $command = $this->_connection2->createCommand($sql);
        $result = $command->queryRow(true, $param);
        if (!empty($result)) {
            return $result['AID'];
        } else {
            return 0;
        }
    }

}
