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

    if(isset($_SESSION['rewarditemdetails'])){
        
        $rewarditemdetailsz = $_SESSION['rewarditemdetails'];
        
        foreach ($rewarditemdetailsz as $vview) {
                $rewarditemid = $vview['RewardItemID'];
                $rewarditemname = $vview['RewardItemName'];
                $rewarditemdesc = $vview['RewardItemDescription'];
                $rewarditemprice = $vview['RewardItemPrice'];
                $rewarditemcode = $vview['RewardItemCode'];
                $rewarditemcount = $vview['RewardItemCount'];
                $expirydate = $vview['ExpiryDate'];
                $availableitemcount = $vview['AvailableItemCount'];
                $showinhomepage = $vview['ShowInHomePage'];
                $iscouponz = $vview['IsCoupon'];
                $rewarditemimage = $vview['RewardItemImagePath'];
                $status = $vview['Status'];
                $headerone = $vview['HeaderOne'];
                $headertwo = $vview['HeaderTwo'];
                $headerthree = $vview['HeaderThree'];
                $detailsoneA = $vview['DetailsOneA'];
                $detailsoneB = $vview['DetailsOneB'];
                $detailsoneC = $vview['DetailsOneC'];
                $detailstwoA = $vview['DetailsTwoA'];
                $detailstwoB = $vview['DetailsTwoB'];
                $detailstwoC = $vview['DetailsTwoC'];
                $detailsthreeA = $vview['DetailsThreeA'];
                $detailsthreeB = $vview['DetailsThreeB'];
                $detailsthreeC = $vview['DetailsThreeC'];
         
        }
    }
    else{
        
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
$txtRewardItemName = new TextBox("txtRewardItemName", "txtRewardItemName", "FirstName");
$txtRewardItemName->ReadOnly = false;
$txtRewardItemName->ShowCaption = false;
$txtRewardItemName->Text = "$rewarditemname";
$txtRewardItemName->Length = 30;
$txtRewardItemName->Size = 15;
$txtRewardItemName->CssClass = "validate[required]";
$txtRewardItemName->Args = 'onkeypress="javascript: return alphanumeric4(event)"';
$fproc->AddControl($txtRewardItemName);

$txtRewardItemDesc = new TextBox("txtRewardItemDesc", "txtRewardItemDesc", "FirstName");
$txtRewardItemDesc->ReadOnly = false;
$txtRewardItemDesc->ShowCaption = false;
$txtRewardItemDesc->Text = "$rewarditemdesc";
$txtRewardItemDesc->Length = 50;
$txtRewardItemDesc->Size = 15;
$txtRewardItemDesc->CssClass = "validate[required]";
$txtRewardItemDesc->Args = 'onkeypress="javascript: return alphanumeric4(event)"';
$fproc->AddControl($txtRewardItemDesc);

$txtItemCode = new TextBox("txtItemCode", "txtItemCode", "Item Code");
$txtItemCode->ReadOnly = false;
$txtItemCode->ShowCaption = false;
$txtItemCode->Text = "$rewarditemcode";
$txtItemCode->Length = 15;
$txtItemCode->Size = 15;
$txtItemCode->CssClass = "validate[required, custom[onlyLetterNumber]]";
$fproc->AddControl($txtItemCode);

$txtRewardItemPrice = new TextBox("txtRewardItemPrice", "txtRewardItemPrice", "FirstName");
$txtRewardItemPrice->ReadOnly = false;
$txtRewardItemPrice->ShowCaption = false;
$txtRewardItemPrice->Text = "$rewarditemprice";
$txtRewardItemPrice->Length = 30;
$txtRewardItemPrice->Size = 15;
$txtRewardItemPrice->CssClass = "validate[required]";
$txtRewardItemPrice->Args = 'onkeypress="javascript: return numberonly(event)"';
$fproc->AddControl($txtRewardItemPrice);

$txtRewardItemCount = new TextBox("txtRewardItemCount", "txtRewardItemCount", "FirstName");
$txtRewardItemCount->ReadOnly = false;
$txtRewardItemCount->ShowCaption = false;
$txtRewardItemCount->Text = "$rewarditemcount";
$txtRewardItemCount->Length = 30;
$txtRewardItemCount->Size = 15;
$txtRewardItemCount->CssClass = "validate[required, custom[onlyNumber], minSize[1]]";
$txtRewardItemCount->Args = 'onkeypress="javascript: return numberonly(event)"';
$fproc->AddControl($txtRewardItemCount);

$dsmaxdate = new DateSelector();
$dsmindate = new DateSelector();

$dsmaxdate->AddYears(+21);
$dsmindate->AddYears(-100);
$expirationdate = new DatePicker("expirationDate", "expirationDate", "From");
$expirationdate->MaxDate = $dsmaxdate->CurrentDate;
$expirationdate->MinDate = $dsmindate->CurrentDate;
$expirationdate->ShowCaption = false;
$expirationdate->SelectedDate = $expirydate;
$expirationdate->Value = $expirydate;
$expirationdate->YearsToDisplay = "-100";
$expirationdate->CssClass = "validate[required]";
$expirationdate->isRenderJQueryScript = true;
$expirationdate->Size = 27;
$fproc->AddControl($expirationdate);

$btnUpdate = new Button("btnUpdate", "btnUpdate", "Update");
$btnUpdate->ShowCaption = true;
$btnUpdate->IsSubmit = true;
$btnUpdate->Enabled = true;
$fproc->AddControl($btnUpdate);

$btnStatus = new Button("btnStatus", "btnStatus", "ChangesStatus");
$btnStatus->ShowCaption = true;
$btnStatus->IsSubmit = false;
$btnStatus->Enabled = true;
$fproc->AddControl($btnStatus);

$btnChangeStatus = new Button("btnChangeStatus", "btnChangeStatus", "UpdateStatus");
$btnChangeStatus->ShowCaption = true;
$btnChangeStatus->IsSubmit = true;
$btnChangeStatus->Enabled = true;
$fproc->AddControl($btnChangeStatus);

$fproc->ProcessForms();

$showresult = false;

if($fproc->IsPostBack)
{
    if($btnUpdate->SubmittedValue == "Update")
    {
        if(isset($_POST['txtRewardItemName']) && isset($_POST['rewarditemdesc']) &&  isset($_POST['txtRewardItemPrice'])
           && isset($_POST['txtRewardItemCount']) && isset($_POST['expirationDate']) && isset($_POST['firstheader'])
                && isset($_POST['detailone1']) ){

                if($_FILES['picUpload1']['size'] > 0){

                    if (isset($_FILES["picUpload1"]["name"])){

                        $rewarditemid = $_POST['rewarditemid']; 
                        $rewarditemname = $_POST['txtRewardItemName'];
                        $rewarditemdesc = $_POST['rewarditemdesc'];
                        $rewarditemcode = $_POST['txtItemCode'];
                        $rewarditemprice = $_POST['txtRewardItemPrice'];
                        $rewarditemcount = $_POST['txtRewardItemCount'];
                        $expirationdate = $_POST['expirationDate'];

                        $firstheader = $_POST['firstheader'];
                        $detail11 = $_POST['detailone1'];

                        if(isset($_POST['detailtwo1'])){
                            $detail12 = $_POST['detailtwo1'];
                        }
                        else{
                            $detail12 = '';
                        }

                        if(isset($_POST['detailthree1'])){
                            $detail13 = $_POST['detailthree1'];
                        }
                        else{
                            $detail13 = '';
                        }

                        if(isset($_POST['secondheader'])){
                            $secondheader = $_POST['secondheader'];
                        }
                        else{
                            $secondheader = '';
                        }

                        if(isset($_POST['detailone2'])){
                            $detail21 = $_POST['detailone2'];
                        }
                        else{
                            $detail21 = '';
                        }

                        if(isset($_POST['detailtwo2'])){
                            $detail22 = $_POST['detailtwo2'];
                        }
                        else{
                            $detail22 = '';
                        }

                        if(isset($_POST['detailthree2'])){
                            $detail23 = $_POST['detailthree2'];
                        }
                        else{
                            $detail23 = '';
                        }

                        if(isset($_POST['thirdheader'])){
                            $thirdheader = $_POST['thirdheader'];
                        }
                        else{
                            $thirdheader = '';
                        }

                        if(isset($_POST['detailone3'])){
                            $detail31 = $_POST['detailone3'];
                        }
                        else{
                            $detail31 = '';
                        }

                        if(isset($_POST['detailtwo3'])){
                            $detail32 = $_POST['detailtwo3'];
                        }
                        else{
                            $detail32 = '';
                        }

                        if(isset($_POST['detailthree3'])){
                            $detail33 = $_POST['detailthree3'];
                        }
                        else{
                            $detail33 = '';
                        }

                        if(isset($_POST['rtyes'])){
                            $rtyes = $_POST['rt'];
                            $rewardtype = $rtyes;
                        }
                        else{
                            $rtno = $_POST['rt'];
                            $rewardtype = $rtno;
                        }

                        if(isset($_POST['vihpyes'])){
                            $vihpyes = $_POST['vihp'];
                            $viewhomepage = $vihpyes;
                        }
                        else{
                            $vihpno = $_POST['vihp'];
                            $viewhomepage = $vihpno;
                        }

                        
                        $filename1 = $_FILES['picUpload1']['name'];
                        $size1 = $_FILES['picUpload1']['size'];   
                        $tmp_name1 = $_FILES['picUpload1']['tmp_name'];

                        $filename2 = $_FILES['picUpload2']['name'];
                        $size2 = $_FILES['picUpload2']['size'];  
                        $tmp_name2 = $_FILES['picUpload2']['tmp_name'];
                        
                        $filename3 = $_FILES['picUpload3']['name'];
                        $size3 = $_FILES['picUpload3']['size'];  
                        $tmp_name3 = $_FILES['picUpload3']['tmp_name'];
                        
                            //check if file size is lessthan 200KB
                            if($size1 < 204800 && $size2 < 204800 && $size3 < 204800){

                                list($filename1, $exten) = preg_split("/\./", $filename1);
                                list($filename2, $exten2) = preg_split("/\./", $filename2);
                                list($filename3, $exten3) = preg_split("/\./", $filename3);
                                
                                list($small, $name) = preg_split("/\_/", $filename1);
                                list($med, $name2) = preg_split("/\_/", $filename2);
                                list($large, $name3) = preg_split("/\_/", $filename3);
                                
                            //check if file name is the same with the other images    
                            if($name == $name2 && $name2 == $name3){
                                
                                
                                $upload_path = App::getParam("images_directory");

                                $rewarditemid = "$rewarditemid";
                                $rewarditemname = "$rewarditemname";
                                $rewarditemdesc = "$rewarditemdesc";
                                $rewarditemcode = "$rewarditemcode";
                                $rewarditemprice = "$rewarditemprice";
                                $expdate = "$expirationdate";
                                $rewarditemcount = "$rewarditemcount";
                                $rewarditemimagepath = "$name"."."."$exten";
                                $iscoupon = "$rewardtype";
                                $showinhomepage = "$viewhomepage";
                                $aid = $_SESSION['aID'];
                                
                                
                                $rewarditems->StartTransaction();
                                
                                   $lastid = $rewarditems->updateRewardItem($rewarditemname, $rewarditemdesc, 
                                           $rewarditemcode, $rewarditemimagepath, $expdate, $rewarditemcount, 
                                           $rewarditemprice, $iscoupon, $showinhomepage, $aid, $rewarditemid);
                                
                                
                                $headerone = $firstheader;
                                $headertwo = $secondheader;
                                $headerthree = $thirdheader;
                                $detailsoneA = $detail11;
                                $detailsoneB = $detail12;
                                $detailsoneC = $detail13;
                                $detailstwoA = $detail21;
                                $detailstwoB = $detail22;
                                $detailstwoC = $detail23;
                                $detailsthreeA = $detail31;
                                $detailsthreeB = $detail32;
                                $detailsthreeC = $detail33;

                                if($lastid == true){
                                    
                                    
                                    $rewarditemdetails->StartTransaction();
                                    
                                    $lastid2 = $rewarditemdetails->updateHeaders($headerone, $detailsoneA, 
                                            $headertwo, $headerthree, $detailsoneB, $detailsoneC, $detailstwoA, 
                                            $detailstwoB, $detailstwoC, $detailsthreeA, $detailsthreeB, $detailsthreeC,
                                            $rewarditemid);
                                    
                                        if($lastid2 == true){

                                        $rewarditems->CommitTransaction();    
                                        $rewarditemdetails->CommitTransaction();

                                             if (move_uploaded_file($tmp_name1, $upload_path . '/' . "$filename1" . '.'."$exten")) {
                                                if (move_uploaded_file($tmp_name2, $upload_path . '/' . "$filename2" . '.'."$exten")) {
                                                    if (move_uploaded_file($tmp_name3, $upload_path . '/' . "$filename3" . '.'."$exten")) {
                                                        $upload = 1;
                                                        }
                                                    }
                                                }
                                                else{
                                                    $upload = 0;
                                                }


                                                if($upload > 0){
                                                    $_Log->logEvent(AuditFunctions::MARKETING_UPDATE_REWARD_ITEM, 'Update Status:Successful', array('ID'=>$_SESSION['userinfo']['AID'], 'SessionID'=>$_SESSION['userinfo']['SessionID']));
                                                    $_SESSION['msg'] = 'Reward Item Successfully Updated';
                                                    header("Location: viewrewarditems.php");
                                                }
                                                else{
                                                    $_SESSION['msg'] = 'Error in Inserting New Item';
                                                    header("Location: viewrewarditems.php");
                                                }


                                    }
                                    else{
                                        $rewarditems->RollBackTransaction();
                                        $rewarditemdetails->RollBackTransaction();
                                        $_SESSION['msg'] = 'Failed to update in reward details table';
                                        header("Location: viewrewarditems.php");
                                    }
                                }
                                else{
                                    $rewarditems->RollBackTransaction();
                                    $_SESSION['msg'] = 'Failed to update in reward items table';
                                    header("Location: viewrewarditems.php");
                                }
                                
                                }
                                else{
                                    $_SESSION['msg'] = 'Failed to update records, Upload Image must be the same';
                                    header("Location: viewrewarditems.php");;
                                }
                                
                            }
                            else{
                                $_SESSION['msg'] = 'File must not be greater than 200KB';
                                header("Location: viewrewarditems.php");
                            }
                    }
                }
        }
        else{
            $_SESSION['msg'] = 'Please complete required fields';
            header("Location: viewrewarditems.php");
        }
        unset($_SESSION['rewarditemdetails']);
    }
}
else{
    $showresult = false;
}
?>
<?php include("header.php"); ?>
<script>
    $(document).ready(function(){
        
        function loadItems(){
            
            if($('#rt').val() == '1'){
            $('#rtno').attr('checked',true);
            $('#rtyes').attr('checked',false);
            }
            else{
                $('#rtyes').attr('checked',true);
                $('#rtno').attr('checked',false);
            }

            if($('#vihp').val() == '1'){
                $('#vihpyes').attr('checked',true);
                $('#vihpno').attr('checked',false);
                    $('#vihp').val('1');
            }
            else{
               $('#vihpno').attr('checked',true);
               $('#vihpyes').attr('checked',false);
                    $('#vihp').val('0');
            }
        }
        
         loadItems();
         
        $("input[name='vihpyes']").click(function()
        {
            $('#vihpno').attr('checked',false);
            $('#vihp').val('1');
        });
        
        $("input[name='vihpno']").click(function()
        {
            $('#vihpyes').attr('checked',false);
            $('#vihp').val('0');
        });
        
        
        $("input[name='rtyes']").click(function()
        {
            $('#rtno').attr('checked',false);
            $('#rt').val('1');
        });
        
        $("input[name='rtno']").click(function()
        {
            $('#rtyes').attr('checked',false);
            $('#rt').val('0');
        });
        
        $("#btnShowImage").click(function() {     
            $("#picBox").dialog("open");
            
        });
        
        $("#btnShowImage2").click(function() {     
            $("#picBox2").dialog("open");
            
        });
        
        $("#btnShowImage3").click(function() {     
            $("#picBox3").dialog("open");
            
        });
        
        $('#picBox').dialog({
            autoOpen: false,
            modal: true,
            width: '400',
            height: '300',
            title : 'Small Reward Item Image',
            closeOnEscape: true,            
            buttons: {
                "Ok": function() {
                    $(this).dialog("close");
                }
            }
        });
        
        $('#picBox2').dialog({
            autoOpen: false,
            modal: true,
            width: '400',
            height: '300',
            title : 'Medium Reward Item Image',
            closeOnEscape: true,            
            buttons: {
                "Ok": function() {
                    $(this).dialog("close");
                }
            }
        });
        
        $('#picBox3').dialog({
            autoOpen: false,
            modal: true,
            width: '400',
            height: '300',
            title : 'Large Reward Item Image',
            closeOnEscape: true,            
            buttons: {
                "Ok": function() {
                    $(this).dialog("close");
                }
            }
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
        <div class="tab">
  <div class="title">Update Reward Items</div>
            <br />
            <input type="hidden" name="rewarditemid" id="rewarditemid" value="<?php echo "$rewarditemid";?>" />
            <input type="hidden" name="vihp" id="vihp" value="<?php echo "$showinhomepage";?>" />
            <input type="hidden" name="rt" id="rt" value="<?php echo "$iscouponz";?>" />
            <table align="center">
                <tr>
                    <td>Reward Item Name : </td>
                    <td><?php echo "$txtRewardItemName"; ?></td>
                </tr>
                <tr>
                    <td>Reward Item Description : </td>
                    <td><textarea name="rewarditemdesc" id="rewarditemdesc" cols="35" rows="6" maxlength="150" class="validate[required]" onkeypress="return alphanumeric4(event);"><?php echo "$rewarditemdesc";?></textarea></td>
                </tr>
                <tr>
                    <td>Reward Item Code : </td>
                    <td><?php echo "$txtItemCode"; ?></td>
                </tr>
                <tr>
                    <td>Reward Item Price : </td>
                    <td><?php echo "$txtRewardItemPrice"; ?></td>
                </tr>
                <tr>
                    <td>Reward Item Count : </td>
                    <td><?php echo "$txtRewardItemCount"; ?></td>
                </tr>                   
            </table>
            <br/><br/>

            <table>
                <tr>
                    <td>Reward Item Image : </td>

                </tr>
                <tr>
                    <td>Small : </td>
                    <td><input type="file" name="picUpload1" id="picUpload1" class="validate[required]" /> <input id="btnShowImage" name="btnShowImage" type="button" value="Show Image"/></td>
                </tr>
                <tr>
                    <td>Medium : </td>
                    <td><input type="file" name="picUpload2" id="picUpload2" class="validate[required]" /> <input id="btnShowImage2" name="btnShowImage2" type="button" value="Show Image"/></td>
                </tr>
                <tr>
                    <td>Large : </td>
                    <td><input type="file" name="picUpload3" id="picUpload3" class="validate[required]" /> <input id="btnShowImage3" name="btnShowImage3" type="button" value="Show Image"/></td>
                </tr>
                <tr>
                    <td><br/></td>
                </tr>
                <tr>
                    <td>Expiration Date : </td>
                    <td><?php echo "$expirationdate";?></td>
                </tr>
                <tr>
                    <td><br/></td>
                </tr>
                <tr>
                    <td>Reward Type</td>
                    <td>
                        <input type="radio" id="rtyes" name="rtyes" value="1"/>Item&nbsp;&nbsp;&nbsp;
                        <input type="radio" id="rtno" name="rtno" value="0"  />Raffle Coupon
                    </td>
                </tr>

            </table>
            <style>
                #tabla {
                    height: 100%;
                    width: 100%;
                    border-collapse: collapse;
                }
                .tab { margin-left: 40px; }
                .tab2 { margin-left: 650px; }
                #accordion-resizer {
                    padding: 10px;
                    width: 650px;
                    height: auto;
                }
                .accordion {
                    height: 100%;
                }
                .divaccordion {
                    height: 500%;
                }
            </style>
            <script>
                $(function() {
                    $( "#accordion" ).accordion({
                        heightStyle: "content", 
                        collapsible: true
                    });
                });
                $(function() {
                    $( "#accordion-resizer" ).resizable({
                        minHeight: 140,
                        minWidth: 200,
                        resize: function() {
                            $( "#accordion" ).accordion( "refresh" );
                        }
                    });
                });
                
                $(document).ready(function(){
                
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
                    
                    $("input[name='vihpyes']").click(function()
                    {
                        $('#vihpno').attr('checked',false);
                        $('#vihp').val('1');
                    });

                    $("input[name='vihpno']").click(function()
                    {
                        $('#vihpyes').attr('checked',false);
                        $('#vihp').val('0');
                    });


                    $("input[name='rtyes']").click(function()
                    {
                        $('#rtno').attr('checked',false);
                        $('#rt').val('1');
                    });

                    $("input[name='rtno']").click(function()
                    {
                        $('#rtyes').attr('checked',false);
                        $('#rt').val('0');
                    });
                }); 
            </script>

            <br/>
            <div id="accordion-resizer" class="ui-widget-content" align="center">
                <div id="accordion" class="accordion">
                    <h3>First Header</h3>
                    <div class="divaccordion">
                        <table align="center" id="tabla">
                            <tr>
                                <br/>
                            </tr>    
                            <tr>
                                <td>First Header : </td>
                                <td><textarea name="firstheader" id="firstheader" cols="50" rows="3" class="validate[required]" onkeypress="return alphanumeric4(event);"><?php echo "$headerone"?></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail One : </td>
                                <td><textarea name="detailone1" id="detailone1" cols="50" rows="10" class="validate[required]" onkeypress="return alphanumeric4(event);"><?php echo "$detailsoneA"?></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Two : </td>
                                <td><textarea name="detailtwo1" id="detailtwo1" cols="50" rows="10" onkeypress="return alphanumeric4(event);"><?php echo "$detailsoneB"?></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Three : </td>
                                <td><textarea name="detailthree1" id="detailthree1" cols="50" rows="10" onkeypress="return alphanumeric4(event);"><?php echo "$detailsoneC"?></textarea></td>
                            </tr>                   
                        </table>
                    </div>
                    <h3>Second Header</h3>
                    <div>
                        <table align="center" id="tabla">
                            <tr>
                                <br/>
                            </tr> 
                            <tr>
                                <td>Second Header : </td>
                                <td><textarea name="secondheader" id="secondheader" cols="50" rows="3" onkeypress="return alphanumeric4(event);"><?php echo "$headertwo"?></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail One : </td>
                                <td><textarea name="detailone2" id="detailone2" cols="50" rows="10" onkeypress="return alphanumeric4(event);"><?php echo "$detailstwoA"?></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Two : </td>
                                <td><textarea name="detailtwo2" id="detailtwo2" cols="50" rows="10" onkeypress="return alphanumeric4(event);"><?php echo "$detailstwoB"?></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Three : </td>
                                <td><textarea name="detailthree2" id="detailthree2" cols="50" rows="10" onkeypress="return alphanumeric4(event);"><?php echo "$detailstwoC"?></textarea></td>
                            </tr>                   
                        </table>
                    </div>
                    <h3>Third Header</h3>
                    <div>
                        <table align="center" id="tabla">
                            <tr>
                                <br/>
                            </tr> 
                            <tr>
                                <td>Third Header : </td>
                                <td><textarea name="thirdheader" id="thirdheader" cols="50" rows="3" onkeypress="return alphanumeric4(event);"><?php echo "$headerthree"?></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail One : </td>
                                <td><textarea name="detailone3" id="detailone3" cols="50" rows="10" onkeypress="return alphanumeric4(event);"><?php echo "$detailsthreeA"?></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Two : </td>
                                <td><textarea name="detailtwo3" id="detailtwo3" cols="50" rows="10" onkeypress="return alphanumeric4(event);"><?php echo "$detailsthreeB"?></textarea></td>
                            </tr>
                            <tr>
                                <td>Detail Three : </td>
                                <td><textarea name="detailthree3" id="detailthree3" cols="50" rows="10" onkeypress="return alphanumeric4(event);"><?php echo "$detailsthreeC"?></textarea></td>
                            </tr>                   
                        </table>
                    </div>
                </div>
            </div>
            <div>
                <table>
                    <tr>
                        <td><br/></td>
                    </tr>
                    <tr>
                        <td>Viewable in Home Page : </td>
                        <td>
                            <input type="radio" id="vihpyes" name="vihpyes" value="1" />Yes&nbsp;&nbsp;&nbsp;
                            <input type="radio" id="vihpno" name="vihpno" value="0"  />No
                        </td>
                    </tr>
                </table>
            </div>
        
        <br/>
        <div class="tab2">

<?php echo "$btnUpdate"; ?>&nbsp;&nbsp;&nbsp;<input type="button" value="Change Status" onclick="window.location.href='controller/editrewarditemscontroller.php?rewarditemid=<?php echo $rewarditemid; ?>'+'&statuspage='+'UpdateStatus'"/>
        </div>
        <br/><br/><br/>
        </div>
        <div id="picBox" name="picBox">
            <div align="center">
                    <?php 
                    list($name, $extension) = preg_split("/\./", $rewarditemimage);
                    $imagepath = App::getParam("images_directory")."small_".$rewarditemimage;
                    $path = substr($imagepath, 8);
                    echo "<img src=\"$path\" title=\"Error\" border=0>";?>
            </div>   
        </div>
        
        <div id="picBox2" name="picBox2">
            <div align="center">
                    <?php 
                    list($name, $extension) = preg_split("/\./", $rewarditemimage);
                    $imagepath = App::getParam("images_directory")."medium_".$name;
                    $path = substr($imagepath, 8);
                    echo "<img src=\"$path\" title=\"Error\" border=0>";?>
            </div>   
        </div>
        
        <div id="picBox3" name="picBox3">
            <div align="center">
                    <?php 
                    list($name, $extension) = preg_split("/\./", $rewarditemimage);
                    $imagepath = App::getParam("images_directory")."large_".$name;
                    $path = substr($imagepath, 8);
                    echo "<img src=\"$path\" title=\"Error\" border=0>";?>
            </div>   
        </div>
    </div>

</div>
<?php include("footer.php"); ?>