<?php

/**
 * @author owliber
 * @date Nov 8, 2012
 * @filename _lists.php
 * 
 */
?>

<?php
    
    $this->widget('zii.widgets.grid.CGridView', array(
        'id' => 'data-grid',
        'dataProvider' => $dataProvider,
        'ajaxUpdate'=>true,
        'rowCssClassExpression'=>'$data["Quantity"] > Utilities::getParameters("STACKER_COUNT_TRIGGER") ?" rowred ":" "',
        'columns' => array(
            array('name' => 'EGMMachine',
                'header' => 'EGM Machine',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["ComputerName"])',
                'htmlOptions' => array('style' => 'text-align:center'),
            ),
            array('name' => 'DateStarted',
                'header' => 'Date Started',
                'type' => 'raw',
                'value' => 'CHtml::encode(date("F d, Y H:i",strtotime($data["DateStarted"])))',
                'htmlOptions' => array('style' => 'text-align:center'),
            ),
            array('name' => 'DateEnded',
                'header' => 'Date Ended',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["DateEnded"] != "" ? date("F d, Y H:i",strtotime($data["DateEnded"])) : "")',
                'htmlOptions' => array('style' => 'text-align:center'),
            ),
            array('name' => 'Quantity',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["Quantity"])',
                'htmlOptions' => array('style' => 'text-align: right;'),
            ),
            array('name' => 'CashAmount',
                'header' => 'Cash Amount',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["CashAmount"])',
                'htmlOptions' => array('style' => 'text-align:right'),
            ),
            array('name' => 'TotalAmount',
                'header' => 'Total Amount',
                'type' => 'raw',
                'value' => 'CHtml::encode($data["TotalAmount"])',
                'htmlOptions' => array('style' => 'text-align: right'),
            ),
            array('class' => 'CButtonColumn',
                'template' => '{buttonDetails}',
                'buttons' => array(
                    'buttonDetails' => array(
                        'label' => 'Details ',
                        //'imageUrl' => Yii::app()->request->baseUrl . '/images/ui-icon-edit.png',
                        'url' => 'Yii::app()->createUrl("/stacker/ajaxStackerDetails", array("SessionID" => $data["EGMStackerSessionID"]))',
                        'options' => array(
                            'ajax' => array(
                                'type' => 'GET',
                                'url' => 'js:$(this).attr("href")',
                                'data'=>array(
                                    'EGM'=>Yii::app()->session['EGM'],
                                ),
                               'beforeSend' => 'function(){
                                     $(".ui-dialog-titlebar").hide()   
                                     $("#ajaxloader").dialog("open")
                                }',
                                'complete' => 'function(){
                                    $(".ui-dialog-titlebar").hide()   
                                    $("#ajaxloader").dialog("close")
                                }',
                                'success' => 'function(data){
                                            $("#results-grid").html(data);  
                                            $("#search").toggle();
                                            $("#linkback").toggle();
                                            $("#refresh").hide();
                               }',
                                'update' => '#results-grid',
                            ),
                                                        
                        ),
                        
                    ),
                    
                ),
                'header' => 'Options',
            ),
        ),
    ));
?>