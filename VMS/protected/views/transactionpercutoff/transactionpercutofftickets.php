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
//        echo Yii::app()->session['transactiondate']." to ".$vdate. " ".Yii::app()->params['cutofftimeend'];
    }
    if(Yii::app()->controller->action->id=='ticket') {
        ?>
        <br>
    <br>
    <h4><u>TICKET TRANSACTIONS PER CUT OFF SUMMARY</u></h4>
        <center>
        <table style="border: 2px #DEDEDE solid; min-width: 30%; max-width: 50%;">
            <tr>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; text-align: center; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;;">NO. OF TICKETS</td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; text-align: center; background: #D0E3EF;">VALUE</td>
            </tr>
            <tr><td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Printed Tickets Total</td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #f4f9fb;">
                    <?php
                    if(isset(Yii::app()->session['PrintedTicketsCount'])) {
                        echo Yii::app()->session['PrintedTicketsCount'];
                    }
                    ?>
                </td>
                <td  style="border-bottom: 0.1px #DEDEDE solid; min-width: 30%; max-width: 50%; text-align: right; background: #f4f9fb;">
                    <?php
                    if(isset(Yii::app()->session['PrintedTicketsTotal'])) {
                        echo Yii::app()->session['PrintedTicketsTotal'];
                    }
                    ?>
                </td>
            </tr>
            <tr><td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td><td colspan="2" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Active (Unused) Tickets</td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid;">
                    <?php
                    if(isset(Yii::app()->session['ActiveTicketsCount'])) {
                        echo Yii::app()->session['ActiveTicketsCount'];
                    }
                    ?>
                </td>
                <td style="border-bottom: 0.1px #DEDEDE solid; text-align: right;">
                    <?php
                    if(isset(Yii::app()->session['ActiveTicketsTotal'])) {
                        echo Yii::app()->session['ActiveTicketsTotal'];
                    }
                    ?>
                </td>
            </tr>
            <tr><td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td colspan="2" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Ticket Redemptions</td>
                <td colspan="2" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #f4f9fb;"></td>
                <td colspan="2" style="border-bottom: 0.1px #DEDEDE solid; background: #f4f9fb;"></td>
            </tr>
            <tr><td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Used (Deposit/Reload)</td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid;">
                    <?php
                    if(isset(Yii::app()->session['DepositReloadTicketsCount'])) {
                        echo Yii::app()->session['DepositReloadTicketsCount'];
                    }
                    ?>
                </td>
                <td  style="border-bottom: 0.1px #DEDEDE solid; text-align: right;">
                    <?php
                    if(isset(Yii::app()->session['DepositReloadTicketsTotal'])) {
                        echo Yii::app()->session['DepositReloadTicketsTotal'];
                    }
                    ?>
                </td>
            </tr>
            <tr><td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Encashed</td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #f4f9fb;">
                    <?php
                    if(isset(Yii::app()->session['EncashedTicketsCount'])) {
                        echo Yii::app()->session['EncashedTicketsCount'];
                    }
                    ?>
                </td>
                <td style="border-bottom: 0.1px #DEDEDE solid; text-align: right; background: #f4f9fb;">
                    <?php
                    if(isset(Yii::app()->session['EncashedTicketsTotal'])) {
                        echo Yii::app()->session['EncashedTicketsTotal'];
                    }
                    ?>
                </td>
            </tr>
<!--            <tr><td style="background: #D0E3EF;"></td><td colspan="2" style="border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Cancelled</td>
                <td colspan="3" style="border-right: 0.1px #DEDEDE solid;">
                    <?php
//                    if(isset(Yii::app()->session['VoidTicketsCount'])) {
//                        echo Yii::app()->session['VoidTicketsCount'];
//                    }
                    ?>
                </td>
                <td style="text-align: right;">
                    <?php
//                    if(isset(Yii::app()->session['VoidTicketsTotal'])) {
//                        echo Yii::app()->session['VoidTicketsTotal'];
//                    }
                    ?>
                </td>
            </tr>-->
        </table>
        </center>
    <h4><u>TRANSACTIONS DETAILS</u></h4>
        <?php
    }
        $grid = array(
        
        array('name'=>'SiteCode',
        'header'=>'Site/PEGS Code',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["SiteCode"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),  
        
        array('name'=>'TerminalName',
        'header'=>'Terminal Name',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["TerminalName"]);',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
        
        array('name'=>'CouponCode',
        'header'=>'Ticket Code',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["VoucherCode"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),
        
        array('name'=>'DateCreated',
        'header'=>'Date and Time Printed',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateCreated"])',
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
            
            
        array('name'=>'DateExpiry',
        'header'=>'Expiration Date',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateExpired"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ), 
        
        array('name'=>'Status',
        'header'=>'Status',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Status"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),        
                
//        array('name'=>'Source',
//        'header'=>'Source',
//        'type'=>'raw',
//        'value'=>'CHtml::encode($data["Source"])',
//        'htmlOptions' => array('style' => 'text-align:center'),    
//        ), 
            
        array('name'=>'DateUpdated',
        'header'=>'Date and Time Processed',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["DateUpdated"])',
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
    <?php if(Yii::app()->controller->action->id=='ticket') { ?>
    <?php $this->createUrl('exporttoexcelticket'); ?>
    <?php echo CHtml::link('<b>Export To Excel</b>','exporttoexcelticket'); ?>
    <?php } else { ?>
    <?php $this->createUrl('exporttoexcelcoupon'); ?>
    <?php echo CHtml::link('<b>Export To Excel</b>','exporttoexcelcoupon'); ?>
    <?php } ?>
    <br><br>
</div>
    </div>