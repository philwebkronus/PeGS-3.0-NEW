<?php

/**
 * Date Created 11 4, 11 2:26:58 PM <pre />
 * Date Modified 10/12/12
 * Description of SiteDenominationModel
 * @author Bryan Salazar
 * @author Edson Perez
 */
class SiteDenominationModel{
    const VIP = 1;
    public static $min;
    public static $max;
    
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new SiteDenominationModel();
        return self::$_instance;
    }

    public function getMinMaxDenominationPerSite($site_id,$denomination_type,$is_vip) {
        if($is_vip == self::VIP) 
            $like = "AND DenominationName LIKE '%VIP%'";
        else
            $like = "AND DenominationName NOT LIKE '%VIP%'";
            
        $sql = 'SELECT DenominationName, MinDenominationValue, MaxDenominationValue, 
            DenominationType FROM sitedenomination WHERE SiteID = :siteid AND DenominationType = :denomination_type ' . $like;
        $param = array(':siteid'=>$site_id,':denomination_type'=>$denomination_type);
        
        $command = $this->_connection->createCommand($sql);
        return $command->queryRow(true, $param);
    }
    
    public function getDenominationPerSiteAndType($site_id,$denomination_type,$is_vip) {
        $min_max = $this->getMinMaxDenominationPerSite($site_id, $denomination_type, $is_vip);
        $max = 0;
        $min = 0;

        $max = $min_max['MaxDenominationValue'];
        $min = $min_max['MinDenominationValue'];
        
        $refDenomination = new RefDenominationsModel();
        $interval = $refDenomination->getAllDenominationInterval();
        $denomination = array();
        
        self::$min = $min;
        self::$max = $max;
        
        foreach($interval as $val) {
            if($val >= $min && $val <= $max) {
                $denomination = array_merge($denomination,array($val=>number_format($val,2)));
            }
        }
        
        return $denomination;
    }
    
    public function getMinMaxInfo($posaccountno)
    {
        $sql = "SELECT MinDenominationValue, MaxDenominationValue, DenominationName FROM sitedenomination WHERE SiteID = :posaccountno
                AND DenominationType = 1";
        $params = array(':posaccountno'=>$posaccountno);
        $command = $this->_connection->createCommand($sql);
        $row = $command->queryAll(true, $params);
        return $row;
    }
    
    public function getRegMinMaxInfoWithAlias($posaccountno)
    {
        $sql = "SELECT MinDenominationValue AS RegMin, MaxDenominationValue AS RegMax FROM sitedenomination WHERE SiteID = :posaccountno
                AND DenominationType = 1 AND DenominationName LIKE '%Regular'";
        $params = array(':posaccountno'=>$posaccountno);
        $command = $this->_connection->createCommand($sql);
        $row = $command->queryAll(true, $params);
        return $row;
    }
    
    public function getVIPMinMaxInfoWithAlias($posaccountno)
    {
        $sql = "SELECT MinDenominationValue AS VIPMin, MaxDenominationValue AS VIPMax FROM sitedenomination WHERE SiteID = :posaccountno
                AND DenominationType = 1 AND DenominationName LIKE '%VIP'";
        $params = array(':posaccountno'=>$posaccountno);
        $command = $this->_connection->createCommand($sql);
        $row = $command->queryAll(true, $params);
        return $row;
    }
    
}

