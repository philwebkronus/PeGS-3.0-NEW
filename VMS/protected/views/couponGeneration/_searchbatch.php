<?php
/**
 * Edit Coupon Batch View
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog-search',
    'options'=>array(
        'autoOpen' => false,
        'modal' => true,
        'title' => 'Coupon Batch Search',
        'resizable' => false,
        'draggable' => false,
        'show' => 'fade',
        'hide' => 'fade',
        'width' => 450,
        'height' => 500,
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'Search'=>'js:function(){
                var batchID = $("#s_batchid").val();
                var amount = $("#s_amount").val();
                var distributiontag = $("#s_distributiontag").val();
                var creditable = $("#s_creditable").val();
                var generatedfrom = $("#s_generatedfrom").val();
                var generatedto = $("#s_generatedto").val();
                var generatedby = $("#s_generatedby").val();
                var validfrom = $("#s_validfrom").val();
                var validto = $("#s_validto").val();
                var status = $("#s_status").val();
                var promoname = $("#s_promoname").val();

                var result1 = checkDateRange(generatedfrom, generatedto);
                var result2 = checkDateRange(validfrom, validto);
                if (result1 == false) {
                    generatedfrom = "";
                    generatedto = "";
                }
                if (result2 == false) {
                    validfrom = "";
                    validto = "";
                }
                if (result1 && result2) {
                    $.ajax({
                        url : "getCouponBatches",
                        type : "post",
                        data : {stop : stop},
                    });
                    $(this).dialog("close");
                }
                loadGrid(batchID, amount, distributiontag, creditable, generatedfrom, generatedto, generatedby, validfrom, validto, status, promoname);
            }',
            'Cancel' => 'js:function(){
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
            <b>Batch ID: </b>
        </td>
        <td>
            <?php echo CHtml::textField('batchid', '', array('id' => 's_batchid', 'onkeypress' => 'return numberonly(event);',
                                                             'maxlength' => 6)); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Amount: </b>
        </td>
        <td>
            <?php echo CHtml::textField('amount', '', array('id' => 's_amount', 'onkeypress' => 'return numberonly(event);', 'maxlength' => 8)); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Distribution Type: </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('distributiontag', '', array(1 => "PRINT", 2 => "SMS", 3 => "EMAIL"),
                                                               array('prompt' => '-Please Select-',
                                                                     'id' => 's_distributiontag'))?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Creditable? </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('creditable', '', array(1 => "YES", 2 => "NO"),
                                                               array('prompt' => '-Please Select-',
                                                                     'id' => 's_creditable'))?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Generated From: </b>
        </td>
        <td>
            <?php
                    Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                    $this->widget('CJuiDateTimePicker', array(
                        'model' => $model,
                        'attribute' => 'generatedfrom',
                        'language' => '',
                        'mode' => 'date',
                        'htmlOptions' => array(
                            'size' => '20',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true,
                            'height' => 40,
                            'id' => 's_generatedfrom'
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
    <tr>
        <td class="edit-lbl">
            <b>Generated To: </b>
        </td>
        <td>
            <?php
                    Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                    $this->widget('CJuiDateTimePicker', array(
                        'model' => $model,
                        'attribute' => 'generatedto',
                        'language' => '',
                        'mode' => 'date',
                        'htmlOptions' => array(
                            'size' => '20',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true,
                            'height' => 40,
                            'id' => 's_generatedto'
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
    <tr>
        <td class="edit-lbl">
            <b>Generated By </b>
        </td>
        <td>
            <?php echo CHtml::textField('generatedby', '', array('id' => 's_generatedby',
                                                                 'onkeypress' => 'return alphanumeric4(event);', 'maxlength' => 50)); ?>
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
                            'id' => 's_validfrom'
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
                            'id' => 's_validto'
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
    <tr>
        <td class="edit-lbl">
            <b>Status: </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('status', '', array(1 => 'Activated',
                                                               0 => 'Inactive',
                                                               2 => 'Deactivated'), array('prompt' => '-Please Select-',
                                                                                          'id' => 's_status')); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Promo Name: </b>
        </td>
        <td>
            <?php echo CHtml::textField('amount', '', array('id' => 's_promoname')); ?>
        </td>
    </tr>
</table>
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
