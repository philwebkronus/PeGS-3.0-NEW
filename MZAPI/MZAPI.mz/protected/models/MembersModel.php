<?php

class MembersModel extends CFormModel {

    public $connection;

    public function __construct() {
        $this->connection = Yii::app()->db2;
    }

    public function updateMembers($ServiceID, $MID) {
        $startTrans = $this->connection->beginTransaction();

        try {
            $sql = "UPDATE members SET OptionID1 = :ServiceID WHERE MID = :MID";

            $command = $this->connection->createCommand($sql);
            $command->bindValue(":ServiceID", $ServiceID);
            $command->bindValue(":MID", $MID);

            $command->execute();
            try {
                $startTrans->commit();
                return true;
            } catch (PDOException $e) {
                $startTrans->rollback();
                Utilities::log($e->getMessage());
                return false;
            }
        } catch (Exception $e) {
            $startTrans->rollback();
            Utilities::log($e->getMessage());
            return 110010;
        }
    }

}

?>

