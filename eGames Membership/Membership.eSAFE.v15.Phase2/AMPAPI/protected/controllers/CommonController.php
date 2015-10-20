<?php
/**
 * Get the return output of the MPapi
 * @return array
 * @date 6-13-2014
 * @author fdlsison
 */

class CommonController {
    
    public static function retMsg($module, $transMsg = '',$errorCode='', $username = '', $password = '', $TPSessionID='', $MID='', $sessionID='', $cardTypeID='',$isVip='', $cardNumber='', $MPSessionID='', $CurrentPoints='', $RewardID='') { //$errorCode = '') {//,$idPresented = '', $nationality = '', $occupation = '', $isSmoker = '', $MID = '') {

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
                'TPSessionID'=>$TPSessionID,
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
             return array('GetGender' => array('ErrorCode'=> $errorCode,
                'ReturnMessage' => $transMsg));
//            return array('GetGender' => array('Gender'=>array(array(
//                'GenderID' => $errorCode,
//                'GenderDescription' => $transMsg))));
            
        }
        
        else if($module == 'GetIDPresented') {
            return array($module => array('ErrorCode'=> $errorCode,
                'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'GetNationality') {
            return array($module => array('ErrorCode'=> $errorCode,
                'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'GetOccupation') {
            return array($module => array('ErrorCode'=> $errorCode,
                'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'GetIsSmoker') {
            return array($module => array('ErrorCode'=> $errorCode,
                'ReturnMessage' => $transMsg));
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
		'RegionID'=>'',
		'CityID'=>'',
                'ErrorCode'=>$errorCode,
                'ReturnMessage'=>$transMsg,    
                ));
        }
        
        else if($module == 'GetReferrer') {
            return array($module => array('ErrorCode'=> $errorCode,
                'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'GetRegion') {
            return array($module => array('ErrorCode'=> $errorCode,
                'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'GetCity') {
            return array($module => array('ErrorCode'=> $errorCode,
                'ReturnMessage' => $transMsg));
        }

        else if($module == 'GetBalance') {
            return array('GetBalance' => array(
		'WithdrawableBalance' => 0.00, 
		'PlayableBalance' => 0.00,
		'BonusBalance' => 0.00,
		'PlaythroughBalance' => 0.00,
		'ErrorCode' => $errorCode,
		'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'Logout') {
            return array('Logout' => array('ErrorCode' => $errorCode,
                         'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'CreateMobileInfo') {
            return array('CreateMobileInfo' => array('MPSessionID'=>$MPSessionID,'CardTypeID'=>$cardTypeID,'IsVIP' => $isVip,'CardNumber' => '','ErrorCode'=>$errorCode, 'ReturnMessage' => $transMsg, 'Remarks' => ''));           
        }
        
        else if($module == 'ListPromos') {
            return array('ListPromos' => array(
                'SiteName'=>'',
                'PromoName'=>'',
                'PromoDetails'=>'',
                'StartDate'=> '',
                'EndDate'=> '',
                'DrawDate' => '',
                'Status' => '',
                'PromoThumbnail' => '',
                'PromoPoster' => '',
                'ErrorCode' => $errorCode,
                'ReturnMessage' => $transMsg));
        }
        
        else if($module == 'ListLocations') {
            return array('ListLocations' => array(
                'SiteName' => '',
                'Address'=>'',
                'TelephoneNumber'=>'',
                'OperatingHours'=>'',
                'Latitude'=> 0.000000,
                'Longitude'=> 0.000000,
                'ErrorCode' => $errorCode,
                'ReturnMessage' => $transMsg));
        }
    }
    //return message function for Login module
    public static function retMsgLogin($module, $mpSessionID, $cardTypeID, $isVIP, $cardNumber, $errorCode, $transMsg, $alterStr) {
        if($module == 'Login')
            return array('Login' => array('MPSessionID' => $mpSessionID, 'CardTypeID' => $cardTypeID, 'IsVIP' => $isVIP, 'CardNumber' => $cardNumber, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg, 'Remarks' => $alterStr));
            
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
    
    public static function retMsgGetBalance($module, $withdrawableBalance, $playableBalance, $bonusBalance, $playthroughBalance, $errorCode, $transMsg) {
        $module = 'GetBalance';
        return array($module => array('WithdrawableBalance' => $withdrawableBalance, 'PlayableBalance' => $playableBalance, 'BonusBalance' => $bonusBalance, 'PlaythroughBalance' => $playthroughBalance, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }

    //@date 08-26-2015
    //@author fdlsison
    public static function retMsgListPromos($module, $listOfPromos, $errorCode, $transMsg) {
        return array($module => array('PromosList' => $listOfPromos, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }
    
    public static function retMsgListLocations($module, $listOfLocations, $errorCode, $transMsg) {
        return array($module => array('LocationsList' => $listOfLocations, 'ErrorCode' => $errorCode, 'ReturnMessage' => $transMsg));
    }
    
}
?>
