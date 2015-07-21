<?php

/* * *****************
 * Author: Ralph Sison
 * Date Created: 2015-04-30
 * ***************** */

require_once("init.inc.php"); //full path of init.inc.php of the project
define('MSG_NO_ACCESS', 'No access');
$referer_list = App::getParam('domain');
$referer = get_domain($_SERVER['HTTP_REFERER']);
if (!$referer || !in_array($referer, $referer_list))
{
    header('HTTP/1.0 403 Forbidden');
    exit(MSG_NO_ACCESS);
}

App::LoadModuleClass('Membership', 'MemberInfo');
App::LoadModuleClass('Membership', 'MembershipTempInfo');
App::LoadModuleClass('Membership', 'MembersTemp');
App::LoadModuleClass('Membership', 'Members');
App::LoadModuleClass('Loyalty', 'MemberCards');
App::LoadModuleClass('Loyalty', 'CardTransactions');
App::LoadModuleClass('Membership', 'Identifications');
App::LoadModuleClass('Kronus', 'Sites');
App::LoadModuleClass('Membership', 'PcwsWrapper');
App::LoadModuleClass('Kronus', 'TransactionSummary');
App::LoadModuleClass('Kronus', 'EwalletTrans');

$_MemberInfo = new MemberInfo();
$_MembershipTempInfo = new MembershipTempInfo();
$_MembersTemp = new MembersTemp();
$_Members = new Members();
$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_Ref_Identifications = new Identifications();
$_Sites = new Sites();
$_TransactionSummary = new TransactionSummary();
$_MemberCards = new MemberCards();
$_EwalletTrans = new EwalletTrans();

$SFID = $_GET['SFDCID'];
$un = $_GET['SFUser'];

$currentPoints = 0;
$lifetimePoints = 0;
$bonusPoints = 0;
$redeemedPoints = 0;
$cardNumber = "";
$siteName = "";
$transDate = "";
$msg = '';
$playableBalance = 0;
$isnew = true;

