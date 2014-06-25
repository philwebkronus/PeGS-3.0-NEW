<?php
/** -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * @Description: Form for Editing of Reward Item/Coupon Details
 * @Author: aqdepliyan
 */

$urlrefresh = Yii::app()->createUrl('manageRewards/managerewards');

?>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'messagedialog2',
    'options'=>array(
        'autoOpen'=>false,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'height'=>200,
        'close' => 'js:function(event, ui){
            window.location.href = "'.$urlrefresh.'"; 
        }',
        'buttons' => array
        (
            'OK'=>'js:function(){
                $(this).dialog("close");
                window.location = "'.$urlrefresh.'"; 
            }',
        ),
    ),
));

echo "<center>";
echo "<br/>";
echo "<span id='message'></span>";
echo "<br/>";
echo "</center>";

    
$this->endWidget('zii.widgets.jui.CJuiDialog');?>

<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'showrewardsdetails',
    'options'=>array(
        'title'=>'REWARD DETAILS',
        'autoOpen'=>false,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'position'=>array("middle", 100),
        'width'=>'700',
        'show'=>'fade',
        'hide'=>'fade',
        'open' => 'js:function(event,ui){
                        $("#displaydetailsform").show();
                        var statusid = $("#statusid").val();
                        if(statusid == "1" || statusid == "3"){ //non-editable page for active and out-of-stock rewards
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show(); 
                        } else if(statusid == "2") { //editable page for inactive rewards
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                        }
        }',
        'buttons' => array
        (
            'EDIT'=>'js:function(){
                $("#editrewarddetails").dialog("open");
                $(this).dialog("close");
            }',
            'CLOSE'=>'js:function(){
                $(this).dialog("close");
                window.location.href = "'.$urlrefresh.'"; 
            }'
        ),
    ),
));
?>
    <table id="displaydetailsform" style=" display: none;">
        <tr>
            <td id="title-row">Promo Period</td>
            <td id="promoperiodtd">
                <input type="text" name="promoperiod" id="promoperiod" style="width: 435px;" readonly>
                <input type="hidden" name="offerstartdate" id="offerstartdate" />
                <input type="hidden" name="offerenddate" id="offerenddate" />
            </td>
        </tr>
        <tr>
            <td id="title-row">Partner</td>
            <td id="partnertd">
                <input type="text" name="partner" id="partner" style="width: 435px;"readonly />
                <input type="hidden" name="partnerid" id="partnerid" />
            </td>
        </tr>
        <tr>
            <td id="title-row">Reward Item</td>
            <td id="rewarditemtd">
                <input type="text" name="rewarditem" id="rewarditem" style="width: 435px;" readonly>
            </td>
        </tr>
        <tr>
            <td id="title-row">Category</td>
            <td id="categorytd">
                <input type="text" name="category" id="category"style=" width: 435px;" readonly>
                <input type="hidden" name="categoryid" id="categoryid" />
            </td>
        </tr>
        <tr>
            <td id="title-row">Points Required</td>
            <td id="pointstd">
                <input type="text" name="points" id="points" style="width: 435px;" readonly>
            </td>
        </tr>
        <tr>
            <td id="title-row">Eligibility</td>
            <td id="eligibilitytd">
                <input type="text" name="eligibility" id="eligibility" style="width: 435px;" readonly>
                <input type="hidden" name="eligibilityid" id="eligibilityid" />
            </td>
        </tr>
        <tr>
            <td id="title-row">Status</td>
            <td id="statustd">
                <input type="text" name="status" id="status" style="width: 435px;" readonly>
                <input type="hidden" name="statusid" id="statusid" />
            </td>
        </tr>
        <tr>
            <td id="title-row">Inventory Balance</td>
            <td id="inventorybalancetd">
                <input type="text" name="availableitemcount" id="availableitemcount" style="width: 435px;" readonly>
                <input type="hidden" name="subtext" id="subtext" />
            </td>
        </tr>
        <tr>
            <td id="abouttherewardtd" colspan="2" >
                <div id="aboutrewards">
                    <div id="accordioneffect1">
                        <a id="accordion-link1" href="javascript:void(0)"  done="no">
                            <img id="accordion-arrow-icon1" src="../../css/redmond/images/accord-down.png">
                        </a>
                        <span id="accordion-title-row1">About the Reward</span>
                    </div><br>
                    <div id="about" style=" display: none;"></div>
                </div>
            </td>
        </tr>
        <tr>
            <td id="termsoftherewardtd" colspan="2">
                <div id="termsrewards">
                    <div id="accordioneffect2">
                        <a id="accordion-link2" href="javascript:void(0)"  done="no">
                            <img id="accordion-arrow-icon2" src="../../css/redmond/images/accord-down.png">
                        </a>
                        <span id="accordion-title-row2">Terms of the Reward</span>
                    </div><br>
                    <div id="terms" style=" display: none;"></div>
                </div>
            </td>
        </tr>
    </table>
