<?php
/*
 * Description: Displays Active, Banned Account Status Summary
 * @author: Junjun S. Hernandez
 * DateCreated: June 27, 2013 11:02:05AM
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Account Status Summary";
$currentpage = "Reports";

App::LoadModuleClass('Membership', 'MemberInfo');

App::LoadControl("DataGrid");

$_MemberInfo = new MemberInfo();

$memInfoActive = $_MemberInfo->getActiveAccountStatus();
$memInfoBanned = $_MemberInfo->getBannedAccountStatus();
$memInfoStatus = $_MemberInfo->getActiveAndBannedAccountStatus();

$countActive = count($memInfoActive);
$countBanned = count($memInfoBanned);
$countStatus = count($memInfoStatus);

//Get the total number of Both Active and Banned Account Status.
if ($countStatus > 0) {
    $ctr = 0;
    do {
        $memInfoStatusCount = $memInfoStatus[0]['COUNT(MID)'];
        $ctr = $ctr + 1;
    } while ($ctr != $countStatus);
}

//Get the total number of Active Account Status only.
if ($countActive > 0) {
    $ctr1 = 0;
    do {
        $memInfoActive[0]['StatusValue'] = $memInfoActive[0]['Status'] == 1 ? "Active" : ($memInfoActive[0]['Status'] == 5 ? "Banned" : "");
        $memInfoActive[0]['PercentValue'] = round(($memInfoActive[0]['COUNT(MID)'] / $memInfoStatusCount) * 100);
        $memInfoActive[0]['PercentValue'] = $memInfoActive[0]['PercentValue'] . " %";
        $ctr1 = $ctr1 + 1;
    } while ($ctr1 != $countActive);

    foreach ($memInfoActive as $value) {
        $newrow[] = $value;
    }
}

//Put the result of Active Account Status to Grid.

$result1 = $newrow;
$datagrid_bn = new DataGrid();
$datagrid_bn->AddColumn("Status", "'StatusValue", DataGridColumnType::Text, DataGridColumnAlignment::Center);
$datagrid_bn->AddColumn("Count", "COUNT(MID)", DataGridColumnType::Text, DataGridColumnAlignment::Center);
$datagrid_bn->AddColumn("Percentage", "PercentValue", DataGridColumnType::Text, DataGridColumnAlignment::Center);
$datagrid_bn->DataItems = $result1;

if ($countBanned > 0) {
    $ctr1 = 0;
    do {
        $memInfoBanned[0]['StatusValue'] = $memInfoBanned[0]['Status'] == 1 ? "Active" : ($memInfoBanned[0]['Status'] == 5 ? "Banned" : "");
        $memInfoBanned[0]['PercentValue'] = round(($memInfoBanned[0]['COUNT(MID)'] / $memInfoStatusCount) * 100);
        $memInfoBanned[0]['PercentValue'] = $memInfoBanned[0]['PercentValue'] . " %";
        $ctr1 = $ctr1 + 1;
    } while ($ctr1 != $countBanned);
}
foreach ($memInfoBanned as $value) {
    $newrow[] = $value;
}

//Put the result of Banned Account Status to Grid.

$result2 = $newrow;
$datagrid_bn = new DataGrid();
$datagrid_bn->AddColumn("Status", "StatusValue", DataGridColumnType::Text, DataGridColumnAlignment::Center);
$datagrid_bn->AddColumn("Count", "COUNT(MID)", DataGridColumnType::Text, DataGridColumnAlignment::Center);
$datagrid_bn->AddColumn("Percentage", "PercentValue", DataGridColumnType::Text, DataGridColumnAlignment::Center);
$datagrid_bn->DataItems = $result2;

//Render the Grid to display.
$acctstatussummary = $datagrid_bn->Render();

?>
<?php include("header.php"); ?>
<div align="center">
</form>
<form name="bannedplayerlists" id="bannedplayerlists" method="POST">
    <div class="maincontainer">
        <?php include('menu.php'); ?>
        <div class="content">
            <div align="right" class="pad5">
                <div style="float: left;" class="title">Account Status Summary:</div>
            </div>
            <div align="right" class="pad5">
                <?php echo $acctstatussummary; ?>
            </div>
        </div>
    </div>
</form>
</div>
<?php include("footer.php"); ?>