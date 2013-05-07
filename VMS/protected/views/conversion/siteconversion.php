<div <?php echo 'style="display:'.Yii::app()->session['display'].'"'; ?>>
<a href="exporttocsv"><b>Export To CSV</b></a>
<?php

        $grid = array(
        /*array('name'=>'VoucherTypeID',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherTypeID"])'),*/

        array('name'=>'SiteCode',
        'header'=>'Site',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["SiteCode"])'),

        array('name'=>'TotalAmountReimbursed',
        'header'=>'Total Amount Reimbursed',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TotalAmountReimbursed"])'),

        array('name'=>'TotalCount',
        'header'=>'Reimbursed Voucher Count',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TotalCount"])'),
            
        );
        
        $this->widget('zii.widgets.grid.CGridView', array(
        'dataProvider' => $arrayDataProvider,
        'enablePagination' => true,
        //'summaryText'=> false,

        //'htmlOptions' => array('style'=>'width: 630px;'),
        'columns' => $grid
        ));
?>
</div>