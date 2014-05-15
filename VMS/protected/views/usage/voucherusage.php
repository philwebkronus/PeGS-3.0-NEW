<div <?php echo 'style="display:'.Yii::app()->session['display'].'"'; ?>>
<br/>
<hr color="black" />
<?php
    $vouchertype = Yii::app()->session['vouchertype'];
    if($vouchertype == 'All')
    {
        $grid = array(
        array('name'=>'Voucher Type',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherType"])',
        'htmlOptions' => array('style' => 'text-align:center'),
        ),

        array('name'=>'Status',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Status"])',
        'htmlOptions' => array('style' => 'text-align:center'),
        ),

        array('name'=>'Total Amount',
        'type'=>'raw',
        'value'=>'CHtml::encode(number_format($data["TotalAmount"],2))',
        'htmlOptions' => array('style' => 'text-align:right'),    
        ),

        array('name'=>'Total Count',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TotalCount"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
        
        );        
    }
    else
    {
        $grid = array(
        array('name'=>'Status',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Status"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),

        array('name'=>'Total Amount',
        'type'=>'raw',
        'value'=>'CHtml::encode(number_format($data["TotalAmount"],2))',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),

        array('name'=>'Total Count',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TotalCount"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),

        );
    }

        $this->widget('zii.widgets.grid.CGridView', array(
        'dataProvider' => $arrayDataProvider,
        'enablePagination' => true,

        //'htmlOptions' => array('style'=>'width: 630px;'),
        'columns' => $grid,
        ));
?>
    <a href="exporttocsv"><b>Export To CSV</b></a>
</div>