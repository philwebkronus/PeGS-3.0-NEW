<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Change Player Status";
$currentpage = "Administration";

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

$hdnAID = new Hidden("AID", "AID", "AID: ");
$hdnAID->ShowCaption = true;
$hdnAID->Text = $_SESSION['userinfo']['AID'];
$fproc->AddControl($hdnAID);

$hdnSearchType = new Hidden("SearchType", "SearchType", "SearchType: ");
$hdnSearchType->ShowCaption = true;
$hdnSearchType->Text = "";
$fproc->AddControl($hdnSearchType);

$hdnMemberCardID = new Hidden("MemberCardID", "MemberCardID", "MemberCardID: ");
$hdnMemberCardID->ShowCaption = true;
$hdnMemberCardID->Text = "";
$fproc->AddControl($hdnMemberCardID);

$hdnCardNumber = new Hidden("CardNumber", "CardNumber", "Card Number: ");
$hdnCardNumber->ShowCaption = true;
$hdnCardNumber->Text = "";
$fproc->AddControl($hdnCardNumber);

$hdnMID = new Hidden("MID", "MID", "MID: ");
$hdnMID->ShowCaption = true;
$hdnMID->Text = "";
$fproc->AddControl($hdnMID);

$hdnStatus = new Hidden("Status", "Status", "Status: ");
$hdnStatus->ShowCaption = true;
$hdnStatus->Text = "";
$fproc->AddControl($hdnStatus);

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
                $bhstatus = $cardInfo[0]['Status'] == 1 ? 2:1;
                $remarks = $_BanningHistory->getRemarks($MID, $bhstatus);
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
            } elseif ($count > 1) {
                $ctr1 = 0;
                $ctr2 = 0;
                $memInfo = array();
                do{
                    $MID = $result[$ctr1]['MID'];
                    $data = $_MemberCards->getMemberCardInfoByMID($MID);
                    $bhstatus = $data[0]['Status'] == 1 ? 2:1;
                    $remarks = $_BanningHistory->getRemarks($MID, $bhstatus);
                    if(isset($remarks[0])){
                        $data[0]['Remarks'] = $remarks[0]['Remarks'];
                    } else {
                        $data[0]['Remarks'] = '';
                    }
                    $data[0]['MID'] = $MID;
                    $cardInfo[$ctr1] = $data[0];
                    $ctr1++;
                }while($ctr1 != $count);

                do{
                    $memInfo[$ctr2]['Remarks'] =  $cardInfo[$ctr2]['Remarks'];
                    $memInfo[$ctr2]['MID'] =  $cardInfo[$ctr2]['MID'];
                    $memInfo[$ctr2]['FullName'] = $result[$ctr2]['LastName'].', '.$result[$ctr2]['FirstName'];
                    $memInfo[$ctr2]['ID'] = $result[$ctr2]['IdentificationName'].' - '.$result[$ctr2]['IdentificationNumber'];
                    $bdate = new DateTime($result[$ctr2]['Birthdate']);
                    $memInfo[$ctr2]['Birthdate'] = $bdate->format('m/d/Y');
                    $memInfo[$ctr2]['Status'] = $cardInfo[$ctr2]['Status'];
                    $statusvalue = $cardInfo[$ctr2]['Status'] == 1  ?  "Active" : ($cardInfo[$ctr2]['Status'] == 5 ? "Banned": "");
                    $memInfo[$ctr2]['StatusValue'] = $statusvalue;
                    $memInfo[$ctr2]['CardNumber'] = $cardInfo[$ctr2]['CardNumber'];
                    $memInfo[$ctr2]['MemberCardID'] = $cardInfo[$ctr2]['MemberCardID'];
                    $ctr2++;
                }while($ctr2 != $count);
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
                    
                    $bhstatus = $result[0]['Status'] == 1 ? 2:1;
                    $remarks = $_BanningHistory->getRemarks($MID, $bhstatus);
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
                    App::SetErrorMessage('Card not found');
                }
        }

        $Pagination = new Pagination($count, $pagesPerSection, $options, $paginationID, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect);
        $start = $Pagination->getEntryStart();
        $end = $Pagination->getEntryEnd();

        if($count > 0){
            foreach ($memInfo as $value) {
                $row = $value;
                $row['Status'] = "<a href='#' style='text-decoration:underline;' class='statuslink'  MemberCardID=".$row['MemberCardID']." CardNumber=".$row['CardNumber']." MID=".$row['MID']." Status=".$row['Status']." >".$row['StatusValue']."</a>";
                $newrow[] = $row;
            }

            $result = $newrow;

            $datagrid_bn = new DataGrid();
            $datagrid_bn->AddColumn("Name", "FullName", DataGridColumnType::Text, DataGridColumnAlignment::Left, "", "Total");
            $datagrid_bn->AddColumn("Card Number", "CardNumber", DataGridColumnType::Text, DataGridColumnAlignment::Left);
            $datagrid_bn->AddColumn("ID", "ID", DataGridColumnType::Text, DataGridColumnAlignment::Left);
            $datagrid_bn->AddColumn("Birthdate", "Birthdate", DataGridColumnType::Text, DataGridColumnAlignment::Left);
            $datagrid_bn->AddColumn("Status", "Status", DataGridColumnType::Text, DataGridColumnAlignment::Center);
            $datagrid_bn->AddColumn("Reason", "Remarks", DataGridColumnType::Text, DataGridColumnAlignment::Left);
            $datagrid_bn->DataItems = $result;
            $dgchangestat = $datagrid_bn->Render();

            if($newrow > 0 && $count >  0)
            {
                $showresult = true;
                $showcardinfo = true;
            }
        }
    }

