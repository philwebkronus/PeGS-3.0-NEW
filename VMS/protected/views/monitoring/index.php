<?php
$this->breadcrumbs = array(
    'Vouchermonitoring',
);
?>
<?php
//$this->pageTitle=Yii::app()->name . ' - Voucher Report';
$vouchermonitoringmodel = new VoucherMonitoringForm;
if (isset($_POST['VoucherMonitoringForm'])) {
    $model->attributes = $_POST['VoucherMonitoringForm'];
} else {
    if (isset($_GET['page'])) {
        $model->from = Yii::app()->session['from'];
        $model->to = Yii::app()->session['to'];
        $model->status = Yii::app()->session['status'];
        $model->site = Yii::app()->session['site'];
        $model->terminal = Yii::app()->session['terminal'];
        $model->vouchercode = Yii::app()->session['vouchercode'];
    } else {
        $model->from = date('Y-m-d H:i');
        $model->to = date('Y-m-d H:i', strtotime('+1 Day', strtotime(date('Y-m-d H:i'))));
    }
}


Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseURL . '/js/jquery-1.7.2.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/validations.js');


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
        if (acctype == 4)
        {
            //var sitecode = 'ICSA-TSTID';
            var sitecode = '<?php echo Yii::app()->session['SiteCode']; ?>';
            $('#site').val(sitecode);
            $('#tsite').hide();
            $.ajax({
                //url: 'http://localhost/VMS/index.php/vouchermonitoring/ajaxGetTerminal?site='+sitecode,
                url : 'ajaxGetTerminal?site='+sitecode,
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
                    //url: 'http://localhost/VMS/index.php/vouchermonitoring/ajaxGetTerminal?site='+ddlsite.value,
                    url: 'ajaxGetTerminal?site='+ddlsite.value,
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
<hr color="black" />
<div class="row" style="padding: 10px 5px; background: #EFEFEF;">

    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'htmlOptions'=>array(
        'class'=>'frmmembership',
        ),

            ));
    ?>
    <?php echo $form->errorSummary($model); ?>
    <table style="width: 600px">
        <tr>
            <td><?php echo CHtml::label("eGames : ", "Site"); ?></td>
            <td id="tsite">
                <?php
                 echo $form->dropDownList($model, 'site', $vouchermonitoringmodel->getSite(), array('id' => 'site',
                        /* 'ajax'=>
                          array(
                          'type'=> 'POST',
                          'url'=> CController::createUrl('getterminal'),
                          'update'=>'#terminal',
                          ) */
                ));
                ?>  
            </td>
            <td><?php echo $form->labelEx($model, 'terminal:') ; ?></td>
            <td>
                <?php
                echo $form->dropDownList($model, 'terminal', array('All' => 'All'), array('id' => 'terminal', 'style' => 'width: 135px;'));
//echo 'Terminal: '.CHtml::dropDownList('terminal', '',array("0" => "All"));
                ?>
            </td>
        </tr>
        <tr>
            <td><?php echo $form->labelEx($model, 'status: '); ?></td>
            <td>
                <?php echo  $form->dropDownList($model, 'status', $vouchermonitoringmodel->getVoucherStatus(), array('id' => 'status')); ?>
            </td>
        </tr>    
        <tr>
            <td><?php echo $form->labelEx($model, 'from: '); ?></td>
            <td>
                <?php
                echo $form->textField($model, 'from', array('id' => 'txtfrom', 'readonly' => 'true', 'style' => 'width: 150px;')) .
                CHtml::image(Yii::app()->request->baseUrl . "/images/calendar.png", "calendar", array("id" => "calbutton", "class" => "pointer", "style" => "cursor: pointer;"));
                $this->widget('application.extensions.calendar.SCalendar', array(
                    'inputField' => 'txtfrom',
                    'button' => 'calbutton',
                    'showsTime' => true,
                    'ifFormat' => '%Y-%m-%d %H:%M',
                ));
                ?>
            </td>
            <td><?php echo $form->labelEx($model, 'to: '); ?></td>
            <td>
                <?php
                echo $form->textField($model, 'to', array('id' => 'txtto', 'readonly' => 'true', 'style' => 'width: 150px;')) .
                CHtml::image(Yii::app()->request->baseUrl . "/images/calendar.png", "calendar", array("id" => "calbutton2", "class" => "pointer", "style" => "cursor: pointer;"));
                $this->widget('application.extensions.calendar.SCalendar', array(
                    'inputField' => 'txtto',
                    'button' => 'calbutton2',
                    'showsTime' => true,
                    'ifFormat' => '%Y-%m-%d %H:%M',
                ));
                ?>
            </td>
        </tr>

        <tr>
            <td><?php echo $form->labelEx($model, 'voucher code:'); ?></td>
            <td colspan="2">
                <?php echo $form->textField($model, 'vouchercode', array('onkeypress' => 'return numberonly(event);', 'maxlength' => 18)); ?>
            </td>
            
        </tr>  
    </table>
    <div style="width: 100%; text-align: center; margin-left: 250px;">
            <?php echo CHtml::submitButton("Submit"); ?>
    </div>    
</div>
<div>
    <?php
    if ($issubmitted == 1) {
        $this->actionDataTable(Yii::app()->session['rawData']);
    }
    ?>
</div>
<?php $this->endWidget(); ?>