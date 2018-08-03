<?php

/*
 * @author : owliber
 * @date : 2013-04-22
 */

class CasinoServices extends BaseEntity {

    public function CasinoServices() {
        $this->ConnString = "kronus";
        $this->TableName = "ref_services";
        $this->Identity = "ServiceID";
    }

    /**
     * Get all active casinos
     */
    public function getCasinoServices() {
        $query = "SELECT ServiceID, ServiceName, Code, UserMode
                    FROM ref_services WHERE ServiceID IN (8,9,10,11,12)
                        AND Status = 1";

        $result = parent::RunQuery($query);
        return $result;
    }

    public function getUserBasedCasinoServices() {
        $query = "SELECT rs.ServiceID, rs.ServiceGroupID, rsg.ServiceGroupName, rs.ServiceName
                    FROM ref_services rs
                    INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                    WHERE rs.Status = 1 AND rs.UserMode IN (1,3)";

        $result = parent::RunQuery($query);
        return $result;
    }

    /*
     * Added : John Aaron Vida
     * Date : October 9, 2015
     * Modify :July 11, 2018
     */

    public function getUserBasedCasinoDetails($Usermode) {
        $query = "SELECT rs.ServiceID, rs.ServiceGroupID, rsg.ServiceGroupName, rs.ServiceName
                    FROM ref_services rs
                    INNER JOIN ref_servicegroups rsg ON rs.ServiceGroupID = rsg.ServiceGroupID
                    WHERE rs.Usermode IN ($Usermode) AND rs.Status = 1";

        $result = parent::RunQuery($query);
        return $result;
    }

    public function getCasinoServiceName($serviceid) {
        $query = "SELECT rs.ServiceName, rsg.ServiceGroupName FROM ref_services rs 
            INNER JOIN ref_servicegroups rsg ON rsg.ServiceGroupID = rs.ServiceGroupID 
            WHERE rs.ServiceID = $serviceid";

        $result = parent::RunQuery($query);
        return $result;
    }

    public function generateCasinoAccounts($MID, $serviceID, $serviceName, $isVIP = 0) {
        App::LoadModuleClass("CasinoProvider", "CasinoProviders");
        App::LoadCore("Randomizer.class.php");
        $_Randomizer = new Randomizer();

        if (strpos($serviceName, 'RTG2') !== false) {
            $isVIP == 0 ? $vipLevel = App::getParam("rtgreg") : $vipLevel = App::getParam("rtgvip");
        }
        if (strpos($serviceName, 'HAB') !== false) {
            $isVIP == 0 ? $vipLevel = App::getParam("habreg") : $vipLevel = App::getParam("habvip");
        }

        $randomnum = mt_rand(1000, 9999);
        $serviceusername = str_pad($MID, 8, '0', STR_PAD_LEFT);

        $services['ServiceID'] = $serviceID;
        $services['MID'] = $MID;
        if (strstr($serviceName, "RTG2")) {
            $fullusername = $randomnum . $serviceusername;
            $services['ServiceUsername'] = $fullusername;
        }
        if (strstr($serviceName, "HAB")) {
            $fullusername = $randomnum . $serviceusername;
            $services['ServiceUsername'] = $fullusername;
        }

        $services['ServicePassword'] = strtoupper($_Randomizer->GenerateAlphaNumeric(8));
        $services['HashedServicePassword'] = $services['ServicePassword'];

        if ($serviceID == 28 || $serviceID == 29) {
            $services['UserMode'] = 3;
        } else {
            $services['UserMode'] = 1;
        }

        $services['DateCreated'] = 'NOW(6)';
        $services['isVIP'] = $isVIP;
        $services['Status'] = 1;
        $services['VIPLevel'] = $vipLevel;

        $newservices[] = $services;

        return $newservices;
    }

    public function getServiceGroupID($serviceid) {
        $query = "SELECT ServiceGroupID FROM ref_services WHERE ServiceID = $serviceid";

        $result = parent::RunQuery($query);
        return $result[0]['ServiceGroupID'];
    }

}

?>
