<?php
/**
 * The landing page.
 * Validating the Mystery Box e-Coupon
 * @author Noel Antonio
 * @dateCreated 11-07-2013
 */

$this->pageTitle = Yii::app()->name . ' - Verify Mystery Box';

$this->breadcrumbs=array(
	'Verify Mystery Box',
);
?>
<script type="text/javascript">
jQuery(document).ready(function(){
       $("#btnSubmit").live("click", function()
       {
              var item = $("#VerifyRewardsForm_rewardItem").val();
              var serialcode = $("#serialCode").val();
              var securitycode = $("#securityCode").val();

              if (item == -1 || serialcode == "" || securitycode == "")
              {
                  $("#errmsg").html("Make sure all fields are filled out.");
                  return false;       
              }
              else
              {
                  $("#errmsg").html("");
                  $("#VerifyRewardsForm").submit();
              }
       });
});
</script>

<h2>Verify Mystery Box</h2>
<hr color="black" />
<div class="row" style="padding: 10px 5px; background: #EFEFEF;">
    
    <?php
    $form = $this->beginWidget('CActiveForm', array(
                'id' => 'mysteryBoxForm',
                'enableClientValidation' => true,
                'clientOptions' => array(
                    'validateOnSubmit' => true,
                 ),
    ));
    ?>
    
    <div id="rewardpage">   
        <table style="width:500px; margin: 50px;">
            <tr>
                <td colspan="2">
                    <span id="errmsg" style="color: red"></span>
                </td>
            </tr>
            <tr>
                <td><?php echo CHtml::label("Reward Item : ", "rewardItem"); ?></td>    
            <td>
                <?php
                
                    $refPartnerModel = new RefPartnerModel();
                    $partnerId = $refPartnerModel->getPartnerIDUsingName(Yii::app()->params['mysteryPartner']);
                    Yii::app()->session['partnerid'] = $partnerId;
                    $arrItems = $model->getMysteryBoxRewardItems($partnerId);
                    array_unshift($arrItems, array('RewardItemID'=>'-1', 'ItemName'=>'-Please Select-'));
                    $itemList = CHtml::listData($arrItems, 'RewardItemID', 'ItemName');
                    echo $form->dropDownList($model, 'rewardItem', $itemList); 
                    
                ?>
            </td>
            </tr>
            <tr>
                <td><?php echo CHtml::label("e-Coupon Serial Code : ", "serialCode");?></td>    
            <td>
                <?php echo $form->textField($model, 'serialCode', array('id'=>'serialCode',  'style'=>'width: 250px;', 'onkeypress' => 'return numberandletter1(event);')); ?>
            </td>
            </tr>
            <tr>
                <td><?php echo CHtml::label("e-Coupon Security Code : ", "securityCode");?></td>    
            <td>
                    <?php echo $form->textField($model, 'securityCode', array('id'=>'securityCode',  'style'=>'width: 250px;', 'onkeypress' => 'return numberandletter1(event);')); ?>
            </td>
            </tr>
        </table>
         
        <div id="submitverify" style="width: 100%; text-align: center;">
            <?php echo CHtml::submitButton("Verify", array('id'=>'btnSubmit','name' => 'btnSubmit')); ?>
        </div> 
    </div>

    <?php $this->endWidget(); ?>

</div>

<?php
/** Start Widget **/
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'alertdialog',
    'options'=>array(
        'autoOpen'=>$this->showDialog2,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>400,
        'height'=>250,
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
            'OK'=>'js:function(){
                $("#LogVerificationForm").submit();
                $(this).dialog("close");
                $("#serialCode").val("");
                $("#securityCode").val("");
                $("#MysteryBoxModel_rewardItem").val(-1);
            }',
        ),
    ),
));

echo $this->dialogMsg;
echo "<br/>";
echo $this->dialogMsg2;
echo "<br/>";
//echo $this->dialogMsg3;
//echo "<br/>";
?>
<?php echo CHtml::beginForm(array('mysteryBox/logVerification'), 'POST', array(
        'id'=>'LogVerificationForm',
        'name'=>'LogVerificationForm')); ?>
        
<?php echo CHtml::endForm(); ?>        
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
/** End Widget **/