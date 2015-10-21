<?php
/**
 * Invoker for KAPI Webservice
 * @author elperez
 */
class WsInvokerController extends CController{
    
     /**
     * Set default action
     * @var string 
     */
    public $defaultAction = 'overview';
    
    /**
     * Set default layout
     * @var string 
     */
    public $layout = 'main';
    
    public $pageTitle;
    
    public function actionOverview(){
        $this->pageTitle = 'KAPI - Overview';
        $this->render('overview');
    }
    
    public function actionAuthenticateClient(){
        $this->pageTitle = 'KAPI - Authenticate Client';
        $result = '';
        if(isset($_POST['machineid']) && isset($_POST['terminalcode'])){
            $machineId = $_POST['machineid'];
            $terminalCode = $_POST['terminalcode'];
            
            $result = $this->_authenticateClient($machineId, $terminalCode);
        }
        
        $this->render('authenticateclient', array('result'=>$result));
    }
    
    public function actionCheckActiveSession(){
        $this->pageTitle = 'KAPI - CheckActiveSession';
        $result = '';
        if(isset($_POST['token']) && isset($_POST['tcode']) && isset($_POST['isvip'])){
            $token = $_POST['token'];    
            $tcode = $_POST['tcode'];
            $isVip = $_POST['isvip'];
            $result = $this->_checkactivesession($token, $tcode, $isVip);
        }
        
        $this->render('checkactivesession', array('result'=>$result));
    }
    
    public function actionCheckTransaction(){
        $this->pageTitle = 'KAPI - CheckTransaction';
        
        $result = '';
        
        if(isset($_POST['token']) && isset($_POST['trackingid']))
        {
            $token = $_POST['token'];
            $trackingID = $_POST['trackingid'];
            
            $result = $this->_checktransaction($token, $trackingID);
        }
        $this->render('checktransaction', array('result'=>$result));
    }
    
    public function actionDeposit() {
        $this->pageTitle = 'KAPI - Deposit';
        
        $result = '';
        
        if(isset($_POST['token']) && isset($_POST['tcode']))
        {
            $token = $_POST['token'];
            $isVip = $_POST['isvip'];
            $terminalCode = $_POST['tcode'];
            $transMethod = $_POST['transmethod'];
            $serviceID = $_POST['serviceid'];
            $amount = $_POST['amount'];
            $loyalty = $_POST['barcode'];
            $transDetail = implode(";", array($serviceID, $amount, $loyalty));
            $trackingID = $_POST['trackingid'];
            
            $result = $this->_deposit($token, $isVip, $terminalCode, $transMethod, $transDetail, $trackingID);
        }
        $this->render('deposit', array('result'=>$result));
    }
    
    public function actionMinMaxInfo() {
        $this->pageTitle = 'KAPI - Min/Max Info';
        $result = '';
        if(isset($_POST['token']))
        {
            $token = $_POST['token'];
            $result = $this->_minmaxinfo($token);
        }
        
        $this->render('minmaxinfo', array('result'=>$result));
    }

    public function actionReload() {
        $this->pageTitle = 'KAPI - Reload';
        
        $result = '';
        
        if(isset($_POST['token']) && isset($_POST['tcode']))
        {
            $token = $_POST['token'];
            $terminalCode = $_POST['tcode'];
            $transMethod = $_POST['transmethod'];
            $amount = $_POST['amount'];
            $transDetail = $amount;
            $trackingID = $_POST['trackingid'];
            
            $result = $this->_reload($token, $terminalCode, $transMethod, $transDetail, $trackingID);
        }
        $this->render('reload', array('result'=>$result));
    }
    
    public function actionWithdraw(){
        $this->pageTitle = 'KAPI - Withdraw';
        
        $result = '';
        
        if(isset($_POST['token']) && isset($_POST['tcode']))
        {
            $token = $_POST['token'];
            $terminalCode = $_POST['tcode'];
            $trackingID = $_POST['trackingid'];
            
            $result = $this->_withdraw($token, $terminalCode, $transMethod = '', $transDetail = '', $trackingID);
        }
        $this->render('withdraw', array('result'=>$result));
    }
    
    public  function actionValidateToken() {
        $this->pageTitle = 'KAPI - Validate Token';
        $result = '';
        if(isset($_POST['token']))
        {
            $token = $_POST['token'];
            $result = $this->_validatetoken($token);
        }
        
        $this->render('validatetoken', array('result'=>$result));
    }
    
    private function _authenticateClient($machineId, $terminalCode){
        $url = Yii::app()->params['authenticate_client'];
        $result = Yii::app()->CURL->run($url.'?machineId='.$machineId.'&terminalCode='.$terminalCode.'');
        return $result;
    }
    
    private function _checkactivesession($token, $terminalCode, $isVip){
        $url = Yii::app()->params['check_active_session'];
        $result = Yii::app()->CURL->run($url.'?token='.$token.'&terminalCode='.$terminalCode.'&isVip='.$isVip);
        return $result;
    }
    
    private function _checktransaction($token, $trackingID){
        $url = Yii::app()->params['check_transaction'];
        $result = Yii::app()->CURL->run($url.'?token='.$token.'&trackingId='.$trackingID);
        return $result;
    }
    
    private function _deposit($token, $isVip, $terminalCode, $transMethod, $transDetail, $trackingID){
        $url = Yii::app()->params['deposit'];
        $result = Yii::app()->CURL->run($url.'?token='.$token.'&isVip='.$isVip.
                                             '&terminalCode='.$terminalCode.
                                             '&transMethod='.$transMethod.'&transDetail='.$transDetail.
                                             '&trackingId='.$trackingID);
        return $result;
    }
    
    private function _minmaxinfo($token) {
        $url = Yii::app()->params['minmaxinfo_kapi'];
        $result = Yii::app()->CURL->run($url.'?token='.$token);
        return $result;
    }
    
    private function _reload($token, $terminalCode, $transMethod, $transDetail, $trackingID){
        $url = Yii::app()->params['reload'];
        $result = Yii::app()->CURL->run($url.'?token='.$token.'&terminalCode='.$terminalCode.
                                             '&transMethod='.$transMethod.'&transDetail='.$transDetail.
                                             '&trackingId='.$trackingID);
        return $result;
    }
    
    private function _validatetoken($token){
        $url = Yii::app()->params['validate_token'];
        $result = Yii::app()->CURL->run($url.'?token='.$token);
        return $result;
    }
    
    private function _withdraw($token, $terminalCode, $transMethod, $transDetail, $trackingID) {
        $url = Yii::app()->params['withdraw'];
        $result = Yii::app()->CURL->run($url.'?token='.$token.'&terminalCode='.$terminalCode.
                                             '&transMethod='.$transMethod.'&transDetail='.$transDetail.
                                             '&trackingId='.$trackingID);
        return $result;
    }
}
?>
