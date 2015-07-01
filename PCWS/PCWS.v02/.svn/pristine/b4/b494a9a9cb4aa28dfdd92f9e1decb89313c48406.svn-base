<?php


class CommonController {
    
    CONST PARAM_MIN_TICKET_TO_PRINT_AMT = 14;
    
    /**
    * @var int site id
    */
    protected $site_id = null;
    
    /**
     * @var int account id
     */
    protected $acc_id = null;
    
    /**
     * @var int status 
     */
    protected $status = 0; //pending
    
    
    /**
     * Sends a json response
     * @param String $username
     * @param String $password
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public  function authenticate($transMsg, $errCode){
        return CJSON::encode(array('System Access Authentication'=>(array('TransactionMessage'=>$transMsg,'ErrorCode'=>(int)$errCode))));
    }
    
    
    /**
     * Sends a json response
     * @param String $username
     * @param String $password
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public  function deposit($transMsg, $errCode){
        return CJSON::encode(array('Deposit'=>(array('TransactionMessage'=>$transMsg,'ErrorCode'=>(int)$errCode))));
    }
    
    
    public  function withdraw($transMsg, $errCode){
        return CJSON::encode(array('Withdraw'=>(array('TransactionMessage'=>$transMsg,'ErrorCode'=>(int)$errCode))));
    }
    
    
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public function getbalance($balance, $bonus, $comp, $playthrough, $withdrawablebal,$transMsg, $errCode){
        return CJSON::encode(array('GetBalance'=>(array('PlayableBalance'=>$balance, 'BonusBalance'=>$bonus, 'CompBalance'=>$comp, 'PlayThroughBalance'=>$playthrough, 'WithdrawableBalance'=>$withdrawablebal,'TransactionMessage'=>$transMsg,'ErrorCode'=>(int)$errCode))));
    }
    
    
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public function updateterminalstate($transMsg, $errCode){
        return CJSON::encode(array('UpdateTerminalState'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public function getcomppoints($transMsg, $errCode, $compBalance){
        return CJSON::encode(array('GetCompPoints'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode, 'CompBalance'=>$compBalance))));
    }
    
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public function addcomppoints($transMsg, $errCode){
        return CJSON::encode(array('AddCompPoints'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    
    
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public function deductcomppoints($transMsg, $errCode){
        return CJSON::encode(array('DeductCompPoints'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    
    
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public function checkPin($transMsg, $errCode){
        return CJSON::encode(array('checkPin'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public function changePin($transMsg, $errCode){
        return CJSON::encode(array('changePin'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public function unlock($transMsg, $errCode){
        return CJSON::encode(array('Unlock'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public static function unlockgenesis($transMsg, $errCode){
        return CJSON::encode(array('Unlock'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public function forceLogout($transMsg, $errCode){
        return CJSON::encode(array('ForceLogout'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public static function removeSession($transMsg, $errCode){
        return CJSON::encode(array('RemoveSession'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
    
    /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public function resetPIN($transMsg, $errCode, $newPIN){
        return CJSON::encode(array('changePin'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode, 'NewPIN'=>$newPIN))));
    }
    
     /**
     * Sends a json response
     * @param String $transMsg
     * @param int $errCode
     * @return json response
     */
    public function eSafeConversion($transMsg, $errCode){
        return CJSON::encode(array('eSafeConversion'=>(array('TransactionMessage'=>$transMsg, 'ErrorCode'=>(int)$errCode))));
    }
}

?>
