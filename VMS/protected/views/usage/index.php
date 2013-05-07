<?php

/*
 * @Date Jan 31, 2013
 * @Author owliber
 * 
 */
?>
<?php
$this->breadcrumbs=array(
	'Voucher Reports','Voucher Usage',
);
?>
<h4>Voucher Usage</h4>

<div id="search" style="display: block">
    <div class="search-form">
        <!-- Render search filter -->
        <?php echo $this->renderPartial('_search'); ?>
    </div>  
</div>

<div id="results-grid">
    <!--<div class="headsummary"><strong>Total Amount</strong> - <span class="emphasize"><php echo number_format($totalAmount,2); ?></span> <strong>Total Count</strong> - <span class="emphasize"><php echo $totalCount; ?></span></div>-->
    <?php $this->renderPartial('_lists',array('dataProvider'=>$dataProvider)); ?>
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