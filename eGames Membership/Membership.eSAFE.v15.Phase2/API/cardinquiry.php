<?php

/*
 * GetCardInfo API
 * @author : owliber
 * @date : 2013-04-18
 */

require_once("../init.inc.php");

/*
 * Load Module Classes
 */

App::LoadModuleClass("Loyalty", "GetCardInfoAPI"); //API Wrapper

App::LoadModuleClass("Loyalty", "Cards");
App::LoadModuleClass("Loyalty", "OldCards");
App::LoadModuleClass("Loyalty", "CardStatus");
App::LoadModuleClass("Loyalty", "CardVersion");
App::LoadModuleClass("Membership", "TempMemberInfo");
App::LoadModuleClass("Membership", "Helper");
App::LoadCore('ErrorLogger.php');

/*
 * Load Core for API Response
 */
App::LoadCore('JSONAPIResponse.class.php');

/*
 * Load models
 */
$_GetCardInfoAPI = new GetCardInfoAPI();
$_Cards = new Cards();
$_OldCards = new OldCards();
$_TempMembers = new TempMemberInfo();
$_JSONAPIResponse = new JSONAPIResponse();
$_Helper = new Helper();

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";
$status = '';


if(!isset($_GET['cardnumber']) || !isset( $_GET['isreg'])){
    $result = array("CardInfo"=>array(
                                    "Username"         => "",
                                    "CardNumber"       => "",
                                    "MemberUsername"   => "",
                                    "CardType"         => "",
                                    "MemberName"       => "",
                                    "RegistrationDate" => "",
                                    "Birthdate"        => "",
                                    "CurrentPoints"    => "",
                                    "LifetimePoints"   => "",
                                    "RedeemedPoints"   => "",
                                    "IsCompleteInfo"   => "",
                                    "MemberID"         => "",
                                    "CasinoArray"      => "",
                                    "CardStatus"       => CardStatus::NOT_EXIST,
                                    "DateVerified"     => "",
                                    "MobileNumber"     => "",
                                    "Email"            => "",
                                    "IsRegs"           => 0,
                                    "CoolingPeriod"    => "",
                                    "StatusCode"       => CardStatus::NOT_EXIST,
                                    "StatusMsg"        => 'Card Not Found',
                                    )
                        );
    $logger->logger($logdate, $logtype, "Card Number is blank");
    $_JSONAPIResponse->_sendResponse(200, json_encode($result));
    exit;
}

$cardNumber = Helper::removeDash($_GET['cardnumber']);
$isReg = $_GET['isreg'];

if(isset($_GET['siteid'])){
    $siteID = $_GET['siteid'];
}
else{
    $siteID = 1;
}


/*
 * Validate input if barcode has value and is alphanumeric
 */
if((ctype_alnum( $cardNumber )) && (ctype_digit( $isReg ) ))
{
    /*
     * Check card version from OLD, UB and TEMPORARY
     */
    $version = $_Cards->getVersion( $cardNumber );

    switch ($version)
    {
        case CardVersion::OLD: // Old version
            $cardNumber = strtoupper($cardNumber);
            $result = $_OldCards->getOldCardInfo( $cardNumber );

            if(count($result) > 0)
            {
                switch ($result[0]['CardStatus'])
                {
                    case 3:
                        $status = CardStatus::OLD;
                        break;
                    case 4:
                        $status = CardStatus::OLD_MIGRATED;
                        break;
                }
            }
            else
            {
                $logger->logger($logdate, $logtype, "Card Not Found[000]: ".$cardNumber);
                $status = CardStatus::NOT_EXIST;
            }

            break;

        case CardVersion::TEMPORARY: // Temporary

            $result = $_TempMembers->getMembersByAccount( $cardNumber );

            if(count($result) > 0)
            {
                if( $result[0]['IsVerified'] == 1)
                {
                    $dateVerified = $result[0]['DateVerified'];
                    $now = date('Y-m-d H:i:s');

                    $datetime1 = new DateSelector();
                    $datetime2 = new DateSelector();
                    $datetime1->CurrentDate = $dateVerified;
                    $datetime2->CurrentDate = $now;

                    $diff = abs(strtotime($datetime1->CurrentDate) - strtotime($datetime2->CurrentDate));

                    $hours = $diff / 60 / 60; //Get the total hours elapsed; $diff / 60 seconds / 60 minutes
                    
					if($hours == 0){
                        $hours = 1;
                    }
					
                    $cardInfo = $_Cards->getCardInfo( $cardNumber );

                    if(count ($cardInfo) > 0)
                    {
                        $status = $cardInfo[0]['Status'];
                    }
                    else
                    {
                       // Check cooling period
                        ( $hours > $_Helper->getParameterValue('COOLING_PERIOD') ) ?  $status = CardStatus::ACTIVE_TEMPORARY : $status = CardStatus::INACTIVE_TEMPORARY;
                    }



                }
                else
                {
                    $logger->logger($logdate, $logtype, "Temporary account is inactive: ".$cardNumber);
                    $status = CardStatus::INACTIVE_TEMPORARY;
                }
            }
            else
            {
                $logger->logger($logdate, $logtype, "Card Not Found[001]: ".$cardNumber);
                $status = CardStatus::NOT_EXIST;
            }

            break;

        case CardVersion::USERBASED: // User-based
            $cardNumber = strtoupper($cardNumber);
            $result = $_Cards->getCardInfo( $cardNumber );

            if(count( $result ) > 0)
            {
                switch ( $result[0]['Status'] )
                {
                    case 0:
                        $status = CardStatus::INACTIVE;
                        break;
                    case 1:
                        $status = CardStatus::ACTIVE;
                        break;
                    case 2:
                        $status = CardStatus::DEACTIVATED;
                        break;
                    case 7:
                        $status = CardStatus::NEW_MIGRATED;
                        break;
                    case 9:
                        $status = CardStatus::BANNED;
                        break;
                }
            }
            else
            {
                $status = CardStatus::NOT_EXIST;
                $logger->logger($logdate, $logtype, "Card Not Found[002]: ".$cardNumber);
            }

            break;

        default :
            $logger->logger($logdate, $logtype, "Card Not Found[003]: ".$cardNumber);
            $status = CardStatus::NOT_EXIST;
            break;
    }

    $result = $_GetCardInfoAPI->GetCardInfo($cardNumber, $status, $isReg, $siteID);
    $_JSONAPIResponse->_sendResponse(200,  json_encode($result));

}
else
{
    $result = array("CardInfo"=>array(
                                    "Username"         => "",
                                    "CardNumber"       => "",
                                    "MemberUsername"   => "",
                                    "CardType"         => "",
                                    "MemberName"       => "",
                                    "RegistrationDate" => "",
                                    "Birthdate"        => "",
                                    "CurrentPoints"    => "",
                                    "LifetimePoints"   => "",
                                    "RedeemedPoints"   => "",
                                    "IsCompleteInfo"   => "",
                                    "MemberID"         => "",
                                    "CasinoArray"      => "",
                                    "CardStatus"       => CardStatus::NOT_EXIST,
                                    "DateVerified"     => "",
                                    "MobileNumber"     => "",
                                    "Email"            => "",
                                    "IsRegs"           => 0,
                                    "CoolingPeriod"    => "",
                                    "StatusCode"       => CardStatus::NOT_EXIST,
                                    "StatusMsg"        => 'Card Not Found',
                                    )
                        );
    $logger->logger($logdate, $logtype, "Invalid card number: ".$cardNumber);
    $_JSONAPIResponse->_sendResponse(200, json_encode($result));
    exit;
}


?>
