<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Card Transaction History";
$currentpage = "Reports";

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
$options = array(5, 10, 25, 50, "All"); // Display options
$paginationID = "trans";     // This is the ID name for pagination object
$stylePageOff = "pageOff";     // The following are CSS style class names. See styles.css
$stylePageOn = "pageOn";
$styleErrors = "paginationErrors";
$styleSelect = "paginationSelect";

$fproc = new FormsProcessor();
include_once("controller/cardsearchcontroller.php");

$fproc->ProcessForms();

if ($fproc->IsPostBack) {
        
    $totalEntries = $_CardTransactions->getTransactionCount($CardNumber);

    $Pagination = new Pagination($totalEntries, $pagesPerSection, $options, $paginationID, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect);
    $start = $Pagination->getEntryStart();
    $end = $Pagination->getEntryEnd();

    $result = $_CardTransactions->getTransactions($CardNumber, $start, $end);
         
    $dgth = new DataGrid();
    $dgth->AddColumn("Site", "Site", DataGridColumnType::Text, DataGridColumnAlignment::Center, '', "Total");
    $dgth->AddColumn("Transaction Type", "TransactionType", DataGridColumnType::Text, DataGridColumnAlignment::Center);
    $dgth->AddColumn("Amount", "Amount", DataGridColumnType::Money, DataGridColumnAlignment::Right, '', '', DataGridFooterCalculation::Sum);
    $dgth->AddColumn("Transaction Date", "NewDate", DataGridColumnType::Text, DataGridColumnAlignment::Center);
    $dgth->DataItems = $result;
    $dgtransactionhistory = $dgth->Render();
    
    if($result > 0 && $totalEntries > 0)
    {
        $showresult = true;
        $showcardinfo = true;
    }
}

$page = QueryString::GetQueryString("trans-page");

if (!empty($page) && isset($_SESSION['CardInfo']['CardNumber'])) {
    
    $showresult = true;
    $showcardinfo = true;
    
    $CardNumber = $_SESSION['CardInfo']['CardNumber'];

    $totalEntries = $_CardTransactions->getTransactionCount($CardNumber);

    $Pagination = new Pagination($totalEntries, $pagesPerSection, $options, $paginationID, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect);
    $start = $Pagination->getEntryStart();
    $end = $Pagination->getEntryEnd();

    $result = $_CardTransactions->getTransactions($CardNumber, $start, $end);

    $dgth = new DataGrid();
    $dgth->AddColumn("Site", "Site", DataGridColumnType::Text, DataGridColumnAlignment::Center, '', "Total");
    $dgth->AddColumn("Transaction Type", "TransactionType", DataGridColumnType::Text, DataGridColumnAlignment::Center);
    $dgth->AddColumn("Amount", "Amount", DataGridColumnType::Money, DataGridColumnAlignment::Right, '', '', DataGridFooterCalculation::Sum);
    $dgth->AddColumn("Transaction Date", "TransactionDate", DataGridColumnType::Text, DataGridColumnAlignment::Center);
    $dgth->DataItems = $result;
    $dgtransactionhistory = $dgth->Render();
}

?>
<?php include("header.php"); ?>

<div align="center">
    <div class="maincontainer">
        <?php include('menu.php'); ?>
        <div class="content">
            <?php include('cardsearch.php'); ?>
        <?php if($showresult)
        {?>
            <div class="title">Transaction History</div>
            <div align="right" class="pad5">
            <?php echo $Pagination->display(); ?>
            </div>
            <div align="right" class="pad5">
                <?php echo $dgtransactionhistory; ?>
            </div>
        <?php
        }?>
        </div>
    </div>
</div>
<?php include("footer.php"); ?>