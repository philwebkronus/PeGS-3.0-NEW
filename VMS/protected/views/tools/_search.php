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

<table style="width: 600px">
    <tr>
        <td>
 <?php
    /*
    $this->widget('zii.widgets.jui.CJuiDatePicker', array(
        'name'=>'DateFrom',
        'value'=>$this->dateFrom,
        'options'=>array(
            'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
            'showOn'=>'button', // 'focus', 'button', 'both'
            'buttonText'=>Yii::t('ui','DateFrom'), 
            'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
            'buttonImageOnly'=>true,
            'dateFormat'=>'yy-mm-dd',
        ),
        'htmlOptions'=>array(
            'style'=>'width:80px;vertical-align:top'
        ),  
    ));
    */
    
    echo CHtml::label("From :", "dateFrom").
        CHtml::textField('DateFrom', date('Y-m-d h:i:s'), array('id'=>'DateFrom','readonly'=>'true', 'value'=>date('Y-m-d h:i:s'), 'style'=>'width: 150px;')).
        CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton","class"=>"pointer","style"=>"cursor: pointer;"));
        $this->widget('application.extensions.calendar.SCalendar',
        array(
        'inputField'=>'DateFrom',
        'button'=>'calbutton',
        'showsTime'=>true,
        'ifFormat'=>'%Y-%m-%d %H:%M:%S',
        )); 

    ?>
            
            </td>
            <td>
                
    <?php            
    /*
    $this->widget('zii.widgets.jui.CJuiDatePicker', array(
        'name'=>'DateTo',
        'value'=>$this->dateTo,
        'options'=>array(
            'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
            'showOn'=>'button', // 'focus', 'button', 'both'
            'buttonText'=>Yii::t('ui','DateTo'), 
            'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
            'buttonImageOnly'=>true,
            'dateFormat'=>'yy-mm-dd',
        ),
        'htmlOptions'=>array(
            'style'=>'width:80px;vertical-align:top'
        ),  
    ));
     * 
     */
    
    echo CHtml::label(" To :", "dateTo").
        CHtml::textField('DateTo', date('Y-m-d h:i:s'), array('id'=>'DateTo','readonly'=>'true', 'value'=>date('Y-m-d h:i:s'), 'style'=>'width: 150px;')).
        CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton2","class"=>"pointer","style"=>"cursor: pointer;"));
        $this->widget('application.extensions.calendar.SCalendar',
        array(
        'inputField'=>'DateTo',
        'button'=>'calbutton2',
        'showsTime'=>true,
        'ifFormat'=>'%Y-%m-%d %H:%M:%S',
        ));

    ?>
                </td>
        </tr>
    </table>
    <div style="width: 100%; text-align: center; margin-left: 250px;">
            <?php
        echo CHtml::ajaxButton("Search", "VMSLogs", array(
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
    </div>
<?php echo CHtml::endForm(); ?>