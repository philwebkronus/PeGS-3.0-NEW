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
//Initialize the value of Current Points

$CurrentPoints = NULL;
$CardPoints = NULL;

//Check if Card Number was set
if (isset($_POST['CardNumber'])) {
    
    //Get the value of posted Card Number
    $cardnumber = $_POST['CardNumber'];
    
    //Initialize Modules
    $_MemberCards = new MemberCards();
    
    //Get Current Points and Status from database : loyaltydb (table : membercards)
    $CardNumberPoints = $_MemberCards->getCurrentPointsAndStatus($cardnumber);

    //Check if current points of the card is not empty, if yes, do the following:
    if (!empty($CardNumberPoints)) {
        
        //Set the value of Current Points and Status based on the retrieved data
        foreach ($CardNumberPoints as $value) {
            $CurrentPoints = $value['CurrentPoints'];
            $Status = $value['Status'];
        }
        
        //Create instances for Status
        switch ($Status) {
            case 0:
                $CardPoints->CurrentPoints = 'Card is Inactive';
                break;
            case 1:
                $CardPoints->CurrentPoints = number_format($CurrentPoints, 0, '', ',');
                break;
            case 2:
                $CardPoints->CurrentPoints = 'Card is Deactivated';
                break;
            case 5:
                $CardPoints->CurrentPoints = number_format($CurrentPoints, 0, '', ',');
                break;
            case 7:
                $CardPoints->CurrentPoints = 'Card is already Migrated';
                break;
            case 8:
                $CardPoints->CurrentPoints = 'Card is already Migrated';
                break;
            case 9:
                $CardPoints->CurrentPoints = 'Card is Banned';
                break;
            default :
                $CardPoints->CurrentPoints = 'Invalid Card';
                break;
        }
    }
    
    //Check if current points of the card is empty, if yes, do the following:
    else {
        $CardPoints->CurrentPoints = 'Invalid Card';
    }
    
    //Encode into json for displaying of data
    echo json_encode($CardPoints);
}
?>
