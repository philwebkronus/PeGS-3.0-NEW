<?php
/** -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * @Description: Form for Deleting of Reward Item/Coupon
 * @Author: aqdepliyan
 */
        $urlrefresh = Yii::app()->createUrl('manageRewards/managerewards');
        
        $form = $this->beginWidget('CActiveForm', array(
            'id' => 'delete-item-form',
            'enableClientValidation' => true,
            'enableAjaxValidation' => true,
            'clientOptions' => array(
                'validateOnSubmit' => true,
            ),
            'action' => $this->createUrl('deleteReward')
        ));
?>
<table style="font-size: 12px;">
    <tr>
        <!----- Controls for filtering rewards list data ----->
        <td style="text-align: right; width: 50%;">
            <?php echo CHtml::label("VIEW REWARDS BY: ", "viewrewardsby", array('style' => 'vertical-align:middle;'));?>
            <?php echo $form->dropDownList($model, 'viewrewardsby', array("0" => "All", "1" => "Active", "2" => "Inactive","3" => "Out-Of-Stock"), array('id' => 'viewrewardsby')) ?>
            &nbsp;&nbsp;&nbsp;&nbsp;
        </td>
        <td>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <?php echo $form->radioButton($model,'rewardtype',array('value'=>'1','uncheckValue'=>null, 'id' => 'rewardtype1',  'checked'=>'checked')); ?>
            <?php echo CHtml::label("Rewards e-Coupons", "rewardsecoupons", array('For'=>'rewardtype1'));?>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <?php echo $form->radioButton($model,'rewardtype',array('value'=>'2','uncheckValue'=>null, 'id' => 'rewardtype2')); ?>
            <?php echo CHtml::label("Raffle e-Coupons", "raffleecoupons",array('For'=>'rewardtype2'));?>
        </td>
        <!---------------------------------------------------------->
    </tr>
</table>
<?php echo CHtml::hiddenField('hdnRewardItemID' , '', array('id'=>'hdnRewardItemID')); ?>
<?php echo CHtml::hiddenField('hdnrewardtype' , '', array('id'=>'hdnrewardtype')); ?>
<table id="rewardslist"></table>
<div id="rewardslistpager"></div>
<div id="main" style="position:relative;">
    <center>
        <br>
        <?php echo CHtml::button('ADD REWARD', array('class'=>'buttonlink-add','id'=>'linkButton', 'style'=>'color:white;', 'onclick' => '$("#addnewreward").dialog("open"); return false;')); ?>
    </center>
</div>
<?php echo CHtml::beginForm(array('manageRewards/managereward'), 'POST', array(
        'id'=>'ManageRewardsForm',
        'name'=>'ManageRewardsForm')); ?>
        
<?php echo CHtml::endForm(); ?>   
<?php $this->endWidget();
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
?>


<?php
/** -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * @Description: Popup Confirmation Dialog box for delete reward item/coupon function
 * @Author: aqdepliyan
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'deleterewardconfirmation1',
    'options'=>array(
        'title'=>'DELETE REWARD',
        'autoOpen'=>false,
        'modal'=>true,
        'closeOnEscape' => false,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'buttons' => array
        (
            'YES'=>'js:function(){
                $("#delete-item-form").submit();
                $(this).dialog("close");
            }',
            'NO'=>'js:function(){ $(this).dialog("close"); }',
        ),
    ),
));
echo "<center>";
echo 'Do you really want to delete this reward?';
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');

$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'deleterewardconfirmation2',
    'options'=>array(
        'title'=>'DELETE REWARD',
        'autoOpen'=>false,
        'modal'=>true,
        'closeOnEscape' => false,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'close' => 'js:function(event, ui){
            window.location.href = "'.$urlrefresh.'"; 
        }',
        'buttons' => array
        (
            'YES'=>'js:function(){
                $("#delete-item-form").submit();
                $(this).dialog("close");
            }',
            'NO'=>'js:function(){ $(this).dialog("close"); window.location.href = "'.$urlrefresh.'"; }',
        ),
    ),
));
echo "<center>";
echo 'Do you really want to delete this raffle?';
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
?>