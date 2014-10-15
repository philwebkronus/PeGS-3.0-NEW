<div <?php echo 'style="display:'.Yii::app()->session['display'].'"'; ?>>
    <br/>
    <hr color="black" />
    <div id='transpercutoffgrid'>
<?php

    if(isset(Yii::app()->session['transactiondate'])){
        $datetime = new DateTime(Yii::app()->session['transactiondate']);
        $datetime->modify('+1 day');
        $vdate = $datetime->format('Y-m-d');
        Yii::app()->session['vdate'] = $vdate;
        //echo Yii::app()->session['transactiondate']." to ".$vdate. " ".Yii::app()->params['cutofftimeend'];
    }

        $grid = array(
           
        array('name'=>'CouponCode',
        'header'=>'Code',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherCode"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
            
        array('name'=>'SiteName',
        'header'=>'Site',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["SiteName"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
            
        array('name'=>'TerminalName',
        'header'=>'Terminal',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TerminalName"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
            
        array('name'=>'Amount',
        'header'=>'Amount',
        'type'=>'raw',
        'value'=>function($data){
            $amt = CHtml::encode($data["Amount"]);
            return number_format(doubleval($amt), 2);
        },
        'htmlOptions' => array('style' => 'text-align:right'),    
        ),
            
        array('name'=>'DateCreated',
        'header'=>'Transaction Date',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateCreated"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ), 
            
            
        array('name'=>'DateExpiry',
        'header'=>'Date Expired',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateExpired"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ), 
            
        array('name'=>'Source',
        'header'=>'Source',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Source"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ), 
            
        array('name'=>'LoyaltyCreditable',
        'header'=>'Is Creditable',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["IsCreditable"] == "1" ? "Creditable" : "Not Creditable")',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ), 
        
         array('name'=>'Status',
        'header'=>'Status',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Status"])',
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
    <br><br>
</div>
    </div>