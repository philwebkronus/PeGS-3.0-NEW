<?php

/**
 * @author fdlsison
 * @date 08-26-2015
 */

class TblPegsSiteInfoModel {
    public static $_instance = null;
    public $_connection;

    public function __construct() {
        $this->_connection = Yii::app()->db7;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new TblPegsSiteInfoModel();
        return self::$_instance;
    }
    
    public function getPegsSiteLocations() {
        $query = 'SELECT psi.fldSiteID, psi.fldOperatingHours, psi.fldLatitude, psi.fldLongitude, sd.IslandID, sd.RegionID, sd.ProvinceID, sd.CityID, sd.BarangayID, sd.SiteAddress, sd.ContactNumber, s.SiteName
                  FROM tblpegssiteinfo psi
                  INNER JOIN npos.sites s ON psi.fldSiteID = s.SiteID
                  INNER JOIN npos.sitedetails sd ON s.SiteID = sd.SiteID
                  WHERE psi.fldStatus = 1';
        $command = $this->_connection->createCommand($query);
        $result = $command->queryAll();
        
        return $result;
    }
}