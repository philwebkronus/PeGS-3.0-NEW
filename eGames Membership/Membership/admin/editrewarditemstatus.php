<?php
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Update Reward Items";
$currentpage = "Administration";

App::LoadModuleClass("Loyalty", "RewardItems");
App::LoadModuleClass("Loyalty", "RewardItemDetails");
App::LoadModuleClass("Loyalty", "RewardOffers");
App::LoadModuleClass("Membership", "AuditFunctions");
App::LoadModuleClass("Membership", "AuditTrail");

App::LoadCore('Validation.class.php');

App::LoadControl("DatePicker");
App::LoadControl("TextBox");
App::LoadControl("Button");
App::LoadControl("Hidden");

$rewarditems = new RewardItems();
$rewarditemdetails = new RewardItemDetails();
$rewardoffers = new RewardOffers();
$_Log = new AuditTrail();

if (isset($_SESSION['rewarditemdetails'])) {

    $rewarditemdetailsz = $_SESSION['rewarditemdetails'];

    foreach ($rewarditemdetailsz as $vview) {
        $rewarditemid = $vview['RewardItemID'];
        $status = $vview['Status'];
    }
    switch ($status)
            {
                case 0:
                    $rstatuz = "Inactive";
                break;
                case 1:
                    $rstatuz = "Active";
                break;
            }
} else {
    
}

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

$btnChangeStatus = new Button("btnChangeStatus", "btnChangeStatus", "Update Status");
$btnChangeStatus->ShowCaption = true;
$btnChangeStatus->IsSubmit = true;
$btnChangeStatus->Enabled = true;
$fproc->AddControl($btnChangeStatus);

$fproc->ProcessForms();

$showresult = false;

if ($fproc->IsPostBack) {
    //for Update Status button
    if ($btnChangeStatus->SubmittedValue == "Update Status") {
        if (isset($_POST['status']) && isset($_POST['oldstatus'])) {

            $rewarditemid = $_POST['rewarditemid'];
            $status = $_POST['status'];
            $oldstatus = $_POST['oldstatus'];

            $arrEntry['RewardItemID'] = $rewarditemid;
            $arrEntry['Status'] = $status;

            $aid = $_SESSION['aID'];
            //check if reward offers item exist 
            $itemexist = $rewardoffers->checkifItemExist($rewarditemid);
            foreach ($itemexist as $value) {
                $counter = $value['Count'];
            }
            //update status
            $lastid = $rewarditems->UpdateByArray($arrEntry);

            if ($counter > 0) {
                $rewardoffers->updateStatus($status, $rewarditemid, $aid);
            }

            //check affected rows
            if ($lastid > 0) {
                $arrEntry2['RewardItemID'] = $rewarditemid;
                $arrEntry2['DateUpdated'] = "now_usec()";
                $arrEntry2['UpdatedByAID'] = $_SESSION['aID'];

                $rewarditems->UpdateByArray($arrEntry2);

                $_Log->logEvent(AuditFunctions::MARKETING_UPDATE_REWARD_ITEM, ':Successful', array('ID' => $_SESSION['userinfo']['AID'], 'SessionID' => $_SESSION['userinfo']['SessionID']));
                $_SESSION['msg'] = 'Reward Item Status: Successfully Updated';
                header("Location: viewrewarditems.php");
            } else {
                $_SESSION['msg'] = 'Reward Item Status: Status did not change';
                header("Location: viewrewarditems.php");
            }
        } else {
            $_SESSION['msg'] = 'Please complete required fields';
            header("Location: viewrewarditems.php");
        }
        unset($_SESSION['rewarditemdetails']);
    }
} else {
    $showresult = false;
}
?>
<?php include("header.php"); ?>
<script>
    $(document).ready(function(){

        function loadItems(){
            
            if($('#oldstatus').val() == '1'){
                $('#statusactive').attr('checked',true);
                $('#statusinactive').attr('checked',false);
            }
            else{
                $('#statusinactive').attr('checked',true);
                $('#statusactive').attr('checked',false);
            }

        }
        
        loadItems();
         
        $("input[name='statusactive']").click(function()
        {
            $('#statusinactive').attr('checked',false);
            $('#status').val('1');
        });

        $("input[name='statusinactive']").click(function()
        {
            $('#statusactive').attr('checked',false);
            $('#status').val('0');
        });
        
    });    
</script>
<style>
    <!--
    .tab { margin-left: 40px; }
    .tab2 { margin-left: 650px; }
    -->
</style>
<div align="center">
    <div class="maincontainer">
<?php include('menu.php'); ?>
        <br />
        <div class="title" style="margin-left: 40px;">Update Reward Items Status</div>
        <br />
        <hr color="black" />
        <br />
        <div align="center">
            <input type="hidden" name="rewarditemid" id="rewarditemid" value="<?php echo "$rewarditemid"; ?>" />
            <input type="hidden" name="status" id="status" value="<?php echo "$status"; ?>" />
            <input type="hidden" name="oldstatus" id="oldstatus" value="<?php echo "$status"; ?>" />
            <br/>
            <div>
                <table>
                    <tr>
                    </tr>
                    <tr>
                        <td>Current Status : </td>
                        <td>
                        <input type="text" readonly="readonly" value="<?php echo  $rstatuz; ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input type="radio" id="statusactive" name="statusactive" value="1" />Active&nbsp;&nbsp;&nbsp;
                            <input type="radio" id="statusinactive" name="statusinactive" value="0"  />Inactive
                        </td>
                    </tr>
                </table>
            </div>
            <br/>
            <br/>

<?php echo "$btnChangeStatus"; ?>


            <br/><br/><br/>
        </div>
    </div>

</div>
<?php include("footer.php"); ?>