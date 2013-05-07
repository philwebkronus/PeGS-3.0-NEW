<?php

/**
 * @author owliber
 * @date Nov 7, 2012
 * @filename _search.php
 * 
 */
?>

<?php Yii::app()->clientScript->registerScript('ui','
        
        var datefrom = $("#DateFrom"),
            dateto = $("#DateTo"),
            status = $("#Status")
   
 ', CClientScript::POS_END);
 ?>

<?php echo CHtml::beginForm(); ?>    

<?php
echo CHtml::label("From ", "DateFrom");
$this->widget('zii.widgets.jui.CJuiDatePicker', array(
    'name'=>'DateFrom',
    'value'=>$this->dateFrom,
    'options'=>array(
        'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
        'showOn'=>'button', // 'focus', 'button', 'both'
        'buttonText'=>Yii::t('ui','From'), 
        'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
        'buttonImageOnly'=>true,
        'dateFormat'=>'yy-mm-dd',
    ),
    'htmlOptions'=>array(
        'style'=>'width:80px;vertical-align:top'
    ),  
));

echo CHtml::label("To ", "DateTo");
$this->widget('zii.widgets.jui.CJuiDatePicker', array(
    'name'=>'DateTo',
    'value'=>$this->dateTo,
    'options'=>array(
        'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
        'showOn'=>'button', // 'focus', 'button', 'both'
        'buttonText'=>Yii::t('ui','To'), 
        'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
        'buttonImageOnly'=>true,
        'dateFormat'=>'yy-mm-dd',
    ),
    'htmlOptions'=>array(
        'style'=>'width:80px;vertical-align:top'
    ),  
));

?>

<?php echo CHtml::label("Status ", "Status"); ?>
<?php echo CHtml::dropDownList('Status',$this->status, Utilities::getBatchStatus()); ?>
<?php /*echo CHtml::submitButton("Submit", array(
            'name'=>'Submit',
            'value'=>'Submit',
     ));*/
      echo CHtml::ajaxButton("Submit", "list", array(
                    'type'=>'POST',                
                    'data'=>array(
                        'DateFrom'=>'js:function(){return datefrom.val();}',
                        'DateTo'=>'js:function(){return dateto.val();}',
                        'Status'=>'js:function(){return status.val();}',
                    ),
                    'success'=>'function(data){
                        $("#data-grid").html(data); 
                    }',
                    'beforeSend' => 'function(){
                         $(".ui-dialog-titlebar").hide()   
                         $("#ajaxloader").dialog("open")
                    }',
                    'complete' => 'function(){
                        $(".ui-dialog-titlebar").hide()   
                        $("#ajaxloader").dialog("close")
                    }',
                    'update'=>'#data-grid',
                    ),                    
                    array(
                        'name'=>'Submit',
                        'id'=>'Submit',
                    )
      );
?>
    
<?php echo CHtml::endForm(); ?>

