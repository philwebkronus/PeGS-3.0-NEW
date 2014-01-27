<?php
/** -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * @Description: Form for Adding of New Reward Item/Coupon Details
 * @Author: aqdepliyan
 */

$urlrefresh = Yii::app()->createUrl('manageMysteryRewards/managemysteryreward');

?>
<?php 
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'messagedialog4',
    'options'=>array(
        'title' => 'ADD REWARD MESSAGE',
        'autoOpen'=>false,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'height'=>200,
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
echo "<span id='message2'></span>";
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<?php 
    $this->beginWidget('zii.widgets.jui.CJuiDialog',array(
        'id'=>'addnewreward',
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
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(8).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(9).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(10).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(11).hide();
                            
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                            $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).show();
                            
                            getActiveCategories(2);
                            $("#addprimarydetails").show();
                            $("#addaboutthereward").hide();
                            $("#addaboutreward").hide();
                            $("#addtermsreward").hide();
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
                                        $("#addprimarydetails").show();
                                        $("#addabouttherewardupload").hide();
                                        $("#addaboutmysteryreward").hide();
                                        $("#addaboutreward").hide();
                                        $("#addtermsmysteryreward").hide();
                                        $("#addtermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(8).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(9).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(10).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(11).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).show();
                            }'),
                array('id' => 'thirdback','text'=>'BACK','click'=> 'js:function(){
                                        $("#addprimarydetails").hide();
                                        $("#addabouttherewardupload").show();
                                        $("#addaboutmysteryreward").hide();
                                        $("#addaboutreward").hide();
                                        $("#addtermsmysteryreward").hide();
                                        $("#addtermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(8).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(9).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(10).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(11).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).show();
                            }'),
                array('id' => 'fourthback','text'=>'BACK','click'=> 'js:function(){
                                        $("#addprimarydetails").hide();
                                        $("#addabouttherewardupload").hide();
                                        $("#addaboutmysteryreward").show();
                                        $("#addaboutreward").hide();
                                        $("#addtermsmysteryreward").hide();
                                        $("#addtermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(9).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(10).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(11).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(8).show();
                            }'),
                array('id' => 'fifthback','text'=>'BACK','click'=> 'js:function(){
                                        $("#addprimarydetails").hide();
                                        $("#addabouttherewardupload").hide();
                                        $("#addaboutmysteryreward").hide();
                                        $("#addaboutreward").show();
                                        $("#addtermsmysteryreward").hide();
                                        $("#addtermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(8).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(10).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(11).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(9).show();
                            }'),
                array('id' => 'sixthback','text'=>'BACK','click'=> 'js:function(){
                                        $("#addprimarydetails").hide();
                                        $("#addabouttherewardupload").hide();
                                        $("#addaboutmysteryreward").hide();
                                        $("#addaboutreward").hide();
                                        $("#addtermsmysteryreward").show();
                                        $("#addtermsreward").hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(8).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(9).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(11).hide();

                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).show();
                                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(10).show();
                            }'),
                array('id' => 'firstnext','text'=>'NEXT','click'=> 'js:function(){
                                        var results = validateinputs(2,1);
                                        
                                        if(results == true){
                                                $("#addprimarydetails").hide();
                                                $("#addabouttherewardupload").show();
                                                $("#addaboutmysteryreward").hide();
                                                $("#addaboutreward").hide();
                                                $("#addtermsmysteryreward").hide();
                                                $("#addtermsreward").hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(8).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(9).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(10).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(11).hide();

                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).show();
                                        } else {
                                                $("#message2").html(results);
                                                $("#messagedialog4").dialog("open");
                                        }      
                            }'),
                array('id' => 'secondnext','text'=>'NEXT','click'=> 'js:function(){
                                        var results = validateinputs(2,2);
                                        if(results == true){
                                                $("#addprimarydetails").hide();
                                                $("#addabouttherewardupload").hide();
                                                $("#addaboutmysteryreward").show();
                                                $("#addaboutreward").hide();
                                                $("#addtermsmysteryreward").hide();
                                                $("#addtermsreward").hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(9).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(10).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(11).hide();

                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(8).show();
                                        } else {
                                                $("#message2").html(results);
                                                $("#messagedialog4").dialog("open");
                                        }  
                            }'),
                array('id' => 'thirdnext','text'=>'NEXT','click'=> 'js:function(){
                                        var results = validateinputs(2,3);
                                        if(results == true){
                                                $("#addprimarydetails").hide();
                                                $("#addabouttherewardupload").hide();
                                                $("#addaboutmysteryreward").hide();
                                                $("#addaboutreward").show();
                                                $("#addtermsmysteryreward").hide();
                                                $("#addtermsreward").hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(8).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(10).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(11).hide();

                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).show();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(9).show();
                                        } else {
                                                $("#message2").html(results);
                                                $("#messagedialog4").dialog("open");
                                        }  
                                        
                            }'),
                array('id' => 'fourthnext','text'=>'NEXT','click'=> 'js:function(){
                                        var results = validateinputs(2,4);
                                        if(results == true){
                                                $("#addprimarydetails").hide();
                                                $("#addabouttherewardupload").hide();
                                                $("#addaboutmysteryreward").hide();
                                                $("#addaboutreward").hide();
                                                $("#addtermsmysteryreward").show();
                                                $("#addtermsreward").hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(8).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(9).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(11).hide();

                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).show();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(10).show();
                                        } else {
                                                $("#message2").html(results);
                                                $("#messagedialog4").dialog("open");
                                        }  
                                        
                            }'),
                array('id' => 'fifthnext','text'=>'NEXT','click'=> 'js:function(){
                                        var results = validateinputs(2,5);
                                        if(results == true){
                                                $("#addprimarydetails").hide();
                                                $("#addabouttherewardupload").hide();
                                                $("#addaboutmysteryreward").hide();
                                                $("#addaboutreward").hide();
                                                $("#addtermsmysteryreward").hide();
                                                $("#addtermsreward").show();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(6).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(7).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(8).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(9).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).hide();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(10).hide();

                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(5).show();
                                                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(11).show();
                                        } else {
                                                $("#message2").html(results);
                                                $("#messagedialog4").dialog("open");
                                        }  
                                        
                            }'),
                array('id' => 'save','text'=>'SAVE','click'=> 'js:function(){
                                        var results = validateinputs(2,6);
                                        if(results == true){
                                                $("#add-item-form").submit();
                                                $(this).dialog("close");
                                        } else {
                                                $("#message2").html(results);
                                                $("#messagedialog4").dialog("open");
                                        }  
                            }')
            ),
        ),
    ));
    
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'add-item-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('addMysteryReward')
    ));
