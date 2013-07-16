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
}
?>
