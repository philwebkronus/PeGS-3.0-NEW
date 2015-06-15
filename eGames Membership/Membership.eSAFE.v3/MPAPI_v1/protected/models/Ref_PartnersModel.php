<?php

/**
 * @author fdlsison
 * 
 * @date 6-26-2014
 */

class Ref_PartnersModel {
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db4;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new Ref_PartnersModel();
        return self::$_instance;
    }
    
    public function getPartnerDetailsUsingPartnerName($partnerName) {
        $sql = 'SELECT pd.CompanyAddress, pd.CompanyPhone, pd.CompanyWebsite
                FROM ref_partners rp
                INNER JOIN partnerdetails pd
                    ON pd.PartnerID = rp.PartnerID
                WHERE rp.PartnerName = :partnerName';
        $param = array(':partnerName' => $partnerName);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
    
    //@date 08-06-2014
    //@purpose //get partner name with partner id as input
    public function getPartnerNameUsingPartnerID($partnerID) {
        $sql = 'SELECT PartnerName
                FROM ref_partners
                WHERE PartnerID = :partnerID';
        $param = array(':partnerID' => $partnerID);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $param);
        
        return $result;
    }
            
}

