<?php
/**
 * Ref_AuditFunction Model
 */
class RefAuditFunctionsModel extends CFormModel
{
    public $connection;
    
    CONST REMOVE_EGM_SESSION = 78;
    
    public function __construct()
    {
        $this->connection = Yii::app()->db;
    }
}
?>
