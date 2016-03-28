<?php

/**
 * @datecreated 05/07/12
 * For EGM Webservice
 * @author JunJun S. Hernandez
 */

class RefParametersModel{
    
    public static $_instance = null;
    public $_connection;


    public function __construct() {
        $this->_connection = Yii::app()->db;
    }
    
    public static function model()
    {
        if(self::$_instance == null)
            self::$_instance = new CommonTransactionsModel();
        return self::$_instance;
    }
    
    public function getParamValueById($param_id) {
        $sql = "SELECT ParamValue FROM ref_parameters 
                WHERE ParamID = :param_id";
        $params = array(":param_id"=>$param_id);
        $command = $this->_connection->createCommand($sql);
        $result = $command->queryRow(true, $params);
        return $result['ParamValue'];
    }
}

?>
