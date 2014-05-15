<?php

/**
 * @author Noel Antonio
 * @dateCreated November 14, 2013
 */

$this->breadcrumbs=array(
	'Ticket Encashment',
);
?>
<h2>Ticket Encashment</h2>
<hr color="black" />
<div class="row" style="padding: 10px; background: #EFEFEF;">
    <?php $form=$this->beginWidget('CActiveForm', array(
            'enableClientValidation'=>true,
            'clientOptions'=>array(
                'validateOnSubmit'=>true,
            ),
        ));
    ?>
    
    <div style="color: red;">
        <?php echo $form->errorSummary($model); ?>
    </div>
    
    <table style="width:500px">
        <tr>
            <td><?php echo $form->labelEx($model, 'Ticket Code:'); ?></td>
            <td><?php echo $form->textField($model, 'ticketCode', array('autocomplete'=>'off', 'onkeypress'=>'return alphanumeric3(event)')); ?></td>
            <!--<td><?php echo $form->error($model,'ticketCode'); ?></td>-->
        </tr>
        <tr>
            <td><?php echo $form->labelEx($model, 'Membership Card Number:'); ?></td>
            <td><?php echo $form->textField($model, 'memberCardNumber', array('autocomplete'=>'off', 'onkeypress'=>'return alphanumeric3(event)')); ?></td>
            <!--<td><?php echo $form->error($model,'memberCardNumber'); ?></td>-->
            <td><?php echo CHtml::submitButton('Verify'); ?></td>
        </tr>
    </table>
    
    <?php $this->endWidget(); // end widget form. ?>
</div>

<!-- dialog box -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'dialog-box',
        'options'=>array(
            'title'=>$this->dialogTitle,
            'modal'=>true,
            'width'=>'350',
            'height'=>'auto',
            'resizable'=>false,
            'autoOpen'=>$this->autoOpen,
            'buttons'=>array(
                'OK'=>'js:function(){
                    $(this).dialog("close");
                }'
            )
        ),
)); ?>

<br />
<?php echo $this->dialogMsg; ?>
<br />

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
<!-- dialog box -->

<!-- confirmation dialog box -->
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'confirm-box',
        'options'=>array(
            'title'=>'CONFIRMATION',
            'modal'=>true,
            'width'=>'350',
            'height'=>'auto',
            'resizable'=>false,
            'autoOpen'=>$this->confirm,
            'buttons'=>array(
                'Yes'=>'js:function(){
                    $("#hidTicketCode").val($("#TicketEncashmentForm_ticketCode").val());
                    $("#UpdateTicketForm").submit();
                    $(this).dialog("close");
                }',
                'No'=>'js:function(){
                    $(this).dialog("close");
                }'
            )
        ),
)); ?>

<br />
<?php echo $this->dialogMsg; ?>
<br />

<?php echo CHtml::beginForm(array('ticketEncashment/index'), 'POST', array(
        'id'=>'UpdateTicketForm',
        'name'=>'UpdateTicketForm')); 
      echo CHtml::hiddenField('hidTicketCode');
      echo CHtml::endForm(); ?>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
<!-- confirmation dialog box -->