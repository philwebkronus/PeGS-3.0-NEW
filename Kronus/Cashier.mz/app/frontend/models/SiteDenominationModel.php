<?php

/**
 * Date Created 11 4, 11 2:26:58 PM <pre />
 * Description of SiteDenominationModel
 * @author Bryan Salazar
 */
class SiteDenominationModel extends MI_Model{
    
//    const NON_VIP = 0;
    const VIP = 1;
    public static $min;
    public static $max;

    public function getMinMaxDenominationPerSite($site_id,$denomination_type,$is_vip) {
        if($is_vip == self::VIP) 
            $like = "AND DenominationName LIKE '%VIP%'";
        else
            $like = "AND DenominationName NOT LIKE '%VIP%'";
            
        $sql = 'SELECT DenominationName, MinDenominationValue, MaxDenominationValue, 
            DenominationType FROM sitedenomination WHERE SiteID = :siteid AND DenominationType = :denomination_type ' . $like;
        $param = array(':siteid'=>$site_id,':denomination_type'=>$denomination_type);
        $this->exec($sql,$param);
        return $this->findAll();
    }
    
    public function getDenominationPerSiteAndType($site_id,$denomination_type,$is_vip) {
        Mirage::loadModels('RefDenominationsModel');
        
        $min_max = $this->getMinMaxDenominationPerSite($site_id, $denomination_type, $is_vip);
        $max = 0;
        $min = 0;
        foreach($min_max as $val) {
            $max = $val['MaxDenominationValue'];
            $min = $val['MinDenominationValue'];
        }
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
}

