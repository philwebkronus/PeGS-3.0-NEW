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
    }
}
?>
