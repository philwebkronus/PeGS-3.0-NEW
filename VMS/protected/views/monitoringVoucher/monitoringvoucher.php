<?php
$model = new MonitoringVoucherForm();

if(!isset(Yii::app()->session['display']))
Yii::app()->session['display'] = 'block';
?>

<div <?php echo 'style="display:'.Yii::app()->session['display'].'"'; ?>>
<br/>

<?php

    $grid = array(
        array('name'=>'Status',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Status"])',
        'htmlOptions' => array('style' => 'text-align:center'),    
        ),    
            
        array('name'=>'Count',
        'type'=>'raw',
        'value'=>'CHtml::encode($data["Count"])',
        'htmlOptions' => array('style' => 'text-align:right'),    
        ),
        
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
                 Note: Percentages are rounded to the nearest whole number.
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
