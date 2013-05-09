<div <?php echo 'style="display:'.Yii::app()->session['display'].'"'; ?>>
<a href="exporttocsv"><b>Export To CSV</b></a>
<?php
    $vouchertype = Yii::app()->session['vouchertype'];
    if($vouchertype == 'All')
    {
        $grid = array(
        array('name'=>'VoucherType',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherType"])'),

        array('name'=>'Status',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Status"])'),

        array('name'=>'TotalAmount',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TotalAmount"])'),

        array('name'=>'TotalCount',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TotalCount"])'),
        
        );        
    }
    else
    {
        $grid = array(
        array('name'=>'Status',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Status"])'),

        array('name'=>'TotalAmount',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TotalAmount"])'),

        array('name'=>'TotalCount',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TotalCount"])'),

        );
    }

        $this->widget('zii.widgets.grid.CGridView', array(
        'dataProvider' => $arrayDataProvider,
        'enablePagination' => true,

        //'htmlOptions' => array('style'=>'width: 630px;'),
        'columns' => $grid,
        ));
?>
</div>