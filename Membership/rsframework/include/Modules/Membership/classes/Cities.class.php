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
}
?>
