<div <?php echo 'style="display:'.Yii::app()->session['display'].'"'; ?>>
<?php
//echo 'test';
$accounttype = Yii::app()->session['AccountType'];
if($accounttype == 4)
{
      $grid = array(
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
        
                    array('name'=>'DateExpiry',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["DateExpiry"])'),
        
                    array('name'=>'Status',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["Status"])'),
    );
}    
    $this->widget('zii.widgets.grid.CGridView', array(
        'dataProvider' => $arrayDataProvider,
        'enablePagination' => true,

        //'htmlOptions' => array('style'=>'width: 630px;'),
        'columns' => $grid
    ));
?>
</div>