?>
<?php echo CHtml::hiddenField('hdnFunctionName' , 'AddReward', array('id' => 'hdnFunctionName')); ?>
<?php echo CHtml::hiddenField('hdnRewardItemID-add' , '', array('id'=>'hdnRewardItemID-add')); ?>
<table id="addprimarydetails" style="display: none;">
    <tr>
        <td colspan="2">
            <div id="add-breadcrumbs">
                <span id="title-breadcrumbs-row">Add New Reward Item</span>
                <img id="add-breadcrumbs-image" src="../../images/primarydetails.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="2"></td></tr>
    <tr>
        <td>Reward Item &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'addrewarditem', array('id'=>'addrewarditem', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
    <tr>
        <td>Mystery Reward Name &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'addmysteryrewarditem', array('id'=>'addmysteryrewarditem', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
    <tr id="addcategory-row">
        <td>Reward Category &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->dropDownList($model, 'addcategory', array("" => "Select Category"), array('id'=>'addcategory', 'style' => 'width: 313px; padding: 2px;')) ?>
        </td>
    </tr>
    <tr>
        <td>Reward Points Requirement &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textField($model, 'addpoints', array('id'=>'addpoints', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>
    <tr>
        <td>Member Eligibility &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->dropDownList($model, 'addeligibility', array("" => "Select Eligibility", "1" => "All", "2" => "Regular", "3" => "VIP"), array('id'=>'addeligibility', 'style' => 'width: 313px; padding: 2px;')) ?>
        </td>
    </tr>
    <tr>
        <td>Status &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->dropDownList($model, 'addstatus', array("" => "Select Status", "1" => "Active", "2" => "Inactive"), array('id'=>'addstatus', 'style' => 'width: 313px; padding: 2px;')) ?>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Subtexts &nbsp;<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textArea($model, 'addsubtext', array('id'=>'addsubtext', 'rows' => 3, 'cols' => 31, 'maxlength' => 200)); ?>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Mystery Subtexts &nbsp;<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php echo $form->textArea($model, 'addmysterysubtext', array('id'=>'addmysterysubtext', 'rows' => 3, 'cols' => 31, 'maxlength' => 200)); ?>
        </td>
    </tr>
    <tr>
        <td style="vertical-align: top;">Promo Period &nbsp<span style="vertical-align: top; color: red; font-weight: bold;">*</span></td>
        <td>
            <?php 
                    $yeartodate = (string)date('Y'); 
                    $maxyear = Yii::app()->params['maximum_datepicker_year']; 
                    $yearrange =  $yeartodate.':'.$maxyear;
                    $dateformat= Yii::app()->params['dateformat']; 
            ?>
            <b>From :</b>
            <?php
            $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                'name'=>'add_from_date',  
                'id'=>'add_from_date',  
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
            <!--  DROPDOWNLIST FOR HRS:MINS:SECS FOR PROMO PERIOD (from)  -->
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
                                                                                                                                "55" => "55", "56" => "56", "57" => "57", "58" => "58", "59" => "59"), array('style'=>'padding:3px; display: none;'));?>
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
                'name'=>'add_to_date',  
                'id'=>'add_to_date',  
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
                    'style'=>'disabled: true; height:20px; width: 255px; margin-top: 5px; margin-left: 18px;',
                    'readonly' => true,
                ),
            ));
            ?>
            <!--  DROPDOWNLIST FOR HRS:MINS:SECS FOR PROMO PERIOD (to)  -->
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
        </td>
    </tr>
