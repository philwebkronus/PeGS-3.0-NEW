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
$txtSearch->Text = $txtSearch->SubmittedValue;
$fp->AddControl($txtSearch);

$btnSearch = new Button('btnSearch', 'btnSearch', 'Search');
$btnSearch->ShowCaption = true;
$btnSearch->IsSubmit = true;
$fp->AddControl($btnSearch);

$fp->ProcessForms();

?>
<?php include('header.php'); ?>
<div class="content">    
     <?php
       echo $txtSearch . $btnSearch;
    ?>
    </form>
    <?php include('menu.php'); ?>
    <h2>Redemption</h2>
</div>
<?php include('footer.php'); ?>
