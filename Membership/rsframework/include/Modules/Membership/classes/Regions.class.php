<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-03-06
 * Company: Philweb
 * ***************** */
class Regions extends BaseEntity
{
    function Regions()
    {
        $this->TableName = "ref_regions";
        $this->Identity = "RegionID";
        $this->ConnString = "membership";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    function getRegionName($regionID){
        $query = "SELECT RegionName FROM $this->TableName WHERE RegionID = $regionID";
        $result = parent::RunQuery($query);
        return $result[0]['RegionName'];
    }
}
?>
