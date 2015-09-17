<?php

/**
 * Description of batchplayerclassificationmapping
 *
 * @author jdlachica & fdlsison
 * @date 08/29/2014
 */

require_once("../init.inc.php");
include('sessionmanager.php');
include('batchPCLogger.php');

$msg = '';

$pagetitle = "Batch Player Classification Mapping";
$currentpage = "Administration";

//App::LoadCore("CSV.class.php");
//App::LoadCore("File.class.php");

App::LoadModuleClass("Membership", "Members");
App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Membership", "Helper");
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Membership", "MemberServices");
App::LoadModuleClass("CasinoProvider", "CasinoAPI");
App::LoadModuleClass("CasinoProvider", "RealtimeGamingCashierAPI2");
App::LoadModuleClass("Membership", "AuditTrail");
App::LoadModuleClass("Kronus", "TerminalSessions");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Kronus", "EgmSessions");
App::LoadModuleClass("Admin", "AccessRights");
App::LoadModuleClass("Admin", "AccountSessions");
App::LoadModuleClass("Kronus", "Accounts");
App::LoadModuleClass("Kronus", "AccountDetails");
App::LoadModuleClass("Kronus", "CasinoServices");
App::LoadCore("PHPMailer.class.php");

App::LoadControl("Button");
App::LoadCOntrol("ComboBox");

$members = new Members();
$memberCards = new MemberCards();
$memberServices = new MemberServices();
$logger = new batchPCLogger();
$casinoAPI = new CasinoAPI();
$auditTrail = new AuditTrail();
$terminalSessions = new TerminalSessions();
$auditFunctions = new AuditFunctions();
$egmSessions = new EgmSessions();
$_AccountSessions = new AccountSessions();
$_CasinoServices = new CasinoServices();
$formCsvUpload = new FormsProcessor();

$casinoservice = new ComboBox("casinoservice","casinoservice","Casino Service: ");
$casinoservice->ShowCaption = true;
$casinoservices = $_CasinoServices->getUserBasedCasinoServices();
$alftnlist = new ArrayList();
$alftnlist->AddArray($casinoservices);
$casinoservice->ClearItems();
$litem = null;
$litem[] = new ListItem("Select One", "-1", true);
$casinoservice->Items = $litem;
$casinoservice->DataSource = $alftnlist;
$casinoservice->DataSourceText = "ServiceName";
$casinoservice->DataSourceValue = "ServiceID";
$casinoservice->DataBind();
$formCsvUpload->AddControl($casinoservice);

$btnUpload = new Button('btnUpload', 'btnUpload', 'Upload');
$btnUpload->ShowCaption = true;
$btnUpload->IsSubmit = false;

$formCsvUpload->AddControl($btnUpload);

$formCsvUpload->ProcessForms();


function ubCardExists($string, $array) {
    $occurrence = 0;
    for($i=0;$i<count($array);$i++){
        $Value = $array[$i];
        if($Value==$string){$occurrence++;}
    }
    $occurrence>0?$Validity=true:$Validity=false; //echo $Validity;
    //return $occurrence;
    return $Validity;
    
   
}

function showDialog($Title, $Message){
    $DialogTitle = array('Success','Error');
    echo "<script>showDialog(".'"'.$DialogTitle[$Title].'"'.",".'"'.$Message.'"'.");</script>";
}

function sendEmail($email,$name, $message=''){
    $email = stripNumbersInEmail($email);
    $mailer = new PHPMailer();
    $mailer->AddAddress($email, $name);
    $mailer->IsHTML(true);

    $mailer->Body = "Hi <label style='font-style: italic;'>$name</label>,<br />";
    $mailer->Body .="\r\n\r\n".$message;

    $mailer->From = "membership@egamescasino.ph";
    $mailer->FromName = "PhilWeb Membership";
    $mailer->Host = "localhost";
    $mailer->Subject = "Batch Player Classification Notification";
    $mailer->Send();
}
function emailUser($MID,$VIPLevel){
    if(isset($MID)){
        $_Helper = new Helper();
        $memberInfo = new MemberInfo();
        $EmailAddress = $memberInfo->getEmail($MID);
        if(isset($EmailAddress)){
            if($EmailAddress!=''){
                $Result = $memberInfo->getFirstNameByMID($MID);
                
                //Check Name validity 
                if($Result[0]['FirstName'] != "" || $Result[0]['FirstName'] != NULL){
                    if($Result[0]['LastName'] != "" || $Result[0]['LastName'] != NULL){
                        $Recipient = $Result[0]['FirstName']." ".$Result[0]['LastName'];
                    }else{ $Recipient = $Result[0]['FirstName']; }
                }else{
                    if($Result[0]['LastName'] != "" || $Result[0]['LastName'] != NULL){
                        $Recipient =$Result[0]['LastName'];
                    }else{ $Recipient="Unknown"; }
                }
                
                if(isset($Recipient)){
                    if($EmailAddress!=''){
                        switch ($VIPLevel) {
                            case 1:
                                $PClassName = "VIP";
                                break;
                            case 2:
                                $PClassName = "Classic";
                                break;
                            default:
                                $PClassName = "Regular";
                                break;
                        }
                        $_Helper->sendPlayerNotification($EmailAddress, $Recipient, $PClassName);
                    }
                }
            }
        }
    }
}

