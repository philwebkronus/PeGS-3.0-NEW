<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-04-16
 * Company: Philweb
 * ***************** */
class Nationality extends BaseEntity
{
    function Nationality()
    {
        $this->TableName = "ref_nationality";
        $this->ConnString = "membership";
        $this->Identity = "NationalityID";
    }
}
?>