<?php $this->endWidget('zii.widgets.jui.CJuiDialog');?>
<?php 
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'messagedialog3',
    'options'=>array(
        'title' => 'EDIT REWARD MESSAGE',
        'autoOpen'=>false,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>'350',
        'height'=>'200',
        'buttons' => array
        (
            'OK'=>'js:function(){
                $(this).dialog("close");
            }',
        ),
    ),
));

echo "<center>";
echo "<br/>";
echo "<span id='message1'></span>";
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<?php 
    $this->beginWidget('zii.widgets.jui.CJuiDialog',array(
        'id'=>'editrewarddetails',
        'options'=>array(
            'title'=>'Rewards Management',
            'autoOpen'=>false,
            'modal'=>true,
            'resizable'=>false,
            'draggable'=>false,
            'position'=>array("middle",30),
            'width'=>650,
            'show'=>'fade',
            'hide'=>'fade',
            'open' => 'js:function(event,ui){
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                            
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).show();
                            
                            //For Checking whether Item or Coupon
                            if($("#rewardtype1:checked").val() == undefined){
                                var rewardtype = $("#rewardtype2:checked").val();
                            } else {
                                var rewardtype = $("#rewardtype1:checked").val();
                            }
                            
                            if($("#thblimitedpath").val() != ""){
                                $("#thblimitedframe").contents().find("#thblimited").attr("src", $("#thblimitedpath").val());
                                $("#thblimitedframe").contents().find("#thbsubmit_limited").attr("ImageName", "thblimited"+"_"+$("#thblimitedphoto").val());
                            }
                            if($("#thboutofstockpath").val() != ""){
                                $("#thboutofstockframe").contents().find("#thboutofstock").attr("src", $("#thboutofstockpath").val());
                                $("#thboutofstockframe").contents().find("#thbsubmit_outofstock").attr("ImageName", "thboutofstock"+"_"+$("#thboutofstockphoto").val());
                            }
                            if($("#ecouponpath").val() != ""){
                                $("#ecouponframe").contents().find("#ecoupon").attr("src", $("#ecouponpath").val());
                                $("#ecouponframe").contents().find("#ecoupon_submit").attr("ImageName", "ecoupon"+"_"+$("#ecouponphoto").val());
                            }
                            if($("#lmlimitedpath").val() != ""){
                                $("#lmlimitedframe").contents().find("#lmlimited").attr("src", $("#lmlimitedpath").val());
                                $("#lmlimitedframe").contents().find("#lmsubmit_limited").attr("ImageName", "lmlimited"+"_"+$("#lmlimitedphoto").val());
                            }
                            if($("#lmoutofstockpath").val() != ""){
                                $("#lmoutofstockframe").contents().find("#lmoutofstock").attr("src", $("#lmoutofstockpath").val());
                                $("#lmoutofstockframe").contents().find("#lmsubmit_outofstock").attr("ImageName", "lmoutofstock"+"_"+$("#lmoutofstockphoto").val());
                            }
                            if($("#websliderpath").val() != ""){
                                $("#websliderframe").contents().find("#webslider").attr("src", $("#websliderpath").val());
                                $("#websliderframe").contents().find("#webslider_submit").attr("ImageName", "webslider"+"_"+$("#websliderphoto").val());
                            }
                            
                            var stats = $("#hdnStatus").val();
                            if(stats == "Active"){
                                $("#thblimitedframe").contents().find("#thbsubmit_limited").attr("disabled","disabled");
                                $("#thboutofstockframe").contents().find("#thbsubmit_outofstock").attr("disabled","disabled");
                                $("#lmlimitedframe").contents().find("#lmsubmit_limited").attr("disabled","disabled");
                                $("#lmoutofstockframe").contents().find("#lmsubmit_outofstock").attr("disabled","disabled");
                                $("#ecouponframe").contents().find("#ecoupon_submit").attr("disabled","disabled");
                                $("#websliderframe").contents().find("#webslider_submit").attr("disabled","disabled");
                                $("#editrewarditem").attr("readonly", "readonly");
                                $("#editpoints").attr("readonly", "readonly");
                                $("#editsubtext").attr("readonly", "readonly");
                                $("#editaboutdiv").removeAttr("style");
                                $("#editaboutdiv").attr("style","display: none;");
                                $("#editaboutrodiv").removeAttr("style");
                                $("#edittermsdiv").removeAttr("style");
                                $("#edittermsdiv").attr("style","display: none;");
                                $("#edittermsrodiv").removeAttr("style");
                                
                                $("#promoperioddiv").removeAttr("style");
                                $("#promoperioddiv").attr("style", "display: none");
                                $("#readonlypromoperioddiv").removeAttr("style");
                                
                                $("#eligibilitydiv").removeAttr("style");
                                $("#eligibilitydiv").attr("style", "display: none");
                                $("#readonlyeligibilitydiv").removeAttr("style");
                                $("#partnerdiv").removeAttr("style");
                                $("#partnerdiv").attr("style", "display: none");
                                $("#readonlypartnerdiv").removeAttr("style");
                                $("#categorydiv").removeAttr("style");
                                $("#categorydiv").attr("style", "display: none");
                                $("#readonlycategorydiv").removeAttr("style");
                            } else {
                                $("#thblimitedframe").contents().find("#thbsubmit_limited").removeAttr("disabled");
                                $("#thboutofstockframe").contents().find("#thbsubmit_outofstock").removeAttr("disabled");
                                $("#lmlimitedframe").contents().find("#lmsubmit_limited").removeAttr("disabled");
                                $("#lmoutofstockframe").contents().find("#lmsubmit_outofstock").removeAttr("disabled");
                                $("#ecouponframe").contents().find("#ecoupon_submit").removeAttr("disabled");
                                $("#websliderframe").contents().find("#webslider_submit").removeAttr("disabled");
                                $("#editrewarditem").removeAttr("readonly");
                                $("#editpoints").removeAttr("readonly");
                                $("#editsubtext").removeAttr("readonly");
                                $("#readonlypromoperioddiv").removeAttr("style");
                                $("#promoperioddiv").removeAttr("style");
                                $("#readonlypromoperioddiv").attr("style", "display: none");
                                $("#editaboutrodiv").removeAttr("style");
                                $("#editaboutrodiv").attr("style","display: none;");
                                $("#editaboutdiv").removeAttr("style");
                                $("#edittermsrodiv").removeAttr("style");
                                $("#edittermsrodiv").attr("style","display: none;");
                                $("#edittermsdiv").removeAttr("style");
                                
                                $("#eligibilitydiv").removeAttr("style");
                                $("#readonlyeligibilitydiv").removeAttr("style");
                                $("#readonlyeligibilitydiv").attr("style", "display: none");
                                $("#partnerdiv").removeAttr("style");
                                $("#readonlypartnerdiv").removeAttr("style");
                                $("#readonlypartnerdiv").attr("style", "display: none");
                                $("#categorydiv").removeAttr("style");
                                $("#readonlycategorydiv").removeAttr("style");
                                $("#readonlycategorydiv").attr("style", "display: none");
                            }
                            
                            if(rewardtype == 2){
                                $("#hdnRewardID-edit").val(rewardtype);
                                var statusid = $("#statusid").val();
                                var eligibilityid = $("#eligibilityid").val();
                                $("#editstatus option[value=\'"+statusid+"\']").attr("selected", "selected");
                                $("#editsubtext").val($("#subtext").val());
                                $("#from_date").val($("#offerstartdate").val());
                                $("#fromdate").val($("#offerstartdate").val());
                                $("#to_date").val($("#offerenddate").val());
                                $("#todate").val($("#offerenddate").val());
                                $("#editrewarditem").val($("#rewarditem").val());
                                $("#editeligibility option[value=\'"+eligibilityid+"\']").attr("selected", "selected");
                                $("#editeligibilityreadonly option[value=\'"+eligibilityid+"\']").attr("selected", "selected");
                                var points = $("#points").val();
                                $("#editpoints").val(points.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                                $("#primarydetails").show();
                                $("#aboutthereward").hide();
                                $("#editaboutreward").hide();
                                $("#edittermsreward").hide();
                            } else {
                                getActivePartners(1);
                                getActiveCategories(1);
                                $("#editrewarditem").val($("#rewarditem").val());
                                var points = $("#points").val();
                                $("#editpoints").val(points.toString().replace(/,/g, "").replace(/\B(?=(\d{3})+(?!\d))/g, ","));
                                $("#primarydetails").show();
                                $("#aboutthereward").hide();
                                $("#editaboutreward").hide();
                                $("#edittermsreward").hide();
                            }
            }',
            'close' => 'js:function(event,ui){
                            window.location.href = "'.$urlrefresh.'";
            }',
            'buttons' => array
            (
                array('id' => 'firstback','text'=>'BACK',
                            'click'=> 'js:function(){
                                        $(this).dialog("close");
                                        window.location.href = "'.$urlrefresh.'";
                            }'),
                array('id' => 'secondback','text'=>'BACK','click'=> 'js:function(){
                                        $("#primarydetails").show();
                                        $("#aboutthereward").hide();
                                        $("#editaboutreward").hide();
                                        $("#edittermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).show();
                            }'),
                array('id' => 'thirdback','text'=>'BACK','click'=> 'js:function(){
                                        $("#primarydetails").hide();
                                        $("#aboutthereward").show();
                                        $("#editaboutreward").hide();
                                        $("#edittermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).show();
                            }'),
                array('id' => 'fourthback','text'=>'BACK','click'=> 'js:function(){
                                        $("#primarydetails").hide();
                                        $("#aboutthereward").hide();
                                        $("#editaboutreward").show();
                                        $("#edittermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).show();
                            }'),
                array('id' => 'firstnext','text'=>'NEXT','click'=> 'js:function(){
                                        var rewardid = $("#hdnRewardID-edit").val();
                                        var stats = $("#hdnStatus").val();
                                        if(stats == "Active"){
                                            var results = validateinputs(1,6, rewardid);

                                            if(results == true){
                                                    $("#primarydetails").hide();
                                                    $("#aboutthereward").show();
                                                    $("#editaboutreward").hide();
                                                    $("#edittermsreward").hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).show();
                                            } else {
                                                    $("#message1").html(results);
                                                    $("#messagedialog3").dialog("open");
                                            }
                                        } else {
                                            var results = validateinputs(1,1, rewardid);

                                            if(results == true){
                                                    $("#primarydetails").hide();
                                                    $("#aboutthereward").show();
                                                    $("#editaboutreward").hide();
                                                    $("#edittermsreward").hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).show();
                                            } else {
                                                    $("#message1").html(results);
                                                    $("#messagedialog3").dialog("open");
                                            }
                                        }
                                               
                            }'),
                array('id' => 'secondnext','text'=>'NEXT','click'=> 'js:function(){
                                        var stats = $("#hdnStatus").val();
                                        
                                        if(stats == "Active"){
                                            $("#primarydetails").hide();
                                            $("#aboutthereward").hide();
                                            $("#editaboutreward").show();
                                            $("#edittermsreward").hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).show();
                                        } else {
                                            var rewardid = $("#hdnRewardID-edit").val();
                                            var results = validateinputs(1,2, rewardid);
                                            if(results == true){
                                                    $("#primarydetails").hide();
                                                    $("#aboutthereward").hide();
                                                    $("#editaboutreward").show();
                                                    $("#edittermsreward").hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();

                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).show();
                                            } else if(results != true && results != false) {
                                                    $("#message1").html(results);
                                                    $("#messagedialog3").dialog("open");
                                            }  
                                        }
                                        
                            }'),
                array('id' => 'thirdnext','text'=>'NEXT','click'=> 'js:function(){
                                        var stats = $("#hdnStatus").val();
                                        
                                        if(stats == "Active"){
                                            $("#primarydetails").hide();
                                            $("#aboutthereward").hide();
                                            $("#editaboutreward").hide();
                                            $("#edittermsreward").show();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();

                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).show();
                                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).show();
                                        } else {
                                            var rewardid = $("#hdnRewardID-edit").val();
                                            var results = validateinputs(1,3, rewardid);
                                            if(results == true){
                                                    $("#primarydetails").hide();
                                                    $("#aboutthereward").hide();
                                                    $("#editaboutreward").hide();
                                                    $("#edittermsreward").show();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();

                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).show();
                                                    $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).show();
                                            } else if(results != true && results != false)  {
                                                    $("#message1").html(results);
                                                    $("#messagedialog3").dialog("open");
                                            }  
                                        }
                                        
                                        
                            }'),
                array('id' => 'save','text'=>'SAVE','click'=> 'js:function(){
                                        var stats = $("#hdnStatus").val();
                                        var rewardid = $("#hdnRewardID-edit").val();

                                        if(stats == "Active"){
                                            var results = validateinputs(1,5,rewardid);
                                            if(results == true){
                                                $("#edit-item-form").submit();
                                                $(this).dialog("close");
                                            } else if(results != true && results != false) {
                                                $("#message1").html(results);
                                                $("#messagedialog3").dialog("open");
                                                $(this).dialog("close");
                                            }
                                        } else {
                                            var results = validateinputs(1,4,rewardid);
                                            if(results == true){
                                                    $("#edit-item-form").submit();
                                                    $(this).dialog("close");
                                            } else if(results != true && results != false) {
                                                    $("#message1").html(results);
                                                    $("#messagedialog3").dialog("open");
                                            }  
                                        }
                                        
                            }')
            ),
        ),
    ));
    
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'edit-item-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('updateReward')
    ));
