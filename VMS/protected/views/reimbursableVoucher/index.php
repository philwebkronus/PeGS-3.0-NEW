<?php
$this->breadcrumbs=array(
	'Reimbursable Voucher',
);?>
<?php
$reimbursablevouchermodel = new ReimbursableVoucherForm;
if(isset($_POST['ReimbursableVoucherForm']))
{
    $model->attributes=$_POST['ReimbursableVoucherForm'];
}
else
{
    if(isset($_GET['page']))
    {
        $model->from=substr(Yii::app()->session['rvfrom'], 0, 10);
        $model->to=substr(Yii::app()->session['rvto'], 0, 10);
        $model->site=Yii::app()->session['site'];
        $model->terminal=Yii::app()->session['terminal'];
    }
    else
    {
        $model->from = date('Y-m-d');
        $model->to = date('Y-m-d', strtotime('+1 Day', strtotime(date('Y-m-d'))));
    }
}
Yii::app()->getClientScript()->registerCoreScript('jquery');
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseURL.'/js/jquery-1.7.2.min.js');
?>
<script type="text/javascript">
    $(document).ready(function(){
        $('#site').live('change', function(){
        var ddlsite = document.getElementById('site')
        $.ajax({
            url: 'http://localhost/VMS/index.php/REimbursablevoucher/ajaxGetTerminal?site='+ddlsite.value,
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
<h2>Reimbursable Vouchers Report</h2>
<div class="row" style="padding: 10px 5px; background: #EFEFEF;">
<?php $form=$this->beginWidget('CActiveForm', array(
    'id'=>'reimburseform',
    'enableClientValidation'=>true,
    'clientOptions'=>array(
    'validateOnSubmit'=>true,
    ),
)); ?>
    <?php echo $form->errorSummary($model); ?>
    <table style="width:650px">
        <tr>
            <td>
                <?php echo $form->labelEx($model,'from: ').$form->textField($model,'from', array('id'=>'txtfrom','readonly'=>'true', 'style'=>'width: 80px;')).
                      CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton","class"=>"pointer","style"=>"cursor: pointer;"));
                      $this->widget('application.extensions.calendar.SCalendar',
                      array(
                      'inputField'=>'txtfrom',
                      'button'=>'calbutton',
                      //'showsTime'=>true,
                      'ifFormat'=>'%Y-%m-%d',
                      ));                
                ?>
            </td>
            <td>
                <?php echo $form->labelEx($model,'to: ').$form->textField($model,'to', array('id'=>'txtto','readonly'=>'true', 'style'=>'width: 80px;')).
                      CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton2","class"=>"pointer","style"=>"cursor: pointer;"));
                      $this->widget('application.extensions.calendar.SCalendar',
                      array(
                      'inputField'=>'txtto',
                      'button'=>'calbutton2',
                      //'showsTime'=>true,
                      'ifFormat'=>'%Y-%m-%d',
                      ));                
                ?>
            </td>
            <td>
                 <?php echo $form->labelEx($model, 'site:').$form->dropDownList($model, 'site', $reimbursablevouchermodel->getSite(), array('id'=>'site',)); ?>
            </td>
            <td>
                 <?php echo $form->labelEx($model,'terminal:').$form->dropDownList($model, 'terminal', array('All'=>'All'), array('id'=>'terminal', 'style'=>'width: 135px;')); ?>
            </td>
        </tr>
        <tr>
            <td>
                <?php echo CHtml::submitButton("Submit", array("id"=>"submitbutton")); ?>
            </td>
            <td>
                <?php echo CHtml::Button("Reimburse", array("id"=>"reimburse", 'onclick'=>'$("#confirm").dialog("open");', "disabled"=>Yii::app()->session['disable'])); ?>
            </td>
        </tr>
    </table>
</div>
<div>
    <?php 
            $this->actionReimbursableVoucherDataTable(Yii::app()->session['rawData']);       
    ?>
</div>
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'confirm',
    'options'=>array(
        'autoOpen'=>false,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'buttons' => array
        (
            'Yes'=>'js:function(){$("#reimburseform").submit();
                                 success: $.fn.yiiGridView.update("reimbursegrid")
                                 $(this).dialog("close");}',
            'No'=>'js:function(){$(this).dialog("close");}',
        ),
    ),
));
echo "Are you sure you want to reimbuse the following?";
echo "<br/>";
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<?php $this->endWidget(); ?>
<?php $this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'message',
    'options'=>array(
        'autoOpen'=>$this->showDialog,
        'modal'=>true,
        'resizable'=>false,
        'draggable'=>false,
        'show'=>'fade',
        'hide'=>'fade',
        'buttons' => array
        (
            'OK'=>'js:function(){$(this).dialog("close");}',
        ),
    ),
));
echo $this->dialogMsg;
echo "<br/>";
$this->endWidget('zii.widgets.jui.CJuiDialog');