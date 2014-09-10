<?php
/**
 * Edit Coupon Batch View
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog-generate',
    'options'=>array(
        'autoOpen' => false,
        'modal' => true,
        'title' => 'Coupon Generation', 
        'resizable' => false,
        'draggable' => false,
        'show' => 'fade',
        'hide' => 'fade',
        'width' => 450, 
        'height' => 500, 
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'Submit' => 'js: function(){
                var count = $("#g_count").val();
                var amount = $("#g_amount").val();
                var promoname = $("#g_promoname").val();
                var distribtype = $("#g_distributiontag").val();
                var creditable = $("#g_creditable").val();
                var status = $("#g_status").val();
                var validfrom = $("#g_validfrom").val();
                var validto = $("#g_validto").val();
                var confirmed = 0;
                
                generateCoupons(count, amount, promoname, distribtype, creditable, status, validfrom, validto, confirmed);
            }', 
            'Cancel' => 'js: function(){
                $(this).dialog("close");
            }'
        ),
    ),
));
?>
<br />
<table id="edittable">
    <tr>
        <td class="edit-lbl">
            <b>Count: </b>
        </td>
        <td>
            <?php echo CHtml::textField('count', '', array('id' => 'g_count', 'maxlength' => 4, 'onkeypress' => 'return numberonly(event);')); ?>
        </td>
    </tr>  
    <tr>
        <td class="edit-lbl">
            <b>Amount: </b>
        </td>
        <td>
            <?php echo CHtml::textField('amount', '', array('id' => 'g_amount', 'maxlength' => 7, 'onkeypress' => 'return numberonly(event);')); ?>
        </td>
    </tr>   
    <tr>
        <td class="edit-lbl">
            <b>Promo Name: </b>
        </td>
        <td>
            <?php echo CHtml::textField('promoname', '', array('id' => 'g_promoname')); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Distribution Type: </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('distributiontag', '', array(1 => "PRINT", 2 => "SMS", 3 => "EMAIL"), 
                                                               array('prompt' => '-Please Select-', 
                                                                     'id' => 'g_distributiontag'))?>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Creditable? </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('creditable', '', array(1 => "YES", 2 => "NO"), 
                                                               array('prompt' => '-Please Select-',
                                                                     'id' => 'g_creditable'))?>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Status: </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('status', '', array(1 => 'Actived', 
                                                               0 => 'Inactive', 
                                                               2 => 'Deactivated'), array('prompt' => '-Please Select-', 
                                                                                          'id' => 'g_status')); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Valid From: </b>
        </td>
        <td>
            <?php
                    Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                    $this->widget('CJuiDateTimePicker', array(
                        'model' => $model,
                        'attribute' => 'validfrom', 
                        'language' => '', 
                        'mode' => 'datetime', 
                        'htmlOptions' => array(
                            'size' => '20',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true, 
                            'height' => 40, 
                            'id' => 'g_validfrom'
                        ),
                        'options' => array(
                            'showOn'=>'button',
                            'buttonImageOnly' => true,
                            'changeMonth' => true, 
                            'showSecond' => true, 
                            'timeFormat' => 'hh:mm:ss', 
                            'changeYear' => true,
                            'buttonText'=> 'Select Date From:',
                            'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.gif',
                            'dateFormat'=>'yy-mm-dd',
                        )
                    ));
                 ?>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Valid To: </b>
        </td>
        <td>
            <?php
                    Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                    $this->widget('CJuiDateTimePicker', array(
                        'model' => $model,
                        'attribute' => 'validto', 
                        'language' => '', 
                        'mode' => 'datetime', 
                        'htmlOptions' => array(
                            'size' => '20',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true, 
                            'height' => 40, 
                            'id' => 'g_validto'
                        ),
                        'options' => array(
                            'showOn'=>'button',
                            'buttonImageOnly' => true,
                            'changeMonth' => true, 
                            'showSecond' => true, 
                            'timeFormat' => 'hh:mm:ss', 
                            'changeYear' => true,
                            'buttonText'=> 'Select Date To:',
                            'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.gif',
                            'dateFormat'=>'yy-mm-dd',
                        )
                    ));
                 ?>
        </td>
    </tr>  
</table>  
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
/***
 * 
 * CONFIRMATION DIALOG
 * 
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'confirm-generation',
    'options'=>array(
        'title' => 'Coupon Generation',
        'autoOpen' => false,
        'modal' => true,
        'resizable' => false,
        'draggable' => false,
        'show' => 'fade',
        'hide' => 'fade',
        'width' => 450, 
        'height' => 420, 
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'Generate'=>'js:function(){ 
                var count = $("#confirm_count").html();
                var amount = $("#confirm_amount").html();
                var distribtype = $("#confirm_distribtag").html();
                var creditable = $("#confirm_creditable").html();
                var promoname = $("#confirm_promoname").html();
                var status = $("#confirm_status").html();
                var validfrom = $("#orig_validfrom").val();
                var validto = $("#orig_validto").val();
                var confirmed = 1;
                
                generateCoupons(count, amount, promoname, distribtype, creditable, status, validfrom, validto, confirmed);
            }', 
            'Cancel' => 'js: function(){
                $(this).dialog("close");
            }'
        ),
    ),
));
?>
You are about to generate coupon with the following info: 
<br /><br />
<table id="edittable">
    <tr>
        <td class="edit-lbl">
            <b>Count: </b>
        </td>
        <td>
            <span id="confirm_count"></span>
        </td>
    </tr>  
    <tr>
        <td class="edit-lbl">
            <b>Amount: </b>
        </td>
        <td>
            <span id="confirm_amount"></span>
        </td>
    </tr>   
    <tr>
        <td class="edit-lbl">
            <b>Promo Name: </b>
        </td>
        <td>
            <span id="confirm_promoname"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Distribution Type: </b>
        </td>
        <td>
            <span id="confirm_distribtag"></span>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Creditable? </b>
        </td>
        <td>
            <span id="confirm_creditable"></span>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Status: </b>
        </td>
        <td>
            <span id="confirm_status"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Valid From: </b>
        </td>
        <td>
            <span id="confirm_validfrom"></span>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Valid To: </b>
        </td>
        <td>
            <span id="confirm_validto"></span>
        </td>
    </tr>  
    <?php echo CHtml::hiddenField('validfromdate', '', array('id' => 'orig_validfrom')); ?>
    <?php echo CHtml::hiddenField('validtodate', '', array('id' => 'orig_validto')); ?>
</table>    
Kindly double-check if correct.
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>