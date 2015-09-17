<?php

/**
 * @author fdlsison
 * @date 08-26-2015
 */

class TblNonPegsSiteInfoModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db7;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new TblNonPegsSiteInfoModel();
        return self::$_instance;
    }
    
    public function getNonPegsSiteLocations() {
        $query = 'SELECT fldSiteName, fldIslandID, fldRegionID, fldProvinceID, fldCityID, fldBarangayID, fldSiteAddress, 
                         fldContactNumber, fldOperatingHours, fldLatitude, fldLongitude 
                  FROM tblnonpegssiteinfo
                  WHERE fldStatus = 1';
        $command = $this->_connection->createCommand($query);
        $result = $command->queryAll();
        
        return $result;
    }
}