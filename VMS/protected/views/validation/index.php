<?php
$this->breadcrumbs=array(
	'Validation',
);?>
<?php
$validationmodel  = new ValidationForm;
if(isset($_POST['ValidationForm']))
{
    $model->attributes = $_POST['ValidationForm'];
}
else
{
    if(isset($_GET['page']))
    {
        $model->from=Yii::app()->session['vfrom'];
        $model->to=Yii::app()->session['vto'];
        $model->site=Yii::app()->session['site'];
        $model->terminal=Yii::app()->session['terminal'];
        $model->vouchercode=Yii::app()->session['vouchercode'];
    }
    else
    {
        $model->from = date('Y-m-d H:i');
        $model->to = date('Y-m-d H:i', strtotime('+1 Day', strtotime(date('Y-m-d H:i'))));
    }
}
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseURL.'/js/jquery-1.7.2.min.js');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $('#site').live('change', function(){
        var ddlsite = document.getElementById('site')
        $.ajax({
            //url: 'http://192.168.30.97/VMS/index.php/validation/ajaxGetTerminal?site='+ddlsite.value,
            url: 'http://<?php echo $_SERVER["HTTP_HOST"]; ?>/index.php/validation/ajaxGetTerminal?site='+ddlsite.value,
            type: 'post',
            dataType: 'json',
            success: function(data)
            {
                $('#terminal').empty();
                var opt = '';
                jQuery.each(data, function(k,v){
                    opt+='<option value="'+ k +'">'+ v + '</option>';
                });
                $('#terminal').html(opt);
            }
        })
        });
    });
</script>
<h2>Voucher Validation</h2>
<div class="row" style="padding: 10px 5px; background: #EFEFEF;">
    
<?php $form=$this->beginWidget('CActiveForm', array(
        'enableClientValidation'=>true,
        'clientOptions'=>array(
        'validateOnSubmit'=>true,
        ),
)); ?>
    <?php echo $form->errorSummary($model); ?>
    <table style="width: 600px">
        <tr>
            <td>
                <?php echo $form->labelEx($model,'from: ');
                           Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                           $this->widget('CJuiDateTimePicker',array(
                           'id'=>'DateFrom',
                           'model'=>$model,
                           'attribute'=>'from',
                           //'value'=>$model->from,
                           'value'=>date('Y-m-d H:i'),
                           'mode'=>'datetime', //use "time","date" or "datetime" (default)
                            'options'=>array(
                                 'dateFormat'=>'yy-mm-dd',
                                 'timeFormat'=> 'hh:mm',
                                 'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
                                 'showOn'=>'button', // 'focus', 'button', 'both'
                                 'buttonText'=>Yii::t('ui','from'), 
                                 'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
                                 'buttonImageOnly'=>true,
                                 //'autoSize'=>true,
                                 //'defaultDate'=>$model->from,
                           ),// jquery plugin options
                           'htmlOptions'=>array('readonly'=>true, 'style'=>'width: 110px;'),
                           'language'=>'',
                           ));
                ?>
            </td>
            <td>
                <?php echo $form->labelEx($model,'to: ');
                           Yii::import('application.extensions.CJuiDateTimePicker.CJuiDateTimePicker');
                           $this->widget('CJuiDateTimePicker',array(
                           'id'=>'DateTo',
                           'model'=>$model,
                           'attribute'=>'to',
                           //'value'=>$model->from,
                           'value'=>date('Y-m-d H:i'),
                           'mode'=>'datetime', //use "time","date" or "datetime" (default)
                           'options'=>array(
                                'dateFormat'=>'yy-mm-dd',
                                'timeFormat'=> 'hh:mm',
                                'showAnim'=>'fold', // 'show' (the default), 'slideDown', 'fadeIn', 'fold'
                                'showOn'=>'button', // 'focus', 'button', 'both'
                                'buttonText'=>Yii::t('ui','to'), 
                                'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
                                'buttonImageOnly'=>true,
                                //'autoSize'=>true,
                                //'defaultDate'=>$model->from,
                           ),// jquery plugin options
                           'htmlOptions'=>array('readonly'=>true, 'style'=>'width: 110px;'),
                           'language'=>'',
                           ));
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo $form->labelEx($model, 'site:').$form->dropDownList($model, 'site', $validationmodel->getSite(), array('id'=>'site',)); 
                ?>  
            </td>
            <td>
                <?php echo $form->labelEx($model,'terminal:').$form->dropDownList($model, 'terminal', array('All'=>'All'), array('id'=>'terminal', 'style'=>'width: 135px;'));
                      //echo 'Terminal: '.CHtml::dropDownList('terminal', '',array("0" => "All"));
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo $form->labelEx($model, 'voucher code:').$form->textField($model,'vouchercode', array('id'=> 'txtvouchercode')); ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo CHtml::submitButton("Submit"); ?>
            </td>
        </tr>
    </table>
</div>
<div>
    <?php $this->actionValidationDataTable(Yii::app()->session['rawData']);  ?>
</div>
<?php $this->endWidget(); ?>
