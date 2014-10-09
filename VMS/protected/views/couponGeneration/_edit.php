<?php
/**
 * Edit Coupon Batch View
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog-edit',
    'options'=>array(
        'autoOpen' => false,
        'modal' => true,
        'title' => 'Coupon Change Status AND/OR Validity',
        'resizable' => false,
        'draggable' => false,
        'show' => 'fade',
        'hide' => 'fade',
        'width' => 450,
        'height' => 500,
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'Submit'=>'js:function(){
                var stat = $("#e_status").val();
                var validfrom = $("#e_validfrom").val();
                var validto = $("#e_validto").val();

                $.ajax({
                    url : "getStatus",
                    type : "post",
                    dataType : "json",
                    data : {stat : stat,
                            validfrom : validfrom,
                            validto : validto},
                    success : function(data){
                        if (data.ErrorCode == 0) {
                            $("#c-batch-id").html($("#batch-id").html());
                            $("#c-count").html($("#count").html());
                            $("#c-amount").html($("#amount").html());
                            $("#c-promoname").html($("#promoname").html());
                            $("#c-distrib-type").html($("#distrib-type").html());
                            $("#c-creditable").html($("#creditable").html());
                            $("#c-validfrom").html(data.ValidFrom);
                            $("#c-validto").html(data.ValidTo);
                            $("#c-status").html(data.Status);


                            $("#dialog-edit-confirm").dialog("open");
                        }
                        else {
                            $("#alert-box").dialog("open");
                            $("#dlgmessage").html(data.Message);
                        }
                    }
                });
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
            <span id="batch-id"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Count: </b>
        </td>
        <td>
            <span id="count"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Amount: </b>
        </td>
        <td>
            <span id="amount"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Promo Name: </b>
        </td>
        <td>
            <span id="promo-name"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Distribution Type: </b>
        </td>
        <td>
            <span id="distrib-type"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Creditable? </b>
        </td>
        <td>
            <span id="creditable"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Status: </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('status', '', array(1 => 'Activated',
                                                               2 => 'Deactivated'), array('id' => 'e_status')); ?>
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
                            'id' => 'e_validfrom'
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
                            'id' => 'e_validto'
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
?>
<?php
/**
 * Edit Coupon Batch View
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog-edit-confirm',
    'options'=>array(
        'autoOpen' => false,
        'modal' => true,
        'title' => 'Coupon Change Status AND/OR Validity',
        'resizable' => false,
        'draggable' => false,
        'show' => 'fade',
        'hide' => 'fade',
        'width' => 450,
        'height' => 500,
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'Update'=>'js:function(){
                var batchID = $("#batch-id").html();
                var status = $("#e_status").val();
                var validfrom = $("#e_validfrom").val();
                var validto = $("#e_validto").val();

                updateCouponBatch(batchID, status, validfrom, validto);
            }',
            'Cancel' => 'js:function(){
                $(this).dialog("close");
            }'
        ),
    ),
));
?>
<br />
<p>You are about to update the coupon with the following info: </p>
<table id="edittable">
    <tr>
        <td class="edit-lbl">
            <b>Batch ID: </b>
        </td>
        <td>
            <span id="c-batch-id"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Count: </b>
        </td>
        <td>
            <span id="c-count"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Amount: </b>
        </td>
        <td>
            <span id="c-amount"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Promo Name: </b>
        </td>
        <td>
            <span id="c-promo-name"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Distribution Type: </b>
        </td>
        <td>
            <span id="c-distrib-type"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Creditable? </b>
        </td>
        <td>
            <span id="c-creditable"></span>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <br />
            <p>Into the following updated info:</p>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Status: </b>
        </td>
        <td>
            <span id="c-status"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Valid From: </b>
        </td>
        <td>
            <span id="c-validfrom"></span>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Valid To: </b>
        </td>
        <td>
            <span id="c-validto"></span>
        </td>
    </tr>
</table>
<br />
Kindly double-check if correct.
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
