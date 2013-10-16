<?php

class RefCategoryModel extends CFormModel
{
    
    public function getCategory()
    {
        $connection = Yii::app()->db;
        $sql="SELECT CategoryID, Description as CategoryName 
                    FROM rewardsdb.ref_category WHERE Status = 1
                    ORDER BY CategoryName ASC";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        return $result;
    }
    
}
?>
