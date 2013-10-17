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
}
?>
