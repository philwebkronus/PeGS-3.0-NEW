<?php
/**
 * Edit Coupon Batch View
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog-coupon-list',
    'options'=>array(
        'autoOpen' => false,
        'modal' => true,
        'resizable' => false,
        'draggable' => false,
        'show' => 'fade',
        'hide' => 'fade',
        'width' => 1000, 
        'height' => 600, 
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'Close' => 'js:function(){
                $(this).dialog("close");
            }'
        ),
    ),
));
?>
<div style="margin: 10px;">
    <br />
    <div style="float:right; margin-right: 10px; ">
        <?php echo CHtml::button('Search', array('id' => 'btnsearchcoupon')); ?>
    </div>    
    <br /><br />
    <div class="clear"></div>
    <table id="list2"></table> 
    <div id="pager2"></div>
</div>
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
