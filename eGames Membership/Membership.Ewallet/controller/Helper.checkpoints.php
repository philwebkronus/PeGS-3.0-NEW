<?php

/*
 * Description: Fetching and encoding data into JSON array to be displayed for Checking of Card Number Points.
 * @author: JunJun S. Hernandez
 * DateCreated: 2013-08-22
 */

//Attach and Initialize framework
include ('../init.inc.php');
//Load the module to be used
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Membership", "PcwsWrapper");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
//Initialize the value of Current Points

$_Log = new AuditTrail();
$CurrentPoints = NULL;
$CardPoints = NULL;

//Check if Card Number was set
if (isset($_POST['CardNumber'])) {
    
 
    //Get the value of posted Card Number
    $cardnumber = $_POST['CardNumber'];
    
    //Initialize Modules
    $_MemberCards = new MemberCards();
        
     $_MemberCardsAPI = new PcwsWrapper();
    
    //Get Current Points and Status from database : loyaltydb (table : membercards)
      $CardNumberStatus = $_MemberCards->getCurrentPointsAndStatus($cardnumber);

      $CardNumberPoints = $_MemberCardsAPI->getCompPoints($cardnumber, 1);//mportal authentication code:1
      
      if(is_numeric($CardNumberPoints['GetCompPoints']['CompBalance']))
      $CardNumberCurrentPoints = number_format($CardNumberPoints['GetCompPoints']['CompBalance'], 0);
      
    //Check if current points of the card is not empty, if yes, do the following:
    if (!empty($CardNumberStatus)&&isset($CardNumberCurrentPoints)) {
        //Set the value of Current Points and Status based on the retrieved data
        foreach ($CardNumberStatus as $value) {
            
            $Status = $value['Status'];
        }
        $CurrentPoints = $CardNumberCurrentPoints;
        //Create instances for Status
        switch ($Status) {
            case 0:
                if (!isset($CardPoints)) 
                $CardPoints = new stdClass();
                $CardPoints->CurrentPoints = 'Card is Inactive';
                break;
            case 1:
                if (!isset($CardPoints)) 
                    $CardPoints = new stdClass();
                $CardPoints->CurrentPoints = number_format($CurrentPoints, 0, '', ',');
                break;
            case 2:
                if (!isset($CardPoints)) 
                    $CardPoints = new stdClass();
                $CardPoints->CurrentPoints = 'Card is Deactivated';
                break;
            case 5:
                if (!isset($CardPoints)) 
                    $CardPoints = new stdClass();
                $CardPoints->CurrentPoints = number_format($CurrentPoints, 0, '', ',');
                break;
            case 7:
                if (!isset($CardPoints)) 
                    $CardPoints = new stdClass();
                $CardPoints->CurrentPoints = 'Card is already Migrated';
                break;
            case 8:
                if (!isset($CardPoints)) 
                    $CardPoints = new stdClass();
                $CardPoints->CurrentPoints = 'Card is already Migrated';
                break;
            case 9:
                if (!isset($CardPoints)) 
                    $CardPoints = new stdClass();
                $CardPoints->CurrentPoints = 'Card is Banned';
                break;
            default :
                if (!isset($CardPoints)) 
                    $CardPoints = new stdClass();
                $CardPoints->CurrentPoints = 'Invalid Card';
                break;
        }
         
         //audit trail
         $cardNumber = $_POST['CardNumber'];   
         $_Log->logEvent(AuditFunctions::CHECK_PLAYER_POINTS,$cardNumber, array('ID' => null, 'SessionID' => null));
    }
    
    //Check if current points of the card is empty, if yes, do the following:
    else {
       
        $CardPoints->CurrentPoints = 'Invalid Card';
    }
    
    //Encode into json for displaying of data
    echo json_encode($CardPoints);
}
?>
