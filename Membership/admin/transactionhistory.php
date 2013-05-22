<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Card Transaction History";

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

$fp = new FormsProcessor();

/*
 * Search Objects
 */
$txtSearch = new TextBox('txtSearch', 'txtSearch', 'Search ');
$txtSearch->ShowCaption = false;
$txtSearch->CssClass = 'validate[required]';
$txtSearch->Text = $txtSearch->SubmittedValue;
$fp->AddControl($txtSearch);


$btnSearch = new Button('btnSearch', 'btnSearch', 'Search');
$btnSearch->ShowCaption = true;
$btnSearch->IsSubmit = true;
$fp->AddControl($btnSearch);

$_MemberCards = new MemberCards();
$_CardTransactions = new CardTransactions();
$_MemberInfo = new MemberInfo();

$fp->ProcessForms();

$showcreateform = false;

// Instantiate pagination object with appropriate arguments
$pagesPerSection = 10;       // How many pages will be displayed in the navigation bar
// former number of pages will be displayed
$options = array(5, 10, 25, 50, "All"); // Display options
$paginationID = "trans";     // This is the ID name for pagination object
$stylePageOff = "pageOff";     // The following are CSS style class names. See styles.css
$stylePageOn = "pageOn";
$styleErrors = "paginationErrors";
$styleSelect = "paginationSelect";

if ($fp->IsPostBack) {
    
    unset($_SESSION['CardInfo']);

    $searchValue = $txtSearch->SubmittedValue;

    $validate = new Validation();
    $showcreateform = true;

    if ($validate->validateEmail($searchValue)) {
        $result = $_MemberInfo->getMemberInfoByUsername($searchValue);
        $_SESSION['CardInfo']['Username'] = $searchValue;
        $MID = $result[0]['MID'];
        $cardInfo = $_MemberCards->getActiveMemberCardInfo($MID);
        $CardNumber = $cardInfo[0]['CardNumber'];
        $_SESSION['CardInfo']['CardNumber'] = $CardNumber;
    } else {
        $membercards = $_MemberCards->getMemberCardInfoByCard($searchValue);
        $MID = $membercards[0]['MID'];
        $CardNumber = $membercards[0]['CardNumber'];

        $_SESSION['CardInfo']['CardNumber'] = $searchValue;
    }

    $_SESSION['CardInfo']['MID'] = $MID;

    $totalEntries = $_CardTransactions->getTransCount($CardNumber);

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
    //$dgth->ShowFooter = true;
    $dgtransactionhistory = $dgth->Render();
}

$page = QueryString::GetQueryString("trans-page");

if (!empty($page) || isset($_SESSION['CardInfo']['CardNumber'])) {
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
    //$dgth->ShowFooter = true;
    $dgtransactionhistory = $dgth->Render();
}
?>
<?php include("header.php"); ?>
<div class="content">    
<?php
echo $txtSearch . $btnSearch;
?>
</form>

<?php include("menu.php"); ?>
    <div align="right" class="pad5">
    <?php
        echo $Pagination->display();

        // Display pagination display option selection interface
        //echo $Pagination->displaySelectInterface();

    ?>
    </div>
    <div align="right" class="pad5">
        <?php
        echo $dgtransactionhistory;
        ?>
    </div>
</div>
    <?php include("footer.php"); ?>