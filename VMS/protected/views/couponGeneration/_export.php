<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog-export',
    'options'=>array(
        'title' => 'Coupon (Export into File)',
        'autoOpen' => false,
        'modal' => true,
        'resizable' => false,
        'draggable' => false,
        'show' => 'fade',
        'hide' => 'fade',
        'width' => 450,
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'Export'=>'js:function(){ 
                var batchID = $("#exp-batch-id").html();
                $("#hdn-batchID").val(batchID);
                $("#export-form").submit();
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
<p>Export into file the batch of coupon with the following info: </p>
<table id="edittable">
    <tr>
        <td class="edit-lbl">
            <b>Batch ID: </b>
        </td>
        <td>
            <span id="exp-batch-id"></span>
        </td>
    </tr>  
    <tr>
        <td class="edit-lbl">
            <b>Count: </b>
        </td>
        <td>
            <span id="exp-count"></span>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Amount: </b>
        </td>
        <td>
            <span id="exp-amount"></span>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Promo Name: </b>
        </td>
        <td>
            <span id="exp-promo-name"></span>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Distribution Type: </b>
        </td>
        <td>
            <span id="exp-distrib-type"></span>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Creditable? </b>
        </td>
        <td>
            <span id="exp-creditable"></span>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Status: </b>
        </td>
        <td>
            <span id="exp-status"></span>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Valid From: </b>
        </td>
        <td>
            <span id="exp-validfrom"></span>
        </td>
    </tr> 
    <tr>
        <td class="edit-lbl">
            <b>Valid To: </b>
        </td>
        <td>
            <span id="exp-validto"></span>
        </td>
    </tr> 
</table>  
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<?php 
echo CHtml::form('exporttocsv', 'post', array('id' => 'export-form'));
echo CHtml::hiddenField('hdnbatchID', '', array('id' => 'hdn-batchID'));
echo CHtml::endForm();
?>