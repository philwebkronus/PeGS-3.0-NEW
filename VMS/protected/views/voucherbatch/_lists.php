<?php

/**
 * @author owliber
 * @date Nov 8, 2012
 * @filename _lists.php
 * 
 */
?>

<div id="export-link" class="prepend-top" style="display:none">
    <span class="ui-icon ui-icon-arrowthickstop-1-s" style="display:inline-block;"></span>
    <?php echo CHtml::link("Export to CSV", array('voucherbatch/exporttocsv?BatchNo='.Yii::app()->session['BatchNo'])); ?>  
</div>

<?php $gridData = array(
                    array('name'=>'BatchNumber',                        
                            'header'=>'Batch Number',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["BatchNumber"] == "" ? "Individual" : $data["BatchNumber"])',
                            'htmlOptions'=>array('style'=>'text-align:center',),
                    ),
                    array('name'=>'VoucherCode',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["VoucherCode"])',
                            'htmlOptions'=>array('style'=>'padding-left: 50px; text-align: right'),
                    ),
                    array('name'=>'Amount',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["Amount"])',
                            'htmlOptions'=>array('style'=>'padding-left: 50px; text-align: right'),

                    ),    
                    array('name'=>'DateCreated',
                            'header'=>'Date Generated',
                            'type'=>'raw',
                            'value'=>'CHtml::encode(date("F d, Y H:i",strtotime($data["DateCreated"])))',
                            'htmlOptions'=>array('style'=>'text-align: left'),

                    ),
                    array('name'=>'DateExpiry',
                            'header'=>'Expiry Date',
                            'type'=>'raw',
                            'value'=>'CHtml::encode(date("F d, Y",strtotime($data["DateExpiry"])))',
                            'htmlOptions'=>array('style'=>'text-align: left'),

                    ),
                    array('name'=>'Status',
                            'type'=>'raw',
                            'value'=>'CHtml::encode($data["Status"])',
                            'htmlOptions'=>array('style'=>'text-align: left'),

                    ),
    
                );
        
    $this->widget('zii.widgets.grid.CGridView',array(
        'id'=>'data-grid',
        'dataProvider'=>$dataProvider,
        'columns'=>$gridData,
        'ajaxUpdate'=>true,        
    )); 
    
?>
