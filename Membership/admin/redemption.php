<?php

/*
 * @author : owliber
 * @date : 2013-05-20
 */

require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Redemption";

App::LoadControl("TextBox");
App::LoadControl("Button");

$fp = new FormsProcessor();

/*
 * Search Objects
 */
$txtSearch = new TextBox('txtSearch', 'txtSearch', 'Search ');
$txtSearch->ShowCaption = false;
$txtSearch->CssClass = 'validate[required]';
$txtSearch->Style = 'color: #666';
$txtSearch->Size = 30;

if(!empty($txtSearch->SubmittedValue) || isset($_SESSION['CardInfo']))
{
    (!empty($txtSearch->SubmittedValue)) ? 
        $txtSearch->Text = $txtSearch->SubmittedValue : 
        $txtSearch->Text = $_SESSION['CardInfo']['CardNumber'];
}
else
{
    $txtSearch->Text = "Card Number or Username";
    $txtSearch->Args = "onclick=\"$(this).val('')\"";
}
$fp->AddControl($txtSearch);

$btnSearch = new Button('btnSearch', 'btnSearch', 'Search');
$btnSearch->ShowCaption = true;
$btnSearch->IsSubmit = true;
$fp->AddControl($btnSearch);

$fp->ProcessForms();

if($fp->IsPostBack || isset($_SESSION['CardInfo'])) 
    $showcardinfo = true;
else
    $showcardinfo = false;

?>
<?php include('header.php'); ?>
<div class="content">    
     <?php
       echo $txtSearch . $btnSearch;
    ?>
    </form>
    <?php if($showcardinfo) include('cardinfo.php'); ?>
    <?php include('menu.php'); ?>
    <h2>Redemption</h2>
</div>
<?php include('footer.php'); ?>