?>
<?php echo CHtml::hiddenField('hdnFunctionName' , 'EditReward', array('id' => 'hdnFunctionName')); ?>
<?php echo CHtml::hiddenField('hdnRewardItemID-edit' , '', array('id'=>'hdnRewardItemID-edit')); ?>
<?php echo CHtml::hiddenField('hdnRewardID-edit' , '', array('id'=>'hdnRewardID-edit')); ?>
<?php echo CHtml::hiddenField('hdnStatus' , '', array('id'=>'hdnStatus')); ?>
<table id="primarydetails" style="display: none;">
    <tr>
        <td colspan="2">
            <div id="edit-breadcrumbs">
                <span id="title-breadcrumbs-row">Edit Reward Details</span>
                <img id="edit-breadcrumbs-image" src="../../images/primarydetails.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="2"></td></tr>
    <tr id="partner-row">
        <td style="width: 250px;">e-Games Partner &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <div id="partnerdiv">
                <?php echo $form->dropDownList($model, 'editpartner', array("" => "Select Partner"), array('id'=>'editpartner', 'style' => 'width: 313px; padding: 2px;')) ?>
            </div>
            <div id="readonlypartnerdiv" style="display: none;">
                <?php echo $form->dropDownList($model, 'editpartnerreadonly', array("" => "Select Partner"), array('id'=>'editpartnerreadonly', 'style' => 'width: 313px; padding: 2px;', 'disabled' => 'disabled')) ?>
            </div>
        </td>
    </tr>
    <tr>
        <td>Reward Item &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'editrewarditem', array('id'=>'editrewarditem', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
    <tr id="category-row">
        <td>Reward Category &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <div id="categorydiv">
                <?php echo $form->dropDownList($model, 'editcategory', array("" => "Select Category"), array('id'=>'editcategory', 'style' => 'width: 313px; padding: 2px;')) ?>
            </div>
            <div id="readonlycategorydiv" style="display: none;">
                <?php echo $form->dropDownList($model, 'editcategoryreadonly', array("" => "Select Category"), array('id'=>'editcategoryreadonly', 'style' => 'width: 313px; padding: 2px;', 'disabled' => 'disabled')) ?>
            </div>
        </td>
    </tr>
    <tr>
        <td>Reward Points Requirement &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'editpoints', array('id'=>'editpoints', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
    <tr>
        <td>Member Eligibility &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <div id="eligibilitydiv">
                <?php echo $form->dropDownList($model, 'editeligibility', array("" => "Select Eligibility","1" => "All", "2" => "Regular", "3" => "VIP"), array('id'=>'editeligibility', 'style' => 'width: 313px; padding: 2px;')) ?>
            </div>
            <div id="readonlyeligibilitydiv" style="display: none;">
                <?php echo $form->dropDownList($model, 'editeligibilityreadonly', array("" => "Select Eligibility", "1" => "All", "2" => "Regular", "3" => "VIP"), array('id'=>'editeligibilityreadonly', 'style' => 'width: 313px; padding: 2px;', 'disabled' => 'disabled')) ?>
            </div>
        </td>
    </tr>
    <tr>
        <td>Status &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->dropDownList($model, 'editstatus', array("" => "Select Status", "1" => "Active", "2" => "Inactive"), array('id'=>'editstatus', 'style' => 'width: 313px; padding: 2px;')) ?>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Subtexts &nbsp;<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textArea($model, 'editsubtext', array('id'=>'editsubtext', 'rows' => 3, 'cols' => 31, 'maxlength' => 200)); ?>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Promo Period &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <div id="promoperioddiv">
            <?php 
                    $yeartodate = (string)date('Y'); 
                    $maxyear = Yii::app()->params['maximum_datepicker_year']; 
                    $yearrange =  $yeartodate.':'.$maxyear;
                    $dateformat= Yii::app()->params['dateformat']; 
            ?>
            <b>From :</b>
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'from_date',  
                'id'=>'from_date',  
                'value'=>  '', 
                 'options'=>array(
                    'showAnim'=>'fade',
                    'dateFormat'=>$dateformat,
                    'changeYear' => true,           // can change year
                    'changeMonth' => true,          // can change month
                     'yearRange' => $yearrange,
                     'minDate' => '0',
                ),
                'htmlOptions'=>array(
                    'style'=>'height:20px; width: 255px;',
                    'readonly' => true,
                ),
            ));
            ?>
            <?php echo CHtml::dropdownlist('from_hour', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23"),
                                                                                                                                array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('from_min', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;', ));?>
            <?php echo CHtml::dropdownlist('from_sec', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
            &nbsp;<br/>
            <b>To :</b>
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'to_date',  
                'id'=>'to_date',  
                'value'=>  '', 
                 'options'=>array(
                    'showAnim'=>'fade',
                    'dateFormat'=>$dateformat,
                    'changeYear' => true,           // can change year
                    'changeMonth' => true,          // can change month
                     'yearRange' => $yearrange,
                     'minDate' => '0',
                ),
                'htmlOptions'=>array(
                    'style'=>'height:20px; width: 255px; margin-top: 5px; margin-left: 18px;',
                    'readonly' => true,
                ),
            ));
            ?>
            <?php echo CHtml::dropdownlist('to_hour', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23"), array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('to_min', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('to_sec', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
        </div>
        <div id="readonlypromoperioddiv" style="display: none;">
            <b>From :</b>
            <?php echo CHtml::textField('fromdate' , '', array('id' => 'fromdate', 'style' => 'height:20px; width: 255px;', 'disabled' => 'disabled')); ?>
            &nbsp;<br/>
            <b>To :</b>
            <?php echo CHtml::textField('todate' , '', array('id' => 'todate', 'style' => 'height:20px; width: 255px; margin-top: 5px; margin-left: 18px;', 'disabled' => 'disabled')); ?>
        </div>
        </td>
    </tr>
    <tr id="editdrawdate-row" style="display: none;">
        <td>Draw Date &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
     <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'editdrawdate',  
                'id'=>'editdrawdate',  
                'value'=>  '', 
                 'options'=>array(
                    'showAnim'=>'fade',
                    'dateFormat'=>$dateformat,
                    'changeYear' => true,           // can change year
                    'changeMonth' => true,          // can change month
                     'yearRange' => $yearrange,
                     'minDate' => '0',
                ),
                'htmlOptions'=>array(
                    'style'=>'height:20px; width: 307px; margin-top: 5px;',
                    'readonly' => true,
                ),
            ));
            ?>
            <!--  DROPDOWNLIST FOR HRS:MINS:SECS FOR DRAWDATE  -->
            <?php echo CHtml::dropdownlist('editdrawdate_hour', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23"), array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('editdrawdate_min', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
            <?php echo CHtml::dropdownlist('editdrawdate_sec', '00', array("00" => "00", "01" => "01", "02" => "02", "03" => "03", "04" => "04",
                                                                                                                                "05" => "05", "06" => "06", "07" => "07", "08" => "08", "09" => "09",
                                                                                                                                "10" => "10", "11" => "11", "12" => "12", "13" => "13", "14" => "14",
                                                                                                                                "15" => "15", "16" => "16", "17" => "17", "18" => "18", "19" => "19",
                                                                                                                                "20" => "20", "21" => "21", "22" => "22", "23" => "23", "24" => "24",
                                                                                                                                "25" => "25", "26" => "26", "27" => "27", "28" => "28", "29" => "29",
                                                                                                                                "30" => "30", "31" => "31", "32" => "32", "33" => "33", "34" => "34",
                                                                                                                                "35" => "35", "36" => "36", "37" => "37", "38" => "38", "39" => "39",
                                                                                                                                "40" => "40", "41" => "41", "42" => "42", "43" => "43", "44" => "44",
                                                                                                                                "45" => "45", "46" => "46", "47" => "47", "48" => "48", "49" => "49",
                                                                                                                                "50" => "50", "51" => "51", "52" => "52", "53" => "53", "54" => "54",
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
        </td>
    </tr>
</table>

<table id="aboutthereward" style="display: none; ">
      <tr>
        <td colspan="3">
            <div id="edit-breadcrumbs">
                <span id="title-breadcrumbs-row">Edit Reward Details</span>
                <img id="edit-breadcrumbs-image" src="../../images/aboutreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td colspan="3">
            <span style="font-size: 12px; height: 10px; width: 100%;"><b>Note :</b> <i> Uploading of Images are best viewed on firefox and incompatible with IE.</i></span><br>
            <span style="font-size: 12px; height: 10px; width: 100%;"><b>Allowed Image Size :</b> <i> 300 KB and Below.</i></span><br/>
            <div id="balancer"></div>
            <iframe id="thblimitedframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/thumbnailLimited') . '?var=' . mt_rand(1000, 9999); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;" >
            </iframe>
            <?php echo CHtml::hiddenField('thblimitedphoto', '', array('id' => 'thblimitedphoto')); ?>
            <?php echo CHtml::hiddenField('thblimitedpicname', '', array('id' => 'thblimitedpicname')); ?>
            <?php echo CHtml::hiddenField('thblimitedpath', '', array('id' => 'thblimitedpath')); ?>
            <iframe id="thboutofstockframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/thumbnailOutofstock') . '?var=' . mt_rand(1000, 9999); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('thboutofstockphoto', '',array('id' => 'thboutofstockphoto')); ?>
            <?php echo CHtml::hiddenField('thboutofstockpicname', '',array('id' => 'thboutofstockpicname')); ?>
            <?php echo CHtml::hiddenField('thboutofstockpath', '',array('id' => 'thboutofstockpath')); ?>
            <iframe id="ecouponframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/eCoupon') . '?var=' . mt_rand(1000, 9999); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('ecouponphoto', '',array('id' => 'ecouponphoto')); ?>
            <?php echo CHtml::hiddenField('ecouponpicname', '',array('id' => 'ecouponpicname')); ?>
            <?php echo CHtml::hiddenField('ecouponpath', '',array('id' => 'ecouponpath')); ?>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <div id="balancer"></div>
            <iframe id="lmlimitedframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/learnMoreLimited') . '?var=' . mt_rand(1000, 9999); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('lmlimitedphoto', '', array('id' => 'lmlimitedphoto')); ?>
            <?php echo CHtml::hiddenField('lmlimitedpicname', '', array('id' => 'lmlimitedpicname')); ?>
            <?php echo CHtml::hiddenField('lmlimitedpath', '', array('id' => 'lmlimitedpath')); ?>
            <iframe id="lmoutofstockframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/learnMoreOutofstock') . '?var=' . mt_rand(1000, 9999); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('lmoutofstockphoto', '', array('id' => 'lmoutofstockphoto')); ?>
            <?php echo CHtml::hiddenField('lmoutofstockpicname', '', array('id' => 'lmoutofstockpicname')); ?>
            <?php echo CHtml::hiddenField('lmoutofstockpath', '', array('id' => 'lmoutofstockpath')); ?>
            <iframe id="websliderframe"
                    src="<?php echo Yii::app()->createUrl('manageRewards/websiteSlider') . '?var=' . mt_rand(1000, 9999); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('websliderphoto', '',array('id' => 'websliderphoto')); ?>
            <?php echo CHtml::hiddenField('websliderpicname', '',array('id' => 'websliderpicname')); ?>
            <?php echo CHtml::hiddenField('websliderpath', '',array('id' => 'websliderpath')); ?>
        </td>
    </tr>
</table>
<table id="editaboutreward" style="display: none;">
    <tr>
        <td colspan="3">
            <div id="edit-breadcrumbs">
                <span id="title-breadcrumbs-row">Edit Reward Details</span>
                <img id="edit-breadcrumbs-image" src="../../images/aboutreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td style="vertical-align: top; width: 200px;">About the Reward</td>
        <td>
            <div id="editaboutdiv"><?php echo $form->textArea($model, 'editabout', array('id' => 'editabout','style' => 'width:100%; height: 400px;')); ?></div>
            <div id="editaboutrodiv" style="display: none;"><?php echo CHtml::textArea('editaboutreadonly','', array('id' => 'editaboutreadonly','style' => 'width:100%; height: 400px;')); ?></div>
        </td>
    </tr>
</table>
<table id="edittermsreward" style="display: none;">
    <tr>
        <td colspan="3">
            <div id="edit-breadcrumbs">
                <span id="title-breadcrumbs-row">Edit Reward Details</span>
                <img id="edit-breadcrumbs-image" src="../../images/termsreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td style="vertical-align: top; width: 200px;">Terms of the Reward</td>
        <td>
            <div id="edittermsdiv"><?php echo $form->textArea($model, 'editterms', array('id' => 'editterms','style' => 'width:100%; height: 400px;')); ?></div>
            <div id="edittermsrodiv"><?php echo CHtml::textArea('edittermsreadonly','', array('id' => 'edittermsreadonly','style' => 'width:100%; height: 400px;')); ?></div>
        </td>
    </tr>
</table>
<?php $this->endWidget(); ?>
<?php $this->endWidget('zii.widgets.jui.CJuiDialog');?>
<?php
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
?>