//check if SFID exists in membership db
$resultMembershipSFID = $_MemberInfo->checkIfSFIDExists($SFID);
if (count($resultMembershipSFID) > 0)
{
    $MID = $_MemberInfo->getMIDUsingSFID($SFID);
    $resultCardNumberInfo = $_MemberCards->getCardInfoUsingMID($MID);
    $cardNumber = $resultCardNumberInfo['CardNumber'];
    $cardDateCreated = $resultCardNumberInfo['DateCreated'];
    $lifetimePoints = $resultCardNumberInfo['LifetimePoints'];
    $redeemedPoints = $resultCardNumberInfo['RedeemedPoints'];
    $bonusPoints = $resultCardNumberInfo['BonusPoints'];
    $resultEnc = $_MemberInfo->getEncPOCDetails($MID);
    $resultNonEnc = $_MemberInfo->getGenericInfo($MID);
    $rowEnc = $resultEnc[0]; //row1
    $rowNonEnc = $resultNonEnc[0];
    $isnew = false;

    if ($rowEnc)
    {
        $resultIDPresented = $_Ref_Identifications->getIDPresented($rowNonEnc['IdentificationID']);
        $rowIDPresented = $resultIDPresented[0]['IdentificationName']; //row2

        $resultLastTransaction = $_CardTransactions->getLastTransaction($cardNumber);
        $rowLastTransaction = $resultLastTransaction[0]; //row3

        if ($rowLastTransaction)
        {
            $resultSite = $_Sites->getSite($rowLastTransaction['SiteID']);
            $rowSite = $resultSite[0]; //row4

            $resultLastReloadTransaction = $_EwalletTrans->getLastReloadTransaction($cardNumber);
            $rowLastReloadTransaction = $resultLastReloadTransaction[0]; //row5

            $pcws = new PcwsWrapper();
            $resultBalance = $pcws->getBalance($cardNumber, 1); //row6
            if ($resultBalance['GetBalance']['ErrorCode'] == 0)
            {
                $playableBalance = number_format($resultBalance['GetBalance']['PlayableBalance'], 2);
            }
            else
            {
                $playableBalance = $resultBalance['GetBalance']['TransactionMessage'];
            }
            
            $resultCompPoints = $pcws->getCompPoints($cardNumber, 1);
            if ($resultCompPoints['GetCompPoints']['ErrorCode'] == 0)
            {
                $currentPoints = number_format($resultCompPoints['GetCompPoints']['CompBalance'], 2);
            }
            else
            {
                $currentPoints = $resultCompPoints['GetCompPoints']['TransactionMessage'];
            }
        }

        $resultStatus = $_Members->getMemberStatus($MID); //row7
        if (count($resultStatus) > 0)
        {
            switch ($resultStatus[0]['Status'])
            {
                case 1:
                    $rowEnc['Status'] = 'Active';
                    break;
                case 2:
                    $rowEnc['Status'] = 'Suspended';
                    break;
                case 3:
                    $rowEnc['Status'] = 'Locked (Attempts)';
                    break;
                case 4:
                    $rowEnc['Status'] = 'Locked (Admin)';
                    break;
                case 5:
                    $rowEnc['Status'] = 'Banned';
                    break;
                case 6:
                    $rowEnc['Status'] = 'Terminated';
                    break;
                default:
                    $rowEnc['Status'] = 'Inactive';
            }
        }
    }
    else
    {
        //echo "0 results";
    }
}
else
{
    //check if SFID exists in membership_temp db
    $resultMembershipTempSFID = $_MembershipTempInfo->checkIfSFIDExists($SFID);
    if (count($resultMembershipTempSFID) > 0)
    {
        $MID = $_MemberInfo->getMIDUsingSFID($SFID);
//        $row['FirstName'] = $rowEnc['FirstName'];
//        $row['MiddleName'] = $rowEnc['MiddleName'];
//        $row['LastName'] = $rowEnc['LastName'];
//        $row['Birthdate'] = $rowNonEnc['Birthdate'];
//        $row['Address1'] = $rowEnc['Address1'];
//        $row['MobileNumber'] = $res2[0]['MobileNumber'];
//        $row['Email'] = $res2[0]['Email'];
//        $row['IdentificationID'] = $res2[0]['IdentificationID'];
//        $row['IdentificationNumber'] = $res2[0]['IdentificationNumber'];
//        $mid = $res2[0]['MID'];

        if ($rowNonEnc['IdentificationID'] != '')
        {
            $resultIDPresented = $_Ref_Identifications->getIDPresented($rowNonEnc['IdentificationID']);
            $rowIDPresented = $resultIDPresented[0];
        }
        else
        {
            $rowIDPresented = '';
        }

        //$row3['TransactionDate'] = '';
        $rowLastTransaction['TransactionDate'] = '';

        $resultTempCode = $_MembersTemp->getTempCode($MID);
        if (count($resultTempCode) > 0)
        {
            $cardNumber = $resultTempCode[0]['TemporaryAccountCode'];
        }

        $resultMemberStatus = $_MembersTemp->getMemberStatus($MID);
        if (count($resultMemberStatus) > 0)
        {
            switch ($resultMemberStatus[0]['Status'])
            {
                case 1:
                    $rowEnc['Status'] = 'Active';
                    break;
                case 2:
                    $rowEnc['Status'] = 'Suspended';
                    break;
                case 3:
                    $rowEnc['Status'] = 'Locked (Attempts)';
                    break;
                case 4:
                    $rowEnc['Status'] = 'Locked (Admin)';
                    break;
                case 5:
                    $rowEnc['Status'] = 'Banned';
                    break;
                case 6:
                    $rowEnc['Status'] = 'Terminated';
                    break;
                default:
                    $rowEnc['Status'] = 'Inactive';
            }
        }
    }
}

$path = dirname(__FILE__) . '/rsframework/include/log/SFLogs/';
$fn = $path . 'logs_' . date("Ymd") . '.txt';
$fp = fopen($fn, "a");
fwrite($fp, date("[d-M-Y H:i:s]") . ' || Player Details: ' . $rowEnc['FirstName'] . ' ' . $rowEnc['LastName'] . ' || ' . $un . "\r\n");
fclose($fp);

function get_domain($url)
{
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : '';

    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs))
    {
        return $regs['domain'];
    }
    return false;
}

?>
