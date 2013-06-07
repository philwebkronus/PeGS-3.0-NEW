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
}
?>
