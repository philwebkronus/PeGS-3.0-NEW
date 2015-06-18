<?php

/* * *****************
 * Author: Roger Sanchez
 * Date Created: 2013-04-08
 * Company: Philweb
 * ***************** */

class Identifications extends BaseEntity
{

    function Identifications()
    {
        $this->TableName = "ref_identifications";
        $this->ConnString = "membership";
        $this->Identity = "IdentificationID";
    }

    public function getIDPresented($idPresented)
    {
        $query = "SELECT IdentificationName FROM $this->TableName WHERE IdentificationID = $idPresented";

        return parent::RunQuery($query);
    }

}

?>
