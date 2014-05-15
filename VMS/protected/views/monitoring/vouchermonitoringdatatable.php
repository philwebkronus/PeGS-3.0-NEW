<div <?php echo 'style="display:'.Yii::app()->session['display'].'"'; ?>>
<br/>
<hr color="black" />
<?php
//echo 'test';
$accounttype = Yii::app()->session['AccountType'];
if($accounttype == 4)
{
    $dv = new DateTime($data["DateCreated"]);
      $dateCreated = $dv->format('m-d-Y');
                    
      $grid = array(
                    array('name'=>'Voucher Code',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["VoucherCode"])',
                    'htmlOptions' => array('style' => 'text-align:center'),    
                    ),
                    
                    array('name'=>'Amount',
                    'type'=>'raw',
                    'value'=>'CHtml::encode(number_format($data["Amount"],2))',
                    'htmlOptions' => array('style' => 'text-align:right'),     
                    ),
        
                    array('name'=>'Terminal Name',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["TerminalCode"])',
                    'htmlOptions' => array('style' => 'text-align:center'),     
                    ),
        
                    array('name'=>'Date Created',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["DateEnded"] != "" ? date("d-m-Y h:i:s", strtotime($data["DateCreated"])) : "")',
                    'htmlOptions' => array('style' => 'text-align:left'),
                    ),
        
                    /*array('name'=>'DateExpiry',
                    'type'=>'raw',
                    'value'=>'CHtml::encode(date("d-m-Y h:i:s", strtotime($data["DateExpiry"])))'),*/
        
                    array('name'=>'Status',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["Status"])',
                    'htmlOptions' => array('style' => 'text-align:center'),
                    ),
    );  
}
else
{
    $grid = array(
                    array('name'=>'Voucher Code',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["VoucherCode"])',
                    'htmlOptions' => array('style' => 'text-align:center'),
                    ),
                    
                    array('name'=>'Amount',
                    'type'=>'raw',
                    'value'=>'CHtml::encode(number_format($data["Amount"],2))',
                    'htmlOptions' => array('style' => 'text-align:right'),
                    ),
        
                    array('name'=>'Terminal Name',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["TerminalCode"])',
                    'htmlOptions' => array('style' => 'text-align:center'),
                    ),
        
                    array('name'=>'Date Created',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["DateCreated"] != "-" ? date("d-m-Y h:i:s", strtotime($data["DateCreated"])) : "-")',
                    'htmlOptions' => array('style' => 'text-align:left'),
                    ),
        
                    array('name'=>'Date Expiry',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["DateExpiry"] != "-" ? date("d-m-Y h:i:s", strtotime($data["DateExpiry"])) : "-")',
                    'htmlOptions' => array('style' => 'text-align:left'),
                    ),
        
                    array('name'=>'Status',
                    'type'=>'raw',
                    'value'=>'CHtml::encode($data["Status"])',
                    'htmlOptions' => array('style' => 'text-align:center'),
                    ),
    );
}    
    $this->widget('zii.widgets.grid.CGridView', array(
        'dataProvider' => $arrayDataProvider,
        'enablePagination' => true,

        //'htmlOptions' => array('style'=>'width: 630px;'),
        'columns' => $grid
    ));
?>
<a href="exporttocsv"><b>Export To CSV</b></a>
</div>
