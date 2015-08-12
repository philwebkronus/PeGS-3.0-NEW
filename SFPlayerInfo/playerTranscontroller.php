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
App::LoadModuleClass('Loyalty', 'MemberCards');
App::LoadModuleClass('Loyalty', 'CardTransactions');
App::LoadModuleClass('Membership', 'Identifications');
App::LoadModuleClass('Kronus', 'Sites');
App::LoadModuleClass('Membership', 'PcwsWrapper');
App::LoadModuleClass('Kronus', 'TransactionSummary');
App::LoadModuleClass('Kronus', 'EwalletTrans');
App::LoadModuleClass('Membership', 'Members');
App::LoadModuleClass('Kronus', 'ManualRedemptions');

$_MemberInfo = new MemberInfo();
$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_Ref_Identifications = new Identifications();
$_Sites = new Sites();
$_TransactionSummary = new TransactionSummary();
$_EwalletTrans = new EwalletTrans();
$_Members = new Members();
$_ManualRedemptions = new ManualRedemptions();

$SFID = $_GET['SFDCID'];
$un = $_GET['SFUser'];

$MID = $_MemberInfo->getMIDUsingSFID($SFID);
if($MID)
{
    $resultIsEwallet = $_Members->checkIfEwallet($MID);
    $isEwallet = $resultIsEwallet['IsEwallet'];
    if($isEwallet == 1)
    {
        $resultDateConverted = $_Members->getDateConverted($MID);
        $dateConverted = $resultDateConverted['DateMigrated'];
        
        $PTS = $_EwalletTrans->getEwalletTransPerSiteByMID($MID, $dateConverted);
        $ptsResult = $PTS[0];
        $ptsCount = count($PTS);
        
        $PTS2 = $_TransactionSummary->getTransSummaryOfEwalletPerSiteByMID($SFID, $dateConverted);
        $ptsResult2 = $PTS2[0];
        $ptsCount2 = count($PTS2);
        $fname = $PTS2[0]['FirstName'];
        $lname = $PTS2[0]['LastName'];
    }
    else
    {
        $PTS = $_TransactionSummary->getTransSummaryPerSiteByMID($SFID);
        $ptsResult = $PTS[0];
        $ptsCount = count($PTS);
        $fname = $PTS[0]['FirstName'];
        $lname = $PTS[0]['LastName'];
    }
}
else
{
    echo 'No member found for that SFID';
    exit;
}



$path = dirname(__FILE__) . '/rsframework/include/log/SFLogs/';
$fn = $path . 'logs_' . date("Ymd") . '.txt';
$fp = fopen($fn, "a");
fwrite($fp, date("[d-M-Y H:i:s]") . ' || Player Transactions: ' . $fname . ' ' . $lname . ' || ' . $un . "\r\n");
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
