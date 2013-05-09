<?php
$this->breadcrumbs=array(
	'Vouchermonitoring',
);?>
<?php
//$this->pageTitle=Yii::app()->name . ' - Voucher Report';
$vouchermonitoringmodel = new VoucherMonitoringForm;
if(isset($_POST['VoucherMonitoringForm']))
{
    $model->attributes=$_POST['VoucherMonitoringForm'];
}
else
{
    if(isset($_GET['page']))
    {
        $model->from=Yii::app()->session['from'];
        $model->to=Yii::app()->session['to'];
        $model->status=Yii::app()->session['status'];
        $model->vouchertype=Yii::app()->session['vouchertype'];
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
    /*function v3_jumpMenu(targ,selObj,restore)
    { //v3.0
        eval(targ+".location='?site="+selObj.options[selObj.selectedIndex].value+"'");
        if (restore) selObj.selectedIndex=0;
    }*/
    
    $(document).ready(function(){
        var acctype = <?php echo Yii::app()->session['AccountType']; ?>;
        //alert(acctype);
        if (acctype == 2||acctype == 3||acctype == 4)
            {
                //var sitecode = 'ICSA-TSTID';
                var sitecode = '<?php echo Yii::app()->session['SiteCode']; ?>';
                $('#site').val(sitecode);
                $('#tsite').hide();
                $.ajax({
                url: 'http://192.168.30.97/VMS/index.php/monitoring/ajaxGetTerminal?site='+sitecode,
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
                });
            }
            else
            {
                $('#site').live('change', function(){
                var ddlsite = document.getElementById('site')
                $.ajax({
                    url: 'http://192.168.30.97/VMS/index.php/monitoring/ajaxGetTerminal?site='+ddlsite.value,
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
            }
    });
</script>
<h2>Voucher Monitoring Report</h2>

<div class="row" style="padding: 10px 5px; background: #EFEFEF;">
    
<?php $form=$this->beginWidget('CActiveForm', array(
        'enableClientValidation'=>true,
        'clientOptions'=>array(
        'validateOnSubmit'=>true,
        ),
)); ?>
    <?php echo $form->errorSummary($model); ?>
    <table style="width: 750px">
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
                <?php 
                    echo $form->labelEx($model,'to: ');
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
                <?php echo $form->labelEx($model,'status: ').$form->dropDownList($model, 'status',$vouchermonitoringmodel->getVoucherStatus(), array('id'=>'status')); ?>
            </td>
            <td>
                <?php echo $form->labelEx($model,'voucher type: ').$form->dropDownList($model, 'vouchertype',$vouchermonitoringmodel->getVoucherType(), array('id'=>'vouchertype')); ?>
            </td>
            <td id="tsite">
                <?php echo $form->labelEx($model, 'site:').$form->dropDownList($model, 'site', $vouchermonitoringmodel->getSite(), array('id'=>'site',
                    /*'ajax'=>
                    array(
                        'type'=> 'POST',
                        'url'=> CController::createUrl('getterminal'),
                        'update'=>'#terminal',
                    )*/
                    )); 
                ?>  
            </td>
            <td>
                <?php echo $form->labelEx($model,'terminal:').$form->dropDownList($model, 'terminal', array('All'=>'All'), array('id'=>'terminal', 'style'=>'width: 135px;'));
                      //echo 'Terminal: '.CHtml::dropDownList('terminal', '',array("0" => "All"));
                ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
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
    <?php 
        if ($issubmitted == 1)
        {
            $this->actionDataTable(Yii::app()->session['rawData']); 
        }

     ?>
</div>
<?php $this->endWidget(); ?>
