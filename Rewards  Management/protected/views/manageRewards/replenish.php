<?php
/** -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 * @Description: Form for Replenishing of Reward Item/Coupon Inventory
 * @Author: aqdepliyan
 */ 

$urlrefresh = Yii::app()->createUrl('manageRewards/managerewards');

$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'messagedialog5',
    'options'=>array(
        'title' => 'REPLENISH MESSAGE',
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
                $("#additems").val("");
                $("#additems").change();
                $(this).dialog("close");
                window.location.href = "'.$urlrefresh.'"; 
            }',
        ),
    ),
));

echo "<center>";
echo "<br/>";
echo "<span id='message3'></span>";
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'replenishform',
    'options'=>array(
        'title'=>'REPLENISHING INVENTORY',
        'autoOpen'=>false,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>true,
        'width'=>500,
        'show'=>'fade',
        'hide'=>'fade',
        'open' => 'js:function(event,ui){
                        $("#replenishingform").show();
        }',
        'close' => 'js:function(event,ui){
                        $("#additems").val("");
                        $("#additems").change();
                        window.location.href = "'.$urlrefresh.'"; 
        }',
        'buttons' => array
        (
            'SAVE'=>'js:function(){
                var additems = $("#additems").val();
                if(additems == "0"){
                    $("#message3").html("0 amount is not valid. <br/> Please enter amount to add.");
                    $("#messagedialog5").dialog("open");
                } else if(additems == ""){
                    $("#message3").html("Please enter amount to add.");
                    $("#messagedialog5").dialog("open");
                } else {
                    $("#replenish-form").submit();
                    $(this).dialog("close");
                }
            }'
        ),
    ),
));
?>
 <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'replenish-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('replenishReward')
    ));
    ?>
<?php echo CHtml::hiddenField('hdnRewardItemID-replenishform' , '', array('id'=>'hdnRewardItemID-replenishform')); ?>
<table id="replenishingform" style="display: none">
    <tr>
        <td>Current Inventory Level</td>
        <td>
            <?php echo $form->textField($model, 'currentinventory', array('id'=>'currentinventory', 'style' => 'width: 200px;', 'readonly' => 'readonly')) ?>
        </td>
    </tr>
    <tr>
        <td>Add Items</td>
        <td>
            <?php echo $form->textField($model, 'additems', array('id'=>'additems', 'style' => 'width: 200px;')) ?>
            <?php echo CHtml::hiddenField('hdnadditems' , '', array('id'=>'hdnadditems')); ?>
        </td>
    </tr>
        <td>Total Inventory Level</td>
        <td>
            <?php echo $form->textField($model, 'inventoryupdate', array('id'=>'inventoryupdate', 'style' => 'width: 200px;', 'readonly' => 'readonly')) ?>
        </td>
    </tr>
</table>
<?php $this->endWidget();?>
<?php $this->endWidget('zii.widgets.jui.CJuiDialog');
/** ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------*/
?>