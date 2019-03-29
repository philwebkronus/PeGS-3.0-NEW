<?php

class CommonController {

    //return message 
    public static function retMsg($module, $errorCode, $transMsg) {
        return CJSON::encode(array($module => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg)));
    }

    //return message function for ValidateLogin module
    public static function retMsgValidateLogin($module, $errorCode, $transMsg, $SiteID, $AID) {
        return CJSON::encode(array($module => array('Details' => array('SiteID' => $SiteID, 'AID' => $AID), 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg)));
    }

    //return message function for TransferWallet module
    public static function retMsgTransferWallet($transMsg, $errorCode) {
        return CJSON::encode(array('TransferWallet' => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg)));
    }

}

?>
