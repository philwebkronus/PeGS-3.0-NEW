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

$_MemberInfo = new MemberInfo();
$_MembershipTempInfo = new MembershipTempInfo();
$_MembersTemp = new MembersTemp();
$_Members = new Members();
$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_Ref_Identifications = new Identifications();
$_Sites = new Sites();
$_TransactionSummary = new TransactionSummary();

$SFID = $_GET['SFDCID'];
$un = $_GET['SFUser'];

$currentPoints = 0;
$lifetimePoints = 0;
$bonusPoints = 0;
$redeemedPoints = 0;
$CardNumber = "";
$siteName = "";
$transDate = "";
$msg = '';
$pb = 0;
$isnew = true;

//if SFID exists in membership db
$res = $_MemberInfo->checkIfSFIDExists($SFID);
if (count($res) > 0)
{
    $result = $_MemberCards->getPOCDetails($SFID);
    $row = $result[0];
    $isnew = false;
    $mid = $result[0]['MID'];

    if ($row)
    {
        $result2 = $_Ref_Identifications->getIDPresented($row['IdentificationID']);
        $row2 = $result2[0];

        $result3 = $_CardTransactions->getLastTransaction($row['CardNumber']);
        $row3 = $result3[0];

        if ($row3)
        {
            $result4 = $_Sites->getSite($row3['SiteID']);
            $row4 = $result4[0];

            $result5 = $_CardTransactions->getLastReloadTransaction($row['CardNumber']);
            $row5 = $result5[0];

            $pcws = new PcwsWrapper();
            $result6 = $pcws->getBalance($row['CardNumber'], 1);
            if ($result6['GetBalance']['ErrorCode'] == 0)
            {
                $pb = number_format($result6['GetBalance']['PlayableBalance'], 2);
            }
            else
            {
                $pb = $result6['GetBalance']['TransactionMessage'];
            }
        }

        $result7 = $_Members->getMemberStatus($mid);
        if (count($result7) > 0)
        {
            switch ($result7[0]['Status'])
            {
                case 1:
                    $row['Status'] = 'Active';
                    break;
                case 2:
                    $row['Status'] = 'Suspended';
                    break;
                case 3:
                    $row['Status'] = 'Locked (Attempts)';
                    break;
                case 4:
                    $row['Status'] = 'Locked (Admin)';
                    break;
                case 5:
                    $row['Status'] = 'Banned';
                    break;
                case 6:
                    $row['Status'] = 'Terminated';
                    break;
                default:
                    $row['Status'] = 'Inactive';
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
    $res2 = $_MembershipTempInfo->checkIfSFIDExists($SFID);
    if (count($res2) > 0)
    {
        $row['FirstName'] = $res2[0]['FirstName'];
        $row['MiddleName'] = $res2[0]['MiddleName'];
        $row['LastName'] = $res2[0]['LastName'];
        $row['Birthdate'] = $res2[0]['Birthdate'];
        $row['Address1'] = $res2[0]['Address1'];
        $row['Birthdate'] = $res2[0]['Birthdate'];
        $row['MobileNumber'] = $res2[0]['MobileNumber'];
        $row['Email'] = $res2[0]['Email'];
        $row['IdentificationID'] = $res2[0]['IdentificationID'];
        $row['IdentificationNumber'] = $res2[0]['IdentificationNumber'];
        $mid = $res2[0]['MID'];

        if ($row['IdentificationID'] != '')
        {
            $res3 = $_Ref_Identifications->getIDPresented($row['IdentificationID']);
            $row2 = $res3[0];
        }
        else
        {
            $row2 = '';
        }
        $row3['TransactionDate'] = '';

        $res4 = $_MembersTemp->getTempCode($mid);
        if (count($res4) > 0)
        {
            $row['CardNumber'] = $res4[0]['TemporaryAccountCode'];
        }

        $res5 = $_MembersTemp->getMemberStatus($mid);
        if (count($res5) > 0)
        {
            switch ($res5[0]['Status'])
            {
                case 1:
                    $row['Status'] = 'Active';
                    break;
                case 2:
                    $row['Status'] = 'Suspended';
                    break;
                case 3:
                    $row['Status'] = 'Locked (Attempts)';
                    break;
                case 4:
                    $row['Status'] = 'Locked (Admin)';
                    break;
                case 5:
                    $row['Status'] = 'Banned';
                    break;
                case 6:
                    $row['Status'] = 'Terminated';
                    break;
                default:
                    $row['Status'] = 'Inactive';
            }
        }
    }
}

$path = dirname(__FILE__) . '/rsframework/include/log/SFLogs/';
$fn = $path . 'logs_' . date("Ymd") . '.txt';
$fp = fopen($fn, "a");
fwrite($fp, date("[d-M-Y H:i:s]") . ' || Player Details: ' . $row['FirstName'] . ' ' . $row['LastName'] . ' || ' . $un . "\r\n");
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
