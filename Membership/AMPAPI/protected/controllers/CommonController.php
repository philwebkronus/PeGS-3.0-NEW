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
    
    
    public static function retMsg($module, $transMsg = '',$errorCode='', $username = '', $password = '', $TPSessionID='', $MID='', $sessionID='', $cardTypeID='',$isVip='', $cardNumber = '',$MPSessionID='', $CurrentPoints='', $RewardID='') { //$errorCode = '') {//,$idPresented = '', $nationality = '', $occupation = '', $isSmoker = '', $MID = '') {

        if($module == 'Login') {
            return array('Login' => array('MPSessionID'=>$MPSessionID,'CardTypeID'=>$cardTypeID,'IsVIP' => $isVip,'CardNumber' => $cardNumber, 'ErrorCode'=>$errorCode, 'ReturnMessage' => $transMsg));
        }
        else if($module=='AuthenticateSession'){
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
        else if($module == 'ChangePassword') {
            return array('ChangePassword' => array('ErrorCode' => $errorCode, 
                         'ReturnMessage' => $transMsg));
        }
        else if($module == 'ForgotPassword') {
            return array('ForgotPassword' => array('ErrorCode' => $errorCode, 
                         'ReturnMessage' => $transMsg));
        }
        else if($module == 'UpdateProfile') {
            return array('UpdateProfile' => array('ErrorCode' => $errorCode,
                         'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'GetGender') {
            return array('GetGender' => array('Gender'=>array(array(
                'GenderID' => $errorCode,
                'GenderDescription' => $transMsg))));
        }
        
        else if($module == 'GetIDPresented') {
            return array('GetIDPresented' => array('PresentedID'=>array(array('PresentedID' => $errorCode,
                         'PresentedIDDescription' => $transMsg))));
        }
        
        else if($module == 'GetNationality') {
            return array('GetNationality' => array('Nationality'=>array(array('NationalityID' => $errorCode,
                         'NationalityDescription' => $transMsg))));
        }
        
        else if($module == 'GetOccupation') {
            return array('GetOccupation' => array('Occupation'=>array(array('OccupationID' => $errorCode,
                         'OccupationDescription' => $transMsg))));
        }
        
        else if($module == 'GetIsSmoker') {
            return array('GetIsSmoker' => array('IsSmoker'=>array(array('IsSmokerID' => $errorCode,
                         'IsSmokerDescription' => $transMsg))));
        }
        
        else if($module == 'CheckPoints') {
            return array('CheckPoints' => array('CurrentPoints'=>$CurrentPoints,'CardNumber'=>'','ErrorCode' => $errorCode,
                         'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'ListItems') {
            return array('ListItems' => array(
                'RewardID'=>'',
                'RewardItemID'=>'',
                'Description'=>'',
                'AvailableItemCount'=>'',
                'ProductName'=>'',
                'PartnerName'=>'',
                'Points'=>'',
                'ThumbnailLimitedImage'=>'',
                'ECouponImage'=>'',
                'LearnMoreLimitedImage'=>'',
                'LearnMoreOutOfStockImage'=>'',
                'ThumbnailOutOfStockImage'=>'',
                'PromoName'=>'',
                'IsMystery'=>'',
                'MysteryName'=>'',
                'MysteryAbout'=>'',
                'MysteryTerms'=>'',
                'MysterSubtext'=>'',
                'About'=>'',
                'Terms'=>'',
                'CompanyAddress'=>'',
                'CompanyPhone'=>'',
                'CompanyWebsite'=>'',
                'ErrorCode' => $errorCode,
                'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'RegisterMember') {
            return array('RegisterMember' => array('ErrorCode' => $errorCode,
                         'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'RedeemItems') {
            
                return array('RedeemItems' => array(
                'ItemImage'=>'',
                'ItemName'=>'',
                'PartnerName'=>'',
                'PlayerName'=>'',
                'CardNumber'=>'',
                'RedemptionDate'=>'',
                'SerialNumber'=>'',
                'SecurityCode'=>'',
                'ValidityDate'=>'',
                'CompanyAddress'=>'',
                'CompanyPhone'=>'',
                'CompanyWebsite'=>'',
                'Quantity'=>'',
                'SiteCode'=>'',
                'PromoCode'=>'',
                'PromoTitle'=>'',
                'PromoPeriod'=>'',
                'DrawDate'=>'',
                'Address'=>'',
                'Birthdate'=>'',
                'EmailAddress'=>'',
                'ContactNo'=>'',
                'CheckSum'=>'',
                'About'=>'',
                'Terms'=>'',
                'ErrorCode' => $errorCode,
                'ReturnMessage' => $transMsg));
          
        }
        
        
        else if($module == 'GetProfile') {
            return array('GetProfile' => array(
                'FirstName'=>'',
                'MiddleName'=>'',
                'LastName'=>'',
                'NickName'=>'',
                'PermanentAddress'=>'',
                'MobileNo'=>'',
                'AlternateMobileNo'=>'',
                'EmailAddress'=>'',
                'AlternateEmail'=>'',
                'Gender'=>'',
                'IDPresented'=>'',
                'IDNumber'=>'',
                'Nationality'=>'',
                'Occupation'=>'',
                'IsSmoker'=>'',
                'Birthdate'=>'',
                'Age'=>'',
                'CurrentPoints'=>'',
                'BonusPoints'=>'',
                'RedeemedPoints'=>'',
                'LifetimePoints'=>'',
                'CardNumber'=>'',
                'ErrorCode'=>$errorCode,
                'ReturnMessage'=>$transMsg,    
                ));
        }
        
        else if($module == 'GetReferrer') {
            return array('GetReferrer' => array('Referrer'=>array(array('ReferrerID' => $errorCode,
                         'ReferrerDescription' => $transMsg))));
        }
        
        else if($module == 'GetRegion') {
            return array('GetRegion' => array('Region'=>array(array('RegionID' => $errorCode,
                         'RegionDescription' => $transMsg))));
        }
        
        else if($module == 'GetCity') {
            return array('GetCity' => array('City'=>array(array('CityID' => $errorCode,
                         'CityDescription' => $transMsg))));
        }
        
        else if($module == 'Logout') {
            return array('Logout' => array('ErrorCode' => $errorCode,
                         'ReturnMessage' => $transMsg));
        }
        
    }
    //return message function for Login module
    public static function retMsgLogin($module, $mpSessionID, $cardTypeID, $isVIP, $cardNumber, $errorCode, $transMsg) {
        if($module == 'Login')
            return array('Login' => array('MPSessionID' => $mpSessionID, 'CardTypeID' => $cardTypeID, 'IsVIP' => $isVIP,'CardNumber' => $cardNumber, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
            
    }
    
    public static function retMsgCheckPoints($module, $currentPoints, $errorCode, $transMsg) {
        if($module == 'CheckPoints')
            return array('CheckPoints' => array('CurrentPoints' => $currentPoints, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
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
    
    public static function retMsgChangePassword($module, $errorCode, $transMsg) {
        $module = 'ChangePassword';
        return array($module => array('ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }
    
}
?>
