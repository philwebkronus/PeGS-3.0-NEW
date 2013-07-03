<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Card Transaction History";
$currentpage = "Reports";

ini_set("display_errors",1);

App::LoadModuleClass("Loyalty", "Cards");
App::LoadModuleClass("Loyalty", "MemberCards");
App::LoadModuleClass("Loyalty", "CardTransactions");
App::LoadModuleClass("Kronus", "Sites");
App::LoadModuleClass('Membership', 'MemberInfo');

App::LoadControl("DataGrid");
App::LoadControl("Hidden");
App::LoadControl("Pagination");
App::LoadControl("TextBox");
App::LoadControl("Button");

App::LoadCore('Validation.class.php');

$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_MemberInfo = new MemberInfo();

$showresult = false;
$showcardinfo = false;

// Instantiate pagination object with appropriate arguments
$pagesPerSection = 10;       // How many pages will be displayed in the navigation bar
// former number of pages will be displayed
$options = 10; // Display options
$paginationID = "trans";     // This is the ID name for pagination object
$stylePageOff = "pageOff";     // The following are CSS style class names. See styles.css
$stylePageOn = "pageOn";
$styleErrors = "paginationErrors";
$styleSelect = "paginationSelect";
$page = QueryString::GetQueryString("trans-page");

$fproc = new FormsProcessor();

include_once("controller/cardsearchcontroller.php");

$fproc->ProcessForms();

if ($fproc->IsPostBack) 
{
    if($btnSearch->SubmittedValue == 'Search')
    {
        $totalEntries = $_CardTransactions->getTransactionCount($CardNumber);

        $Pagination = new Pagination($totalEntries, $pagesPerSection, $options, $paginationID, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect);
        $Pagination->gotoFirstPage();
        $start = $Pagination->getEntryStart();
        $end = $Pagination->getEntryEnd();

        $trans = $_CardTransactions->getTransactions($CardNumber, $start, $end);

        if($totalEntries > 0)
        {
            foreach($trans as $val)
            {
                $row = $val;
                $row['TerminalLogin'] = trim(str_replace(range(0,9),'',$row['TerminalLogin']));
                $newrow[] = $row;
            }

            $result = $newrow;

            $dgth = new DataGrid();
            $dgth->AddColumn("Site", "TerminalLogin", DataGridColumnType::Text, DataGridColumnAlignment::Center, '', "Total");
            $dgth->AddColumn("Transaction Type", "TransactionType", DataGridColumnType::Text, DataGridColumnAlignment::Center);
            $dgth->AddColumn("Amount", "Amount", DataGridColumnType::Money, DataGridColumnAlignment::Right, '', '', DataGridFooterCalculation::Sum);
            $dgth->AddColumn("Transaction Date", "TransactionDate", DataGridColumnType::Text, DataGridColumnAlignment::Center);
            $dgth->DataItems = $result;
            $dgtransactionhistory = $dgth->Render();

            if($newrow > 0 && $totalEntries > 0)
            {
                $showresult = true;
                $showcardinfo = true;
            }
        }
        else
        {
            
            if(isset($result) && count($result) > 0 )
                App::SetErrorMessage ('No transactions found');
            else
                App::SetErrorMessage('Invalid Card');
        }
    }
    
}



if (!empty($page) && isset($_SESSION['CardInfo']['CardNumber']) && $btnSearch->SubmittedValue != 'Search') {
    
    $showresult = true;
    $showcardinfo = true;
    $newrow = null;
    
    
    $CardNumber = $_SESSION['CardInfo']['CardNumber'];

    $totalEntries = $_CardTransactions->getTransactionCount($CardNumber);

    $Pagination = new Pagination($totalEntries, $pagesPerSection, $options, $paginationID, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect);
    $start = $Pagination->getEntryStart();
    $end = $Pagination->getEntryEnd();

    $trans2 = $_CardTransactions->getTransactions($CardNumber, $start, $end);
    
    if($totalEntries > 0)
    {        
        foreach($trans2 as $val)
        {
            $row = $val;
            $row['TerminalLogin'] = trim(str_replace(range(0,9),'',$row['TerminalLogin']));
            $newrow[] = $row;
        }

        $result = $newrow;
        
        $dgth = new DataGrid();
        $dgth->AddColumn("Site", "TerminalLogin", DataGridColumnType::Text, DataGridColumnAlignment::Center, '', "Total");
        $dgth->AddColumn("Transaction Type", "TransactionType", DataGridColumnType::Text, DataGridColumnAlignment::Center);
        $dgth->AddColumn("Amount", "Amount", DataGridColumnType::Money, DataGridColumnAlignment::Right, '', '', DataGridFooterCalculation::Sum);
        $dgth->AddColumn("Transaction Date", "TransactionDate", DataGridColumnType::Text, DataGridColumnAlignment::Right);
        $dgth->DataItems = $result;
        $dgtransactionhistory = $dgth->Render();
        
        if($newrow > 0 && $totalEntries > 0)
        {
            $showresult = true;
            $showcardinfo = true;
        }
    }
    else
    {
        if(isset($result) && count($result) > 0 )
            App::SetErrorMessage ('No transactions found');
        else
            App::SetErrorMessage('Invalid Card');
    }
    
}

?>
<?php include("header.php"); ?>

<div align="center">
    <div class="maincontainer">
        <?php include('menu.php'); ?>
        <div class="content">
            <?php include('cardsearch.php'); ?>
        <?php if($showresult)
        {?><br>
            <div align="right" class="pad5">
                <div style="float: left;" class="title">Transaction History</div>
            <?php echo $Pagination->display(); ?>
            </div>
            <div align="right" class="pad5">
                <?php 
                    if(isset($dgtransactionhistory)){
                        echo $dgtransactionhistory; 
                    }
                ?>
            </div>
        <?php
        }?>
        </div>
    </div>
</div>
<?php include("footer.php"); ?>