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
<hr color="black" />
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
        <td><?php echo CHtml::label("eGames : ", "Site");?></td>    
        <td>
                <?php echo $form->dropDownList($model, 'site', $siteconversionmodel->getSite(), array('id'=>'site')); ?>
        </td>
        </tr>
        <tr>
            <td>
            </td>    
        <tr/>
        <tr>
            <td><?php echo $form->labelEx($model,'from : ');?></td>    
            <td>
                
                <?php echo $form->textField($model,'from', array('id'=>'txtfrom','readonly'=>'true', /*'value'=>date('Y-m-d'),*/ 'style'=>'width: 120px;')).
                      CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton","class"=>"pointer","style"=>"cursor: pointer;"));
                      $this->widget('application.extensions.calendar.SCalendar',
                      array(
                      'inputField'=>'txtfrom',
                      'button'=>'calbutton',
                      'showsTime'=>false,
                      'ifFormat'=>'%Y-%m-%d',
                      ));                
                ?>
            </td>
            <td><?php echo $form->labelEx($model,'to : ')?></td>    
            <td>
              <?php echo $form->textField($model,'to', array('id'=>'txtto','readonly'=>'true', /*'value'=>date('Y-m-d', strtotime('+1 Day', strtotime(date('Y-m-d')))),*/ 'style'=>'width: 120px;')).
                    CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton2","class"=>"pointer","style"=>"cursor: pointer;"));
                    $this->widget('application.extensions.calendar.SCalendar',
                    array(
                    'inputField'=>'txtto',
                    'button'=>'calbutton2',
                    'showsTime'=>false,
                    'ifFormat'=>'%Y-%m-%d',
                    ));                
                ?>
            </td>
        </tr>   
    </table>
    <div style="width: 100%; text-align: center; margin-left: 250px;">
            <?php echo CHtml::submitButton("Submit"); ?>
    </div> 
</div>
<div>
    <?php $this->actionSiteConversionDataTable(Yii::app()->session['rawData']); ?>
</div>
<?php $this->endWidget(); ?>
<?php //$this->renderPartial('siteconversion', array('arrayDataProvider'=>$arrayDataProvider)) ?>