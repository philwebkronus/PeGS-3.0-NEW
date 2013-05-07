<?php

/*
 * @author : owliber
 * @date : 2013-04-30
 */

class Sites extends BaseEntity
{
    public function Sites()
    {
        $this->ConnString = "kronus";
        $this->TableName = "sites";
    }
    
    
    public function getSite( $siteid )
    {
        $where = " WHERE SiteID = $siteid";
        return parent::SelectByWhere($where);
    }
    
    public function getSiteByCode($sitecode)
    {
        $where = " where SiteCode = '$sitecode'";
        return parent::SelectByWhere($where);
    }
}
?>