function sendErrorViaEmail($AID, $Message='', $FileName){
    $Details = getAccountDetails($AID);
    if($Message!=''){
        if(isset($Details)){
            $Email = trim($Details['Email']);
            $Name = trim($Details['Name']);
            $NewLine = "<br/>";
            $Headers = "$NewLine Copy of errors encountered in updating player classification.$NewLine $NewLine Filename: $FileName $NewLine Date Uploaded: ".date("Y-m-d H:i:s")."$NewLine";
            sendEmail($Email, $Name, $Headers.$Message);
        }
    }
}

function stripNumbersInEmail($Value){
    $Email = trim($Value);
    $index = (strpos($Email, '@')+1);
    $EmailPrefix = substr($Email, 0,$index);
    $EmailSuffix = preg_replace("/[^a-z-.]+/i", "",substr($Email,$index,strlen($Email)));
    return $EmailPrefix.$EmailSuffix;
}


function getAccountDetails($AID){
    $AccountDetails = new AccountDetails();
    $Details = array('Name'=>null,'Email'=>null);
    
    if(isset($AID)){
        $Result = $AccountDetails->getNameAndEmailByAID($AID);
        if(isset($Result)){
            $Details['Email'] = $Result[0]['Email'];
            $Details['Name'] = $Result[0]['Name'];
        }
    }
    
    return $Details;
}

function uDateTime($format, $utimestamp = null) {
        if (is_null($utimestamp))
            $utimestamp = microtime(true);

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }

function logError($CompiledLogMessages='',$IsTransactionCompleted=false, $filename='', $dateStarted=''){
    $logger = new batchPCLogger();
   
    if($IsTransactionCompleted===true){
       
        $dateEnded = date("Y-m-d H:i:s");

        $logMessage = "\r\n";
        $logMessage .= "----------------------------------------";
        $logMessage .= "\r\n";
        $logMessage .= "Error Logs\r\n";
        $logMessage .= "\r\n";
        $logMessage .= "Filename: $filename\r\n";
        $logMessage .= "\r\n";
        $logMessage .= "Date Started: $dateStarted\r\n";
        $logMessage .= "\r\n";
        $logMessage .= "Date Ended: $dateEnded\r\n";
        $logMessage .= "\r\n";
        $logMessage .= $CompiledLogMessages;
        $logger->log("", "", $logMessage);
       
    }
   
}

function compileUBCardToArray($arr){
    $compiledArray = array();
    foreach($arr as $Value){
        $UBCard = trim($Value['UBCard']);
        array_push($compiledArray,$UBCard);
    }
    return $compiledArray;
}

function checkSession(){
    $Validity = 0;
    if(isset($_SESSION['userinfo']) && is_array($_SESSION['userinfo']) && count($_SESSION['userinfo']) > 0)
    {
        if(isset($_SESSION['sessionID'])){
            $sessionid = $_SESSION['sessionID'];
            $aid = $_SESSION['aID'];
            $Validity=1;
        }
        else{
            $sessionid = 0;
            $aid = 0;
            $Validity=0;
        }
    }
    return $Validity;
}

function generateMessage($UBCard, $Message, $num){
    return "<br/>$num".'. UBCard: '.$UBCard.'- ErrorMessage: '.$Message;
}

