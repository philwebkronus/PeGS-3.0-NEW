<div <?php echo 'style="display:'.Yii::app()->session['display'].';"'; ?>>
    <br/>
    <hr color="black" />
<?php

        $grid = array(
        array('name'=>'Voucher Type',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherType"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
         
        array('name'=>'Tracking ID',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TrackingID"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
            
        array('name'=>'Voucher Code',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherCode"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),

        array('name'=>'Terminal Code',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TerminalCode"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
        
        array('name'=>'Amount',
        'type'=>'raw',
        'value'=>'CHtml::encode(number_format($data["Amount"],2))',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),

        array('name'=>'Date Created',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateCreated"] != "-" ? date("Y-m-d H:i:s",strtotime($data["DateCreated"])) : "-")',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
            
        array('name'=>'Date Used',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateUsed"] != "-" ? date("Y-m-d H:i:s",strtotime($data["DateUsed"])) : "-")',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
            
        array('name'=>'Date Claimed',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateClaimed"] != "-" ? date("Y-m-d H:i:s",strtotime($data["DateClaimed"])) : "-")',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
            
        array('name'=>'Date Reimbursed',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateReimbursed"] != "-" ? date("Y-m-d H:i:s",strtotime($data["DateReimbursed"])) : "-")',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
        
        array('name'=>'Date Expiry',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateExpiry"] != "-" ? date("Y-m-d H:i:s",strtotime($data["DateExpiry"])) : "-")',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),

        array('name'=>'Date Cancelled',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateCancelled"] != "-" ? date("Y-m-d H:i:s",strtotime($data["DateCancelled"])) : "-")',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
            
        array('name'=>'Status',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Status"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
            
        );
        
        $this->widget('zii.widgets.grid.CGridView', array(
        'dataProvider' => $arrayDataProvider,
        'enablePagination' => true,

        'htmlOptions' => array('style'=>'overflow: auto;'/*'width: 630px;'*/),
        'columns' => $grid
        ));
?>
    <a href="exporttocsv"><b>Export To CSV</b></a>
</div>
