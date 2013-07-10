<?php
/**
 * Audit Trail Report
 * @author Mark Kenneth Esguerra <mgesguerra@philweb.com.ph>
 * Date Created: July 8, 2013
 */
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Audit Trail Report";
$currentpage = "Reports";

App::LoadControl("Button");
App::LoadControl("DatePicker");

$fproc = new FormsProcessor();

$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$transactionDate = new DatePicker("transactionDate","transactionDate","TransactionDate: ");
$transactionDate->MaxDate = $dsmaxdate->CurrentDate;
$transactionDate->MinDate = $dsmindate->CurrentDate - 22;
$transactionDate->SelectedDate = date('Y-m-d');
$transactionDate->Value = date('Y-m-d');
$transactionDate->ShowCaption = true;
$transactionDate->YearsToDisplay = "-100";
$transactionDate->CssClass = "validate[required]";
$transactionDate->isRenderJQueryScript = true;
$transactionDate->Size = 27;
$fproc->AddControl($transactionDate);

$btnQuery = new Button("btnQuery", "btnQuery", "Query");
$btnQuery->IsSubmit = true;
$btnQuery->ShowCaption = true;
$btnQuery->Enabled = true;
$btnQuery->Style = "margin-left: 100px;";
$fproc->AddControl($btnQuery);

$fproc->ProcessForms();

?>
<?php include("header.php"); ?>
<script type='text/javascript' src='js/jquery.jqGrid.js' media='screen, projection'></script>
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.jqgrid.css' />
<link rel='stylesheet' type='text/css' media='screen' href='css/ui.multiselect.css' />
<script type='text/javascript'>
    
    $(document).ready(function(){
        jQuery("#btnQuery").live('click', function(){
            getCardList();
            return false;
        });
        function loadData(){
                getCardList('');
        } 
        loadData();     
        function getCardList()
        {
            var date = $("#transactionDate").val();
            var url = "Helper/helper.audittrail.php";
            jQuery('#auditlogs').GridUnload();
            jQuery("#auditlogs").jqGrid({
                    url:url,
                    mtype:'post',
                    postData: {
                                TransactionDate : function() {return date;}
                              },
                    datatype: "json",
                    colNames:['Username', 'Transaction Details', 'Transaction Date','IP Address'],
                    colModel:[
                            {name: 'Username', index: 'username', align: 'left', width: 170},
                            {name: 'TransactionDetails', index: 'TransactionDetails', align: 'left', width: 350},
                            {name: 'TransactionDate', index: 'TransactionDate', align: 'left', width: 250},
                            {name: 'IPAddress', index: 'IPAddress', align: 'left', width: 130},   
                    ],
                            
                    rowNum: 10,
                    rowList: [10,20,30],
                    height: 250,
                    width: 970,
                    pager: "#pager2",
                    refresh: true,
                    viewrecords: true,
                    loadtext: "Loading...",
                    sortorder: "desc",
                    sortname: "AuditTrailID",
                    caption:"Audit Trail Reports"
            });
            jQuery("#auditlogs").jqGrid('navGrid','#pager2',
                                {
                                    edit:false,add:false,del:false, search:false, refresh: true});
        }
    });
</script>
<div align="center">
    </form>
    <form name="bannedaccountlists" id="bannedaccountlists" method="POST">
        <div class="maincontainer">
            <?php include('menu.php'); ?>
            <div class="content">
                    <br><br>
                    <?php echo $transactionDate; ?>
                    <?php echo $btnQuery; ?><br /><br />
                    <div align="center" id="pagination">
                        <table border="1" id="auditlogs">

                        </table>
                        <div id="pager2"></div>
                        <span id="errorMessage"></span>
                    </div>
            </div>
        </div>
    </form>
</div>
<?php include("footer.php"); ?>