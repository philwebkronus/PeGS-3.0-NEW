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
                searchCouponBatch();
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
            <?php echo CHtml::textField('batchid', '', array('id' => 's_batchid', 'onkeypress' => 'return numberonly(event); ',
                                                             'maxlength' => 6, 'onkeyup' => 'return quickSearch(event);')); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Amount: </b>
        </td>
        <td>
            <?php echo CHtml::textField('amount', '', array('id' => 's_amount', 'onkeypress' => 'return numberonly(event);', 'maxlength' => 8,
                                        'onkeyup' => 'return quickSearch(event); ')); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Distribution Type: </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('distributiontag', '', array(1 => "Print", 3 => "Email", 2 => "SMS"),
                                                               array('prompt' => '-Please Select-',
                                                                     'id' => 's_distributiontag', ))?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Creditable? </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('creditable', '', array(1 => "Yes", 2 => "No"),
                                                               array('prompt' => '-Please Select-',
                                                                     'id' => 's_creditable', ))?>
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
                                                                 'onkeypress' => 'return alphanumeric4(event);', 'maxlength' => 50,
                                        'onkeyup' => 'return quickSearch(event); ')); ?>
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
                                                               2 => 'Deactivated'), array('prompt' => '-Please Select-',
                                                                                          'id' => 's_status', )); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Promo Name: </b>
        </td>
        <td>
            <?php echo CHtml::textField('amount', '', array('id' => 's_promoname',
                                        'onkeyup' => 'return quickSearch(event);')); ?>
        </td>
    </tr>
</table>
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