<!--     <tr id="additemcount-row">
        <td>Inventory Balance </td>
        <td>
            <?php // echo $form->textField($model, 'additemcount', array('id'=>'additemcount', 'style' => 'width: 307px;')) ?>
        </td>
    </tr>-->
</table>

<table id="addabouttherewardupload" style="display: none; ">
      <tr>
        <td colspan="3">
            <div id="add-breadcrumbs">
                <span id="title-breadcrumbs-row">Add Reward Details</span>
                <img id="add-breadcrumbs-image" src="../../images/aboutreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td colspan="3">
            <span style="font-size: 12px; height: 10px; width: 100%;"><b>Note :</b> <i> Uploading of Images are best viewed on firefox and incompatible with IE.</i></span><br>
            <span style="font-size: 12px; height: 10px; width: 100%;"><b>Allowed Image Size :</b> <i> 300 KB and Below.</i></span><br/>
            <div id="balancer"></div>
            <iframe id="addthblimitedframe"
                    src="<?php echo Yii::app()->createUrl('manageMysteryRewards/thumbnailLimited'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;" >
            </iframe>
            <?php echo CHtml::hiddenField('addthblimitedphoto', '', array('id' => 'addthblimitedphoto')); ?>
            <iframe id="addthboutofstockframe"
                    src="<?php echo Yii::app()->createUrl('manageMysteryRewards/thumbnailOutofstock'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('addthboutofstockphoto', '',array('id' => 'addthboutofstockphoto')); ?>
            <iframe id="addecouponframe"
                    src="<?php echo Yii::app()->createUrl('manageMysteryRewards/eCoupon'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('addecouponphoto', '',array('id' => 'addecouponphoto')); ?>
        </td>
    </tr>
    <tr>
        <td colspan="3">
            <div id="balancer"></div>
            <iframe id="addlmlimitedframe"
                    src="<?php echo Yii::app()->createUrl('manageMysteryRewards/learnMoreLimited'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('addlmlimitedphoto', '', array('id' => 'addlmlimitedphoto')); ?>
            <iframe id="addlmoutofstockframe"
                    src="<?php echo Yii::app()->createUrl('manageMysteryRewards/learnMoreOutofstock'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('addlmoutofstockphoto', '', array('id' => 'addlmoutofstockphoto')); ?>
            <iframe id="addwebsliderframe"
                    src="<?php echo Yii::app()->createUrl('manageMysteryRewards/websiteSlider'); ?>" 
                    style="height: 248px; width: 160px; overflow: hidden; border: none; background: #FFFFFF;">
            </iframe>
            <?php echo CHtml::hiddenField('addwebsliderphoto', '',array('id' => 'addwebsliderphoto')); ?>
        </td>
    </tr>
</table>
<table id="addaboutmysteryreward" style="display: none;">
    <tr>
        <td colspan="3">
            <div id="add-breadcrumbs">
                <span id="title-breadcrumbs-row">Add Reward Details</span>
                <img id="add-breadcrumbs-image" src="../../images/aboutreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td style="vertical-align: top; width: 200px;">About the Mystery Reward</td>
        <td>
            <?php echo $form->textArea($model, 'addmysteryabout', array('id' => 'addmysteryabout','style' => 'width:100%; height: 400px;')); ?>
        </td>
    </tr>
</table>
<table id="addaboutreward" style="display: none;">
    <tr>
        <td colspan="3">
            <div id="add-breadcrumbs">
                <span id="title-breadcrumbs-row">Add Reward Details</span>
                <img id="add-breadcrumbs-image" src="../../images/aboutreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td style="vertical-align: top; width: 200px;">About the Reward</td>
        <td>
            <?php echo $form->textArea($model, 'addabout', array('id' => 'addabout','style' => 'width:100%; height: 400px;')); ?>
        </td>
    </tr>
</table>
<table id="addtermsmysteryreward" style="display: none;">
    <tr>
        <td colspan="3">
            <div id="add-breadcrumbs">
                <span id="title-breadcrumbs-row">Add Reward Details</span>
                <img id="add-breadcrumbs-image" src="../../images/termsreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td style="vertical-align: top; width: 200px;">Terms of the Mystery Reward</td>
        <td>
             <?php echo $form->textArea($model, 'addmysteryterms', array('id' => 'addmysteryterms','style' => 'width:100%; height: 400px;')); ?>
        </td>
    </tr>
</table>
<table id="addtermsreward" style="display: none;">
    <tr>
        <td colspan="3">
            <div id="add-breadcrumbs">
                <span id="title-breadcrumbs-row">Add Reward Details</span>
                <img id="add-breadcrumbs-image" src="../../images/termsreward.png">
            </div>
        </td>
    </tr>
    <tr><td colspan="3"></td></tr>
    <tr>
        <td style="vertical-align: top; width: 200px;">Terms of the Reward</td>
        <td>
             <?php echo $form->textArea($model, 'addterms', array('id' => 'addterms','style' => 'width:100%; height: 400px;')); ?>
        </td>
    </tr>
</table>
<?php $this->endWidget(); ?>
<?php $this->endWidget('zii.widgets.jui.CJuiDialog');?>
<?php
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
?>