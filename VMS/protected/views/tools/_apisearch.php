<?php

/*
 * @Date Dec 11, 2012
 * @Author owliber
 */
?>
<?php Yii::app()->clientScript->registerScript('ui','
        
        var datefrom = $("#DateFrom"), dateto = $("#DateTo")
   
 ', CClientScript::POS_END);
 ?>
 <?php echo CHtml::beginForm(); ?> 
 <?php Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker'); ?>
 <?php
    echo CHtml::label("From ", "DateFrom");
       
    $this->widget('CJuiDateTimePicker',array(
        'name'=>'DateFrom',
        'id'=>'DateFrom',
        'value'=>$this->dateFrom,
        'mode'=>'datetime', //use "time","date" or "datetime" (default)
        'options'=>array(
            'dateFormat'=>'yy-mm-dd',
            'timeFormat'=> 'hh:mm',
            'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
            'showOn'=>'button', // 'focus', 'button', 'both'
            'buttonText'=>Yii::t('ui','DateFrom'), 
            'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
            'buttonImageOnly'=>true,
        ),// jquery plugin options
        'htmlOptions'=>array('readonly'=>'readonly'),
        'language'=>'',
        
    ));


    echo CHtml::label(" To ", "DateTo");
        
    $this->widget('CJuiDateTimePicker',array(
        'name'=>'DateTo',
        'id'=>'DateTo',
        'value'=>$this->dateTo,
        'mode'=>'datetime', //use "time","date" or "datetime" (default)
        'options'=>array(
            'dateFormat'=>'yy-mm-dd',
            'timeFormat'=> 'hh:mm',
            'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
            'showOn'=>'button', // 'focus', 'button', 'both'
            'buttonText'=>Yii::t('ui','DateTo'), 
            'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
            'buttonImageOnly'=>true,
        ),// jquery plugin options
        'htmlOptions'=>array('readonly'=>'readonly'),
        'language'=>'',
        
    ));

    ?>
    <?php
        echo CHtml::ajaxButton("Search", "APILogs", array(
                    'type'=>'GET',                
                    'data'=>array(
                        'DateFrom'=>'js:function(){return datefrom.val();}',
                        'DateTo'=>'js:function(){return dateto.val();}',
                    ),
                    'success'=>'function(data){
                        $("#results-grid").html(data); 
                    }',
                    'update'=>'#results-grid',
                    ),
                    array(
                        'name'=>'Search',
                        'id'=>'Search',
                    )
      );
    ?>
<?php echo CHtml::endForm(); ?>