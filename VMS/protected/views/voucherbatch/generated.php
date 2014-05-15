<?php

/**
 * @author owliber
 * @date Nov 6, 2012
 * @filename generated.php
 * 
 */
?>

<?php
$this->breadcrumbs=array(
        'Voucher Generation'=>array('manage'),
        'Generated vouchers'
);
?>

<?php

if($this->viewByBatch)
{
    $this->menu=array(
        array('label'=>'Voucher generation','url'=>array('voucherBatch/manage')),
        array('label'=>'Export to CSV','url'=>array('voucherBatch/exporttocsv','BatchNo'=>$_GET['batchno'])),
    );
}
else
{
    $this->menu=array(
        array('label'=>'Voucher generation','url'=>array('voucherBatch/manage')),
    );
}

?>

<h4> Search Generated Vouchers </h4>
<div class="information"><span> Search and filter all generated vouchers </span></div>

<!-- Show or hide search filter -->
<?php $toggle = $this->toggle_search == true ? 'block' : 'none'; ?>

<div class="search-form" style="display:<?php echo $toggle; ?>">
    <!-- Render search filter -->
    <?php echo $this->renderPartial('_search'); ?>
</div>

<div id="list-grid" class="list-grid">
    <!-- Render results -->
    <?php echo $this->renderPartial('_lists',array('arrayDataProvider'=>$arrayDataProvider)); ?>
</div>
