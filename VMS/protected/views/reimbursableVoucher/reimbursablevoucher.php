<?php
if(!isset(Yii::app()->session['display']))
Yii::app()->session['display'] = 'none';
?>

<div <?php echo 'style="display:'.Yii::app()->session['display'].'"'; ?>>
<br/>
<hr color="black" />
<?php

        $grid = array(
        array('name'=>'Voucher Type',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherType"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),    
            
        array('name'=>'Voucher Code',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherCode"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),

        array('name'=>'Terminal Name',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TerminalCode"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
        
        array('name'=>'Amount',
        'type'=>'raw',
        'value'=>'CHtml::encode(number_format($data["Amount"],2))',
        'htmlOptions' => array('style' => 'text-align:right'),    
        ),

        array('name'=>'Date Created',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateCreated"] != "-" ? date("Y-m-d h:i:s", strtotime($data["DateCreated"])) : "-")',
        'htmlOptions' => array('style' => 'text-align:left'),    
        ),
            
        array('name'=>'Date Used',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateUsed"] != "-" ? date("Y-m-d h:i:s", strtotime($data["DateUsed"])) : "-")',
        'htmlOptions' => array('style' => 'text-align:left'),    
        ),
            
        array('name'=>'Date Claimed',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateClaimed"] != "-" ? date("Y-m-d h:i:s", strtotime($data["DateClaimed"])) : "-")',
        'htmlOptions' => array('style' => 'text-align:left'),    
        ),
        
        array('name'=>'Date Expiry',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateExpiry"] != "-" ? date("Y-m-d h:i:s", strtotime($data["DateExpiry"])) : "-")',
        'htmlOptions' => array('style' => 'text-align:left'),        
        ),

        array('id'=>'forreimburse',
        'class'=>'CCheckBoxColumn',
        'checked'=>'0',
        'value'=>'$data["VoucherCode"]'),    
        
        );
        
        $this->widget('zii.widgets.grid.CGridView', array(
        'id'=>'reimbursegrid',
        'dataProvider' => $arrayDataProvider,
        'enablePagination' => true,
        'selectableRows'=>2,
        'ajaxUpdate'=>true,
        //'htmlOptions' => array('style'=>'width: 630px;'),
        'columns' => $grid
        ));
?>
    <table>
        <tr>
            <td>
                <?php echo CHtml::Button("Reimburse", array("id"=>"reimburse", 'onclick'=>'$("#confirm").dialog("open");', "disabled"=>Yii::app()->session['disable'])); ?>
            </td>
        </tr> 
        <tr>
            <td>
                <br/>
            <a href="exporttocsv"><b>Export To CSV</b></a>
            </td> 
        </tr>  
        </table>
    
    
    
</div>
