<?php

/**
 * Get the return output of the MPapi
 * @return array
 * @date 6-13-2014
 * @author fdlsison
 */

class CommonController {

    public static function retMsg($module, $couponNumber = '', $expiryDate = '', $errorCode = '', $transMsg = '', $username = '', $password = '', $TPSessionID = '') {
        if($module=='AuthenticateSession'){
            return array('AuthenticateSession' => array(
                'TPSessionID'=>$TPSessionID,
                'ErrorCode' => $errorCode,
                'ReturnMessage' => $transMsg,
                    ));
        }

        else if($module=='GetActiveSession'){
            return array('GetActiveSession' => array(
                'ErrorCode' => $errorCode,
                'ReturnMessage' => $transMsg,
            ));
        }
        else if($module=='RegisterMemberBT') {
            return array('RegisterMemberBT' => array(
                'CouponNumber' => $couponNumber,
                'ExpiryDate' => $expiryDate,
                'ErrorCode' => $errorCode,
                'ReturnMessage' => $transMsg,
            ));
        }
    }
}
?>
