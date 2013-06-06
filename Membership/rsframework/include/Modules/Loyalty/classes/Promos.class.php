<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-06-05
 * Company: Philweb
 * ***************** */
class Promos extends BaseEntity
{
    function Promos()
    {
        $this->TableName = "promos";
        $this->ConnString = "loyalty";
        $this->Identity = "PromoID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
}
?>
