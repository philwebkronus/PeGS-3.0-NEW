<?php
/**
 * Coupon/Ticket Generation Tool (View)
 * @author Mark Kenneth Esguerra
 * @date October 30, 2013
 * @copyright (c) 2013, Philweb Corporation
 */
?>
<script type="text/javascript">
    $(document).ready(function(){
       $("#vouchertype").val("");
       $("#count").hide();
       $("#amount").hide();
       $("#tag").hide();
       $("#creditable").hide();
       $("#autogen").hide();
       $("#GenerationToolModel_count").val("");
       $("#GenerationToolModel_amount").val("");
       $("#GenerationToolModel_distributiontag").val("");
       $("#GenerationToolModel_iscreditable").val("");
       $("#submit").hide();
    });
    function showFields()
    {
        $(document).ready(function(){
            var vtype = $("#vouchertype").val();
            $("#submit").show();
            if (vtype == 1){ //if coupon
                $("#count").show();
                $("#amount").show();
                $("#tag").show();
                $("#creditable").show();
                $('#GenerationToolModel_iscreditable_0').attr('checked', false);
                $('#GenerationToolModel_iscreditable_1').attr('checked', false);
            }
            else if (vtype == 2){ //if ticket
                $("#count").show();
                $("#autogen").show();
                $("#amount").hide();
                $("#tag").hide();
                $("#creditable").hide();
                $('#GenerationToolModel_iscreditable_0').attr('checked', true);
            }
            else if (vtype == ""){
                $("#vouchertype").val("");
                $("#count").hide();
                $("#validfrom").hide();
                $("#validto").hide();
                $("#amount").hide();
                $("#tag").hide();
                $("#creditable").hide();
                $("#submit").hide();
            }
        });
    }
</script>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog_box',
    // additional javascript options for the dialog plugin
    'options'=>array(
        'title'=> $this->title,
        'autoOpen' => $this->showdialog,
        'resizable' => false,
        'draggable' => false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'modal'=>true,
        'buttons' => array(
            'OK'=>'js:function(){
                        $(this).dialog("close");
                   }',
        ),
    ),
));
?>
<p id="dlg_msg" style="text-align: left;"><?php echo $this->message; ?></p>
    
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog_box_warn',
    // additional javascript options for the dialog plugin
    'options'=>array(
        'title'=> $this->title,
        'autoOpen' => $this->showdialog2,
        'resizable' => false,
        'draggable' => false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'modal'=>true,
        'open' => 'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array(
            'Retry' => 'js:function(){
                        $("#regenerate-form").submit();
                   }',
        ),
    ),
));
?>
<p id="dlg_msg" style="text-align: left;"><?php echo $this->message; ?></p>

<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<h2>Coupon/Ticket Generation Tool</h2>
<br/>
<hr style="color:#000;background-color:#000;">
<br />
<?php
$form = $this->beginWidget('CActiveForm', array(
        'id' => 'generate-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('index')
    ));
?>
<div id="gentool">
<table id="gentooltbl">
    <tr id="vtype">
        <td id="label">
            Voucher Type
        </td>
        <td>
            <?php
                echo $form->dropDownList($model, 'vouchertype', array('1'=> 'Coupon', '2' => 'Ticket'), 
                                                                array('id' => 'vouchertype',
                                                                      'prompt' => 'Please Select',
                                                                      'onchange' => 'return showFields()'))
            ?>
        </td> 
    </tr>
    <tr id="count">
        <td id="label">
            Count
        </td>
        <td>
            <?php
                echo $form->dropDownList($model, 'count', array('1000' => '1000', '2500' => '2500', '5000' => '5000'), 
                                                          array('prompt' => 'Please Select'))
            ?>
        </td>
    </tr>
    <tr id="amount">
        <td id="label">
            Amount
        </td>
        <td>
            <?php echo $form->textField($model, 'amount', array('onkeypress' => 'return numberonly(event)')); ?>
        </td>    
    </tr>
    <tr id="tag">
        <td id="label">
            Distribution Tag
        </td>
        <td>
            <?php
                echo $form->dropDownList($model, 'distributiontag', array('1' => 'Print', '2' => 'SMS', '3' => 'Email'), array('prompt' => 'Please Select'))
            ?>
        </td>
    </tr>
    <tr id="creditable">
        <td id="label">
            Is Creditable
        </td>    
        <td>
            <?php
                echo $form->radioButtonList($model, 'iscreditable', array('1' => 'Yes', '2' => 'No'));
            ?>
        </td>
    </tr>  
</table> 
<div id="submit">
    <?php echo CHtml::submitButton('Submit', array('style' => 'margin-left: 600px;')); ?>
</div>
</div>
<?php
$this->endWidget();
?>
<div id="autogen">
    <?php echo CHtml::submitButton('Ticket Auto-Generation', array('submit'=>'viewTicketConf')); ?>
</div>
<?php
if (isset($this->showdialog2))
{
    $form = $this->beginWidget('CActiveForm', array(
            'id' => 'regenerate-form',
            'enableClientValidation' => true,
            'enableAjaxValidation' => true,
            'clientOptions' => array(
                'validateOnSubmit' => true,
            ),
            'action' => $this->createUrl('regenerate')
        ));
        echo $form->hiddenField($model, 'amount', array('value' => $this->amount));
        echo $form->hiddenField($model, 'iscreditable', array('value' => $this->iscreditable));
        echo $form->hiddenField($model, 'remainingcount', array('value' => $this->remainingcount));
        echo $form->hiddenField($model, 'couponbatch', array('value' => $this->batchID));
        echo $form->hiddenField($model, 'vouchertype', array('value' => $this->vtype));

    $this->endWidget();
}
?>
