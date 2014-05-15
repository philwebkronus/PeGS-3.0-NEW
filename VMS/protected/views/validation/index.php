<?php
$this->breadcrumbs = array(
    'Validation',
);
?>
<?php
$validationmodel = new ValidationForm;
if (isset($_POST['ValidationForm'])) {
    $model->attributes = $_POST['ValidationForm'];
} else {
    if (isset($_GET['page'])) {
        $model->from = Yii::app()->session['from'];
        $model->to = Yii::app()->session['from'];
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
    $(document).ready(function(){
        $('#site').live('change', function(){
            var ddlsite = document.getElementById('site')
            $.ajax({
                //url: 'http://localhost/VMS/index.php/validation/ajaxGetTerminal?site='+ddlsite.value,
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
    });
</script>
<h4>Voucher Validation</h4>
<hr color="black" />
<div id="search-form" class="row filterbg">

<?php
$form = $this->beginWidget('CActiveForm', array(
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
        ));
?>
    <table style="width: 600px">
        <tr>
            <td><?php echo CHtml::label("eGames : ", "Site");?></td>
            <td>
<?php echo $form->dropDownList($model, 'site', $validationmodel->getSite(), array('id' => 'site',)); ?>  
                </td>
                <td><?php echo $form->labelEx($model, 'terminal:');?></td>
                <td>
                <?php echo $form->dropDownList($model, 'terminal', array('All' => 'All'), array('id' => 'terminal', 'style' => 'width: 100px;'));
                //echo 'Terminal: '.CHtml::dropDownList('terminal', '',array("0" => "All"));
                ?>
            </td> 
        </tr>    
        <tr>
            <td><?php echo $form->labelEx($model, 'from: ');?></td>
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
                <td><?php echo $form->labelEx($model, 'to: ');?></td>
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
            <td><?php echo $form->labelEx($model, 'voucher code:');?></td>
            <td>
<?php echo $form->textField($model, 'vouchercode', array('id' => 'txtvouchercode', 'onkeypress' => 'return numberonly(event);', 'maxlength' => 18,)); ?>
        
                
            </td>
        </tr>    
    </table>    
    <div style="width: 100%; text-align: center; margin-left: 250px;">
            <?php echo CHtml::submitButton("Submit"); ?>
    </div>
<?php echo $form->errorSummary($model); ?>


</div>
<div>
<?php $this->actionValidationDataTable(Yii::app()->session['rawData']); ?>
</div>
    <?php $this->endWidget(); ?>
