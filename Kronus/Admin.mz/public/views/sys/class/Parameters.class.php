<?php
/**
 * Created By: JunJun S. Hernandez
 * Created On: March 18, 2014
 * Purpose: Parameters Maintenance
 */
include "DbHandler.class.php";
ini_set('display_errors',true);
ini_set('log_errors',true);

class Parameters extends DBHandler
{
       public function __construct($sconnectionstring)
    {
        parent::__construct($sconnectionstring);
    }

    /**
     * This function is used to retrieve all parameters
     * @param int $start row start
     * @param int $limit row limit
     * @return array
     */
    public function getAllParameters() {
        $stmt = 'SELECT ParamID, ParamName, ParamValue, ParamDesc FROM ref_parameters ORDER BY ParamName';
        $this->prepare($stmt);
        $this->execute();
        $result = $this->fetchAllData();

        return $result;
        
    }
    
    /**
     * This function is used to retrieve all parameters
     * @param int $start row start
     * @param int $limit row limit
     * @return array
     */
    public function getParamNameByParamName($paramName) {
        $stmt = 'SELECT ParamName FROM ref_parameters WHERE ParamName = :ParamName';
        $this->prepare($stmt);
        $this->bindparameter(':ParamName', $paramName);
        $this->execute();
        $result = $this->fetchData();

        return $result['ParamName'];
        
    }
    
     /**
     * This function is used to insert a parameter
     * @param string $paramName
     * @param string $paramValue
     * @param string $paramDescription
     * @return 1-true, 0-false
     */
    public function addNewParameter($paramName, $paramValue, $paramDescription) {
        $this->begintrans();
        $this->prepare("INSERT INTO ref_parameters(ParamName, ParamValue, ParamDesc) VALUES(:ParamName, :ParamValue, :ParamDesc)");
        $this->bindparameter(':ParamName', $paramName);
        $this->bindparameter(':ParamValue', $paramValue);
        $this->bindparameter(':ParamDesc', $paramDescription);
        if($this->execute())
        {
            try {
            $this->committrans();
            return 1;
            } catch (Exception $e) {
            $this->rollbacktrans();
            return 0;
        }
        }
        else
        {
            $this->rollbacktrans();
            return 0;
        }
        
    }
    
    /**
     * This function is used to update a parameter
     * @param string $paramID
     * @param string $paramValue
     * @param string $paramDescription
     * @return 1-true, 0-false
     */
    public function updateParameter($paramID, $paramValue, $paramDescription) {
        $this->begintrans();
        $this->prepare("UPDATE ref_parameters SET ParamValue = :ParamValue, ParamDesc = :ParamDesc WHERE ParamID = :ParamID");
        $this->bindparameter(':ParamID', $paramID);
        $this->bindparameter(':ParamValue', $paramValue);
        $this->bindparameter(':ParamDesc', $paramDescription);
        if($this->execute())
        {
            try {
            $this->committrans();
            $affectedRows = $this->rowCount();
            if($affectedRows > 0) {
                return 1;
            } else {
                return 2;
            }
            } catch (Exception $e) {
            $this->rollbacktrans();
            return 0;
        }
        }
        else
        {
            $this->rollbacktrans();
            return 0;
        }
        
    }
    
    /**
     * This function is used to retrieve all parameters
     * @param int $start row start
     * @param int $limit row limit
     * @return array
     */
    public function getParamDataByParamID($paramID) {
        
        $stmt = 'SELECT ParamName, ParamValue, ParamDesc FROM ref_parameters WHERE ParamID = :ParamID';
        $this->prepare($stmt);
        $this->bindparameter(':ParamID', $paramID);
        $this->execute();
        $result = $this->fetchData();

        return $result;
        
    }
}
?>
