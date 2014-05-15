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
                    'value' => 'CHtml::encode(date("Y-m-d H:i:s",strtotime($data["TransDateTime"])))',
                    'htmlOptions' => array('style' => 'text-align:center'),
                ),
                array('name' => 'APIMethod',
                    'header' => 'API Method',
                    'type' => 'raw',
                    'value' => 'CHtml::encode($data["APIMethod"])',
                ),           
                array('name' => 'Source',
                     'header' => 'Source',
                    'type' => 'raw',
                    'value' => 'CHtml::encode($data["Source"])',
                    'htmlOptions' => array('style' => 'text-align: center'),
                ),
                array('name' => 'TerminalCode',
                     'header' => 'Terminal Code',
                    'type' => 'raw',
                    'value' => 'CHtml::encode($data["TerminalCode"])',
                ),
                array('name' => 'TransDetails',
                    'header' => 'Details',
                    'type' => 'raw',
                    'value' => 'CHtml::encode($data["TransDetails"])',
                ),        
                array('name' => 'TrackingID',
                    'header' => 'Tracking ID',
                    'type' => 'raw',
                    'value' => 'CHtml::encode($data["TrackingID"])',
                ), 
                array('name' => 'RemoteIP',
                    'header' => 'Remote IP Address',
                    'type' => 'raw',
                    'value' => 'CHtml::encode($data["RemoteIP"])',
                ),
                array('name' => 'Status',
                    'header' => 'Status',
                    'type' => 'raw',
                    'value' => 'CHtml::encode($data["Status"])',
                ),
            ),
           // 'htmlOptions'=>array('class'=>'logs')
        ));
?>
