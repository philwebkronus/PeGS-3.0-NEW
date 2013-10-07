<?php
ini_set('max_execution_time', 0);
include 'web.config.php';
include 'PlayTechReportViewAPI.class.php';

$playtechReportViewAPI = new PlayTechReportViewAPI($reportUri, $rptCasinoName, $admin, $password);

$conn = explode( ",", $_DBConnectionString[0]);
$oconnectionstring1 = $conn[0];
$oconnectionstring2 = $conn[1];
$oconnectionstring3 = $conn[2];

$dbh = new PDO( $oconnectionstring1, $oconnectionstring2, $oconnectionstring3);

//query all member services
$stmt = "SELECT MID, ServiceUsername FROM tmpmemberservices";

$sth = $dbh->prepare($stmt);
$sth->execute();
$result = $sth->fetchAll(PDO::FETCH_ASSOC);

//iteration of getting player code based from username 
//and update the members service table
$playerCode = null;
foreach ($result as $val){
    $username = $val['ServiceUsername'];
    $mid = $val['MID'];
    
    $result = $playtechReportViewAPI->export($reportCode, 'exportxml', array('username'=>$username));
    
    $playerCode = $result['PlayerCode'];
    
    //query all member services
    $stmt = "UPDATE tmpmemberservices SET PlayerCode = ? WHERE MID = ?";
    $sth = $dbh->prepare($stmt);
    $sth->bindParam(1, $playerCode);
    $sth->bindParam(2, $mid);
    $sth->execute();
}

echo 'Script execution done!';

?>