<?php

/*
 * Description: Helper file to transfer points from red card to new UB card
 * @Author: Gerardo Jagolino Jr.
 */

//Attach and Initialize framework
require_once("../../init.inc.php");


//Load Modules to be use.
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "OldCards");
App::LoadModuleClass("Loyalty", "CardPointsTransfer");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass('Membership', 'TempMembers');
App::LoadModuleClass("Kronus", "TransactionSummary");
App::LoadModuleClass("Kronus", "Sites");
App::LoadModuleClass("Kronus", "Sites");
App::LoadModuleClass("Loyalty", "Cards");
App::LoadModuleClass("Loyalty", "MemberPointsTransferLog");
App::LoadModuleClass("Loyalty", "CardStatus");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Membership", "PcwsWrapper");

//Load Needed Core Class.
App::LoadCore('Validation.class.php');

//Initialize Modules
$_MemberCards = new MemberCards();
$_MemberInfo = new MemberInfo();
$_Cards = new Cards();
$_OldCards = new OldCards();
$_MemberPointsTransferLog = new MemberPointsTransferLog();
$_Members = new Members();
$_CardPointsTransfer = new CardPointsTransfer();
$_TempMember = new TempMembers();
$_TransactionSummary = new TransactionSummary();
$_Sites = new Sites();
$_Log = new AuditTrail();
$_PcwsWrapper = new PcwsWrapper();

$profile = null;
$response = null;

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";


