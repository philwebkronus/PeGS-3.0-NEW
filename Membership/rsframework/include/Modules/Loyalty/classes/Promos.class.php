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
    
    function getPromoDetails($PromoID){
        $query = "SELECT Name as PromoName, PromoCode, DrawDate
                            FROM $this->TableName WHERE PromoID = $PromoID";
        $result = parent::RunQuery($query);
        return $result[0];
    }
    
    public function getPromos()
    {
        $query = "SELECT PromoID, Name FROM promos";
        return parent::RunQuery($query);
    }
    public function getPromoNameByID($promoid)
    {
        $query = "SELECT Name FROM promos WHERE PromoID = '$promoid'";
        return parent::RunQuery($query);
    }
    
    /**
     * Get Total number of the promo(s)
     * @author Mark Kenneth Esguerra
     * Date Created: July 11, 2013
     */
    function getNumberOfPromos()
    {
        $query = "SELECT COUNT(PromoID) as count
                  FROM $this->TableName";
        return parent::RunQuery($query);
    }
    
    /**
     * Load Promos
     * @author Mark Kenneth Esguerra
     * Date Created: July 11, 2013
     */
    function loadPromos()
    {
        $query = "SELECT PromoID, Name, Description, StartDate, EndDate, Status
                  FROM $this->TableName
                  ORDER BY PromoID DESC";
        return parent::RunQuery($query);
    }
    
    /**
     * Load Promo Details by PromoID
     * @author Mark Kenneth Esguerra
     * Date Created: July 11, 2013
     * @param int $ID ID of the Promo
     */
    function loadPromoByID($ID)
    {
        $query = "SELECT PromoID, Name, Description, StartDate, EndDate, DrawDate
                  FROM $this->TableName
                  WHERE PromoID = $ID";
        return parent::RunQuery($query);
    }
    
    /**
     * Get the status of the Promo
     * @author Mark Kenneth Esguerra
     * Date Created: July 11, 2013
     * @param int $ID ID of the Promo
     */
    function getPromoStatus($ID)
    {
        $query = "SELECT Status
                  FROM $this->TableName
                  WHERE PromoID = $ID";
        return parent::RunQuery($query);
    }
}
?>
