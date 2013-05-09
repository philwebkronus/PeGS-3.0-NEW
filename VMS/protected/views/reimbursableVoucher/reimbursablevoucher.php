<div <?php echo 'style="display:'.Yii::app()->session['display'].'"'; ?>>
    <a href="exporttocsv"><b>Export To CSV</b></a>
<?php

        $grid = array(
        array('name'=>'VoucherType',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherType"])'),    
            
        array('name'=>'VoucherCode',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherCode"])'),

        array('name'=>'TerminalCode',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TerminalCode"])'),
        
        array('name'=>'Amount',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Amount"])'),

        array('name'=>'DateCreated',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateCreated"])'),
            
        array('name'=>'DateUsed',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateUsed"])'),
            
        array('name'=>'DateClaimed',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateClaimed"])'),
        
        array('name'=>'DateExpiry',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateExpiry"])'),

        array('id'=>'forreimburse',
        'class'=>'CCheckBoxColumn',
        'checked'=>'0',
        'value'=>'CHtml::encode($data["VoucherCode"])'),    
        
        );
        
        $this->widget('zii.widgets.grid.CGridView', array(
        'dataProvider' => $arrayDataProvider,
        'enablePagination' => true,
        'selectableRows'=>2,
        //'htmlOptions' => array('style'=>'width: 630px;'),
        'columns' => $grid
        ));
?>
</div>
