<?php

/*
 * @author : owliber
 * @date : 2013-04-24
 */

class CardTypes extends BaseEntity
{
    const GOLD = 1;
    const GREEN = 2;
    const TEMPORARY = 3;
    
    public function CardTypes()
    {
        $this->ConnString = "loyalty";
        $this->TableName = "ref_cardtypes";
        $this->Identity = "CardTypeID";
    }
    
    public function getCardTypeByName ( $cardtypename )
    {
        $where = " WHERE CardTypeName = '$cardtypename'";
        $result = parent::SelectByWhere($where);
        return $result[0]['CardTypeID'];
    }
    
    /*
     * Description: Get the Card Type without condition
     * @author: Junjun S. Hernandez
     * DateCreated: July 12, 2013 12:26:35PM
     */
    public function getCardTypes()
    {
        $query = "SELECT CardTypeID, CardTypeName FROM ref_cardtypes WHERE CardTypeID != 3";
        return parent::RunQuery($query);
    }
    
    public function getCardTypeNameByID($cardtypeid)
    {
        $query = "SELECT CardTypeName FROM ref_cardtypes WHERE CardTypeID = '$cardtypeid'";
        return parent::RunQuery($query);
    }
}
?>
