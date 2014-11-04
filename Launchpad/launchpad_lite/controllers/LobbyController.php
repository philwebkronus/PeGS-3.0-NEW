<?php

/**
 * Default controller
 * @package application.modules.launchpad.controllers
 * @author Bryan Salazar
 */
foreach (glob("../models/*.php") as $filename){
    include_once "$filename";
}
include_once '../components/CasinoApi.php';

class LobbyController
{

    
    public function saveTerminalcode() 
    {
        if(isset($_GET['terminalCode'])) {
            echo "The terminal with code {$_GET['terminalCode']} has been saved";
        } else {
            echo "The terminal with code {$_GET['terminalCode']} is not saved";
        }  
    }
    
    /**
     *
     * @param array $casinos
     * @return array 
     */
    protected function getCurrCasinoCasAndType($casinos)
    {
        
        $casinoPosition = LPConfig::app()->params['casino_position'];
        $checkBy = $casinoPosition['check_by'];
        $positions = $casinoPosition['position'];
        $cas = array();
        $type = 'N/A';
        $currentCasino = 'N/A';
        foreach($positions as $position) { // foreach casino position
            foreach($casinos as $casino) { // foreach available casino
                if($casino['ServiceID'] == $this->getCurrentServiceID()) {
                    $currentCasino = $casino['Alias'];
                    $type=$casino['type'];
                    $servicegroupname=$casino['ServiceGroupName'];
                }
                if($casino[$checkBy] == $position) {
                    if($casino["ServiceGroupName"] == "RTG2"){
                        $casino["type"] = "rtg2";
                        $cas[$position] = $casino;
                    } else {
                        $cas[$position] = $casino;
                    }
                    
                }
            }
            if(!isset($cas[$position])) {
                $cas[$position] = 'N/A';
            }
        }
        
        return array('currentCasino'=>$currentCasino,'cas'=>$cas,'type'=>$type, 'servicegroupname' => $servicegroupname);
    }
    
    /**
     * This will call upon clicking of casino
     */
//    public function actionCasinoClick()
//    {
//        $currentServiceID = $this->getCurrentServiceID();
//        
//        if(!$currentServiceID) {
//            //echo CJSON::encode(array('istransfer'=>false,'html'=>'not ok')); //Deprecated message on 08/30/2012
//            echo CJSON::encode(array('istransfer'=>false,'html'=> "Current Service ID {$currentServiceID} cannot be transferred"));
//            Yii::app()->end(); 
//        }
//        
//        $transferCasino = $_GET['serviceid'];
//        if($currentServiceID != $_GET['serviceid']) {
//            $html = $this->_getDisplaytransfer($currentServiceID, $transferCasino);
//            $response = array('istransfer'=>true,'html'=>$html);
//        } else {
//            $html = $this->_getDisplaySame($currentServiceID);
//            $response = array('istransfer'=>false,'html'=>$html);
//        }
//        echo CJSON::encode($response);
//    }
    
    public function casinoServiceClick()
    {
        $serviceID = $this->getCurrentServiceID();
        
        if($serviceID != false){
            list($currentBalance, $currentBet, $wcasinoApiHandler) = $this->getBalance($serviceID, $this->getCasinoServiceType($serviceID));  
            $response = array('currentbal'=>$currentBalance, 'status' => 'ok');
        }else{
            $response = array('currentbal'=>0, 'status' => 'not ok');
        }
        
        return $response;
    }
    
//    public function actions()
//    {
//        return array(
//            'checkLogin'=>'application.modules.launchpad.components.actions.CheckLoginAction',
//            'getBalance'=>'application.modules.launchpad.components.actions.GetBalanceAction',
//            'getcasinoandabalance'=>'application.modules.launchpad.components.actions.GetCasinoAndBalanceAction',
//        );
//    }
    
    protected function getCurrentServiceID($terminalCode)
    {
        try {
            $row=LPTerminalSessions::model()->getCurrentCasino($terminalCode);
        }catch(Exception $e) {
            if(!isset($row['ServiceID']))
                return false;
        }
        
       return $row['ServiceID'];
    }
    
    public function getServiceID($terminalCode){
        return $this->getCurrentServiceID($terminalCode);
    }

    
    /**
     * Get casino balance
     * @param int $serviceID
     * @param string $serviceType
     * @return array 
     */
    protected function getBalance($terminalCode,$serviceID,$serviceType)
    {
        $casinoApi = new CasinoApi();
        $isUserBased = LPRefServices::model()->getUserMode($serviceID);
        
        if($isUserBased == 0){
            $login = $terminalCode;
        }
        
        if($isUserBased == 1){ 
            $MID = LPTerminalSessions::model()->getMID($terminalCode);
            $logincredentials = LPMemberServices::model()->GetUBCredentials($serviceID, '', $MID);
            $login = $logincredentials["ServiceUsername"];
        }
        
        $getBalanceResult = $casinoApi->getBalance($login, $serviceID, $serviceType);
        
        if(!is_array($getBalanceResult)) {
            return $balance = 'N/A';
        }
        
        $currentBet = $getBalanceResult['CurrentBet'];
        $balance = number_format($getBalanceResult['TerminalBalance'], 2);
        
        return array($balance, $currentBet, $getBalanceResult['CasinoAPIHandler']);
    }
    
    
    public function getUserBaseLogin($terminalCode){
        $data =  LPTerminalSessions::model()->isLogin($terminalCode);
        return $data[0];
    }
    
    public function getTerminalBaseLogin($terminalCode,$serviceID){
        $data = LPTerminalServices::model()->getTBCredentials($terminalCode, $serviceID);
        $data[0]['TerminalCode'] = $terminalCode;
        return $data[0];
    }
    
    protected function getCasinoServiceType($serviceID){
        $data = LPRefServices::model()->getServiceGroupName($serviceID);
        return $data;
    }
    
    public function getAllCasinos($terminalCode){
        $terminalid = LPTerminals::model()->getTerminalID($terminalCode);
        $casinos = LPTerminalServices::model()->getAllAvailableCasino($terminalid["TerminalID"]);

        $info = $this->getCurrCasinoCasAndType($casinos);
        $cas = $info['cas'];
        $currentCasino = $info['currentCasino'];
        if($info["servicegroupname"] == "RTG2"){
            $type = "rtg2";
        } else {
            $type = $info['type'];
        }

        $currentServiceID = $this->getCurrentServiceID();

        if(!$currentServiceID) {
            $this->logerror("File: launchpad.controller.lobbycontroller, Message: Can't get current casino");
        } else {
            list($currentBalance, $currentBet, $casinoApiHandler) = $this->getBalance($terminalCode,$currentServiceID, $type);
        }   
        
        return array("cas" => $cas, "currentCasino" => $currentCasino, 
                                "type" => $type, "currentBalance" => $currentBalance,
                                "currentServiceID" => $currentServiceID);
    }
    
}
