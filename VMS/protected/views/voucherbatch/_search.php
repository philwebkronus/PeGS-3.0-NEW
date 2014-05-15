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
<table style="width: 600px">
    <tr>
        <td>
<?php
echo CHtml::label("From ", "DateFrom").
		CHtml::textField('DateFrom', date('Y-m-d'), array('id'=>'DateFrom','readonly'=>'true', 'value'=>date('Y-m-d'), 'style'=>'width: 80px;')).
		CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton","class"=>"pointer","style"=>"cursor: pointer;"));
		$this->widget('application.extensions.calendar.SCalendar',
		array(
		'inputField'=>'DateFrom',
		'button'=>'calbutton',
		'showsTime'=>false,
		'ifFormat'=>'%Y-%m-%d',
		));
       ?>
</td>
<td>
    <?php
echo CHtml::label("To ", "DateTo").
        CHtml::textField('DateTo', date('Y-m-d'), array('id'=>'DateTo','readonly'=>'true', 'value'=>date('Y-m-d'), 'style'=>'width: 80px;')).
        CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton2","class"=>"pointer","style"=>"cursor: pointer;"));
        $this->widget('application.extensions.calendar.SCalendar',
        array(
        'inputField'=>'DateTo',
        'button'=>'calbutton2',
        'showsTime'=>false,
        'ifFormat'=>'%Y-%m-%d',
        ));

?>
</td> 

<td>
<?php echo CHtml::label("Status ", "Status"); ?>
<?php echo CHtml::dropDownList('Status',$this->status, Utilities::getBatchStatus()); ?>
    </td>  
        </tr>
    </table>   
<div style="width: 100%; text-align: center; margin-left: 250px;">
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
    
</div>
<?php echo CHtml::endForm(); ?>

