<?php

/*
 * @author : owliber
 * @date : 2013-05-20
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Banned Players";
$currentpage = "Reports";

App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("DataGrid");

App::LoadModuleClass("Admin", "Players");

$players = new Players();

$fproc = new FormsProcessor();

/*
 * Search Objects
 */

$defaultsearchvalue = "Enter Card Number or Username";
$showresult = false;

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

//Display banned players on page load
$bannedplayers = $players->getBannedPlayers();

$datagrid = new DataGrid();
$datagrid->AddColumn("Username", "UserName", DataGridColumnType::Text, DataGridColumnAlignment::Left,'','','','30%');
$datagrid->AddColumn("Card Number", "CardNumber", DataGridColumnType::Text, DataGridColumnAlignment::Center);
$datagrid->AddColumn("First Name", "FirstName", DataGridColumnType::Text, DataGridColumnAlignment::Center);
$datagrid->AddColumn("Last Name", "LastName", DataGridColumnType::Text, DataGridColumnAlignment::Center);

if($fproc->IsPostBack) 
{
    if($btnSearch->SubmittedValue == "Search")
    {
        $searchValue = $txtSearch->SubmittedValue;
        $bannedplayers = $players->getBannedPlayersByFilter($searchValue);

        $showresult = true;
    }    
    
    if ($btnClear->SubmittedValue == "Clear")
    {
        unset($_SESSION['CardInfo']);
        $txtSearch->Text = $defaultsearchvalue;
    }
    
}



$datagrid->DataItems = $bannedplayers;
$datagrid->Style = "width: 80%";
$resultdata = $datagrid->Render();

?>
<?php include('header.php'); ?>
<script language="javascript" type="text/javascript">
    $(document).ready(
    function()
    {
        defaultvalue = "<?php echo $defaultsearchvalue; ?>";
        $("#txtSearch").click(function(){
            $("#txtSearch").change();
            if ($("#txtSearch").val() == defaultvalue)
            {
                $("#txtSearch").val("");
                $("#btnSearch").attr("disabled", "disabled");
            }
        });
        $("#txtSearch").keyup(function(){
            $("#txtSearch").change();
        });
        $("#txtSearch").blur(function(){
            $("#txtSearch").change();
        });
        $("#txtSearch").change(function(){
            if ($("#txtSearch").val() == "" || $("#txtSearch").val() == defaultvalue)
            {
                $("#btnSearch").attr("disabled", "disabled");
                $("#txtSearch").val(defaultvalue);
            }
            else
            {
                $("#btnSearch").removeAttr("disabled");
            }
            
        });
        $("#btnClear").click(function(){
            $("#txtSearch").val("");
            $("#txtSearch").change();
        });
        
    
    });
</script>
<div align="center">
    <div class="maincontainer">
        <?php include('menu.php'); ?>
        <div class="content">  
            <div class="searchbar formstyle">
                <?php
                echo $txtSearch . $btnSearch . $btnClear;
                ?>
                </form>
            </div>
            <?php if($showresult || count($bannedplayers) > 0)
            {?>
            <div class="title">Banned Players</div>
            <?php
            echo $resultdata;
            ?>
            <?php
            }?>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>
