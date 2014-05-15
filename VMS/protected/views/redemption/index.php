<?php
/**
 * @author owliber
 * @date Nov 11, 2012
 * @filename index.php
 * 
 */
?>
<?php Yii::app()->clientScript->registerCoreScript('jquery'); ?>
<?php Yii::app()->clientScript->registerCoreScript('jquery.ui'); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/custom.js'); ?>
<?php Yii::app()->clientScript->registerScript('validation', '

    function checkInput(){
        if($("#SearchCode").val().length > 0)
            document.getElementById("Verify").disabled  = false;
        else
            document.getElementById("Verify").disabled = true;
            
    }

', CClientScript::POS_HEAD); ?>
<?php
$this->breadcrumbs = array(
    'Voucher Maintenance','Voucher Redemption',
);
?>

<h4> Verify payout ticket for redemption</h4>
<hr color="black" />
<div class="search-form">
    <?php
    echo CHtml::beginForm(array(
        '/redemption/verify'), 'POST', array(
        'id' => 'SearchForm',
        'name' => 'SearchForm'));
    ?>
    <?php echo CHtml::label("Ticket Code ", "SearchCode"); ?>
    <?php
    echo CHtml::textField("SearchCode", "", array(
        'onkeypress' => 'checkInput();return isNumberKey(event);',
        'onblur' => 'checkInput()',
        'maxlength' => 18,
            //'onpaste'=>'return false;'
    ));
    ?>
    <?php
    echo CHtml::submitButton("Verify Voucher", array(
        'id' => 'Verify',
        'name' => 'Verify',
        'disabled' => 'disabled',
        ));
    ?>
    <?php echo CHtml::endForm(); ?>

    
    

    <!-- Search result message -->
    <?php
    $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id' => 'verification-dialog',
        'options' => array(
            'title' => 'Ticket Verification',
            'modal' => true,
            'width' => '400',
            'height' => '170',
            'resizable' => false,
            'autoOpen' => $this->invalid_voucher == 1 ? true : false,
            'buttons' => array(
                'Close' => 'js:function(){
                        $("#verification-dialog").dialog("close");
                        document.location = "verify";
                    }',
            ),
        ),
    ));
    ?>

    <p><?php echo $this->status; ?></p>

    <?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>
    
</div>

<?php if (!$result): ?>
    <div class="view">Scan payout ticket barcode or enter code to verify</div>
<?php endif; ?>

<!-- Render voucher info -->
<?php $this->renderPartial('_voucherinfo', array('result' => $result)); ?>
