<?php

/**
 * Get the return output of the MPapi
 * @return array
 * @date 6-13-2014
 * @author fdlsison
 */

class CommonController {

    public static function retMsg($module, $couponNumber = '', $expiryDate = '', $errorCode = '', $transMsg = '', $username = '', $password = '', $TPSessionID = '') {
//                                  $username = '', $sessionID = '', $cardTypeID = '', $isVip = '',
//                                  $currentPoints = '', $bonusPoints = '',
//                                  $redeemedPoints = '', $lifetimePoints = '', $message = '',
//                                  $rewardItemID = '', $productName = '', $description = '',
//                                  $thumbnailLimitedImage = '', $points = '', $partnerName = '', $isMystery = '', $firstname = '',
//                                  $middlename = '', $lastname = '', $nickname = '', $permanentAddress = '',
//                                  $mobileNumber = '', $alternateMobileNumber = '', $emailAddress = '',
//                                  $alternateEmail = '', $idNumber = '',
//                                  $nationality = '', $isSmoker = '', $birthdate = '', $age = '', $lastInsertedID = '', $oldCurrentPoints = '' ) {
//        if($module == 'Login') {
//            return array('Login' => array('MPSessionID' => $mpSessionID, 'CardTypeID' => $cardTypeID, 'IsVIP' => $isVIP, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
//        }
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
        else if($module=='RegisterMemberBTNoEmail') {
            return array('RegisterMemberBTNoEmail' => array(
                'CouponNumber' => $couponNumber,
                'ExpiryDate' => $expiryDate,
                'ErrorCode' => $errorCode,
                'ReturnMessage' => $transMsg,
            ));
        }
    }

//    public static function retMsgRegisterMemberBT($module, $couponNumber, $expiryDate, $errorCode, $transMsg) {
//        $module = 'RegisterMemberBT';
//        return array($module => array('CouponNumber' => $couponNumber, 'ExpiryDate' => $expiryDate, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
//    }

//    public static function retMsgBTRegisterMember($module, $errorCode, $transMsg) {
//        $module = 'BTRegisterMember';
//        return array($module => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
//    }

}
?>
