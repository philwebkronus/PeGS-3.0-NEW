<div <?php echo 'style="display:'.Yii::app()->session['display'].';"'; ?>>
<?php

        $grid = array(
        array('name'=>'VoucherType',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherType"])'),
         
        array('name'=>'TrackingID',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TrackingID"])'),
            
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
            
        array('name'=>'DateReimbursed',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateReimbursed"])'),
        
        array('name'=>'DateExpiry',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateExpiry"])'),

        array('name'=>'DateCancelled',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateCancelled"])'),
            
        array('name'=>'Status',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Status"])'),
            
        );
        
        $this->widget('zii.widgets.grid.CGridView', array(
        'dataProvider' => $arrayDataProvider,
        'enablePagination' => true,

        'htmlOptions' => array('style'=>'overflow: auto;'/*'width: 630px;'*/),
        'columns' => $grid
        ));
?>
</div>
