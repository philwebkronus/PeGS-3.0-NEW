<div <?php echo 'style="display:'.Yii::app()->session['display'].'"'; ?>>
    <a href="exporttocsv" <?php echo 'style="display:'.Yii::app()->session['showcsv'].'"'; ?>><b>Export To CSV</b></a>
<?php
//echo 'test';
$accounttype = Yii::app()->session['AccountType'];
if($accounttype == 4)
{
      $grid = array(
                    array('name'=>'VoucherType',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["VoucherType"])'),
                    
                    array('name'=>'VoucherCode',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["VoucherCode"])'),
                    
                    array('name'=>'Amount',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["Amount"])'),
        
                    array('name'=>'TerminalCode',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["TerminalCode"])'),
        
                    array('name'=>'DateCreated',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["DateCreated"])'),
        
                    /*array('name'=>'DateExpiry',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["DateExpiry"])'),*/
        
                    array('name'=>'Status',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["Status"])'),
    );  
}
else
{
    $grid = array(
                    array('name'=>'VoucherType',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["VoucherType"])'),
        
                    array('name'=>'VoucherCode',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["VoucherCode"])'),
                    
                    array('name'=>'Amount',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["Amount"])'),
        
                    array('name'=>'TerminalCode',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["TerminalCode"])'),
        
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
}    
    $this->widget('zii.widgets.grid.CGridView', array(
        'dataProvider' => $arrayDataProvider,
        'enablePagination' => true,

        'htmlOptions' => array('style'=>'overflow: auto;'),
        'columns' => $grid
    ));
?>
</div>
