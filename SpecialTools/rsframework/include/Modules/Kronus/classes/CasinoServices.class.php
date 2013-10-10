<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 */

class CasinoServices extends BaseEntity
{
    public function CasinoServices()
    {
        $this->ConnString = "kronus";
        $this->TableName = "ref_services";
        $this->Identity = "ServiceID";
    }
    
    /**
     * Get all active casinos
     */
    public function getCasinoServices()
    {
        $query = "SELECT ServiceID, ServiceName, Code, UserMode
                    FROM ref_services WHERE ServiceID IN (8,9,10,11,12)
                        AND Status = 1";
        
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function getUserBasedCasinoServices()
    {
        $query = "SELECT *
                    FROM ref_services 
                    WHERE Status = 1 AND UserMode = 1";
        
        $result = parent::RunQuery($query);
        return $result;
    }
    
    public function generateCasinoAccounts( $MID, $serviceID, $isVIP = 0 )
    {
        App::LoadModuleClass("CasinoProvider", "CasinoProviders");
        App::LoadCore("Randomizer.class.php");
        $_Randomizer = new Randomizer();
        
        $isVIP == 0 ? $vipLevel = App::getParam("ptreg") : $vipLevel = App::getParam("ptvip");
        
        $services['ServiceID'] = $serviceID;
        $services['MID'] = $MID;
        $services['ServiceUsername'] = str_pad($MID, 12, '0', STR_PAD_LEFT); 
        $services['ServicePassword'] = strtoupper($_Randomizer->GenerateAlphaNumeric(8));  
        $services['HashedServicePassword'] = $services['ServicePassword'];
        $services['UserMode'] = 1;
        $services['DateCreated'] = 'now_usec()';
        $services['isVIP'] = $isVIP;
        $services['Status'] = 1;
        $services['VIPLevel'] = $vipLevel;
        
        $newservices[] = $services;
        
        return $newservices;
    }
}
?>
