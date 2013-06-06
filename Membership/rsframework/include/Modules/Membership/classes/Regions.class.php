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
}
?>