?>
<?php include("header.php"); ?>
<script language="javascript" type='text/javascript'>
    $(document).ready(function(){
        
        $("#updatestatus").validationEngine();
        
        $(".statuslink").live('click', function(){
            var cardnumber = $(this).attr('CardNumber');
            if(cardnumber != ''){
                $("#CardNumber").val(cardnumber);
                $("#MemberCardID").val($(this).attr('MemberCardID'));
                var status = $(this).attr('Status');
                if(status == 5 ){
                    $("#Status").val(1);
                } else {
                    $("#Status").val(5);
                }
                $("#MID").val($(this).attr('MID'));

                    if($("#updates").dialog('isOpen') !== true){
                        $("#updates").dialog({
                            modal: true,
                            width: 300,
                            height: 'auto',
                            position: 'center',
                            buttons: {
                                "Submit": function(){
                                    if($("#updatestatus").validationEngine("validate")){
                                        $.post("ajaxhandler.php",
                                        {
                                            'Module': 'Admin',
                                            'Class': 'PlayerBanningWrapper',
                                            'Method': 'updatePlayerStatus',
                                            'MethodArgs': $("#updatestatus").serialize()
                                        },
                                        function(data){
                                            if(data != "Change Player Status: Transaction Successful."){
                                                $("#failedmessage").html("<p>"+data+"</p>");
                                                $("#failedmessage").dialog({
                                                    modal: true,
                                                    width: 350,
                                                    height: 'auto',
                                                    position: 'center',
                                                    buttons: {
                                                        "Ok": function(){
                                                            $("#updatestatus").dialog('close');
                                                            $(this).dialog('close');
                                                            $("#updateplayerstatus").submit();
                                                        }
                                                    }
                                                });
                                            } else {
                                                $("#successmessage").html("<p>"+data+"</p>");
                                                $("#successmessage").dialog({
                                                    modal: true,
                                                    width: 350,
                                                    height: 'auto',
                                                    position: 'center',
                                                    buttons: {
                                                        "Ok": function(){
                                                            $("#updatestatus").dialog('close');
                                                            $(this).dialog('close');
                                                            $("#updateplayerstatus").submit();
                                                        }
                                                    }
                                                });
                                            }
                                        }, 'json');
                                    }
                                },
                                "Cancel": function(){
                                    $("#txtRemarks").val('');
                                    $(this).dialog('close');
                                }
                            },
                            title: 'Change Player Status'
                        }).parent().appendTo($("#updatestatus"));
                    }
            }

        });
    });
</script>
<div align="center">
    </form>
    <form name="updateplayerstatus" id="updateplayerstatus" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                <?php include('bannedcardsearch.php'); ?>
                <?php if($showresult)
                {?>
                    <div align="right" class="pad5">
                    <?php echo $Pagination->display(); ?>
                    </div>
                    <div align="right" class="pad5">
                        <?php echo $dgchangestat; ?>
                    </div>
                <?php
                }?>
            </div>
        </div>
    </form>
    <form name="updatestatus" class="updatestatus" id="updatestatus">
        <div id="updates" class="updates" style="display:none;">
            <?php echo $hdnAID; ?>
            <?php echo $hdnMemberCardID; ?>
            <?php echo $hdnCardNumber; ?>
            <?php echo $hdnMID; ?>
            <?php echo $hdnStatus; ?>
            <p align="left">Do you wish to Ban/Unban this Player?</p>
            <p align="left">Please input remarks to continue.</p>
            <label style="float: left;width: 60px;display: block;">Remarks:</label>
            <div style="float:right;"><textarea name="txtRemarks" id="txtRemarks" cols="20" rows="1" class="validate[required]" style="color: #666; width: 200px;"></textarea></div>
            <br>
        </div>
    </form>
    <div id="successmessage" style="display:none; color: green;">
        
    </div>
    <div id="failedmessage" style="display:none; color: red;">
        
    </div>
</div>
<?php include("footer.php"); ?>