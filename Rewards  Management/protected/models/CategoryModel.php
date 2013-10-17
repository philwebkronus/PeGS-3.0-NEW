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
        
        $sql = "SELECT CategoryID, Description FROM ref_category WHERE Status = 1";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
}
?>
