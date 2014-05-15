<div <?php echo 'style="display:'.Yii::app()->session['display'].'"'; ?>>
    <br/>
    <hr color="black" />
<?php

        $grid = array(
        /*array('name'=>'VoucherTypeID',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherTypeID"])'),*/

        array('name'=>'SiteCode',
        'header'=>'Site',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["SiteCode"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
           
        array('name'=>'TotalAmountReimbursed',
        'header'=>'Total Amount Reimbursed',
        'type'=>'raw',
        'value'=>'CHtml::encode(number_format($data["TotalAmountReimbursed"],2))',
        'htmlOptions' => array('style' => 'text-align:right'),    
        ),

        array('name'=>'TotalCount',
        'header'=>'Reimbursed Voucher Count',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TotalCount"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
            
        );
        
        $this->widget('zii.widgets.grid.CGridView', array(
        'dataProvider' => $arrayDataProvider,
        'enablePagination' => true,
        //'summaryText'=> false,

        //'htmlOptions' => array('style'=>'width: 630px;'),
        'columns' => $grid
        ));
?>
    
    <a href="exporttocsv"><b>Export To CSV</b></a>
</div>