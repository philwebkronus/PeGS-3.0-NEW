<?php

/**
 * Description of GetCasinoAndBalanceAction
 * @package application.modules.launchpad.components.actions
 * @author Bryan Salazar
 */
class GetCasinoAndBalanceAction extends CAction
{
    public function run()
    {
        if(!Yii::app()->request->isAjaxRequest)
            throw new CHttpException(404,"Invalid request");
        
        $terminalID = Yii::app()->user->getState('terminalID');
        
        if($this->isUserBased() == 0)
            $login = $this->getTerminalCode();
        if($this->isUserBased() == 1)
            $login = $this->getUBLogin();
        
        try {
            $row = LPTerminalSessions::model()->getCurrentCasinoByTerminalID($terminalID);
        }catch(Exception $e) {
            echo CJSON::encode(array('balance'=>'N/A','status'=>false,'casino'=>'N/A'));
            Yii::app()->end();
        }
        
        $serviceID = $row['ServiceID'];
        $casinoName = $row['Alias'];
        
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
        echo CJSON::encode(array('balance'=>$balance,'status'=>$status,'casino'=>$casinoName));
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
