<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Banned Players";
$currentpage = "Reports";

App::LoadModuleClass("Loyalty", "Cards");
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "CardTransactions");
App::LoadModuleClass("Kronus", "Sites");
App::LoadModuleClass('Membership', 'BanningHistory');
App::LoadModuleClass('Membership', 'MemberInfo');

App::LoadCore('Validation.class.php');

App::LoadControl("DataGrid");
App::LoadControl("Hidden");
App::LoadControl("Pagination");
App::LoadControl("TextBox");
App::LoadControl("Button");

$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_MemberInfo = new MemberInfo();
$_BanningHistory = new BanningHistory();

$showresult = false;
$showcardinfo = false;
$defaultsearchvalue = "Enter Card Number or Player Name";

// Instantiate pagination object with appropriate arguments
$pagesPerSection = 10;       // How many pages will be displayed in the navigation bar
// former number of pages will be displayed
$options = array(5, 10, 25, 50, "All"); // Display options
$paginationID = "changestat";     // This is the ID name for pagination object
$stylePageOff = "pageOff";     // The following are CSS style class names. See styles.css
$stylePageOn = "pageOn";
$styleErrors = "paginationErrors";
$styleSelect = "paginationSelect";

$fproc = new FormsProcessor();

$txtSearch = new TextBox('txtSearch', 'txtSearch', 'Search ');
$txtSearch->ShowCaption = false;
$txtSearch->CssClass = 'validate[required]';
$txtSearch->Style = 'color: #666';
$txtSearch->Size = 30;
$txtSearch->Text = $defaultsearchvalue;
$txtSearch->AutoComplete = false;
$fproc->AddControl($txtSearch);

$btnSearch = new Button('btnSearch', 'btnSearch', 'Search');
$btnSearch->ShowCaption = true;
$btnSearch->IsSubmit = true;
$btnSearch->Enabled = false;
$fproc->AddControl($btnSearch);

$btnClear = new Button('btnClear', 'btnClear', 'Clear');
$btnClear->ShowCaption = true;
$btnClear->IsSubmit = true;
$fproc->AddControl($btnClear);

$fproc->ProcessForms();

