<?php

/**
 * @author owliber
 * @date Nov 8, 2012
 * @filename _details.php
 * 
 */
?>

<br /><h5><?php echo $egmmachine; ?> Transaction Details</h5>
<span>Total Amount : <strong><?php echo number_format($totals,2); ?></strong></span>
<?php

$this->widget('zii.widgets.grid.CGridView', array(
    'id' => 'data-grid',
    'dataProvider' => $dataProvider,
    'columns' => array(
            array('name' => 'TransactionDate',
                'header' => 'Transaction Date',
                'type' => 'raw',
                'value' => 'CHtml::encode(date("Y-m-d H:i:s",strtotime($data["TransactionDate"])))',
                'htmlOptions' => array('style' => 'text-align:center',),
            ),
            array('name' => 'TerminalID',
                'header' => 'Terminal ID',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["TerminalID"])',
                'htmlOptions' => array('style' => 'text-align:center',),
            ),
            array('name' => 'TransactionType',
                'header' => 'Transaction Type',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["TransactionType"])',
                'htmlOptions' => array('style' => 'text-align: center'),
            ),
            array('name' => 'CashType',
                'header' => 'Cash Type',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["CashType"])',
                'htmlOptions' => array('style' => 'text-align: center'),
            ),
            array('name' => 'VoucherCode',
                'header' => 'Voucher Code',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["VoucherCode"])',
                'htmlOptions' => array('style' => 'text-align: center'),
            ),
            array('name' => 'Amount',
                'type' => 'raw',
                'value' => 'CHtml::encode(number_format($data["Amount"],2))',
                'htmlOptions' => array('style' => 'text-align: right'),
            ),
        ),
    
));
?>
