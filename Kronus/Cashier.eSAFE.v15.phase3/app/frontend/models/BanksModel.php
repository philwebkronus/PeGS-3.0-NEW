<?php


class BanksModel extends MI_Model{
        
    public function generateBanks(){
        Mirage::loadModels('RefBanksModel');
        $refBanksModel = new RefBanksModel();
        $refBanks = $refBanksModel->getBanks();
        $banks = array(''=>'Select Bank');
        foreach($refBanks as $key=>$values){
            $bankID = ''.$values['BankID'].'';
            $bankName = $values['BankName'];
            $banks += array($bankID=>$bankName);
        }
        return $banks;
}
}

