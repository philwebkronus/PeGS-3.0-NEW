<?php
/**
 * Player Classification Model
 * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * @date Sep-06-13
 * @copyright (c) 2013, Philweb Corp.
 */
class PlayerClassificationModel extends CFormModel
{
    /**
     * Select Players
     * @return array
     */
    public function selectPlayers()
    {
        $connection = Yii::app()->db;
        
        $sql = "SELECT PClassID, Description FROM ref_playerclassification";
        $command = $connection->createCommand($sql);
        $result = $command->queryAll();
        
        return $result;
    }
    /**
     * Get the Player Class Description (Name)
     * @param int $PClassID PClassID
     * @return string Description Name
     * @author Mark Kenneth Esguerra
     * @date December 12, 2013
     */
    public function getPlayerDescription($PClassID)
    {
        $connection = Yii::app()->db;
        
        $query = "SELECT Description FROM ref_playerclassification 
                  WHERE PClassID = :pclassID";
        $command = $connection->createCommand($query);
        $command->bindParam(":pclassID", $PClassID);
        $result = $command->queryRow();
        
        return $result['Description'];
        
    }
}
?>
