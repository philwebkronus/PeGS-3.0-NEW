<?php

/*
 * @author : owliber
 * @date : 2013-05-20
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Banned Players";

App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("DataGrid");

App::LoadModuleClass("Admin", "Players");

$players = new Players();

$fp = new FormsProcessor();

/*
 * Search Objects
 */
$txtSearch = new TextBox('txtSearch', 'txtSearch', 'Search ');
$txtSearch->ShowCaption = false;
$txtSearch->CssClass = 'validate[required]';
$txtSearch->Style = 'color: #666';
$txtSearch->Size = 40;

if(!empty($txtSearch->SubmittedValue) || isset($_SESSION['CardInfo']))
{
    (!empty($txtSearch->SubmittedValue)) ? 
        $txtSearch->Text = $txtSearch->SubmittedValue : 
        $txtSearch->Text = $_SESSION['CardInfo']['CardNumber'];
}
else
{
    $txtSearch->Text = "Card Number, Username, Fname or Lname";
    $txtSearch->Args = "onclick=\"$(this).val('')\"";
}
$fp->AddControl($txtSearch);

$btnSearch = new Button('btnSearch', 'btnSearch', 'Search');
$btnSearch->ShowCaption = true;
$btnSearch->IsSubmit = true;
$fp->AddControl($btnSearch);

$fp->ProcessForms();

//Display banned players on page load
$bannedplayers = $players->getBannedPlayers();

$datagrid = new DataGrid();
$datagrid->AddColumn("Username", "UserName", DataGridColumnType::Text, DataGridColumnAlignment::Left,'','','','30%');
$datagrid->AddColumn("Card Number", "CardNumber", DataGridColumnType::Text, DataGridColumnAlignment::Center);
$datagrid->AddColumn("First Name", "FirstName", DataGridColumnType::Text, DataGridColumnAlignment::Center);
$datagrid->AddColumn("Last Name", "LastName", DataGridColumnType::Text, DataGridColumnAlignment::Center);

if($fp->IsPostBack) 
{
    $searchValue = $txtSearch->SubmittedValue;
    $bannedplayers = $players->getBannedPlayersByFilter($searchValue);
    
}

$datagrid->DataItems = $bannedplayers;
$datagrid->Style = "width: 80%";
$resultdata = $datagrid->Render();

?>
<?php include('header.php'); ?>
<div id="page-wrap">    
     <?php
       echo $txtSearch . $btnSearch;
    ?>
    </form>
    <?php include('menu.php'); ?>
</div>
<div id="page-wrap">
    <div class="title">Banned Players</div>
    <?php
    echo $resultdata;
    
    ?>
</div>
<?php include('footer.php'); ?>
