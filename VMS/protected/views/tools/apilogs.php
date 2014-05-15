<?php

/*
 * @Date Dec 11, 2012
 * @Author owliber
 */
?>
<?php

$this->breadcrumbs=array(
	'Tools','API Logs'
);
?>
<h4>API Logs</h4>
<hr color="black" />
<div id="options">
    <span class="ui-icon ui-icon-refresh" style="display:inline-block;"></span>
    <?php
        echo CHtml::ajaxlink("Refresh", array('tools/apilogs'),array(
                'type'=>'GET',
                'success'=>'$.fn.yiiGridView.update("data-grid")'
        ));
    ?>
    <span class="ui-icon ui-icon-search" style="display:inline-block;"></span>

    <?php echo CHtml::link(" Advance Search", "", array(
            'onclick'=>'$("#search").toggle();
                        $("#Submit").toggle();
                        $(this).hide();
                        $("#hide-as").show();',
            'style'=>'cursor:pointer',
            'id'=>'show-as'
    )); ?>
     <?php echo CHtml::link(" Hide Advance Search", "", array(
            'onclick'=>'$("#search").toggle();
                        $("#Submit").toggle();
                        $("#show-as").show(); 
                        $(this).hide();',
            'style'=>'cursor:pointer; display:none',
            'id'=>'hide-as'
    )); ?>
    
</div>

<?php $display = $this->advanceFilter == true ? 'block' : 'none'; ?>

<div id="search" class="search-form" style="display: <?php echo $display; ?>;">
    <?php echo $this->renderPartial('_apisearch'); ?>
</div>
<br/>
<hr color="black" />
<div id="results-grid">
    <?php echo $this->renderPartial('_apilogresults',array('dataProvider'=>$dataProvider)); ?>
</div>