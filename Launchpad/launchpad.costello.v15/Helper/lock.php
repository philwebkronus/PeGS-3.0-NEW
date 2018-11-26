<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once '../Helper/Logger.class.php';
include_once '../controllers/LobbyController.php';
include_once '../models/LPConfig.php';
include_once '../models/LPTerminals.php';
include_once '../models/LPTerminalSessions.php';
include_once '../models/LPMembers.php';
include_once '../models/LPMemberCards.php';
include_once '../models/LPRefAllowedEwalletUser.php';
include_once '../models/LPEGMSessions.php';
include_once '../controllers/PcwsWrapper.php';
require_once '../models/LPConfig.php';
include_once '../models/LPRefServices.php';

$function = $_POST["data"];
$pin = "";
$terminalCodePath = "";
if (!empty($function)) {
    $lobbycontroller = new LobbyController();
    $_PCWS = new PcwsWrapper();
    switch ($function) {

        case 'checkUbCard':
            $ubCard = $_POST['ubCard'];
            $CardDetails = LPMemberCards::model()->getCardStatus($ubCard);
            $IsEwallet = LPMembers::model()->checkUBCard($CardDetails['MID']);
            $result['Status'] = (int) $CardDetails['Status'];
            $result['MID'] = (int) $CardDetails['MID'];
            $result['IsEwallet'] = (int) $IsEwallet['IsEwallet'];
            break;
        case 'checkPassword':
            $pass = md5($_POST['pass']);
            $mid = $_POST['mid'];

            $count = LPMembers::model()->checkPassword($mid, $pass);
            $result = $count;
            break;

        case 'checkPin':
            $mid = $_POST['mid'];
            $cardNumber = $_POST['cardNumber'];
            $enteredPIN = $_POST['pinVal'];

            $checkPin = $_PCWS->checkPin($cardNumber, $enteredPIN);

            $PIN = LPMembers::model()->getPIN($mid);

            $result['DatePINLastChange'] = $PIN['DatePINLastChange'];
            $result['PINLoginAttemps'] = (int) $PIN['PINLoginAttemps'];

            if ($result['DatePINLastChange'] == null) {
                $result['DatePINLastChange'] = 0;
            }

            $result['pinRes'] = $checkPin['checkPin']['ErrorCode'];
            $result['pinMsg'] = $checkPin['checkPin']['TransactionMessage'];
            break;

        case 'getTerminalID':
            $terminalCode = $_POST['terminalCode'];
            $terminalID = LPTerminals::model()->getTerminalID($terminalCode);
            $result = (int) $terminalID[0]['TerminalID'];
            break;

        case 'checkIfTerminalSession':
            $terminalCode = $_POST['terminalCode'];
            $terminalID = LPTerminalSessions::model()->checkSession($terminalCode);
	    $result['TerminalID'] = (int) $terminalID['TerminalID'];
            $result['Usermode'] = (int) $terminalID['Usermode'];
            $result['ServiceID'] = (int) $terminalID['ServiceID'];

            break;

        case 'checkIfTerminalSessionLobby':
            $terminalCode = $_POST['terminalCode'];
            $serviceID = $_POST['ServiceID'];
            
            $count = LPTerminalSessions::model()->checkIfTerminalSessionLobby($terminalCode, $serviceID);

            $result['Count'] = (int) $count['Counter'];
            if ($serviceID == 28 || $serviceID == 29) {
                $result['ServiceUsername'] = $count['UBServiceLogin'];
                $result['HashedServicePassword'] = $count['UBHashedServicePassword'];
                $result['ServicePassword'] = $count['UBServicePassword'];
            } else {
                $result['ServiceUsername'] = $count['TerminalCode'];
                $result['HashedServicePassword'] = $count['HashedServicePassword'];
            }

            if ($serviceID == 28 || $serviceID == 22) {
                $result['HabaneroPath'] = LPConfig::app()->params["topaz_path"];
            } else {
                $result['HabaneroPath'] = LPConfig::app()->params["habanero_path"];
            }


            /*
             * For Habanero Integration
             * Added John Aaron Vida
             * 12/21/2017
             */
            $result['isVIP'] = $count['isVIP'];
            break;


        case 'isEwallet':
            $mid = $_POST['mid'];
            $isEwallet = LPMembers::model()->isEwallet($mid);
            $result = (int) $isEwallet['IsEwallet'];
            break;

        case 'tagEwallet':
            $mid = $_POST['mid'];
            $pin = sha1($_POST['pin']);
            $result = LPMembers::model()->tagEwallet($mid, $pin);
            break;

        case 'getMaxAttempts':

            $result = LPConfig::app()->params['maxPinAttempts'];

            break;

        case 'updateAttempts':
            $mid = $_POST['mid'];
            $attempts = $_POST['attempts'];

            $result['result'] = LPMembers::model()->updateAttempts($attempts, $mid);
            break;

        case 'checkEGMSession':
            $mid = $_POST['mid'];

            $count = LPEGMSessions::model()->checkEGMSession($mid);

            $result = (int) $count['Count'];

            break;

        case 'getServiceGroupID':

            $terminalCode = $_POST['terminalCode'];
            $serviceGroupID = LPTerminalServices::model()->getServiceGroupID($terminalCode);

            //$result['count'] = (int)count($serviceID);
            $result = (int) $serviceGroupID[0]["ServiceGroupID"];
            break;

        case 'checkSessionMode':

            $serviceId = $_POST['serviceID'];

            $count = LPRefServices::model()->checkSessionMode($serviceID);
            $result = (int) $count['Count'];

            break;

        case 'updatePin':

            $cardNumber = $_POST['cardNumber'];
            $oldPin = $_POST['oldP'];
            $newPin = $_POST['newP'];

            $APIresult = $_PCWS->changePin($cardNumber, $oldPin, $newPin);
            $result = $APIresult['changePin']['ErrorCode'];

            break;

        case 'checkTerminaServicesSession':
            $terminalCode = $_POST['terminalCode'];
            $option = $_POST['option'];

            $services = LPTerminalServices::model()->checkTerminalServicesSession($terminalCode, $option);

            $result['Count'] = count($services);

            if ($result['Count'] != 0) {
                $result['TerminalID'] = (int) $services[0]['TerminalID'];
                if (isset($services[1]['TerminalID']) && isset($services[1]['ServiceID'])) {
                    $result['TerminalIDVIP'] = (int) $services[1]['TerminalID'];
                    $result['ServiceIDVIP'] = (int) $services[1]['ServiceID'];
                } else {
                    $result['TerminalIDVIP'] = "";
                    $result['ServiceIDVIP'] = "";
                }

                $result['ServiceID'] = (int) $services[0]['ServiceID'];
                $result['ServiceGroupID'] = (int) $services[0]['ServiceGroupID'];
                $result['ServicePassword'] = (string) $services[0]['ServicePassword'];
                $result['HashedServicePassword'] = (string) $services[0]['HashedServicePassword'];
                $result['UserMode'] = (int) $services[0]['UserMode'];
            }
            break;

        case 'checkEwalletSession':

            $terminalCode = $_POST['terminalCode'];
            $option = $_POST['option'];

            $isEwalletSession = LPTerminalSessions::model()->checkExistingEwalletSession($terminalCode, $option);

            if ($isEwalletSession === false) {
                $result['UBServiceLogin'] = 0;
                $result['IsEwallet'] = -1;
            } else {
                $IsEwallet = LPMembers::model()->isEwallet((int) $isEwalletSession['MID']);

                $result['LoyaltyCardNumber'] = $isEwalletSession['LoyaltyCardNumber'];
                $result['UBServiceLogin'] = (float) $isEwalletSession['UBServiceLogin'];
                $result['UserMode'] = (int) $isEwalletSession['UserMode'];
                $result['ServiceID'] = (int) $isEwalletSession['ServiceID'];
                $result['ServiceGroupID'] = (int) $isEwalletSession['ServiceGroupID'];
                $result['UBServicePassword'] = $isEwalletSession['UBServicePassword'];
                $result['UBHashedServicePassword'] = $isEwalletSession['UBHashedServicePassword'];
                $result['TransactionSummaryID'] = $isEwalletSession['TransactionSummaryID'];  // CCT - added from Prod

                if (isset($IsEwallet['IsEwallet'])) {
                    $result['IsEwallet'] = (int) $IsEwallet['IsEwallet'];
                } else {
                    $result['IsEwallet'] = -1;
                }
            }

            break;

        case 'deleteExistingSession':

            $ubServiceLogin = $_POST['UBServiceLogin'];

            $ubServiceID = $_POST['UBServiceID'];

            $APIresult = $_PCWS->logoutLaunchPad($ubServiceLogin, $ubServiceID);

            // CCT BEGIN - comment out codes       
            //Checking for usermode
            /*
              $usermode = LPRefServices::model()->checkUsermode($ubServiceID);
              $source = "3";
              //Every Logout will Change the Password
              $_PCWS->changePassword($ubServiceLogin,$ubServiceID,$usermode['UserMode'],$source);
             */
            // CCT END - comment out codes                            
            $result = $APIresult['ForceLogout']['ErrorCode'];

            break;

        case 'insertNewSession':

            $terminalCode = $_POST['terminalCode'];
            $serviceID = $_POST['ServiceID'];
            $cardNumber = $_POST['ubCard'];

            $APIresult = $_PCWS->sessionStart($terminalCode, $serviceID, $cardNumber);
            $result['ErrorCode'] = (int) $APIresult['Unlock']['ErrorCode'];
            $result['TransactionMessage'] = $APIresult['Unlock']['TransactionMessage'];


            break;

        case "checkTerminalBasedSession":

            $terminalID = (float) $_POST['terminalID'];
            $servicepassword = LPTerminalSessions::model()->checkTerminalBasedSession($terminalID);
            $result = (string) $servicepassword['ServicePassword'];
            break;


        case "countMappedCasinos":
            $terminalID = $_POST['TerminalID'];
            $terminalIDVIP = $_POST['TerminalIDVIP'];
            $option = $_POST['option'];

            $count = LPTerminalServices::model()->countMappedCasino($terminalID, $terminalIDVIP, $option);

            $result = (int) $count['Count'];

            break;

        case 'checkIsCardSession':

            $ubCard = $_POST['ubCard'];
            $option = $_POST['option'];
            $count = LPTerminalSessions::model()->checkIsCardSession($ubCard, $option);

            $result['Count'] = count($count);
            $result['SiteID'] = (int) $count['SiteID'];
            $result['UBServiceLogin'] = $count['UBServiceLogin'];
            $result['UserMode'] = (int) $count['UserMode'];
            $result['TerminalCode'] = $count['TerminalCode'];

            break;

        case 'isAllowed':

            $ubCard = $_POST['ubCard'];
            $CardDetails = LPMemberCards::model()->getCardStatus($ubCard);
            $isAllowed = LPConfig::app()->params['isAllowed'];
            if ($isAllowed) {
                $count = LPRefAllowedEwalletUser::model()->isAllowed($CardDetails["MID"]);
            } else {
                $count['Count'] = 1;
            }

            $result = (int) $count['Count'];
            break;

        case 'getMID':

            $ubCard = $_POST['ubCard'];
            if ($ubCard == '') {
                $result = -1;
            } else {
                $count = LPMemberCards::model()->getCardStatus($ubCard);

                if (isset($count['MID'])) {
                    $result = (int) $count['MID'];
                } else {
                    $result = -1;
                }
            }

            break;



        default:
            break;
    }
    echo json_encode($result);
}
?>

