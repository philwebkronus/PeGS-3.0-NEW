<?php
/**
 * Category Model
 * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * @date Sep-06-13
 * @copyright (c) 2013, Philweb Corp.
 */
class CategoryModel extends CFormModel
{
    /**
     * Select Active Categories
     * @date Sep-06-13
     */
    public function selectCategories()
    {
        $connection = Yii::app()->db;
        
        $sql = "SELECT CategoryID, Description FROM ref_category WHERE Status = 1 
                ORDER BY Description ASC";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
    /**
     * Get Category Description
     * @param int $categoryID CategoryID
     * @return string Description
     */
    public function getCategoryDescription($categoryID)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT Description FROM ref_category 
                  WHERE CategoryID = :categoryID";
        $command = $connection->createCommand($query);
        $command->bindParam(":categoryID", $categoryID);
        $result = $command->queryRow();
        
        return $result['Description'];
    }
}
?>
