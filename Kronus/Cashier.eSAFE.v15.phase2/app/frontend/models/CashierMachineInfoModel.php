<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CashierMachineInfoModel
 *
 * @author bryan
 */
class CashierMachineInfoModel extends MI_Model{
    //check computer credential
    public function checkComputerCredential($machineid) {
        $sql = 'SELECT COUNT(POSAccountNo) as ctrsite, CashierMachineInfoId_PK FROM cashiermachineinfo WHERE Machine_Id = :machineid AND isActive = 1';
        $param = array(':machineid'=>$machineid);
        $this->exec($sql, $param);
        return $this->find();
    }

    public function addComputerCredential($zcpuid ,$zcpuname,$zbiosid,$zmbid,$zosid ,$zmacid,$zipid,$zguid,$zsiteid,$zmachineid ,$zdate) {
        $sql = 'INSERT INTO cashiermachineinfo(
                    ComputerName,
                    CPU_Id,
                    BIOS_SerialNumber,
                    MAC_Address,
                    Motherboard_SerialNumber,
                    OS_Id,
                    Machine_Id,
                    GUID,
                    IPAddress,
                    POSAccountNo,
                    IsActive,
                    RegisteredOn)  
                    VALUES (:cpuname,:cpuid,:biosid,:macid,:mbid,:osid,:machineid,:guid,:ipid,:siteid,1,:date)';
        $param = array(
            ':cpuname'=>$zcpuname,
            ':cpuid'=>$zcpuid,
            ':biosid'=>$zbiosid,
            ':macid'=>$zmacid,
            ':mbid'=>$zmbid,
            ':osid'=>$zosid,
            ':machineid'=>$zmachineid,
            ':guid'=>$zguid,
            ':ipid'=>$zipid,
            ':siteid'=>$zsiteid,
            ':date'=>$zdate
        );
        if(!$this->exec($sql, $param))
            return false;
        return $this->getLastInsertId();  
    }    
    
   /**
    * count if machine id is conflicting
    */
   public function checkmachineid($zmachineID) {
       $sql = 'SELECT COUNT(*) as ctrmachine, POSAccountNo FROM cashiermachineinfo WHERE Machine_Id = :machineid AND isActive = 1';
       $param = array(':machineid'=>$zmachineID);
       $this->exec($sql, $param);
       return $this->find();
   }    
   
   //count no. of site on cashiermachineinfo
   public function checksitecount($zsiteid) {
       $sql = 'SELECT COUNT(POSAccountNo) as ctrsite  FROM cashiermachineinfo WHERE POSAccountNo = :posaccountno AND isActive = 1';
       $param = array(':posaccountno'=>$zsiteid);
       $this->exec($sql, $param);
       return $this->find();
   }
}