<?php

class GeneratedPasswordBatch extends BaseEntity
{
    public function GeneratedPasswordBatch()
    {
        $this->ConnString = "membership";
        $this->TableName = "generatedpasswordbatch";
        $this->Identity = "GeneratedPasswordBatchID";
    }
    
    
    
    public function getInactivePasswordBatch()
    {
        $query = "SELECT gpb.GeneratedPasswordBatchID FROM generatedpasswordbatch gpb
                  WHERE gpb.Status = 0 LIMIT 1";
        $result = parent::RunQuery($query);
        return $result[0]['GeneratedPasswordBatchID'];
    }
    
    public function getInactivePasswordBatchDetails()
    {
        $query = "SELECT gpb.GeneratedPasswordBatchID, gpp.PlainPassword, gpp.EncryptedPassword FROM generatedpasswordbatch gpb 
            INNER JOIN generatedpasswordpool gpp ON gpp.GeneratedPasswordBatchID = gpb.GeneratedPasswordBatchID 
            WHERE gpb.Status = 0 LIMIT 1";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function getPasswordByCasino($batchID, $serviceGrpID){
        $query = "SELECT gpp.PlainPassword, gpp.EncryptedPassword FROM generatedpasswordpool gpp
                  WHERE gpp.GeneratedPasswordBatchID = $batchID AND ServiceGroupID = $serviceGrpID";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function getExistingPasswordBatch($MID)
    {
        $query = "SELECT gpb.GeneratedPasswordBatchID FROM generatedpasswordbatch gpb 
                    WHERE gpb.MID = $MID;";
        $result = parent::RunQuery($query);
        
        $genPasswordBatchId = '';
        if(isset($result[0]['GeneratedPasswordBatchID']))
            $genPasswordBatchId = $result[0]['GeneratedPasswordBatchID'];
        
        return $genPasswordBatchId;
    }
    
    
    public function updatePasswordBatch($MID, $genpassbacthID)
    {
        $query = "UPDATE generatedpasswordbatch SET DateUsed = NOW(6), MID = $MID, Status = 1 WHERE GeneratedPasswordBatchID = $genpassbacthID";
        return parent::ExecuteQuery($query);
    }
    
    
    public function checkGenPassBatch($MID)
    {
        $query = "SELECT COUNT(GeneratedPasswordBatchID) AS Count FROM generatedpasswordbatch WHERE MID = $MID";
        $result = parent::RunQuery($query);
        return $result[0]['Count'];
    }
    
    public function getGenPassBatchPassword($MID, $servicegroupID)
    {
        $query = "SELECT gpb.GeneratedPasswordBatchID, gpb.PlainPassword, gpp.EncryptedPassword FROM generatedpasswordbatch gpb 
            INNER JOIN generatedpasswordpool gpp ON gpb.GeneratedPasswordBatchID = gpp.GeneratedPasswordBatchID WHERE gpb.MID = $MID AND gpp.ServiceGroupID = $servicegroupID";
        $result = parent::RunQuery($query);
        return $result;
    }
    
    /**
     * @author: Ralph Sison
     * @dateadded: Oct. 19, 2015
     */
    public function getInactivePasswordBatchInfo()
    {
        $query = "SELECT gpb.GeneratedPasswordBatchID, gpp.PlainPassword, gpp.EncryptedPassword FROM generatedpasswordbatch gpb 
            INNER JOIN generatedpasswordpool gpp ON gpp.GeneratedPasswordBatchID = gpb.GeneratedPasswordBatchID 
            WHERE gpb.Status = 0 AND gpb.PlainPassword = gpp.PlainPassword LIMIT 1";
        $result = parent::RunQuery($query);
        return $result;
    }
}
?>
