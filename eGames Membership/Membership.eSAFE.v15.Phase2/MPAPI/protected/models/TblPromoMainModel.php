<?php

/**
 * @author fdlsison
 * @date 08-24-2015
 */

class TblPromoMainModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db7;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new TblPromoMainModel();
        return self::$_instance;
    }
    
    public function getSitePromos($viewBy) {
        $query = 'SELECT pm.fldPromoName, pm.fldPromoDetails, pm.fldStartDate, pm.fldEndDate, pm.fldDrawDate, pm.fldPromoThumbnail, pm.fldPromoPoster, pm.fldSiteID, s.SiteName, pm.fldStatus
                  FROM tblpromomain pm
                  INNER JOIN npos.sites s ON pm.fldSiteID = s.SiteID
                  WHERE pm.fldStatus = 1
                  AND pm.fldPromoTypeID = :ViewBy
                  AND pm.fldEndDate >= NOW(6)';
        $param = array(':ViewBy' => $viewBy);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryAll(true, $param);
        
        return $result;
    }
    
    public function getAllPromosBasedOnViewBy($viewBy) {
        $query = 'SELECT fldPromoName, fldPromoDetails, fldStartDate, fldEndDate, fldDrawDate, fldPromoThumbnail, fldPromoPoster, fldSiteID, fldStatus
                  FROM tblpromomain
                  WHERE fldStatus = 1
                  AND fldPromoTypeID = :ViewBy
                  AND fldEndDate <= NOW(6)';
        $param = array(':ViewBy' => $viewBy);
        $command = $this->_connection->createCommand($query);
        $result = $command->queryAll(true, $param);
        
        return $result;
    }
}