if($formCsvUpload->IsPostBack) {
    if(isset($_POST['CheckSession'])){
        if($_POST['CheckSession']==true){
            echo checkSession();
        }
    }else{
        $serviceID = $_POST['casinoservice'];
        
        if($btnUpload->SubmittedValue == 'Upload') {
            if(isset($_SESSION['userinfo'])
                && is_array($_SESSION['userinfo'])
                && count($_SESSION['userinfo']) > 0)
            {
                if(isset($_SESSION['sessionID'])){
                    $sessionid = $_SESSION['sessionID'];
                    $aid = $_SESSION['aID'];
                }
                else{
                    $sessionid = 0;
                    $aid = 0;
            }
                //Check restricted page



                $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);
                foreach ($sessioncount as $value) {
                    foreach ($value as $value2) {
                        $sessioncount = $value2['Count'];
                    }
                }


    //            $page = 'login.php';
    //            App::pr("<script> window.location = '$page'; </script>");
                if($sessioncount > 0) {
             //print_r('here');
                    if($_FILES['fileUpload1']['type'][0] == '') {
                        $logMessage = "\r\nError Msg: Please specify file.\r\n";
                        //$logger->log($logger->logdate, " [0] ", $logMessage);
                        $CompiledLogMessages .=$logMessage;
                        //$errorMsgTitle = 'ERROR!';
                    }
                    else if(($_FILES['fileUpload1']['type'][0] == 'application/octet-stream') || ($_FILES['fileUpload1']['type'][0] == 'text/csv') || ($_FILES['fileUpload1']['type'][0] == 'application/vnd.ms-excel') || ($_FILES['fileUpload1']['type'][0] == 'text/x-comma-separated-values')) {
                        $filename = $_FILES['fileUpload1']['name'][0];
                        $fullPath = 'csv/'.$filename;

                        //sendEmail("jeremiahlachica@outlook.com",'Ogie Pogz');
                        //showDialog(1,'Testing again!');
                        $retVal = move_uploaded_file($_FILES['fileUpload1']['tmp_name'][0], $fullPath);
                        //showDialog(1, 'error');
                        if(!$retVal) {
                            $logMessage = "\r\nError Msg: Failed to log event.\r\n";
                            //$logger->log($logger->logdate, " [0] ", $logMessage);
                            $CompiledLogMessages .=$logMessage;
                            //$errorMsgTitle = 'ERROR!';
                        }
                        else {
                            //$dateStarted = $logger->logdate;
                            $dateStarted = date("Y-m-d H:i:s");
                            //$fileName = 'bpcm.csv';


                            $csvStr = trim(file_get_contents($fullPath));

                            $csvData = explode("\n", $csvStr);
                            $countCsv = count($csvData);
                            //$countCsv = $countCsv+1;
                            //print_r($countCsv);
                            //$arrEntries = array();
                            //app::pr($countCsv);

                            for ($ctr = 1; $ctr < $countCsv; $ctr++) {
                                $entry1 = '';
                                $csvRow = $csvData[$ctr];
                                $array = explode(',', $csvRow);
                                //$arrUBCard[] = trim($array[0]);
                                //$arrIsVIP[] = trim($array[1]);
                                //$arrVIPLevel[] = trim($array[1]);
                                //$arrToBeEmailed[] = trim($array[2]);
                                $entry1['UBCard'] = trim($array[0]);
                                //$entry1['IsVIP'] = trim($array[1]);
                                $entry1['VIPLevel'] = trim($array[1]);
                                $entry1['ToBeEmailed'] = trim($array[2]);
                                $arrEntries1[] = $entry1;
                            }

                            //app::pr($array);

                            //app::Pr($arrEntries1);

                            //print_r($arrEntries1);



            //                if(hasDuplicate($arrUBCard) || hasDuplicate($arrIsVIP) || hasDuplicate($arrVIPLevel) || hasDuplicate($arrToBeEmailed)) {
            //                    $msg = "Invalid file content. Please upload a valid csv file.";
            //                    //$errorMsgTitle = "ERROR!";
            //                    unset($arrUBCard);
            //                    unset($arrIsVIP);
            //                    unset($arrVIPLevel);
            //                    unset($arrToBeEmailed);
            //                }
                            //else {

                            $ctr=0;
                            $num=0;
                            $ArraySize=(count($arrEntries1)-1);
                            $CompiledLogMessages = '';
                            $ErrorMessageToBeEmailed = '';
                            $arrUBCard = compileUBCardToArray($arrEntries1);
                            $listUBCard  = array();
                            $AID = $_SESSION['userinfo']['AID'];
                            //print_r($arrEntries1);
                            foreach($arrEntries1 as $Key=>$Value){
                                
                                $UBCard = trim($Value['UBCard']);
                                $VIPLevel =  trim($Value['VIPLevel']);
                                $ToBeEmailed = trim($Value['ToBeEmailed']);

                                //$occurrence = hasDuplicate($UBCard, $arrUBCard);

                                if($UBCard !='' && $VIPLevel !='' && $ToBeEmailed !=''){
                                    //if(hasDuplicate($UBCard, $arrUBCard) == FALSE){
                                        $memberCardStatus = $memberCards->getStatusByCard($UBCard);
                                        //app::Pr($memberCardStatus);
                                        if(isset($memberCardStatus) && $memberCardStatus!=null){
                                            if($VIPLevel == 0 || $VIPLevel == 1 || $VIPLevel == 2) {
                                                if($ToBeEmailed == 0 || $ToBeEmailed == 1) {
                                                    $Status = $memberCardStatus[0]['Status'];
                                                    if($Status==1){

                                                        if(ubCardExists($UBCard,$listUBCard)==false){
                                                            $memberCardMID = $memberCards->getMIDByCard($UBCard);
                                                            $MID = $memberCardMID[0]['MID'];
                                                            $ctrEGMSessions = $egmSessions->checkForEGMSession($MID);
                                                            $EGMSession = $ctrEGMSessions[0]['ctrEGMSessions'];
                                                            if($EGMSession==0){
                                                                $ctrTerminalSessions = $terminalSessions->checkForTerminalSession($UBCard);

                                                                $TerminalSessions=$ctrTerminalSessions[0]['ctrTerminalSessions']; 
                                                                if($TerminalSessions==0){
                                                                    //$memberServicesArr = $memberServices->getCasinoAccountsByMIDAndServiceID($MID, 19);
                                                                    $memberServicesArr = $memberServices->getCasinoAccountsByMIDAndServiceID($MID, $serviceID);
//                                                                    var_dump($memberServicesArr);exit;
                                                                    if(!empty($memberServicesArr)) {
                                                                        $serviceUsername = $memberServicesArr[0]['ServiceUsername'];
                                                                        $serverID = $memberServicesArr[0]['ServiceID'];
                                                                    } else {
                                                                        $serviceUsername = '';
                                                                    }
                                                                    if($serviceUsername!=null || $serviceUsername!=''){

                                                                        $serviceAPI = App::getParam('service_api');
                                                                        //$VIPLevel = $memberServicesArr[0]['VIPLevel'];

                                                                        $url = $serviceAPI[$serverID - 1];
                                                                        $certFilePath = App::getParam('rtg_cert_dir').$serverID.'/cert.pem';
                                                                        $keyFilePath = App::getParam('rtg_cert_dir').$serverID.'/key.pem';

                                                                        $rtgCashierAPI = new RealtimeGamingCashierAPI2($url, $certFilePath, $keyFilePath, '');

                                                                        $apiResult = $rtgCashierAPI->GetPIDfromlogin($serviceUsername);

                                                                        $PID = $apiResult['GetPIDFromLoginResult'];
                                                                        $IsSucceed = false;
                                                                        if(!empty($PID)) {
                                                                            $userID = 0;
                                                                            $getPClassID = $casinoAPI->GetPlayerClassification("RTG2", $PID, $serverID);
                                                                            $oldPClassIDAPI = $getPClassID['IsSucceed'] == true ? $getPClassID['ClassID']:NULL;
                                                                            $changePlayerClassResult = $casinoAPI->ChangePlayerClassification('RTG2', $PID, $VIPLevel, $userID, $serverID);
                                                                            header("Content-Type:text/html");
                                                                            $IsSucceed= $changePlayerClassResult['IsSucceed'];
                                                                            //app::Pr($IsSucceed);
                                                                        }

                                                                        if($IsSucceed==1 || $IsSucceed==true){
                                                                            if($VIPLevel == 0 || $VIPLevel == 2)
                                                                                $isVIP = 0;
                                                                            else if($VIPLevel == 1)
                                                                                $isVIP = 1;

                                                                            $members->StartTransaction();
                                                                            $members->updatePlayerClassificationByMID($isVIP, $MID);

                                                                            if(!App::HasError()) {
                                                                                $commonPDOConnection = $members->getPDOConnection();
                                                                                $memberServices->setPDOConnection($commonPDOConnection);
                                                                                $memberServices->updatePlayerClassificationByMIDAndServiceID($isVIP, $VIPLevel, $MID, $serverID);

                                                                                if(!App::HasError()) {
                                                                                    $memberServicesArr = $memberServices->getCasinoAccounts($MID);
                                                                                    //app::pr($memberServicesArr);
                                                                                    $VIPLevel = $memberServicesArr[0]['VIPLevel'];
                                                                                    $getPlayerClassResult = $casinoAPI->GetPlayerClassification("RTG2", $PID, $serverID);
                                                                                    //app::pr($getPlayerClassResult);

                                                                                    if($getPlayerClassResult['IsSucceed'] == true) {
                                                                                        //app::Pr('KronusDB: '.$VIPLevel);
                                                                                        //app::pr($getPlayerClassResult);
                                                                                        //app::Pr($VIPLevel);
                                                                                        //app::pr($getPlayerClassResult['ClassID']);
                                                                                        if($VIPLevel == $getPlayerClassResult['ClassID']) {
                                                                                            //app::pr('here');
                                                                                            $auditTrail->setPDOConnection($commonPDOConnection);
                                                                                            $auditTrail->logEvent(AuditFunctions::BATCH_PLAYER_CLASSIFICATION_MAPPING, ' Batch Change Player Classification to '. $VIPLevel, array('ID' => $MID, 'SessionID' => $_SESSION['sessionID']));
                                                                                            if(!App::HasError()) {
                                                                                                //app::pr('here2');
                                                                                                $members->CommitTransaction();
                                                                                                //$logMessage = 'Batch Player Classification Mapping has completed.';
                                                                                                //$logger->log($logger->logdate, " [1] ", $logMessage
                                                                                                array_push($listUBCard, $UBCard);
                                                                                                
                                                                                                //Check if the Player has been promoted
                                                                                                $IsPromoted = $oldPClassIDAPI == 1 && $VIPLevel != $oldPClassIDAPI ? 0:$VIPLevel > $oldPClassIDAPI ? 1:0;
                                                                                                
                                                                                                if($ToBeEmailed==1 && $IsPromoted == 1){   
                                                                                                    emailUser($MID, $VIPLevel);
                                                                                                }
                                                                                                
                                                                                                if($ctr==$ArraySize){
                                                                                                    $message = 'Batch Player Classification Mapping has completed.';
                                                                                                    if($ErrorMessageToBeEmailed!=''){
                                                                                                        $message .="However, some of the cards failed to update. Kindly check your email.";
                                                                                                    }
                                                                                                    showDialog(0,$message);
                                                                                                    logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                                                                    sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                                                                    
                                                                                                }
                                                                                                
                                                                                            }
                                                                                            else {
                                                                                                $members->RollBackTransaction();
                                                                                                $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                                                $logMessage .= " Error Msg: Failed to log event.\r\n";
                                                                                                //$logger->log($logger->logdate, " [0] ", $logMessage);
                                                                                                $CompiledLogMessages .=$logMessage;
                                                                                                //$msg = 'Failed to log event.';
                                                                                                App::ClearStatus();
                                                                                            }
                                                                                        }
                                                                                        else {
                                                                                            $members->RollBackTransaction();
                                                                                            $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                                            $logMessage .= " Error Msg: VIP Level in Kronus DB and Casino BE are different.\r\n";
                                                                                            //$logger->log($logger->logdate, " [0] ", $logMessage);
                                                                                            $CompiledLogMessages .=$logMessage;
                                                                                        }
                                                                                    }
                                                                                    else {
                                                                                        $members->RollBackTransaction();
                                                                                        $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                                        $logMessage .= " Error Msg: Get Player classification failed.\r\n";
                                                                                        //$logger->log($logger->logdate, " [0] ", $logMessage);
                                                                                        $CompiledLogMessages .=$logMessage;
                                                                                    }

                                                                                }else{
                                                                                    $logMessage = "[ERROR] UB Card: $UBCard  Error Msg: Failed to update database.\r\n";
                                                                                    
                                                                                    $memberServicesArr = $memberServices->getCasinoAccounts($MID);
                                                                                    $VIPLevel = $memberServicesArr[0]['VIPLevel'];
                                                                                    $changePlayerClassResult = $casinoAPI->ChangePlayerClassification('RTG2', $PID, $VIPLevel, $userID, $serverID);
                                                                                    if($changePlayerClassResult['IsSucceed'] != true) {
                                                                                        $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                                        $logMessage .= " Error Msg: Failed to rollback API Update.\r\n";
                                                                                        //$logger->log($logger->logdate, " [0] ", $logMessage);
                                                                                        $CompiledLogMessages .=$logMessage;
                                                                                    }
                                                                                    $getPlayerClassResult = $casinoAPI->GetPlayerClassification("RTG2", $PID, $serverID);
                                                                                    if($getPlayerClassResult['IsSucceed'] == true) {
                                                                                        if($VIPLevel == $getPlayerClassResult['ClassID']) {
                                                                                            $members->RollBackTransaction();
                                                                                            $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                                            $logMessage .= " Error Msg: Get Player classification failed.\r\n";
                                                                                            //$logger->log($logger->logdate, " [0] ", $logMessage);
                                                                                            $CompiledLogMessages .=$logMessage;

                                                                                        }
                                                                                    }
                                                                                    else {
                                                                                        $members->RollBackTransaction();
                                                                                        $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                                        $logMessage .= " Error Msg: Get Player classification failed.\r\n";
                                                                                        //$logger->log($logger->logdate, " [0] ", $logMessage);
                                                                                        $CompiledLogMessages .=$logMessage;
                                                                                    }

                                                                                }


                                                                            }else{
                                                                                $logMessage = "[ERROR] UB Card: $UBCard  Error Msg: Failed to update database.\r\n";
                                                                                
                                                                                $memberServicesArr = $memberServices->getCasinoAccounts($MID);
                                                                                $VIPLevel = $memberServicesArr[0]['VIPLevel'];
                                                                                $changePlayerClassResult = $casinoAPI->ChangePlayerClassification('RTG2', $PID, $VIPLevel, $userID, $serverID);
                                                                                if($changePlayerClassResult['IsSucceed'] != true) {
                                                                                    $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                                    $logMessage .= " Error Msg: Failed to rollback API Update.\r\n";
                                                                                    //$logger->log($logger->logdate, " [0] ", $logMessage);
                                                                                    $CompiledLogMessages .=$logMessage;
                                                                                }
                                                                                $getPlayerClassResult = $casinoAPI->GetPlayerClassification("RTG2", $PID, $serverID);
                                                                                if($getPlayerClassResult['IsSucceed'] == true) {
                                                                                    if($VIPLevel == $getPlayerClassResult['ClassID']) {

                            //                                                        }
                            //                                                        else {
                                                                                        $members->RollBackTransaction();
                                                                                        $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                                        $logMessage .= " Error Msg: Get Player classification failed.\r\n";
                                                                                        //$logger->log($logger->logdate, " [0] ", $logMessage);
                                                                                        $CompiledLogMessages .=$logMessage;

                                                                                    }
                                                                                }
                                                                                else {
                                                                                    $members->RollBackTransaction();
                                                                                    $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                                    $logMessage .= " Error Msg: Get Player classification failed.\r\n";
                                                                                    //$logger->log($logger->logdate, " [0] ", $logMessage);
                                                                                    $CompiledLogMessages .=$logMessage;
                                                                                    
                                                                                }
                                              
                                                                            }


                                                                        }
                                                                        else{
                                                                            $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                            $logMessage .= " Error Msg: No API response.\r\n";
                                                                            //$logger->log($logger->logdate, " [0] ", $logMessage);
                                                                            $CompiledLogMessages .=$logMessage;
                                                                            $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Player classification update failed.", $num+=1);
                                                                            if($ctr==$ArraySize){
                                                                                showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                                                logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                                                sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                                            }
                                                                        }



                                                                    }else{
                                                                        $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                        $logMessage .= " Error Msg: Card doesn't exist in the Casino Backend.\r\n";
                                                                        //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                                        $CompiledLogMessages .=$logMessage;
                                                                        $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Card does not exist.", $num+=1);
                                                                        if($ctr==$ArraySize){
                                                                            showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                                            logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                                            sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                                        }
                                                                    }

                                                                }else{
                                                                    $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                    $logMessage .= " Error Msg: Card has an existing terminal session.\r\n";
                                                                    //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                                    $CompiledLogMessages .=$logMessage;
                                                                    $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Card has an existing terminal session.", $num+=1);
                                                                    if($ctr==$ArraySize){
                                                                        showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                                        logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                                        sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                                    }
                                                                }


                                                            }else{
                                                                $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                $logMessage .= " Error Msg: Card has an existing EGM session.\r\n";
                                                                //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                                $CompiledLogMessages .=$logMessage;
                                                                $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Card has an existing EGM session.", $num+=1);
                                                                
                                                                if($ctr==$ArraySize){
                                                                    showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                                    logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                                    sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                                }
                                                            }
                                                        }else{
                                                            $logMessage = "[ERROR] UB Card: $UBCard ";
                                                                $logMessage .= " Error Msg: Duplicate Entry.\r\n";
                                                                //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                                $CompiledLogMessages .=$logMessage;
                                                                $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Duplicate Entry.", $num+=1);
                                                          
                                                                if($ctr==$ArraySize){
                                                                    showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                                    logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                                    sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                                }
                                                        }

                                                    }else if($Status==0){
                                                        $logMessage = "[ERROR] UB Card: $UBCard ";
                                                        $logMessage .= " Error Msg: Card is inactive.\r\n";
                                                        //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                        $CompiledLogMessages .=$logMessage;
                                                        $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Card is inactive.",$num+=1);
                                                        
                                                        if($ctr==$ArraySize){
                                                            showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                            logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                            sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                        }
                                                    }else if($Status==2){
                                                        $logMessage = "[ERROR] UB Card: $UBCard ";
                                                        $logMessage .= " Error Msg: Card is Deactivated.\r\n";
                                                        //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                        $CompiledLogMessages .=$logMessage;
                                                        $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Card is Deactivated.",$num+=1);
                                                        
                                                        if($ctr==$ArraySize){
                                                            showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                            logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                            sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                        }
                                                    }else if($Status==5){
                                                        $logMessage = "[ERROR] UB Card: $UBCard ";
                                                        $logMessage .= " Error Msg: Temporary Cards are not allowed.\r\n";
                                                        //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                        $CompiledLogMessages .=$logMessage;
                                                        $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Temporary Cards are not allowed.",$num+=1);
                                                        
                                                        if($ctr==$ArraySize){
                                                            showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                            logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                            sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                        }
                                                    }else if($Status==7){
                                                        $logMessage = "[ERROR] UB Card: $UBCard ";
                                                        $logMessage .= " Error Msg: Card is already Migrated to another red card.\r\n";
                                                        //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                        $CompiledLogMessages .=$logMessage;
                                                        $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Card is already Migrated to another red card .",$num+=1);
                                                        
                                                        if($ctr==$ArraySize){
                                                            showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                            logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                            sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                        }
                                                    }else if($Status==8){
                                                        $logMessage = "[ERROR] UB Card: $UBCard ";
                                                        $logMessage .= " Error Msg: Temporary Card already Migrated.\r\n";
                                                        //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                        $CompiledLogMessages .=$logMessage;
                                                        $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Temporary Card already Migrated.",$num+=1);
                                                        
                                                        if($ctr==$ArraySize){
                                                            showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                            logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                            sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                        }
                                                    }else if($Status==9){
                                                        $logMessage = "[ERROR] UB Card: $UBCard ";
                                                        $logMessage .= " Error Msg: Card is Banned.\r\n";
                                                        //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                        $CompiledLogMessages .=$logMessage;
                                                        $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Card is Banned.",$num+=1);
                                                        
                                                        if($ctr==$ArraySize){
                                                            showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                            logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                            sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                        }
                                                    }
                                                    
                                                }
                                                else {
                                                    $logMessage = "[ERROR] UB Card: $UBCard  Error Msg: Invalid ToBeEmailed input.\r\n";
                                                    //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                    $CompiledLogMessages .=$logMessage;
                                                    $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Invalid ToBeEmailed input.", $num+=1);
                                                    if($ctr==$ArraySize){
                                                        showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                        logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                        sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                    }
                                                }
                                            }
                                            else {
                                                $logMessage = "[ERROR] UB Card: $UBCard  Error Msg: Invalid VIPLevel input.\r\n";
                                                //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                                $CompiledLogMessages .=$logMessage;
                                                $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Invalid VIPLevel input.", $num+=1);
                                                if($ctr==$ArraySize){
                                                    showDialog(0,'Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.');
                                                    logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                    sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                                }
                                            }
                                        }else{
                                            $logMessage = "[ERROR] UB Card: $UBCard  Error Msg: Card does not exist.\r\n";
                                            //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
                                            $CompiledLogMessages .=$logMessage;
                                            $ErrorMessageToBeEmailed .=generateMessage($UBCard, "Card does not exist.", $num+=1);
                                            
                                            if($ctr==$ArraySize){
                                                showDialog(0,"Batch Player Classification Mapping has completed. However, some of the cards failed to update. Kindly check your email.");
                                                logError($CompiledLogMessages,true, $filename, $dateStarted);
                                                sendErrorViaEmail($AID, $ErrorMessageToBeEmailed, $filename);
                                            }
                                        }
            //                        }
            //                        else {
            //                            //app::Pr($occu)
            //                            $logMessage = "\r\nUB Card: $UBCard\r\n";
            //                            $logMessage .= "Error Msg: Card already exists.";
            //                            //$logger->log($logger->logdate, " [ERROR] ", $logMessage);
            //                            $CompiledLogMessages .=$logMessage;
            //                            if($ctr==$ArraySize){
            //                                showDialog(0,'Batch Player Classification Mapping has completed.');
            //                                logError($CompiledLogMessages,true, $filename, $dateStarted);
            //                            }
            //                        }
                                }else{
                                    $logMessage = "[ERROR] UB Card: $UBCard  Error Msg: A field in the csv is blank.\r\n";
                                    //$logger->log($logger->logdate, " [0] ", $logMessage);
                                    $CompiledLogMessages .=$logMessage;
                                    $ErrorMessageToBeEmailed .=generateMessage($UBCard, "A field in the csv is blank.", $num+=1);
                                }
                                $ctr+=1;
                                
                            }
                            

                        }
                    }
                    else {
                        $logMessage = "\r\nIncorrect file format. Please upload a valid file format.\r\n";
                        //$logger->log($logger->logdate, " [0] ", $logMessage);
                        $CompiledLogMessages .=$logMessage;
                        
                        //$errorMsgTitle = 'ERROR!';
                    }
                }
                else {
                    showDialog(1,'Session expired.');
                    $page = 'login.php';
                    App::pr("<script> window.location = '$page'; </script>");
                //showDialog(1,'Session expired.');
        //        session_destroy();
        //        URL::Redirect("login.php?mess=".$msg);
                }
            }
        }
    }
   
}
else{

//function hasDuplicate($array) {
//    return (count($array)==count(array_unique($array))) ? false:true;
//}
?>

<?php include("header.php"); ?>
<script type='text/javascript' src='js/jqgrid/i18n/grid.locale-en.js' media='screen, projection'></script>
<script type='text/javascript' src='js/jquery.jqGrid.min.js' media='screen, projection'></script>
<!--<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />-->
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript' src='js/checkinput.js' media='screen, projection'></script>  
<script type="text/javascript">
    $(document).ready(function(){
        var ReturnMessage = {
            0:'Successful!',
            1:'FileUpload object is not found.',
            2:'No File Selected.',
            3:'Incorrect File Type. Please upload CSV File Only.',
            4:'FileReader is not supported in this browser.',
            5:'File contains no valid data.',
            6:'File has incorrect format.'
        };
       
        var DialogTitle = {0:'Success',1:'Error'};
       
        //It contains names of column in form of an array. This will be the basis for validation.
        var CSVField = genArray(['UBCard','VIPLevel','ToBeEmailed']);
       
        var ObjectName = 'fileUpload1';
//        $("#btnUpload").click(function(e){
//            checkSession(ObjectName, 'csv');
//        });
       
       $('#btnUpload').live('click', function() {

           //var cardnumber = $("#txtCardNumber").val();
           var casinoservice = $("#casinoservice").val();
           
           if(casinoservice <= 0){
               alert('Please Select a Casino Service');
           }
           else{
               checkSession(ObjectName, 'csv');
           }
        });
       
        function genArray(arr){
            var array = {};
            for(var i=0;i<arr.length;i++){
                var field = arr[i];
                array[field]=false;
            }
            return array;
        }
       
//        $("#MainForm").submit(function(e){
//            validateFile('fileUpload1', 'csv');
//            e.preventDefault();
//        });
        function checkSession(ObjectName, FileType){
            // var response=0;
             $.ajax({
                 type:'POST',
                 url:document.URL,
                 data:{CheckSession:true},
                 dataType:'text',
                 success:function(res){
                     if(res==1){
                         validateFile(ObjectName, FileType);
                     }else{
                         //window.location = 'login.php';
                         showSessionExpireDialog(DialogTitle[1],'Session has expired. Please login again.');
                     }
                 }
             });

        }
        var Validity=false;
        function validateFile(ObjectName, FileType){
           
            var ReturnValue = _getFileDetails(ObjectName);
            if(ReturnValue['Valid']===true){
                var FileName = ReturnValue['FileName'];
                if(_validateFileType(FileName, FileType)===true){
                    _validateFileContent(ObjectName);
                }
            } 
          
        }
           
            function _getFileDetails(ObjectName){
                var ReturnValue = {'Valid':false,'FileName':''};
                try{
                    var File = document.getElementById(ObjectName).value;
                    if(File==='' || File===null){
                   
                        ReturnValue['Valid']=false;
                        showDialog(DialogTitle[1],ReturnMessage[2]);
                       
                    }else{
                        ReturnValue['Valid']=true;
                        ReturnValue['FileName']=File;
                    }
                    return ReturnValue;
                }
                catch(err){
                    alert(err);
                    return false;
                }
            }
           
            function _validateFileType(FileName, FileType){
                var Valid = false;
                var extension = FileName.substring(FileName.lastIndexOf('.')+1).toLowerCase();
                FileType===extension?Valid=true:showDialog(DialogTitle[1],ReturnMessage[3]);
                return Valid;
            }
           
            function _validateFileContent(ObjectName){
                var Files = document.getElementById(ObjectName).files;
                __handleFiles(Files);
            }
               
                function __handleFiles(files){
                    if(window.FileReader){
                        __getAsText(files[0]);
                    }else{
                        showDialog(DialogTitle[1],ReturnMessage[4]);
                    }
                }
               
                function __getAsText(FileToRead){
                    var reader = new FileReader();
                    reader.onload = __loadHandler;
                    reader.onerror = __errorHandler; 
                    reader.readAsText(FileToRead);
                }
               
                function __loadHandler(event){
                    var csv = event.target.result;
                    __processData(csv);
                }
               
                function __processData(csv){
                    var allTextLines = csv.split(/\r\n|\n/);
                    var lines = [];
                   
                    try{
                        lines = allTextLines[0].split(",");
                    }catch(err){
                        alert(err);
                    }
//                    while (allTextLines.length) {
//                        lines.push(allTextLines.shift().split(','));
//                  }
                    if(allTextLines.length<=1 || lines.length<=1){
                        showDialog(DialogTitle[1],ReturnMessage[5]);
                    }
                    else{
                        var line2 = allTextLines[1].trim().length;
                        if(line2==0){
                            showDialog(DialogTitle[1],ReturnMessage[5]);
                        }else{
                            compareResult(lines);
                        }
                       
                    }
                }
               
                function __errorHandler(evt){
                    if(evt.target.error.name == "NotReadableError") {
                        alert("Cannot read file !");
                    }
                }
       
        function compareResult(Result){
           
            try{
                var arr = Result;
                var i=0;

                for(var key in CSVField){
                    if(CSVField.hasOwnProperty(key)){
                        if(key==arr[i].trim()){
                            CSVField[key]=true;
                        }else{
                            CSVField[key]=false;
                        }
                        i++;
                    }
                }
                if(i===arr.length){
                    var isValid = compareBooleans(CSVField);
                    //isValid===true?showDialog(DialogTitle[0],ReturnMessage[0]):showDialog(DialogTitle[1],ReturnMessage[6]);
                    if(isValid===true){
                        var FormData = bindForm();
                        var casinoservice = $("#casinoservice").val();
                        FormData.append('btnUpload', 'Upload');
                        FormData.append('casinoservice', casinoservice);
                        console.log(FormData);
//                     
                        ajaxPost(document.URL,FormData, "callBackContainer");
                    }else{
                        showDialog(DialogTitle[1],ReturnMessage[6]);
                    }
                }else{
                    showDialog(DialogTitle[1], ReturnMessage[6]);
                }

            }catch(err){alert(err);
                //showDialog(DialogTitle[1],ReturnMessage[6]);
            }

        }
      
        function bindForm(){
            var files = document.getElementById(ObjectName).files;
            var formData = new FormData();
            var file = files[0];

            formData.append('fileUpload1[]', file, file.name);
           
            return formData;
        }

        function compareBooleans(arr){
            var Valid = false;
            for(var key in arr){
                if(arr.hasOwnProperty(key)){
                    if(arr[key]===true){
                        Valid=true;
                    }else{
                        Valid=false;
                        return false;
                    }
                }
            }
            return Valid;
        }
       
        function showDialog(Title, Message){
            //console.log(e);alert();
            //console.log(e.preventDefault);alert();
           
            var ContainerMessage='#containerReturnMessage';
            $("#dialogReturnMessage").dialog({
                modal : true,
                title : Title,
                resizable : false,
                draggable :false,
                buttons : {
                    'OK' : function(){$(this).dialog('close');$(ContainerMessage).text('');}
                }
            });
            $(ContainerMessage).text(Message);
            //alert(Title+": "+Message);
        }
        
        function showSessionExpireDialog(Title, Message){
            //console.log(e);alert();
            //console.log(e.preventDefault);alert();
           
            var ContainerMessage='#containerReturnMessage';
            $("#dialogReturnMessage").dialog({
                modal : true,
                title : Title,
                resizable : false,
                draggable :false,
                buttons : {
                    'OK' : function(){$(this).dialog('close');window.location='login.php';}
                },
                close:function(){window.location='login.php';}
            });
            $(ContainerMessage).text(Message);
        }
       
        function ajaxPost(getDetails, formData, elementID)
        {
            var xmlhttp;
            window.XMLHttpRequest?xmlhttp = new XMLHttpRequest():xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
           
            xmlhttp.onreadystatechange = function()
            {
                if(xmlhttp.readyState == 4 && xmlhttp.status == 200)
                {
                    var response = xmlhttp.responseText;
                    $("#"+elementID).html(response);
                }
            };
           
            xmlhttp.open("POST",getDetails,true);
            xmlhttp.send(formData);
        }
       
    });
   
   
    function showDialog(Title, Message){
        var ContainerMessage='#containerReturnMessage';
        $("#dialogReturnMessage").dialog({
            modal : true,title : Title,resizable : false,draggable :false,buttons : {'OK' : function(){$(this).dialog('close');$(ContainerMessage).text('');window.location.href = "batchplayerclassificationmapping.php";}}
        });
        $(ContainerMessage).text(Message);
    }
  
</script>
<div align="center">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                <h2>Batch Player Classification Mapping</h2><br/>  

                <div class="searchbar formstyle">
                    <form name="frmSearch" id="frmSearch" method="POST" enctype="multipart/form-data">
                        <?php echo $casinoservice; ?>
                        CSV File:
                        <input type="file" name="fileUpload1" id="fileUpload1" style="outline:1px solid #999;"/>
                        <?php echo $btnUpload; ?>
                    </form>
                </div>
                <br/><br/>
                <div><pre>CSV Format: UBCard,VIPLevel,ToBeEmailed</pre></div>
                <div><pre>            XXXXXXXX,X,X   </pre></div>
                <br/><br/>
               
             
            </div>
        </div>
</div>
<div id="dialogReturnMessage">
    <p id="containerReturnMessage">
       
    </p>
</div>

<div id="callBackContainer">
   
</div>
<?php  include("footer.php"); }?>