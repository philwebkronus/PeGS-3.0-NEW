<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php
class OptionsForm extends CFormModel
{
    /**
     * Get all parameters stored in the database
     * @return string
     */
    public function getAllParams()
    {
        $query = "SELECT * FROM ref_parameters";
        $sql = Yii::app()->db->createCommand($query);
        return $sql->queryAll();
    }
    
    /**
     * 
     * @param ing $paramID
     * @return string
     */
    public function getParamByID($paramID)
    {
        $query = "SELECT * FROM ref_parameters WHERE ParamID =:paramid";
        $sql = Yii::app()->db->createCommand($query);
        $sql->bindValue(":paramid", $paramID);
        return $sql->queryRow();
    }
    
    /**
     * 
     * @param string $params
     * @return int
     */
    public function updateParameters($params)
    {
        $conn = Yii::app()->db;
        
        $trx = $conn->beginTransaction();
        
        $query = "UPDATE ref_parameters
                  SET ParamName =:name,
                      ParamValue =:value,
                      ParamDesc =:desc
                  WHERE ParamID =:paramid";
        $sql = $conn->createCommand($query);
        $sql->bindValues(array( ":paramid"=>$params['ParamID'],
                                ":name"=>$params['ParamName'],
                                ":value"=>$params['ParamValue'],
                                ":desc"=>$params['ParamDesc']));
        
        try 
        {
            $sql->execute();
            $trx->commit();
            return 1;
        } 
        catch (Exception $exc) {
            $trx->rollback();
            return 0;
        }
    }
    
}
?>