<?php
/**
 * Edit Coupon Batch View
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog-search-coupon',
    'options'=>array(
        'autoOpen' => false,
        'modal' => true,
        'title' => 'Coupon Search',
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
                var batchID = $("#hdnbatchID").val();
                var couponcode = $("#sc_couponcode").val();
                var status = $("#sc_status").val();
                var transdatefrom = $("#sc_transdatefrom").val();
                var transdateto = $("#sc_transdateto").val();
                var site = $("#sc_site").val();
                var terminal = $("#sc_terminal").val();
                var source = $("#sc_source").val();
                var promoname = "";

                loadCouponGrid(batchID, couponcode, status, transdatefrom, transdateto, site, terminal, source, promoname)

                $(this).dialog("close");
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
            <b>Coupon Code: </b>
        </td>
        <td>
            <?php echo CHtml::textField('couponcode', '', array('id' => 'sc_couponcode',
                                                                'maxlength' => 7,
                                                                'onkeypress' => 'return alphanumeric5(event); ')); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Status: </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('status', '', array(1 => 'Activated',
                                                               0 => 'Inactive',
                                                               2 => 'Deactivated',
                                                               3 => 'Used',
                                                               4 => 'Cancelled',
                                                               5 => 'Reimbursed'), array('prompt' => '-Please Select-',
                                                                                          'id' => 'sc_status')); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Transaction Date From: </b>
        </td>
        <td>
            <?php
                    Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                    $this->widget('CJuiDateTimePicker', array(
                        'model' => $model,
                        'attribute' => 'transdatefrom',
                        'language' => '',
                        'mode' => 'datetime',
                        'language' => '',
                        'mode' => 'date',
                        'htmlOptions' => array(
                            'size' => '20',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true,
                            'height' => 40,
                            'id' => 'sc_transdatefrom'
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
            <b>Transaction Date To: </b>
        </td>
        <td>
            <?php
                    Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                    $this->widget('CJuiDateTimePicker', array(
                        'model' => $model,
                        'attribute' => 'transdateto',
                        'language' => '',
                        'mode' => 'datetime',
                        'htmlOptions' => array(
                            'size' => '20',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true,
                            'height' => 40,
                            'id' => 'sc_transdateto'
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
            <b>Site: </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('site', '', $sitelist, array('id' => 'sc_site',
                                                                        'prompt' => '-Please Select-',
                                                                        'ajax' => array(
                                                                            'url' => CController::createUrl('getSiteTerminals'),
                                                                            'type' => 'post',
                                                                            'update' => '#sc_terminals',
                                                                            'data' => array('siteID' => 'js:this.value')
                                                                        ))); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Terminal: </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('terminals', '', array(), array('id' => 'sc_terminal',
                                                                      'prompt' => '-Please Select-')); ?>
        </td>
    </tr>
    <tr>
        <td class="edit-lbl">
            <b>Source: </b>
        </td>
        <td>
            <?php echo CHtml::dropDownList('source', '', array(3 => 'Cashier'), array('id' => 'sc_source',
                                                                      'prompt' => '-Please Select-')); ?>
        </td>
    </tr>
</table>
<?php echo CHtml::hiddenField('hdnbatchID', '', array('id' => 'hdnbatchID')); ?>
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