if ($fproc->IsPostBack)
{
    if ($btnSearch->SubmittedValue == "Search" && $txtSearch->SubmittedValue != "")
    {
        unset($_SESSION['CardData']);
        $validate = new Validation();
        $searchValue = $txtSearch->SubmittedValue;
        if($validate->validateAlphaSpaceDashAndDot($searchValue)){
            $result =  $_MemberInfo->getMemberInfoByName($searchValue);
            $count = count($result);
            if($count > 0){
                $_SESSION['CardData']['Name'] = $searchValue;
            } else {
                App::SetErrorMessage("Player not found.");
            }
        } elseif (preg_match ("/^[A-Za-z0-9]+$/", $searchValue)) {
            $membercards = $_MemberCards->getMemberCardInfoByCardNumber($searchValue);
            $count = count($membercards);
            if($count > 0){
                $_SESSION['CardData']['CardNumber'] = $searchValue;
            } else {
                App::SetErrorMessage("Player not found.");
            }
        } else {
            App::SetErrorMessage("Invalid Input");
            $txtSearch->Text = $defaultsearchvalue;
        }
    }

    if ($btnClear->SubmittedValue == "Clear")
    {
        unset($_SESSION['CardData']);
        $txtSearch->Text = $defaultsearchvalue;
    }
}

    if(isset($_SESSION['CardData'])){
        if(isset($_SESSION['CardData']['Name'])){
            $txtSearch->Text = $_SESSION['CardData']['Name'];
            $result =  $_MemberInfo->getMemberInfoByName($_SESSION['CardData']['Name']);
            $count = count($result);
            if($count == 1) {
                $MID = $result[0]['MID'];
                $cardInfo = $_MemberCards->getMemberCardInfoByMID($MID);
                if($cardInfo[0]['Status'] == 5 ){
                    $remarks = $_BanningHistory->getRemarks($MID, 1);
                    if(isset($remarks[0])){
                        $memInfo[0]['Remarks'] =  $remarks[0]['Remarks'];
                    } else {
                        $memInfo[0]['Remarks'] =  '';
                    }
                    $memInfo[0]['MID'] =  $MID;
                    $memInfo[0]['CardNumber'] = $cardInfo[0]['CardNumber'];
                    $memInfo[0]['FullName'] = $result[0]['LastName'].', '.$result[0]['FirstName'];
                    $memInfo[0]['ID'] = $result[0]['IdentificationName'].' - '.$result[0]['IdentificationNumber'];
                    $bdate = new DateTime($result[0]['Birthdate']);
                    $memInfo[0]['Birthdate'] = $bdate->format('m/d/Y');
                    $memInfo[0]['Status'] = $cardInfo[0]['Status'];
                    $statusvalue = $cardInfo[0]['Status'] == 1  ?  "Active" : ($cardInfo[0]['Status'] == 5 ? "Banned": "");
                    $memInfo[0]['StatusValue'] = $statusvalue;
                    $memInfo[0]['MemberCardID'] = $cardInfo[0]['MemberCardID'];
                } else {
                    App::SetErrorMessage("Player is not banned.");
                }
                
            } elseif ($count > 1) {
                $ctr1 = 0;
                $ctr2 = 0;
                $ctr3 = 0;
                do{
                    $MID = $result[$ctr1]['MID'];
                    $data = $_MemberCards->getMemberCardInfoByMID($MID);

                    if($data[0]['Status'] == 5 ){
                        $remarks = $_BanningHistory->getRemarks($MID, 1);
                        if(isset($remarks[0])){
                            $data[0]['Remarks'] = $remarks[0]['Remarks'];
                        } else {
                            $data[0]['Remarks'] = '';
                        }
                        $data[0]['MID'] = $MID;
                        $data[0]['LastName'] = $result[$ctr1]['LastName'];
                        $data[0]['FirstName'] = $result[$ctr1]['FirstName'];
                        $data[0]['IdentificationName'] = $result[$ctr1]['IdentificationName'];
                        $data[0]['IdentificationNumber'] = $result[$ctr1]['IdentificationNumber'];
                        $data[0]['Birthdate'] = $result[$ctr1]['Birthdate'];
                        $cardInfo[$ctr3] = $data[0];
                        $ctr3++;
                    }
                    $ctr1++;
                }while($ctr1 != $count);
                if(isset($cardInfo) && count($cardInfo) != 0){
                    do{
                        $memInfo[$ctr2]['Remarks'] =  $cardInfo[$ctr2]['Remarks'];
                        $memInfo[$ctr2]['MID'] =  $cardInfo[$ctr2]['MID'];
                        $memInfo[$ctr2]['FullName'] = $cardInfo[$ctr2]['LastName'].', '.$cardInfo[$ctr2]['FirstName'];
                        $memInfo[$ctr2]['ID'] = $cardInfo[$ctr2]['IdentificationName'].' - '.$cardInfo[$ctr2]['IdentificationNumber'];
                        $bdate = new DateTime($cardInfo[$ctr2]['Birthdate']);
                        $memInfo[$ctr2]['Birthdate'] = $bdate->format('m/d/Y');
                        $memInfo[$ctr2]['Status'] = $cardInfo[$ctr2]['Status'];
                        $statusvalue = $cardInfo[$ctr2]['Status'] == 1  ?  "Active" : ($cardInfo[$ctr2]['Status'] == 5 ? "Banned": "");
                        $memInfo[$ctr2]['StatusValue'] = $statusvalue;
                        $memInfo[$ctr2]['CardNumber'] = $cardInfo[$ctr2]['CardNumber'];
                        $memInfo[$ctr2]['MemberCardID'] = $cardInfo[$ctr2]['MemberCardID'];
                        $ctr2++;
                    }while($ctr2 != count($cardInfo));
                } else {
                    App::SetErrorMessage('Player is not banned.');
                }
                
            } else {
                App::SetErrorMessage('Player not found');
            }
        } elseif (isset ($_SESSION['CardData']['CardNumber'])){
            $txtSearch->Text = $_SESSION['CardData']['CardNumber'];
            $membercards = $_MemberCards->getMemberCardInfoByCardNumber($_SESSION['CardData']['CardNumber']);
            $count = count($membercards);
            if($count > 0){
                    $MID = $membercards[0]['MID'];
                    $result = $_MemberInfo->getMemberInfoByMID($MID);
                    $CardNumber = $_SESSION['CardData']['CardNumber'];
                    if($result[0]['Status'] == 5){
                        $remarks = $_BanningHistory->getRemarks($MID, 1);
                        if(isset($remarks[0])){
                            $memInfo[0]['Remarks'] =  $remarks[0]['Remarks'];
                        } else {
                            $memInfo[0]['Remarks'] =  '';
                        }
                        $memInfo[0]['MID'] =  $MID;
                        $memInfo[0]['CardNumber'] = $CardNumber;
                        $memInfo[0]['FullName'] = $result[0]['LastName'].', '.$result[0]['FirstName'];
                        $memInfo[0]['ID'] = $result[0]['IdentificationName'].' - '.$result[0]['IdentificationNumber'];
                        $bdate = new DateTime($result[0]['Birthdate']);
                        $memInfo[0]['Birthdate'] = $bdate->format('m/d/Y');
                        $memInfo[0]['Status'] = $result[0]['Status'];
                        $statusvalue = $result[0]['Status'] == 1  ?  "Active" : ($result[0]['Status'] == 5 ? "Banned": "");
                        $memInfo[0]['StatusValue'] = $statusvalue;
                        $memInfo[0]['MemberCardID'] = $membercards[0]['MemberCardID'];
                    } else {
                        App::SetErrorMessage('Player is not banned.');
                    }
                } else {
                    App::SetErrorMessage('Card not found');
                }
        }
        if(isset($memInfo) && count($memInfo) != 0){
            $Pagination = new Pagination($count, $pagesPerSection, $options, $paginationID, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect);
            $start = $Pagination->getEntryStart();
            $end = $Pagination->getEntryEnd();

            if($count > 0){
                foreach ($memInfo as $value) {
                    $newrow[] = $value;
                }

                $result = $newrow;

                $datagrid_bn = new DataGrid();
                $datagrid_bn->AddColumn("Name", "FullName", DataGridColumnType::Text, DataGridColumnAlignment::Left, "", "Total");
                $datagrid_bn->AddColumn("Card Number", "CardNumber", DataGridColumnType::Text, DataGridColumnAlignment::Left);
                $datagrid_bn->AddColumn("ID", "ID", DataGridColumnType::Text, DataGridColumnAlignment::Left);
                $datagrid_bn->AddColumn("Birthdate", "Birthdate", DataGridColumnType::Text, DataGridColumnAlignment::Left);
                $datagrid_bn->AddColumn("Status", "StatusValue", DataGridColumnType::Text, DataGridColumnAlignment::Center);
                $datagrid_bn->AddColumn("Reason", "Remarks", DataGridColumnType::Text, DataGridColumnAlignment::Left);
                $datagrid_bn->DataItems = $result;
                $dgbannedplayerlists = $datagrid_bn->Render();

                if($newrow > 0 && $count >  0)
                {
                    $showresult = true;
                    $showcardinfo = true;
                }
            }
        }
        
    } else {
        $memcards = $_MemberCards->getAllBannedMemberCardInfo();
        $count = count($memcards);
        if($count > 1){
            $ctr1 = 0;
            $ctr2 = 0;
            do{
                $MID = $memcards[$ctr1]['MID'];
                $data = $_MemberInfo->getMemberInfoByMID($MID);
                $remarks = $_BanningHistory->getRemarks($MID, 1);
                if(isset($remarks[0])){
                    $data[0]['Remarks'] = $remarks[0]['Remarks'];
                } else {
                    $data[0]['Remarks'] = '';
                }
                $data[0]['MID'] = $MID;
                $data[0]['MemberCardID'] = $memcards[$ctr1]['MemberCardID'];
                $data[0]['CardNumber'] = $memcards[$ctr1]['CardNumber'];
                $cardInfo[$ctr1] = $data[0];
                $ctr1++;
            }while($ctr1 != $count);
            
            do{
                $memInfo[$ctr2]['Remarks'] =  $cardInfo[$ctr2]['Remarks'];
                $memInfo[$ctr2]['MID'] =  $cardInfo[$ctr2]['MID'];
                $memInfo[$ctr2]['FullName'] = $cardInfo[$ctr2]['LastName'].', '.$cardInfo[$ctr2]['FirstName'];
                $memInfo[$ctr2]['ID'] = $cardInfo[$ctr2]['IdentificationName'].' - '.$cardInfo[$ctr2]['IdentificationNumber'];
                $bdate = new DateTime($cardInfo[$ctr2]['Birthdate']);
                $memInfo[$ctr2]['Birthdate'] = $bdate->format('m/d/Y');
                $memInfo[$ctr2]['Status'] = $cardInfo[$ctr2]['Status'];
                $statusvalue = $cardInfo[$ctr2]['Status'] == 1  ?  "Active" : ($cardInfo[$ctr2]['Status'] == 5 ? "Banned": "");
                $memInfo[$ctr2]['StatusValue'] = $statusvalue;
                $memInfo[$ctr2]['CardNumber'] = $cardInfo[$ctr2]['CardNumber'];
                $memInfo[$ctr2]['MemberCardID'] = $cardInfo[$ctr2]['MemberCardID'];
                $ctr2++;
            }while($ctr2 != $count);
            
        }
        
        if(isset($memInfo) &&count($memInfo) != 0){
            $Pagination = new Pagination($count, $pagesPerSection, $options, $paginationID, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect);
            $start = $Pagination->getEntryStart();
            $end = $Pagination->getEntryEnd();

            if($count > 0){
                foreach ($memInfo as $value) {
                    $newrow[] = $value;
                }

                $result = $newrow;

                $datagrid_bn = new DataGrid();
                $datagrid_bn->AddColumn("Name", "FullName", DataGridColumnType::Text, DataGridColumnAlignment::Left, "", "Total");
                $datagrid_bn->AddColumn("Card Number", "CardNumber", DataGridColumnType::Text, DataGridColumnAlignment::Left);
                $datagrid_bn->AddColumn("ID", "ID", DataGridColumnType::Text, DataGridColumnAlignment::Left);
                $datagrid_bn->AddColumn("Birthdate", "Birthdate", DataGridColumnType::Text, DataGridColumnAlignment::Left);
                $datagrid_bn->AddColumn("Status", "StatusValue", DataGridColumnType::Text, DataGridColumnAlignment::Center);
                $datagrid_bn->AddColumn("Reason", "Remarks", DataGridColumnType::Text, DataGridColumnAlignment::Left);
                $datagrid_bn->DataItems = $result;
                $dgbannedplayerlists = $datagrid_bn->Render();

                if($newrow > 0 && $count >  0)
                {
                    $showresult = true;
                    $showcardinfo = true;
                }
            }
        }
        
        
    }

?>
<?php include("header.php"); ?>
<div align="center">
    </form>
    <form name="bannedplayerlists" id="bannedplayerlists" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                <?php include('bannedcardsearch.php'); ?>
                <?php if($showresult)
                {?>
                    <div align="right" class="pad5">
                    <div style="float: left;" class="title">Banned Players:</div>
                    <?php echo $Pagination->display(); ?>
                    </div>
                    <div align="right" class="pad5">
                        <?php echo $dgbannedplayerlists; ?>
                    </div>
                <?php
                }?>
            </div>
        </div>
    </form>
</div>
<?php include("footer.php"); ?>
