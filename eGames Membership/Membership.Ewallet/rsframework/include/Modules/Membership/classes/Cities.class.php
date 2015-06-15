<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-03-06
 * Company: Philweb
 * ***************** */
class Cities extends BaseEntity
{
    function Cities()
    {
        $this->TableName = "ref_cities";
        $this->Identity = "CityID";
        $this->ConnString = "membership";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    
    function getCityName($cityID){
        $query = "SELECT CityName FROM $this->TableName WHERE CityID = $cityID";
        $result = parent::RunQuery($query);
        return $result[0]['CityName'];
    }
    
    function checkCitiesAndRegionsValidity($regionid, $cityid){
        $query = "SELECT rc.CityID FROM membership.ref_cities rc
                            INNER JOIN membership.ref_provinces rf ON rf.ProvinceID = rc.ProvinceID
                            INNER JOIN membership.ref_regions rg ON rg.RegionID = rf.RegionID
                            WHERE rg.RegionID =".$regionid." AND rc.CityID =".$cityid;
        $result = parent::RunQuery($query);
        if(isset($result[0]['CityID'])){
            return $result[0]['CityID'];
        } else {
            return $result = "";
        }
    }
    
    function getCitiesUsingRegionID($regionid){
        $query = "SELECT rc.CityID, rc.CityName FROM membership.ref_cities rc
                            INNER JOIN membership.ref_provinces rf ON rf.ProvinceID = rc.ProvinceID
                            INNER JOIN membership.ref_regions rg ON rg.RegionID = rf.RegionID
                            WHERE rg.RegionID =".$regionid;
        $result = parent::RunQuery($query);
        return $result;
    }
}
?>
