<?php

/*
 * @Date Dec 11, 2012
 * @Author owliber
 */
?>

<?php   $this->widget('zii.widgets.grid.CGridView', array(
            'id' => 'data-grid',
            'dataProvider' => $dataProvider,
            'ajaxUpdate'=>true,
            'columns' => array(
                 array('name' => 'TransDateTime',
                    'header' => 'Transaction Time',
                    'type' => 'raw',
                    'value' => 'CHtml::encode(date("M d, Y H:i",strtotime($data["TransDateTime"])))',
                    'htmlOptions' => array('style' => 'text-align:center'),
                ),
                array('name' => 'AID',
                    'header' => 'Account ID',
                    'type' => 'raw',
                    'value' => 'CHtml::encode($data["AID"])',
                    'htmlOptions' => array('style' => 'text-align:center'),
                ),           
                array('name' => 'SiteCode',
                     'header' => 'Site Code',
                    'type' => 'raw',
                    'value' => 'CHtml::encode($data["SiteCode"])',
                    'htmlOptions' => array('style' => 'text-align: center'),
                ),
                array('name' => 'TransDetails',
                    'header' => 'Details',
                    'type' => 'raw',
                    'value' => 'CHtml::encode($data["TransDetails"])',
                ),

                array('name' => 'RemoteIP',
                    'header' => 'Remote IP Address',
                    'type' => 'raw',
                    'value' => 'CHtml::encode($data["RemoteIP"])',
                ),
            ),
           // 'htmlOptions'=>array('class'=>'logs')
        ));
    
?>
