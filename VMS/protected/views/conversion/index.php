<?php
$this->breadcrumbs=array(
	'Site Conversion',
);?>
<?php
$siteconversionmodel = new SiteConversionForm;
if(isset($_POST['SiteConversionForm']))
{
    $model->attributes=$_POST['SiteConversionForm'];
}
else
{
    if(isset($_GET['page']))
    {
        $model->from=substr(Yii::app()->session['scfrom'], 0, 10);
        $model->to=substr(Yii::app()->session['scto'], 0, 10);
        $model->site=Yii::app()->session['site'];
    }
    else
    {
        $model->from = date('Y-m-d');
        $model->to = date('Y-m-d', strtotime('+1 Day', strtotime(date('Y-m-d')))); 
    }
}
?>
<h2>Site Conversion Report</h2>
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
            <td>
                <?php echo $form->labelEx($model, 'site:').$form->dropDownList($model, 'site', $siteconversionmodel->getSite(), array('id'=>'site')); ?>
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
    <?php $this->actionSiteConversionDataTable(Yii::app()->session['rawData']); ?>
</div>
<?php $this->endWidget(); ?>
<?php //$this->renderPartial('siteconversion', array('arrayDataProvider'=>$arrayDataProvider)) ?>