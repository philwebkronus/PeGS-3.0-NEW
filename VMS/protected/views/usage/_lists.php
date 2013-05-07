<?php

/**
 * @author owliber
 * @date Nov 8, 2012
 * @filename _lists.php
 * 
 */
?>

<?php
    /* Toggle columns per voucher status */
    $show_used = Yii::app()->session['Status'] == 3 ? true : false;
    $show_claimed = Yii::app()->session['Status'] == 4 ? true : false;
    $show_expired = Yii::app()->session['Status'] == 6 ? true : false;
    
    /* Column data */
    $gridData = array(
            array('name' => 'VoucherType',
                'header' => 'Voucher Type',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["VoucherType"])',
                'htmlOptions' => array('style' => 'text-align:center'),
            ),
            array('name' => 'VoucherCode',
                'header' => 'Voucher Code',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["VoucherCode"])',
                'htmlOptions' => array('style' => 'text-align:center'),
            ),
            array('name' => 'EGM',
                'header' => 'EGM',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["ComputerName"])',
            ),
            array('name' => 'Amount',
                'header' => 'Amount',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["Amount"])',
                'htmlOptions' => array('style' => 'text-align:right'),
                //'footer'=>'$data["Total"]'
            ),
            array('name' => 'DateCreated',
                'header' => 'Date Created',
                'type' => 'raw',
                'value' => 'CHtml::encode(date("M d, Y H:i",strtotime($data["DateCreated"])))',
                'htmlOptions' => array('style' => 'text-align:center'),
            ),
            array('name' => 'DateUsed',
                'header' => 'Date Used',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["DateUsed"] != "" ? date("M d, Y H:i",strtotime($data["DateUsed"])) : "")',
                'visible'=> $show_used,
                'htmlOptions' => array('style' => 'text-align:center'),
            ),  
            array('name' => 'DateClaimed',
                'header' => 'Date Claimed',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["DateClaimed"] != "" ? date("M d, Y H:i",strtotime($data["DateClaimed"])) : "")',
                'visible'=> $show_claimed,
                'htmlOptions' => array('style' => 'text-align:center'),                
            ),
            array('name' => 'DateExpired',
                'header' => 'Date Expired',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["DateExpiry"] != "" ? date("M d, Y H:i",strtotime($data["DateExpiry"])) : "")',
                'visible'=> $show_expired,
                'htmlOptions' => array('style' => 'text-align:center'),                
            ),
            array('name' => 'Status',
                'header' => 'Status',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["Status"])',
            )
        );
    
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'ajaxUpdate'=>true,
        'columns' => $gridData,
    ));
?>