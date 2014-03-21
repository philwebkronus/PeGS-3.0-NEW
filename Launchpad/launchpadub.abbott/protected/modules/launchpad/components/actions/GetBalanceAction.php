<?php

/**
 * This class is action to get balance
 * @package application.modules.launchpad.components.actions
 * @author Bryan Salazar
 */
class GetBalanceAction extends CAction
{
    public function run()
    {
        if(!Yii::app()->request->isAjaxRequest || !isset($_GET['serviceID']))
            throw new CHttpException(404,"Invalid request");
        
        if($this->isUserBased() == 0)
            $login = $this->getTerminalCode();
        if($this->isUserBased() == 1)
            $login = $this->getUBLogin();
        
        $serviceID = $_GET['serviceID'];
        
        // attach behavior fron CasinoConfigGenerator
        $this->attachBehavior('casinoConfigGenerator', new CasinoConfigGenBehavior());
        
        $serviceInfo = LPRefServices::model()->getServiceInfoWithType($serviceID);
        
        $casinoApi = new CasinoApi();
        $getBalanceResult = $casinoApi->getBalance($login, $serviceID, $serviceInfo['type']);
        
        if(is_array($getBalanceResult))
            $status = true;
        else
            $status = false;
        
        header('Content-type: application/json');
        if(isset($_GET['format']))
            $balance = number_format ($getBalanceResult['TerminalBalance'],2);
        echo CJSON::encode(array('balance'=>$balance,'status'=>$status));
        
    }
    
    protected function getUBLogin(){
        return Yii::app()->user->getState('UBUsername');
    }
    
    protected function isUserBased(){
        return Yii::app()->user->getState('casinoMode');
    }
    
    protected function getTerminalCode()
    {
        return Yii::app()->user->getState('terminalCode');
    }
}