<?php

/* * ***************** 
 * Author: Junjun S. Hernandez
 * Date Created: July 12, 2013 12:26:35PM
 * ***************** */
class Partners extends BaseEntity
{
    function Partners()
    {
        $this->TableName = "ref_partners";
        $this->ConnString = "loyalty";
        $this->Identity = "PartnerID";
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
    
    public function getPartnerDetailsUsingPartnerName($PartnerName){
        $query = "SELECT pd.CompanyAddress, pd.CompanyPhone, pd.CompanyWebsite 
                            FROM $this->TableName rp 
                            INNER JOIN loyaltydb.partnerdetails pd ON pd.PartnerID = rp.PartnerID
                            WHERE rp.PartnerName='$PartnerName' ";
        return parent::RunQuery($query);
    }
}
?>