if (isset($_POST['pager'])) {
    
    $vpage = $_POST['pager'];
    switch ($vpage){
        case "SearchMigrate1":
            
        $card = $_POST['Card'];
        $cardtype = $_POST['CardType'];
        
        switch ($cardtype) {
            case "1":
               $carddetails = $_MemberCards->getTempcardDetails($card);
                      
               if(!empty($carddetails)){
                   
                    if(APP::getParam('PointSystem') == 2) {
                        $CurrentPoints = $_PcwsWrapper->getCompPoints($card, 0);
                        $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                    }
                    else {
                        $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($card);
                        if(empty($CurrentPoints)) {
                            $CurrentPoints = 0;
                        }
                        else {
                            $CurrentPoints = $CurrentPoints[0]['CurrentPoints'];
                        }
                    }

                    foreach ($carddetails as $value) {
                     $mid = $value['MID'];
                     $status = $value['Status'];
                     $siteid = $value['SiteID'];
                     $lifetimepoints = number_format($value['LifeTimePoints']);
                     $currentpoints = number_format($CurrentPoints);
                     $redeemedpoints = number_format($value['RedeemedPoints']);
                     $bonuspoints = number_format($value['BonusPoints']);
                 }

                 $sitedetails = $_Sites->getSite($siteid);

                 foreach ($sitedetails as $row) {
                     $sitename = $row['SiteName'];
                 }

                 if($status == 5){
                     $tempdetails = $_TempMember->getTempMemberInfo($card);

                     foreach ($tempdetails as $value1) {

                     $dateverified = date("Y-m-d H:i:s",strtotime($value1['DateVerified']));
                     $isverified = $value1['IsVerified'];

                     }
                    $status = $status;


                     switch($status)
                     {
                         case 0: $vstatus = 'InActive';break;
                         case 1: $vstatus = 'Active';    break;
                         case 2: $vstatus = 'Deactivated';break;
                         case 5: $vstatus = 'Active Temporary';break;
                         case 7: $vstatus = 'New Migrated'; break;   
                         case 8: $vstatus = 'Temporary Migrated';  break;
                         case 9: $vstatus = 'Banned';  break;
                         default: $vstatus = 'Card Not Found'; break;
                     }

                         $msg->IDdetect = '1.1';
                         $msg->Msg = 'Card is already verified';
                         $msg->CardType = $cardtype;
                         $msg->DateTimeMigration = $dateverified;
                         $msg->Site = $sitename;;
                         $msg->LifeTimePoints = $lifetimepoints;
                         $msg->CurrentPoints = $currentpoints;
                         $msg->RedeemedPoints = $redeemedpoints;
                         $msg->BonusPoints = $bonuspoints;


                 }
                 elseif($status == 8){
                    
                     $membercarddetails = $_MemberCards->getCardDetailsActiveDeactivateBanned($mid);
                     if(APP::getParam('PointSystem') == 2) {
                        $CurrentPoints = $_PcwsWrapper->getCompPoints($card, 0);
                        $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                     }
                     else {
                        $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($card);
                        if(empty($CurrentPoints)) {
                            $CurrentPoints = 0;
                        }
                        else {
                            $CurrentPoints = $CurrentPoints[0]['CurrentPoints'];
                        }
                     }
                      
                     if(!empty($membercarddetails)){
                         foreach ($membercarddetails as $value) {
                            $cardnumber = $value['CardNumber'];
                            $activestatus = $value['Status'];
                            $datetimemigration = date("Y-m-d H:i:s",strtotime($value['DateCreated']));
                            $lifetimepoints = number_format($value['LifeTimePoints']);
                            $currentpoints = number_format($value['CurrentPoints']);
                            $redeemedpoints = number_format($value['BonusPoints']);
                            $bonuspoints = number_format($value['BonusPoints']);
                        }
                        
                        $status = $activestatus;

                        switch($status)
                           {
                               case 0: $vstatus = 'InActive';break;
                               case 1: $vstatus = 'Active';    break;
                               case 2: $vstatus = 'Deactivated';break;
                               case 5: $vstatus = 'Active Temporary';break;
                               case 7: $vstatus = 'New Migrated'; break;   
                               case 8: $vstatus = 'Temporary Migrated';  break;
                               case 9: $vstatus = 'Banned';  break;
                               default: $vstatus = 'Card Not Found'; break;
                           }

                        $msg->IDdetect = '1.2';
                        $msg->Msg = 'Temporary Account is already migrated to '.$cardnumber;
                        $msg->CardType = $cardtype;
                        $msg->DateTimeMigration = $datetimemigration;
                        $msg->Site = $sitename;
                        $msg->LifeTimePoints = $lifetimepoints;
                        $msg->CurrentPoints = $currentpoints;
                        $msg->RedeemedPoints = $redeemedpoints;
                        $msg->BonusPoints = $bonuspoints;
                     } else {
                        $msg->IDdetect = '1.3';
                        $msg->Msg = 'Migrated temporary cards';
                        $msg->CardType = "Invalid";  
                     }
                 }
                 elseif($status == 2){
                    
                     $membercarddetails = $_MemberCards->getCardDetailsActiveDeactivateBanned($mid);
                     if(APP::getParam('PointSystem') == 2) {
                        $CurrentPoints = $_PcwsWrapper->getCompPoints($card, 0);
                        $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                     }
                     else {
                        $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($card);
                        if(empty($CurrentPoints)) {
                            $CurrentPoints = 0;
                        }
                        else {
                            $CurrentPoints = $CurrentPoints[0]['CurrentPoints'];
                        }
                     }
                      
                     if(!empty($membercarddetails)){
                         foreach ($membercarddetails as $value) {
                            $cardnumber = $value['CardNumber'];
                            $activestatus = $value['Status'];
                            $datetimemigration = date("Y-m-d H:i:s",strtotime($value['DateCreated']));
                            $lifetimepoints = number_format($value['LifeTimePoints']);
                            $currentpoints = number_format($CurrentPoints);
                            $redeemedpoints = number_format($value['BonusPoints']);
                            $bonuspoints = number_format($value['BonusPoints']);
                        }
                        
                        $status = $activestatus;

                        switch($status)
                           {
                               case 0: $vstatus = 'InActive';break;
                               case 1: $vstatus = 'Active';    break;
                               case 2: $vstatus = 'Deactivated';break;
                               case 5: $vstatus = 'Active Temporary';break;
                               case 7: $vstatus = 'New Migrated'; break;   
                               case 8: $vstatus = 'Temporary Migrated';  break;
                               case 9: $vstatus = 'Banned';  break;
                               default: $vstatus = 'Card Not Found'; break;
                           }

                        $msg->IDdetect = '1.2';
                        $msg->Msg = 'Temporary Account is Deactivated '.$cardnumber;
                        $msg->CardType = $cardtype;
                        $msg->DateTimeMigration = $datetimemigration;
                        $msg->Site = $sitename;
                        $msg->LifeTimePoints = $lifetimepoints;
                        $msg->CurrentPoints = $currentpoints;
                        $msg->RedeemedPoints = $redeemedpoints;
                        $msg->BonusPoints = $bonuspoints;
                     } else {
                        $msg->IDdetect = '1.3';
                        $msg->Msg = 'Migrated temporary cards';
                        $msg->CardType = "Invalid";  
                     }
                 }
                 else{

                     $msg->IDdetect = '1.3';
                     $msg->CardType = 'Invalid';
                     $msg->Msg = 'Card Number Invalid.';
                 }
               }
               else{
                     $msg->IDdetect = '1.3';
                     $msg->CardType = 'Invalid';
                     $msg->Msg = 'Card Number Invalid.';
               }
                
                
            break;  
        
            case "2":
                $card = $_POST['Card'];
                $cardtype = $_POST['CardType'];
                      
                $oldcarddetails = $_OldCards->getOldCardInfo($card);
                if(!empty($oldcarddetails)){
                
                        if(APP::getParam('PointSystem') == 2) {
                           $CurrentPoints = $_PcwsWrapper->getCompPoints($card, 0);
                           $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                        }
                        else {
                           $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($card);
                           if(empty($CurrentPoints)) {
                               $CurrentPoints = 0;
                           }
                           else {
                               $CurrentPoints = $CurrentPoints[0]['CurrentPoints'];
                           }
                           
                        }

                        foreach ($oldcarddetails as $val) {
                            $oldcardstatus = $val['CardStatus'];
                            $oldcardid = $val['OldCardID'];
                            $lifetimepoints = number_format($val['LifetimePoints']);
                            $currentpoints = 0;
                            $redeemedpoints = number_format($val['RedeemedPoints']);
                            $bonuspoints = 0;
                            $dateregistered = date("Y-m-d H:i:s",strtotime($val['RegistrationDate']));
                        }

                        if($oldcardstatus == 3){

                            switch($oldcardstatus)
                            {
                                case 3: $oldcardstatus = 'Old Loyalty Card';    break;
                                case 4: $oldcardstatus = 'Migrated Loyalty Card';break;
                                default: $oldcardstatus = 'Card Not Found'; break;
                            }

                            $msg->IDdetect = '2.1';
                            $msg->Msg = 'Active Loyalty Card';
                            $msg->CardType = $cardtype;
                            $msg->DateTimeMigration = $dateregistered;
                            $msg->Site = $oldcardstatus;
                            $msg->LifeTimePoints = $lifetimepoints;
                            $msg->CurrentPoints = $currentpoints;
                            $msg->RedeemedPoints = $redeemedpoints;
                            $msg->BonusPoints = $bonuspoints;

                        }
                        else{

                            $cardstransferdetails = $_CardPointsTransfer->getTransferrredUBCard($oldcardid);

                            foreach ($cardstransferdetails as $var) {
                                $mid = $var['MID'];
                                $tomembercardid = $var['ToMemberCardID'];
                                $lifetimepoints = number_format($var['LifeTimePoints']);
                                $currentpoints = number_format($var['CurrentPoints']);
                                $redeemedpoints = number_format($var['RedeemedPoints']);
                                $bonuspoints = number_format($var['BonusPoints']);
                                $datetransferred = date("Y-m-d H:i:s",strtotime($var['DateTransferred']));

                            }
                            $membercarddetails = $_MemberCards->getMemberCardDetails($tomembercardid);

                            foreach ($membercarddetails as $valuez) {
                                $cardnumber = $valuez['CardNumber'];
                                $siteid = $valuez['SiteID'];
                            }

                            $sitedetails = $_Sites->getSite($siteid);

                            foreach ($sitedetails as $row) {
                                $sitename = $row['SiteName'];
                            }

                            $msg->IDdetect = '2.2';
                            $msg->Msg = 'Loyalty Card is already Migrated to '.$cardnumber;
                            $msg->CardType = $cardtype;
                            $msg->DateTimeMigration = $datetransferred;
                            $msg->Site = $sitename;
                            $msg->LifeTimePoints = $lifetimepoints;
                            $msg->CurrentPoints = $currentpoints;
                            $msg->RedeemedPoints = $redeemedpoints;
                            $msg->BonusPoints = $bonuspoints;
                        }
                }
                else{
                    $msg->IDdetect = '1.3';
                    $msg->CardType = 'Invalid';
                    $msg->Msg = 'Card Number Invalid.';
                }
                
                
            break;  
        
            case "3":
                $card = $_POST['Card'];
                $cardtype = $_POST['CardType'];
                
                $ubcarddetails = $_MemberCards->getUBCardDetails($card);
                      
                if(!empty($ubcarddetails)){
                    
                        if(APP::getParam('PointSystem') == 2) {
                           $CurrentPoints = $_PcwsWrapper->getCompPoints($card, 0);
                           $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                        }
                        else {
                           $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($card);
                           if(empty($CurrentPoints)) {
                               $CurrentPoints = 0;
                           }
                           else {
                               $CurrentPoints = $CurrentPoints[0]['CurrentPoints'];
                           }
                        }

                        foreach ($ubcarddetails as $arr1) {
                            $membercardID = $arr1['MemberCardID'];
                            $mid = $arr1['MID'];
                            $status = $arr1['Status'];
                            $lifetimepoints = number_format($arr1['LifeTimePoints']);
                            $currentpoints = number_format($CurrentPoints);
                            $redeemedpoints = number_format($arr1['RedeemedPoints']);
                            $bonuspoints = number_format($arr1['BonusPoints']);
                            $datecreated = date("Y-m-d H:i:s",strtotime($arr1['DateCreated']));
                            $siteid = $arr1['SiteID'];
                        }
                        
                        if($status == '1' || $status == '2' || $status == '7' || $status == '9'){
                        
                        $olddetails = $_CardPointsTransfer->getOldUBCard($mid);
                        
                        if(!empty($olddetails)){
                            foreach ($olddetails as $varz) {
                                $oldcardid = $varz['FromOldCardID'];
                            }
                            
                            $oldcard = $_OldCards->getOldCardInfobyOldCardID($oldcardid);
                            
                            $oldcarddetails = ' - Migrated From Loyalty Card '.$oldcard;
                        }
                        else{
                            $oldcarddetails = '';
                        }
                        
                        $sitedetails = $_Sites->getSite($siteid);

                        foreach ($sitedetails as $row) {
                            $sitename = $row['SiteName'];
                        }

                        if($status != 1){

                            $status = $status;


                            switch($status)
                            {
                                case 0: $vstatus = 'InActive';break;
                                case 1: $vstatus = 'Active';    break;
                                case 2: $vstatus = 'Deactivated';break;
                                case 5: $vstatus = 'Active Temporary';break;
                                case 7: $vstatus = 'New Migrated'; break;   
                                case 8: $vstatus = 'Temporary Migrated';  break;
                                case 9: $vstatus = 'Banned';  break;
                                default: $vstatus = 'Card Not Found'; break;
                            }
                            
                            if($vstatus == 'New Migrated' || $vstatus == 'Temporary Migrated' ){
                                $msg->Msg = 'Membership Card is already Migrated';
                                $currentpoints = number_format($arr1['CurrentPoints']);
                            }
                            else{
                                $msg->Msg = 'Card is '.$vstatus;
                            }
                            
                            $msg->IDdetect = '3.1';
                            
                            $msg->CardType = $cardtype;
                            $msg->DateTimeMigration = $datecreated;
                            $msg->Site = $sitename;
                            $msg->LifeTimePoints = $lifetimepoints;
                            $msg->CurrentPoints = $currentpoints;
                            $msg->RedeemedPoints = $redeemedpoints;
                            $msg->BonusPoints = $bonuspoints;

                        }
                        else {

                            $allcarddetails = $_MemberCards->getAllCardDetails($mid);

                            $countallcarddetails = count($allcarddetails);
                            
                            if($countallcarddetails == 1){

                                $msg->IDdetect = '3.2';
                                $msg->Msg = 'Card is Active'.$oldcarddetails;
                                $msg->CardType = $cardtype;
                                $msg->DateTimeMigration = $datecreated;
                                $msg->Site = $sitename;
                                $msg->LifeTimePoints = $lifetimepoints;
                                $msg->CurrentPoints = $currentpoints;
                                $msg->RedeemedPoints = $redeemedpoints;
                                $msg->BonusPoints = $bonuspoints;
                                
                                $migratedtempcarddetails = $_MemberCards->getCardDetailsFromStatus($mid, 8);
                                
                                $fromcardid = $_MemberPointsTransferLog->getFromCardID($membercardID);
                                
                                if(APP::getParam('PointSystem') == 2) {
                                    $CurrentPoints = $_PcwsWrapper->getCompPoints($card, 0);
                                    $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                                }
                                else {
                                    $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($card);
                                    if(empty($CurrentPoints)) {
                                        $CurrentPoints = 0;
                                    }
                                    else {
                                        $CurrentPoints = $CurrentPoints[0]['CurrentPoints'];
                                    }
                                }
                     
                                if(!empty($fromcardid)){
                                    foreach ($fromcardid as $arg) {
                                        $fromcardid = $arg['FromMemberCardID'];
                                        $redcardlifetimepoints = number_format($arg['LifeTimePoints']);
                                        $redcardcurrentpoints = number_format($CurrentPoints);
                                        $redcardredeemedpoints = number_format($arg['RedeemedPoints']);
                                        $redcardbonuspoints = 0;
                                        $redcarddatecreated = date("Y-m-d H:i:s",strtotime($arg['DateTransferred']));
                                    }
                                    $redcarddetails = $_MemberCards->getCardDetailsByMemID($fromcardid);
                                }
                                else {
                                    $redcarddetails = array();
                                }    
                                    

                                if(!empty($migratedtempcarddetails)){
                                    foreach ($migratedtempcarddetails as $mgrtdtemp) {
                                        $migratedcardnumber = $mgrtdtemp['CardNumber'];
                                        $migratedmid = $mgrtdtemp['MID'];
                                        $migratedstatus = $mgrtdtemp['Status'];
                                        $migratedlifetimepoints = number_format($mgrtdtemp['LifeTimePoints']);
                                        $migratedcurrentpoints = number_format($mgrtdtemp['CurrentPoints']);
                                        $migratedredeemedpoints = number_format($mgrtdtemp['RedeemedPoints']);
                                        $migratedbonuspoints = number_format($mgrtdtemp['BonusPoints']);
                                        $migrateddatecreated = date("Y-m-d H:i:s",strtotime($mgrtdtemp['DateCreated']));
                                        $migratedsiteid = $mgrtdtemp['SiteID'];
                                    }

                                    $mgsitedetails = $_Sites->getSite($migratedsiteid);

                                    foreach ($mgsitedetails as $rowz) {
                                        $migratedsitename = $rowz['SiteName'];
                                    }

                                    $msg->Migrated = '1';
                                    $msg->MigratedInfo = 'Migrated From Temporary Card '.$migratedcardnumber;
                                    $msg->MigratedSite = $migratedsitename;
                                    $msg->MigratedDate = $migrateddatecreated;
                                    $msg->MigratedLifeTimePoints = $migratedlifetimepoints;
                                    $msg->MigratedCurrentPoints = $migratedcurrentpoints;
                                    $msg->MigratedRedeemedPoints = $migratedredeemedpoints;
                                    $msg->MigratedBonusPoints = $migratedbonuspoints;


                                }

                                if(!empty($redcarddetails)){
                                    
                                    
                                    foreach ($redcarddetails as $redcard) {
                                        $redcardnumber = $redcard['CardNumber'];
                                        $redcardmid = $redcard['MID'];
                                        $redcardstatus = $redcard['Status'];
                                        $redcardsiteid = $redcard['SiteID'];
                                    }
                                    $redsitedetails = $_Sites->getSite($redcardsiteid);

                                    foreach ($redsitedetails as $rowz2) {
                                        $redcardsitename = $rowz2['SiteName'];
                                    }


                                    $msg->RedCard = '1';
                                    $msg->RedCardInfo = 'Transferred From '.$redcardnumber;
                                    $msg->RedCardSite = $redcardsitename;
                                    $msg->RedCardDate = $redcarddatecreated;
                                    $msg->RedCardLifeTimePoints = $redcardlifetimepoints;
                                    $msg->RedCardCurrentPoints = $redcardcurrentpoints;
                                    $msg->RedCardRedeemedPoints = $redcardredeemedpoints;
                                    $msg->RedCardBonusPoints = $redcardbonuspoints;
                                }

                            }
                            else{
                                $msg->IDdetect = '3.3';
                                $msg->Msg = 'Card is Active'.$oldcarddetails;
                                $msg->CardType = $cardtype;
                                $msg->DateTimeMigration = $datecreated;
                                $msg->Site = $sitename;
                                $msg->LifeTimePoints = $lifetimepoints;
                                $msg->CurrentPoints = $currentpoints;
                                $msg->RedeemedPoints = $redeemedpoints;
                                $msg->BonusPoints = $bonuspoints;

                                $migratedtempcarddetails = $_MemberCards->getCardDetailsFromStatus($mid, 8);
                                
                                $fromcardid = $_MemberPointsTransferLog->getFromCardID($membercardID);
                                
                                if(APP::getParam('PointSystem') == 2) {
                                    $CurrentPoints = $_PcwsWrapper->getCompPoints($card, 0);
                                    $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                                }
                                else {
                                    $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($card);
                                    if(empty($CurrentPoints)) {
                                        $CurrentPoints = 0;
                                    }
                                    else {
                                        $CurrentPoints = $CurrentPoints[0]['CurrentPoints'];
                                    }
                                }
                      

                                if(!empty($fromcardid)){
                                    foreach ($fromcardid as $arg) {
                                        $fromcardid = $arg['FromMemberCardID'];
                                        $redcardlifetimepoints = number_format($arg['LifeTimePoints']);
                                        $redcardcurrentpoints = number_format($CurrentPoints);
                                        $redcardredeemedpoints = number_format($arg['RedeemedPoints']);
                                        $redcardbonuspoints = 0;
                                        $redcarddatecreated = date("Y-m-d H:i:s",strtotime($arg['DateTransferred']));
                                    }
                                    $redcarddetails = $_MemberCards->getCardDetailsByMemID($fromcardid);
                                }
                                else {
                                    $redcarddetails = array();
                                }    
                                    
                                if(APP::getParam('PointSystem') == 2) {
                                    $CurrentPoints = $_PcwsWrapper->getCompPoints($card, 0);
                                    $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                                }
                                else {
                                    $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($card);
                                    if(empty($CurrentPoints)) {
                                        $CurrentPoints = 0;
                                    }
                                    else {
                                        $CurrentPoints = $CurrentPoints[0]['CurrentPoints'];
                                    }
                                }
                                 
                                if(!empty($migratedtempcarddetails)){
                                    foreach ($migratedtempcarddetails as $mgrtdtemp) {
                                        $migratedcardnumber = $mgrtdtemp['CardNumber'];
                                        $migratedmid = $mgrtdtemp['MID'];
                                        $migratedstatus = $mgrtdtemp['Status'];
                                        $migratedlifetimepoints = number_format($mgrtdtemp['LifeTimePoints']);
                                        $migratedcurrentpoints = number_format($mgrtdtemp['CurrentPoints']);
                                        $migratedredeemedpoints = number_format($mgrtdtemp['RedeemedPoints']);
                                        $migratedbonuspoints = number_format($mgrtdtemp['BonusPoints']);
                                        $migrateddatecreated = date("Y-m-d H:i:s",strtotime($mgrtdtemp['DateCreated']));
                                        $migratedsiteid = $mgrtdtemp['SiteID'];
                                    }

                                    $mgsitedetails = $_Sites->getSite($migratedsiteid);

                                    foreach ($mgsitedetails as $rowz) {
                                        $migratedsitename = $rowz['SiteName'];
                                    }

                                    $msg->Migrated = '1';
                                    $msg->MigratedInfo = 'Migrated From Temporary Card '.$migratedcardnumber;
                                    $msg->MigratedSite = $migratedsitename;
                                    $msg->MigratedDate = $migrateddatecreated;
                                    $msg->MigratedLifeTimePoints = $migratedlifetimepoints;
                                    $msg->MigratedCurrentPoints = $migratedcurrentpoints;
                                    $msg->MigratedRedeemedPoints = $migratedredeemedpoints;
                                    $msg->MigratedBonusPoints = $migratedbonuspoints;


                                }

                                if(!empty($redcarddetails)){
                                    
                                    
                                    foreach ($redcarddetails as $redcard) {
                                        $redcardnumber = $redcard['CardNumber'];
                                        $redcardmid = $redcard['MID'];
                                        $redcardstatus = $redcard['Status'];
                                        $redcardsiteid = $redcard['SiteID'];
                                    }
                                    $redsitedetails = $_Sites->getSite($redcardsiteid);

                                    foreach ($redsitedetails as $rowz2) {
                                        $redcardsitename = $rowz2['SiteName'];
                                    }


                                    $msg->RedCard = '1';
                                    $msg->RedCardInfo = 'Transferred From '.$redcardnumber;
                                    $msg->RedCardSite = $redcardsitename;
                                    $msg->RedCardDate = $redcarddatecreated;
                                    $msg->RedCardLifeTimePoints = $redcardlifetimepoints;
                                    $msg->RedCardCurrentPoints = $redcardcurrentpoints;
                                    $msg->RedCardRedeemedPoints = $redcardredeemedpoints;
                                    $msg->RedCardBonusPoints = $redcardbonuspoints;
                                }


                            }
                        }
                        
                        }
                        else{
                            $msg->IDdetect = '1.3';
                            $msg->CardType = 'Invalid';
                            $msg->Msg = 'Card Number Invalid.';
                        }
                }
                else{
                    $msg->IDdetect = '1.3';
                    $msg->CardType = 'Invalid';
                    $msg->Msg = 'Card Number Invalid.';
                }
                
            break;  
        }
        
        echo json_encode($msg);
        
        
        break;    
    
    
        case "SearchMigrate2":
       
            $email = $_POST['Email'];
            $cardtype = 3;
           
                if($email == ''){
                    
                    $msg->IDdetect = '1.3';
                    $msg->CardType = 'Invalid';
                    $msg->Msg = 'Enter Valid Email Address';
                    
                }
                else{
                     if($email != ''){
                        $midbyemail = $_MemberInfo->getMIDByEmailSP($email);
                        if(!empty($midbyemail)){
                            foreach ($midbyemail as $value1) {
                                $mid2 = $value1['MID'];
                            }
                            $mid1 = '';
                        }
                        else{
                            $mid1 = '';
                            $mid2 = '';
                        }
                        
                        
                    }
                    
                    if($mid1 != ''){
                        $mid = $mid1;
                    }else{
                        $mid = $mid2;
                    }
                    $oldcarddetails = '';

                        $allcarddetails = $_MemberCards->getAllCardDetails($mid);
                        $cardz = $_MemberCards->getCardDetailsFromStatus($mid, 5);
                        
                        if(!empty($allcarddetails)){
                            $countallcarddetails = count($allcarddetails);
                            
                            $card1 = $_MemberCards->getCardDetailsFromStatus($mid, 1);

                            if(!empty($card1)){
                                foreach ($card1 as $vari) {
                                    $card = $vari['CardNumber'];
                                }
                            }
                            else{
                                $card = '';

                            }
                            $ubcarddetails = $_MemberCards->getUBCardDetails($card);
                            
                        if(APP::getParam('PointSystem') == 2) {
                            $CurrentPoints = $_PcwsWrapper->getCompPoints($card, 0);
                            $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                        }
                        else {
                            $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($card);
                            if(empty($CurrentPoints)) {
                                $CurrentPoints = 0;
                            }
                            else {
                                $CurrentPoints = $CurrentPoints[0]['CurrentPoints'];
                            }
                        }
                        if(!empty($ubcarddetails)){
                           foreach ($ubcarddetails as $arr1) {
                                    $membercardID = $arr1['MemberCardID'];
                                    $mid = $arr1['MID'];
                                    $status = $arr1['Status'];
                                    $lifetimepoints = number_format($arr1['LifeTimePoints']);
                                    $currentpoints = number_format($arr1['CurrentPoints']);
                                    $redeemedpoints = number_format($CurrentPoints);
                                    $bonuspoints = number_format($arr1['BonusPoints']);
                                    $datecreated = date("Y-m-d H:i:s",  strtotime($arr1['DateCreated']));
                                    $siteid = $arr1['SiteID'];
                                }

                                $cardtype = 3;

                                $sitedetails = $_Sites->getSite($siteid);

                                foreach ($sitedetails as $row) {
                                    $sitename = $row['SiteName'];
                                }
                                
                                $olddetails = $_CardPointsTransfer->getOldUBCard($mid);
                                
                                if(!empty($olddetails)){
                                    foreach ($olddetails as $varz) {
                                        $oldcardid = $varz['FromOldCardID'];
                                    }

                                    $oldcard = $_OldCards->getOldCardInfobyOldCardID($oldcardid);

                                    $oldcarddetails = ' - Migrated From Loyalty Card '.$oldcard;
                                }
                                else{
                                    $oldcarddetails = '';
                                }
                        
                        }
                        else{
                            $msg->IDdetect = '1.3';
                            $msg->CardType = 'Invalid';
                            $msg->Msg = 'Invalid Email Address.';
                        }

                                $msg->IDdetect = '3.3';
                                $msg->Msg = 'Card is Active - '.$card.$oldcarddetails;
                                $msg->CardType = $cardtype;
                                $msg->DateTimeMigration = $datecreated;
                                $msg->Site = $sitename;
                                $msg->LifeTimePoints = $lifetimepoints;
                                $msg->CurrentPoints = $currentpoints;
                                $msg->RedeemedPoints = $redeemedpoints;
                                $msg->BonusPoints = $bonuspoints;

                                $migratedtempcarddetails = $_MemberCards->getCardDetailsFromStatus($mid, 8);
                                
                                 $fromcardid = $_MemberPointsTransferLog->getFromCardID($membercardID);
                                
                                if(APP::getParam('PointSystem') == 2) {
                                    $CurrentPoints = $_PcwsWrapper->getCompPoints($card, 0);
                                    $CurrentPoints = $CurrentPoints['GetCompPoints']['CompBalance'];
                                }
                                else {
                                    $CurrentPoints = $_MemberCards->getCurrentPointsByCardNumber($card);
                                    if(empty($CurrentPoints)) {
                                        $CurrentPoints = 0;
                                    }
                                    else {
                                        $CurrentPoints = $CurrentPoints[0]['CurrentPoints'];
                                    }
                                }
                                
                                if(!empty($fromcardid)){
                                    foreach ($fromcardid as $arg) {
                                        $fromcardid = $arg['FromMemberCardID'];
                                        $redcardlifetimepoints = number_format($arg['LifeTimePoints']);
                                        $redcardcurrentpoints = number_format($CurrentPoints);
                                        $redcardredeemedpoints = number_format($arg['RedeemedPoints']);
                                        $redcardbonuspoints = 0;
                                        $redcarddatecreated = date("Y-m-d H:i:s",strtotime($arg['DateTransferred']));
                                    }
                                    $redcarddetails = $_MemberCards->getCardDetailsByMemID($fromcardid);
                                }
                                else {
                                    $redcarddetails = array();
                                }

                                if(!empty($migratedtempcarddetails)){
                                    foreach ($migratedtempcarddetails as $mgrtdtemp) {
                                        $migratedcardnumber = $mgrtdtemp['CardNumber'];
                                        $migratedmid = $mgrtdtemp['MID'];
                                        $migratedstatus = $mgrtdtemp['Status'];
                                        $migratedlifetimepoints = number_format($mgrtdtemp['LifeTimePoints']);
                                        $migratedcurrentpoints = number_format($CurrentPoints);
                                        $migratedredeemedpoints = number_format($mgrtdtemp['RedeemedPoints']);
                                        $migratedbonuspoints = number_format($mgrtdtemp['BonusPoints']);
                                        $migrateddatecreated = date("Y-m-d H:i:s",strtotime($mgrtdtemp['DateCreated']));
                                        $migratedsiteid = $mgrtdtemp['SiteID'];
                                    }

                                    $mgsitedetails = $_Sites->getSite($migratedsiteid);

                                    foreach ($mgsitedetails as $rowz) {
                                        $migratedsitename = $rowz['SiteName'];
                                    }

                                    $msg->Migrated = '1';
                                    $msg->MigratedInfo = 'Migrated From Temporary Card '.$migratedcardnumber;
                                    $msg->MigratedSite = $migratedsitename;
                                    $msg->MigratedDate = $migrateddatecreated;
                                    $msg->MigratedLifeTimePoints = $migratedlifetimepoints;
                                    $msg->MigratedCurrentPoints = $migratedcurrentpoints;
                                    $msg->MigratedRedeemedPoints = $migratedredeemedpoints;
                                    $msg->MigratedBonusPoints = $migratedbonuspoints;


                                }


                                if(!empty($redcarddetails)){
                                    foreach ($redcarddetails as $redcard) {
                                        $redcardnumber = $redcard['CardNumber'];
                                        $redcardmid = $redcard['MID'];
                                        $redcardstatus = $redcard['Status'];
                                        $redcardsiteid = $redcard['SiteID'];
                                    }
                                    $redsitedetails = $_Sites->getSite($redcardsiteid);

                                    foreach ($redsitedetails as $rowz2) {
                                        $redcardsitename = $rowz2['SiteName'];
                                    }


                                    $msg->RedCard = '1';
                                    $msg->RedCardInfo = 'Transferred From '.$redcardnumber;
                                    $msg->RedCardSite = $redcardsitename;
                                    $msg->RedCardDate = $redcarddatecreated;
                                    $msg->RedCardLifeTimePoints = $redcardlifetimepoints;
                                    $msg->RedCardCurrentPoints = $redcardcurrentpoints;
                                    $msg->RedCardRedeemedPoints = $redcardredeemedpoints;
                                    $msg->RedCardBonusPoints = $redcardbonuspoints;
                                }


                            
                        }
                        elseif(!empty ($cardz)){
                            $countallcarddetails = count($cardz);
                                
                            $card1 = $_MemberCards->getCardDetailsFromStatus($mid, 5);

                            if(!empty($card1)){
                                foreach ($card1 as $vari) {
                                    $card = $vari['CardNumber'];
                                }
                            }
                            else{
                                $card = '';

                            }
                            $ubcarddetails = $_MemberCards->getUBCardDetails($card);

                        if(!empty($ubcarddetails)){
                           foreach ($ubcarddetails as $arr1) {
                                    $membercardID = $arr1['MemberCardID'];
                                    $mid = $arr1['MID'];
                                    $status = $arr1['Status'];
                                    $lifetimepoints = number_format($arr1['LifeTimePoints']);
                                    $currentpoints = number_format($CurrentPoints);
                                    $redeemedpoints = number_format($arr1['RedeemedPoints']);
                                    $bonuspoints = number_format($arr1['BonusPoints']);
                                    $datecreated = date("Y-m-d H:i:s",  strtotime($arr1['DateCreated']));
                                    $siteid = $arr1['SiteID'];
                                }

                                $cardtype = 3;

                                $sitedetails = $_Sites->getSite($siteid);

                                foreach ($sitedetails as $row) {
                                    $sitename = $row['SiteName'];
                                }
                        }
                        else{
                            $msg->IDdetect = '1.3';
                            $msg->CardType = 'Invalid';
                            $msg->Msg = 'Invalid Email Address.';
                        }
                        
                            if($countallcarddetails == 1){

                                $msg->IDdetect = '3.2';
                                $msg->Msg = 'Card is Active - '.$card.$oldcarddetails;
                                $msg->CardType = $cardtype;
                                $msg->DateTimeMigration = $datecreated;
                                $msg->Site = $sitename;
                                $msg->LifeTimePoints = $lifetimepoints;
                                $msg->CurrentPoints = $currentpoints;
                                $msg->RedeemedPoints = $redeemedpoints;
                                $msg->BonusPoints = $bonuspoints;

                            }
                            else{
                                $msg->IDdetect = '3.3';
                                $msg->Msg = 'Card is Active - '.$card.$oldcarddetails;
                                $msg->CardType = $cardtype;
                                $msg->DateTimeMigration = $datecreated;
                                $msg->Site = $sitename;
                                $msg->LifeTimePoints = $lifetimepoints;
                                $msg->CurrentPoints = $currentpoints;
                                $msg->RedeemedPoints = $redeemedpoints;
                                $msg->BonusPoints = $bonuspoints;

                                $migratedtempcarddetails = $_MemberCards->getCardDetailsFromStatus($mid, 8);
                                
                                 $fromcardid = $_MemberPointsTransferLog->getFromCardID($membercardID);
                                
                                if(!empty($fromcardid)){
                                    foreach ($fromcardid as $arg) {
                                        $fromcardid = $arg['FromMemberCardID'];
                                        $redcardlifetimepoints = number_format($arg['LifeTimePoints']);
                                        $redcardcurrentpoints = number_format($CurrentPoints);
                                        $redcardredeemedpoints = number_format($arg['RedeemedPoints']);
                                        $redcardbonuspoints = 0;
                                        $redcarddatecreated = date("Y-m-d H:i:s",strtotime($arg['DateTransferred']));
                                    }
                                    $redcarddetails = $_MemberCards->getCardDetailsByMemID($fromcardid);
                                }
                                else {
                                    $redcarddetails = array();
                                }

                                if(!empty($migratedtempcarddetails)){
                                    foreach ($migratedtempcarddetails as $mgrtdtemp) {
                                        $migratedcardnumber = $mgrtdtemp['CardNumber'];
                                        $migratedmid = $mgrtdtemp['MID'];
                                        $migratedstatus = $mgrtdtemp['Status'];
                                        $migratedlifetimepoints = number_format($mgrtdtemp['LifeTimePoints']);
                                        $migratedcurrentpoints = number_format($CurrentPoints);
                                        $migratedredeemedpoints = number_format($mgrtdtemp['RedeemedPoints']);
                                        $migratedbonuspoints = number_format($mgrtdtemp['BonusPoints']);
                                        $migrateddatecreated = date("Y-m-d H:i:s",strtotime($mgrtdtemp['DateCreated']));
                                        $migratedsiteid = $mgrtdtemp['SiteID'];
                                    }

                                    $mgsitedetails = $_Sites->getSite($migratedsiteid);

                                    foreach ($mgsitedetails as $rowz) {
                                        $migratedsitename = $rowz['SiteName'];
                                    }

                                    $msg->Migrated = '1';
                                    $msg->MigratedInfo = 'Migrated From Temporary Card '.$migratedcardnumber;
                                    $msg->MigratedSite = $migratedsitename;
                                    $msg->MigratedDate = $migrateddatecreated;
                                    $msg->MigratedLifeTimePoints = $migratedlifetimepoints;
                                    $msg->MigratedCurrentPoints = $migratedcurrentpoints;
                                    $msg->MigratedRedeemedPoints = $migratedredeemedpoints;
                                    $msg->MigratedBonusPoints = $migratedbonuspoints;


                                }


                                if(!empty($redcarddetails)){
                                    foreach ($redcarddetails as $redcard) {
                                        $redcardnumber = $redcard['CardNumber'];
                                        $redcardmid = $redcard['MID'];
                                        $redcardstatus = $redcard['Status'];
                                        $redcardsiteid = $redcard['SiteID'];
                                    }
                                    $redsitedetails = $_Sites->getSite($redcardsiteid);

                                    foreach ($redsitedetails as $rowz2) {
                                        $redcardsitename = $rowz2['SiteName'];
                                    }


                                    $msg->RedCard = '1';
                                    $msg->RedCardInfo = 'Transferred From '.$redcardnumber;
                                    $msg->RedCardSite = $redcardsitename;
                                    $msg->RedCardDate = $redcarddatecreated;
                                    $msg->RedCardLifeTimePoints = $redcardlifetimepoints;
                                    $msg->RedCardCurrentPoints = $redcardcurrentpoints;
                                    $msg->RedCardRedeemedPoints = $redcardredeemedpoints;
                                    $msg->RedCardBonusPoints = $redcardbonuspoints;
                                }


                            }
                        }
                        else{
                             $CurrentPoints = null;
                                
                             $card1 = $_MemberCards->getCardDetailsFromStatus($mid, 9);
                             
                             if(!empty($card1)){
                                 foreach ($card1 as $vari) {
                                    $card = $vari['CardNumber'];
                                }
                             }
                             else{
                                 $card1 = $_MemberCards->getCardDetailsFromStatus($mid, 8);
                             
                                if(!empty($card1)){
                                    foreach ($card1 as $vari) {
                                       $card = $vari['CardNumber'];
                                   }
                                }
                                else{
                                    $card1 = $_MemberCards->getCardDetailsFromStatus($mid, 7);
                             
                                    if(!empty($card1)){
                                        foreach ($card1 as $vari) {
                                           $card = $vari['CardNumber'];
                                       }
                                    }
                                    else{
                                        $card = '';
                                    }
                                }
                             }
                                
                                
                                $ubcarddetails = $_MemberCards->getUBCardDetails($card);
                                
                             if(!empty($ubcarddetails)){
                           foreach ($ubcarddetails as $arr1) {
                                    $membercardID = $arr1['MemberCardID'];
                                    $mid = $arr1['MID'];
                                    $status = $arr1['Status'];
                                    $lifetimepoints = number_format($arr1['LifeTimePoints']);
                                    $currentpoints = number_format($CurrentPoints);
                                    $redeemedpoints = number_format($arr1['RedeemedPoints']);
                                    $bonuspoints = number_format($arr1['BonusPoints']);
                                    $datecreated = date("Y-m-d H:i:s",  strtotime($arr1['DateCreated']));
                                    $siteid = $arr1['SiteID'];
                                }

                                $cardtype = 0;

                                $sitedetails = $_Sites->getSite($siteid);

                                foreach ($sitedetails as $row) {
                                    $sitename = $row['SiteName'];
                                }
                                
                                switch($status)
                            {
                                case 0: $vstatus = 'InActive';break;
                                case 1: $vstatus = 'Active';    break;
                                case 2: $vstatus = 'Deactivated';break;
                                case 5: $vstatus = 'Active Temporary';break;
                                case 7: $vstatus = 'New Migrated'; break;   
                                case 8: $vstatus = 'Temporary Migrated';  break;
                                case 9: $vstatus = 'Banned';  break;
                                default: $vstatus = 'Card Not Found'; break;
                            }
                            
                            if($vstatus == 'New Migrated' || $vstatus == 'Temporary Migrated' ){
                                    $msg->Msg = 'Membership Card '.$card.' is already Migrated.';
                                }
                                else{
                                    $msg->Msg = 'Card '.$card.' is '.$vstatus;
                                }

                                $msg->IDdetect = '3.1';

                                $msg->CardType = $cardtype;
                                $msg->DateTimeMigration = $datecreated;
                                $msg->Site = $sitename;
                                $msg->LifeTimePoints = $lifetimepoints;
                                $msg->CurrentPoints = $currentpoints;
                                $msg->RedeemedPoints = $redeemedpoints;
                                $msg->BonusPoints = $bonuspoints;
                            }
                            else{
                                $msg->IDdetect = '1.3';
                                $msg->CardType = 'Invalid';
                                $msg->Msg = 'Invalid Email Address.';
                            }
                            
                          
                        }
                }
                
                
            echo json_encode($msg);
        break;    
    }
}
?>
