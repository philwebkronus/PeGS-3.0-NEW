<?php

/*
 * @Date Nov 20, 2012
 * @Author owliber
 */
?>
<?php
$this->breadcrumbs=array(
	'Voucher Maintenance','Stacker Monitoring',
);
?>
<h4>EGM machines stacker sessions</h4>
<hr color="black" />

<div id="refresh" style="display:inline" class="prepend-top right">
    <?php echo CHtml::ajaxLink(" Refresh", array('stacker/ajaxLastQuery'),array(
            'type'=>'GET',
            'success'=>'function(data){
                $("#results-grid").html(data);
                
            }',
            'beforeSend' => 'function(){
                 $(".ui-dialog-titlebar").hide()   
                 $("#ajaxloader").dialog("open")
            }',
            'complete' => 'function(){
                $(".ui-dialog-titlebar").hide()   
                $("#ajaxloader").dialog("close")
            }',
            'update'=>'#results-grid',
            ),
            array(
                'id' => 'refresh-'.uniqid(),
                'live'=>false
            )
     ); ?>
</div>

<div id="search" style="display: block">
    <div class="search-form">
        <!-- Render search filter -->
        <?php echo $this->renderPartial('_search'); ?>
    </div>  
</div>
<div id="linkback" style="display:none">
    <span class="ui-icon ui-icon-arrowreturnthick-1-w" style="display:inline-block;"></span>
    <?php echo CHtml::link("Go Back", array('stacker/monitor')); ?>

</div>

<div id="results-grid">
    <?php  $this->renderPartial('_lists',array('dataProvider'=>$dataProvider)); ?>
</div>

<?php $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id'=>'ajaxloader',
        'options'=>array(
            'title'=>'Loading',
            'modal'=>true,
            'width'=>'200',
            'height'=>'45',
            'resizable'=>false,
            'autoOpen'=>false,
        ),
)); ?>

<div class="loading"></div><div class="loadingtext">Loading, please wait...</div>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
