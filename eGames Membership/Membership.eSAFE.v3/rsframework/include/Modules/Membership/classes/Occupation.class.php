<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-04-16
 * Company: Philweb
 * ***************** */
class Occupation extends BaseEntity
{
    function Occupation()
    {
        $this->TableName = "ref_occupations";
        $this->ConnString = "membership";
        $this->Identity = "OccupationID";
    }
}
?>
