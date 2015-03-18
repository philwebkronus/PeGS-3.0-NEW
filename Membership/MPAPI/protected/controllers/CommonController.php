<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Get the return output of the MPapi
 * @return array
 * @date 6-13-2014
 * @author fdlsison
 */

class CommonController {

    CONST SOURCE_CASHIER = 0;
    CONST SOURCE_PLAYER = 1;
    CONST CARD_TYPE_GOLD = 1;
    CONST CARD_TYPE_GREEN = 2;
    CONST CARD_TYPE_TEMPORARY = 3;
    CONST PLAYER_TYPE_NONVIP = 0;
    CONST PLAYER_TYPE_VIP = 1;
    CONST LOGIN_STATUS_SUCCESSFUL = 0;
    CONST LOGIN_STATUS_INVALID_USERNAME = 1;
    CONST LOGIN_STATUS_INVALID_PASSWORD = 2;
    CONST LOGIN_STATUS_INVALID_ACCOUNT = 3;
    CONST FP_STATUS_SUCCESSFUL = 0;
    CONST FP_STATUS_INVALID_EMAIL = 1;
    CONST FP_STATUS_INVALID_CARD_NUMBER = 2;
    CONST GENDER_MALE = 1;
    CONST GENDER_FEMALE = 2;
    CONST IDPRESENTED_SSS = 1;
    CONST IDPRESENTED_GSIS = 2;
    CONST IDPRESENTED_PASSPORT = 3;
    CONST IDPRESENTED_POSTAL_ID = 4;
    CONST IDPRESENTED_DRIVERS_LICENSE = 5;
    CONST IDPRESENTED_VOTERS_ID = 6;
    CONST IDPRESENTED_SENIOR_CITIZENS_ID = 7;
    CONST IDPRESENTED_UMID = 8;
    CONST IDPRESENTED_OTHERS = 9;
    CONST NATIONALITY_FILIPINO = 1;
    CONST NATIONALITY_CHINESE = 2;
    CONST NATIONALITY_AMERICAN = 3;
    CONST NATIONALITY_KOREAN = 4;
    CONST NATIONALITY_JAPANESE = 5;
    CONST NATIONALITY_OTHERS = 6;
    CONST OCCUPATION_BUSINESSMAN = 1;
    CONST OCCUPATION_PROFESSIONAL = 2;
    CONST OCCUPATION_EMPLOYEE = 3;
    CONST OCCUPATION_RETIRED = 4;
    CONST OCCUPATION_OTHERS = 5;
    CONST IS_SMOKER_YES = 1;
    CONST IS_SMOKER_NO = 2;
    CONST UP_STATUS_SUCCESSFUL = 0;
    CONST UP_STATUS_FAILED = 1;
    CONST CP_STATUS_INACTIVE = 0;
    CONST CP_STATUS_ACTIVE = 1;
    CONST CP_STATUS_DEACTIVATED = 2;
    CONST CP_STATUS_ACTIVE_TEMPORARY = 5;
    CONST CP_STATUS_NEW_MIGRATED = 7;
    CONST CP_STATUS_TEMP_MIGRATED = 8;
    CONST CP_STATUS_BANNED = 9;
    CONST IS_MYSTERY_NO = 0;
    CONST IS_MYSTERY_YES = 1;
    CONST IR_STATUS_SUCCESSFUL = 0;
    CONST IR_STATUS_FAILED = 1;


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
        if($module == 'ForgotPassword') {
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
        else if($module == 'Logout') {
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
        if($module == 'Login')
            return array('Login' => array('MPSessionID' => $mpSessionID, 'CardTypeID' => $cardTypeID, 'IsVIP' => $isVIP,'CardNumber' => $cardNumber, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg, 'Remarks' => $alterStr));

    }
    
    public static function retMsgChangePassword($module, $errorCode, $transMsg) {
        if($module == 'ChangePassword')
            return array('ChangePassword' => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }
    
    public static function retMsgCheckPoints($module, $currentPoints, $cardNumber, $errorCode, $transMsg) {
        if($module == 'CheckPoints')
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
        $module == 'CreateMobileInfo';
        return array('CreateMobileInfo' => array('MPSessionID' => $mpSessionID, 'CardTypeID' => $cardTypeID, 'IsVIP' => $isVIP, 'CardNumber' => $cardNumber, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg, 'Remarks' => $alterStr));
            
    }

    public static function retMsgVerifyTracking2($module, $tracking1 , $remarks, $errorCode, $transMsg) {
        $module == 'VerifyTracking2';
        return array('VerifyTracking2' => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));

    }

}