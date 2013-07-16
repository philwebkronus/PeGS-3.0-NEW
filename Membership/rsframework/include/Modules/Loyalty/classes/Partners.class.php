<?php

/* * ***************** 
 * Author: Junjun S. Hernandez
 * Date Created: July 12, 2013 12:26:35PM
 * ***************** */
class Partners extends BaseEntity
{
    function Partners()
    {
        $this->TableName = "promos";
        $this->ConnString = "loyalty";
        $this->Identity = "PromoID";
        $this->DatabaseType = DatabaseTypes::PDO;
    }
    
    //Get Partner details without condition
    public function getPartners()
    {
        $query = "SELECT PartnerID, PartnerName FROM ref_partners";
        return parent::RunQuery($query);
    }
    
    //Get Partner details with by ID
    public function getPartnerNameByID($partnerid)
    {
        $query = "SELECT PartnerName FROM ref_partners WHERE PartnerID = $partnerid";
        return parent::RunQuery($query);
    }
}
?>
