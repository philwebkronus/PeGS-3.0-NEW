<?php
$this->breadcrumbs=array(
	'Verify Rewards',
);?>
<?php 
$this->pageTitle = Yii::app()->name . ' - Verify Rewards';

Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseURL . '/js/jquery-1.7.2.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/idle.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/idlechecker.js');

?>

<!--Disable Right Click--Added by: mgesguerra 09-16-13 -->
<script type="text/javascript">
    $(function () {
      $(document).bind("contextmenu",function(e){
        e.preventDefault();
      });
    });
</script>
<script type="text/javascript">
    $(document).ready(function(){
        $("#partnernamecashier").keyup(function(){
           var branchdtls = $("#partnernamecashier").val();
           if (branchdtls.substring(0, 1) === " "){
               alert("Warning: Trailing space/s is/are not allowed");
               $("#partnernamecashier").val("");
           }
           else{
               return true;
           }
        });
        $("#branchdetails").keyup(function(){
           var branchdtls = $("#branchdetails").val();
           if (branchdtls.substring(0, 1) === " "){
               alert("Warning: Trailing space/s is/are not allowed");
               $("#branchdetails").val("");
           }
           else{
               return true;
           }
        });
        $("#remarks").keyup(function(){
           var branchdtls = $("#remarks").val();
           if (branchdtls.substring(0, 1) === " "){
               alert("Warning: Trailing spaces is/are not allowed");
               $("#remarks").val("");
           }
           else{
               return true;
           }
        });
    });
</script>
<h2>Record The Reward Transaction</h2>
<hr color="black" />
<div class="row" style="padding: 10px 5px; background: #EFEFEF;">
<?php
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'VerifyRewardsForm',
            'enableClientValidation' => true,
            'clientOptions' => array(
                'validateOnSubmit' => true,
            ),
                ));
        ?>

    <table style="text-align:center; margin-left: 100px">
        <tr>
        <td width="25%" align="left">
            <?php echo CHtml::label("Partner :"." ".Yii::app()->session['partnername'], "rewardsecoupons");?>
        </td>
        <td width="40%" align="left">
            <?php echo CHtml::label("Serial Code :"." ".Yii::app()->session['serialcode'], "raffleecoupons");?>
        </td>
        </tr>
        <tr>
        <td width="25%" align="left">
            <?php echo CHtml::label("Reward :"." ".$rewardname, "rewardsecoupons");?>
        </td>
        <td width="40%" align="left">
            <?php echo CHtml::label("Security Code :"." ".Yii::app()->session['securitycode'], "raffleecoupons");?>
        </td>
        </tr>
   
    
    
        <tr>
        <td width="25%" align="left">
            <?php echo CHtml::label("Full name of Member :", "membername");?>
       </td>
        <td width="40%" align="left">
            <?php echo CHtml::label(Yii::app()->session['membername'], "membername");?>
        </td>
        </tr>
        <tr>
        <td width="30%" align="left">
            <?php echo CHtml::label("Membership Card Number :", "cardnumber");?>
        </td>
        <td width="40%" align="left">
            <?php echo CHtml::label(Yii::app()->session['cardnumber'], "cardnumber");?>
        </td>
        </tr>
        <tr>
        <td width="30%" align="left">
            <?php echo CHtml::label("ID Presented :", "idpresented");?>
        </td>
        <td width="40%" align="left">
            <?php echo CHtml::label(Yii::app()->session['identificationname'], "idpresented");?>
        </td>
        </tr>
        <tr>
        <td width="30%" align="left">
            <?php echo CHtml::label("*Name of partner cashier :", "partnernamecashier");?>
        </td>
        <td width="40%" align="left">
            <?php echo $form->textField($model,'partnernamecashier', array('id'=>'partnernamecashier', 'onkeypress' => 'return AlphaNumericOnlyWithSpace(event);', 'style'=>'width: 130px;')); ?>
        </td>
        </tr>
        <tr>
        <td width="30%" align="left">
            <?php echo CHtml::label("*Branch Details :", "branchdetails");?>
        </td>
        <td width="40%" align="left">
            <?php echo $form->textField($model,'branchdetails', array('id'=>'branchdetails', 'onkeypress' => 'return AlphaNumericOnlyWithSpace(event);', 'style'=>'width: 130px;')); ?>
        </td>
        </tr>
    </table>
    <div style="margin-left: 105px;">
            <?php echo CHtml::label("Remarks :", "remarks");?>
            <?php echo $form->textField($model,'remarks', array('id'=>'remarks',  'style'=>'width: 550px;', 'onkeypress' => 'return AlphaNumericOnlyWithSpace(event);')); ?>
    </div> 
    <br/>
    <br/>
    <div id="submitverify" style="width: 100%; text-align: center;">
        <?php echo CHtml::submitButton("Record", array('name' => 'Submit')); ?>
    </div> 

</div>
<?php $this->endWidget(); ?>
        <input id="Timeout" type="hidden" value="<?php echo Yii::app()->params->idletimelogout;;?>" />
        <input id="logout" type="hidden" value="<?php echo Yii::app()->params->autologouturl;;?>" />
<?php
/** Start Widget **/
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'mydialog',
    'options'=>array(
        'title'=>'Alert',
        'autoOpen'=>false,
        'closeOnEscape' => false,
        'resizable'=>false,
        'draggable'=>false,
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'OK'=>'js:function(){
                window.location.href = $("#logout").val();
                $(this).dialog("close");
            }',
        ),
    ),
));
echo "<center>";
echo 'Session Expired';
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');
/** End Widget **/

?>

<?php
/** Start Widget **/
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'alertdialog',
    'options'=>array(
        'title' => $this->title,
        'autoOpen'=>$this->showDialog2,
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

echo $this->dialogMsg;
echo "<br/>";
echo "<br/>";
echo $this->dialogMsg2;
echo "<br/>";

    
$this->endWidget('zii.widgets.jui.CJuiDialog');
/** End Widget **/

?>

        <?php
/** Start Widget **/
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'successdialog',
    'options'=>array(
        'autoOpen'=>$this->showDialogSuccess,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'height'=>200,
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'OK' =>'js:function(){
                $(this).dialog("close");
                window.location = "verifyrewards"
            }',
        ),
    ),
));

echo $this->dialogMsg;
echo "<br/>";
echo $this->dialogMsg2;
echo "<br/>";
?>
<?php echo CHtml::beginForm(array('verifyRewards/logVerification'), 'POST', array(
        'id'=>'LogVerificationForm',
        'name'=>'LogVerificationForm')); ?>
        
<?php echo CHtml::endForm(); ?>        
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
/** End Widget **/

?>
