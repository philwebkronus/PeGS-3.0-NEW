<?php
$this->breadcrumbs=array(
	'VoucherUsage',
);?>
<?php
$voucherusagemodel = new VoucherUsageForm;
if(isset($_POST['VoucherUsageForm']))
{
    $model->attributes=$_POST['VoucherUsageForm'];
}
else
{
    $model->from = date('Y-m-d');
    $model->to = date('Y-m-d', strtotime('+1 Day', strtotime(date('Y-m-d'))));
}

?>
<h2>Voucher Usage Report</h2>

<div class="row" style="padding: 10px 5px; background: #EFEFEF;">
<?php $form=$this->beginWidget('CActiveForm', array(
    'enableClientValidation'=>true,
    'clientOptions'=>array(
    'validateOnSubmit'=>true,
    ),
)); ?>
    <?php echo $form->errorSummary($model); ?>
    <table style="width:500px">
        <tr>
            <td>
                <?php echo $form->labelEx($model,'from: ');
                           $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                           'id'=>'datefrom',
                           'model'=>$model,
                           'attribute'=>'from',
                           //'value'=>$model->from,
                           'value'=>date('Y-m-d'),
                           // additional javascript options for the date picker plugin
                           'options'=>array(
                                'showAnim'=>'fold',
                                'showOn'=>'button',
                                'buttonText'=>Yii::t('ui','from'), 
                                'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
                                'buttonImageOnly'=>true,
                                'autoSize'=>true,
                                'dateFormat'=>'yy-mm-dd',
                                //'defaultDate'=>$model->from,
                           ),
                           'htmlOptions'=>array('readonly'=>true),
                           ));
                ?>
            </td>
            <td>
                <?php echo $form->labelEx($model,'to: ');
                           $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                           'id'=>'dateto',
                           'model'=>$model,
                           'attribute'=>'to',
                           //'value'=>$model->to,
                           'value'=>date('Y-m-d'),
                           // additional javascript options for the date picker plugin
                           'options'=>array(
                                'showAnim'=>'fold',
                                'showOn'=>'button',
                                'buttonText'=>Yii::t('ui','to'), 
                                'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.png', 
                                'buttonImageOnly'=>true,
                                'autoSize'=>true,
                                'dateFormat'=>'yy-mm-dd',
                                //'defaultDate'=>$model->to,
                           ),
                           'htmlOptions'=>array('readonly'=>true),
                           ));
                ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo $form->labelEx($model,'voucher type: ').$form->dropDownList($model, 'vouchertype',$voucherusagemodel->getVoucherType(), array('id'=>'vouchertype')); ?>
            </td>
            <td>
                <?php echo $form->labelEx($model, 'site:').$form->dropDownList($model, 'site', $voucherusagemodel->getSite(), array('id'=>'site')); ?>
            </td>
            <td>
                <?php echo $form->labelEx($model,'status: ').$form->dropDownList($model, 'status',$voucherusagemodel->getVoucherStatus(), array('id'=>'status')); ?>
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
    <?php $this->actionVoucherUsageDataTable(Yii::app()->session['rawData']); ?>
</div>
<?php $this->endWidget(); ?>
