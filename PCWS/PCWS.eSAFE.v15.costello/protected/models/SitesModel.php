<?php

class SitesModel extends CFormModel
{
    public $connection;
    
    public function __construct() 
    {
        $this->connection = Yii::app()->db;
    }
    
    public function getSitesClassification($siteid)
    {
        $sql = "SELECT SiteClassificationID as SitesClass FROM sites WHERE SiteID = :siteid";
        $command = $this->connection->createCommand($sql);
        $command->bindValue(":siteid", $siteid);
        $result = $command->queryRow();
        
        return $result;
    }
  
}
?>
