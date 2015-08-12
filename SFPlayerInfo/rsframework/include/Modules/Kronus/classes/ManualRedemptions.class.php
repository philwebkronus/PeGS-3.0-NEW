<?php

class ManualRedemptions extends BaseEntity
{

    public function ManualRedemptions()
    {
        $this->ConnString = "kronus";
        $this->TableName = "manualredemptions";
    }

    public function getManualRedemptions($MID, $siteID)
    {
        $query = "SELECT SUM(ActualAmount) AS ActualAmount,SiteID FROM $this->TableName
                  WHERE MID = '$MID' AND Status = 1 AND SiteID = $siteID";

        return parent::RunQuery($query);
    }

}

?>