<?php

/**
 * Get the return output of the MPapi
 * @return array
 * @date 6-13-2014
 * @author fdlsison
 */
class CommonController {




    public static function retMsg($module, $errorCode = '', $transMsg = '') {
//                                  $username = '', $sessionID = '', $cardTypeID = '', $isVip = '',
//                                  $currentPoints = '', $bonusPoints = '',
//                                  $redeemedPoints = '', $lifetimePoints = '', $message = '',
//                                  $rewardItemID = '', $productName = '', $description = '',
//                                  $thumbnailLimitedImage = '', $points = '', $partnerName = '', $isMystery = '', $firstname = '',
//                                  $middlename = '', $lastname = '', $nickname = '', $permanentAddress = '',
//                                  $mobileNumber = '', $alternateMobileNumber = '', $emailAddress = '',
//                                  $alternateEmail = '', $idNumber = '',
//                                  $nationality = '', $isSmoker = '', $birthdate = '', $age = '', $lastInsertedID = '', $oldCurrentPoints = '', $encryptedTracking2 = '' ) {
//        if($module == 'Login') {
//            return array('Login' => array('MPSessionID' => $mpSessionID, 'CardTypeID' => $cardTypeID, 'IsVIP' => $isVIP, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
//        }
        if ($module == 'ForgotPassword') {
            return array('ForgotPassword' => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
        }
//        else if($module == 'RegisterMember') {
//            return array('RegisterMember' => array('ErrorCode' => $errorCode,
//                         'ReturnMessage' => $transMsg));
//        }
//        else if($module == 'UpdateProfile') {
//            return array('UpdateProfile' => array('ErrorCode' => $errorCode,
//                         'ReturnMessage' => $transMsg));
//        }
//        else if($module == 'RedeemItems') {
//            return array('RedeemItems' => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
//        }
        else if ($module == 'Logout') {
            return array('Logout' => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
        }
//        else if($module == 'GetProfile') {
//            return array('GetProfile' => array('ErrorCode' => $errorCode,
//                                               'FirstName' => $firstname, 'MiddleName' => $middlename,
//                                               'LastName' => $lastname, 'NickName' => $nickname,
//                                               'Address1' => $permanentAddress, 'MobileNo' => $mobileNumber,
//                                               'AlternateMobileNo' => $alternateMobileNumber, 'EmailAddress' => $emailAddress,
//                                               'AlternateEmail' => $alternateEmail, 'Gender' => $gender,
//                                               'IDPresented' => $idPresented, 'IDNumber' => $idNumber,
//                                               'Nationality' => $nationality, 'Occupation' => $occupation,
//                                               'IsSmoker' => $isSmoker, 'Birthdate' => $birthdate, 'Age' => $age, 'ReturnMessage' => $transMsg));
//        }
    }

    //return message function for Login module
    public static function retMsgLogin($module, $mpSessionID, $cardTypeID, $isVIP, $cardNumber, $errorCode, $transMsg, $alterStr) {
        if ($module == 'Login')
            return array('Login' => array('MPSessionID' => $mpSessionID, 'CardTypeID' => $cardTypeID, 'IsVIP' => $isVIP, 'CardNumber' => $cardNumber, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg, 'Remarks' => $alterStr));
    }

    public static function retMsgChangePassword($module, $errorCode, $transMsg) {
        if ($module == 'ChangePassword')
            return array('ChangePassword' => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    public static function retMsgCheckPoints($module, $currentPoints, $cardNumber, $errorCode, $transMsg) {
        if ($module == 'CheckPoints')
            return array('CheckPoints' => array('CurrentPoints' => $currentPoints, 'CardNumber' => $cardNumber, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    public static function retMsgUpdateProfile($module, $errorCode, $transMsg) {
        $module = 'UpdateProfile';
        return array($module => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    public static function retMsgGetGender($module, $gender) {
        $module = 'GetGender';
        return array($module => array('Gender' => $gender));
    }

    public static function retMsgGetIDPresented($module, $idPresented) {
        $module = 'GetIDPresented';
        return array($module => array('PresentedID' => $idPresented));
    }

    public static function retMsgGetNationality($module, $nationality) {
        $module = 'GetNationality';
        return array($module => array('Nationality' => $nationality));
    }

    public static function retMsgGetOccupation($module, $occupation) {
        $module = 'GetOccupation';
        return array($module => array('Occupation' => $occupation));
    }

    public static function retMsgGetIsSmoker($module, $isSmoker) {
        $module = 'GetIsSmoker';
        return array($module => array('IsSmoker' => $isSmoker));
    }

    public static function retMsgListItems($module, $listOfItems, $errorCode, $transMsg) {
        $module = 'ListItems';
        return array($module => array('ItemsList' => $listOfItems, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    public static function retMsgRegisterMember($module, $errorCode, $transMsg) {
        $module = 'RegisterMember';
        return array($module => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    public static function retMsgGetProfile($module, $profile, $errorCode, $transMsg) {
        $module = 'GetProfile';
        return array($module => array('Profile' => $profile, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    public static function retMsgItemRedemptionSuccess($module, $itemRedemption, $errorCode, $transMsg) {
        $module = 'RedeemItems';
        return array($module => array('ItemRedemption' => $itemRedemption, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    public static function retMsgCouponRedemptionSuccess($module, $couponRedemption, $errorCode, $transMsg) {
        $module = 'RedeemItems';
        return array($module => array('CouponRedemption' => $couponRedemption, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    public static function retMsgRedemption($module, $redemption, $errorCode, $transMsg) {
        $module = 'RedeemItems';
        return array($module => array('Redemption' => $redemption, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    ///@date 08-08-2014
    public static function retMsgGetReferrer($module, $referrer) {
        $module = 'GetReferrer';
        return array($module => array('Referrer' => $referrer));
    }

    public static function retMsgGetRegion($module, $region) {
        $module = 'GetRegion';
        return array($module => array('Region' => $region));
    }

    public static function retMsgGetCity($module, $city) {
        $module = 'GetCity';
        return array($module => array('City' => $city));
    }

    public static function retMsgRegisterMemberBT($module, $couponNumber, $expiryDate, $errorCode, $transMsg) {
        $module = 'RegisterMemberBT';
        return array($module => array('CouponNumber' => $couponNumber, 'ExpiryDate' => $expiryDate, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    //@date 10-27-2014
    public static function retMsgCreateMobileInfo($module, $mpSessionID, $cardTypeID, $isVIP, $cardNumber, $errorCode, $transMsg, $alterStr) {
        $module = 'CreateMobileInfo';
        return array('CreateMobileInfo' => array('MPSessionID' => $mpSessionID, 'CardTypeID' => $cardTypeID, 'IsVIP' => $isVIP, 'CardNumber' => $cardNumber, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg, 'Remarks' => $alterStr));
    }

    public static function retMsgVerifyTracking2($module, $tracking1, $remarks, $errorCode, $transMsg) {
        $module = 'VerifyTracking2';
        return array('VerifyTracking2' => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    public static function retMsgGetBalance($module, $withdrawableBalance, $playableBalance, $bonusBalance, $playthroughBalance, $errorCode, $transMsg) {
        $module = 'GetBalance';
        return array($module => array('WithdrawableBalance' => $withdrawableBalance, 'PlayableBalance' => $playableBalance, 'BonusBalance' => $bonusBalance, 'PlaythroughBalance' => $playthroughBalance, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    public static function retMsgResetPin($module, $result) {
        $module = 'ResetPin';
        return $result;
    }
    
    public static function retMsgListPromos($module, $promos, $errorCode, $transMsg) {
        return array($module => array('Promos' => $promos, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }
    
    public static function retMsgListLocations($module, $locations, $errorCode, $transMsg) {
        return array($module => array('Locations' => $locations, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }
}
?>