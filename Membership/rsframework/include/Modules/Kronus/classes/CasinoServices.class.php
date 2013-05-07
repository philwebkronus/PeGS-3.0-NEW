<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 */

class CasinoServices extends BaseEntity
{
    public function CasinoServices()
    {
        $this->ConnString = 'kronus';
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
    
    public function generateCasinoAccounts( $MID )
    {
        App::LoadModuleClass("CasinoProvider", "CasinoProviders");
        App::LoadCore("Randomizer.class.php");
        $_Randomizer = new Randomizer();
                
        //$casinoServices = $this->getCasinoServices();                
        $casinoServices = $this->getUserBasedCasinoServices();

        foreach($casinoServices as $val)
        {
            $services['ServiceID'] = $val['ServiceID'];
            $services['MID'] = $MID;
            $services['ServiceUsername'] = str_pad($MID, 12, '0', STR_PAD_LEFT); 
            $services['ServicePassword'] = strtoupper($_Randomizer->GenerateAlphaNumeric(8));  
            $services['HashedServicePassword'] = $services['ServicePassword'];
            $services['UserMode'] = $val['UserMode'];
            $services['DateCreated'] = 'now_usec()';
            $services['isVIP'] = 0;
            $services['Status'] = 1;

            $newservices[] = $services;

        }
        
        return $newservices;
    }
}
?>
