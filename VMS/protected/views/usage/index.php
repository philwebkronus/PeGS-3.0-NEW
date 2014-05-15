<?php
$this->breadcrumbs = array(
    'VoucherUsage',
);
?>
<?php
$voucherusagemodel = new VoucherUsageForm;
if (isset($_POST['VoucherUsageForm'])) {
    $model->attributes = $_POST['VoucherUsageForm'];
} else {
    $model->from = date('Y-m-d');
    $model->to = date('Y-m-d', strtotime('+1 Day', strtotime(date('Y-m-d'))));
}
?>
<h2>Voucher Usage Report</h2>
<hr color="black" />
<div class="row" style="padding: 10px 5px; background: #EFEFEF;">
<?php
$form = $this->beginWidget('CActiveForm', array(
    'enableClientValidation' => true,
    'clientOptions' => array(
        'validateOnSubmit' => true,
    ),
        ));
?>
    <?php echo $form->errorSummary($model); ?>
    <table style="width:500px">
        <tr>
            <td><?php echo CHtml::label("eGames : ", "Site"); ?></td>
            <td>
                <?php echo $form->dropDownList($model, 'site', $voucherusagemodel->getSite(), array('id' => 'site')); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo $form->labelEx($model, 'voucher type : '); ?></td>
            <td>
                <?php echo $form->dropDownList($model, 'vouchertype', $voucherusagemodel->getVoucherType(), array('id' => 'status')); ?>
            </td>
            <td><?php echo $form->labelEx($model, 'status : '); ?></td>
            <td>
                <?php echo $form->dropDownList($model, 'status', $voucherusagemodel->getVoucherStatus(), array('id' => 'status')); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo $form->labelEx($model, 'from : '); ?></td>
            <td>
                <?php
                echo $form->textField($model, 'from', array('id' => 'txtfrom', 'readonly' => 'true', 'style' => 'width: 120px;')) .
                CHtml::image(Yii::app()->request->baseUrl . "/images/calendar.png", "calendar", array("id" => "calbutton", "class" => "pointer", "style" => "cursor: pointer;"));
                $this->widget('application.extensions.calendar.SCalendar', array(
                    'inputField' => 'txtfrom',
                    'button' => 'calbutton',
                    //'showsTime'=>true,
                    'ifFormat' => '%Y-%m-%d',
                ));
                ?>
            </td>
            <td><?php echo $form->labelEx($model, 'to : '); ?></td>
            <td>
                <?php
                echo $form->textField($model, 'to', array('id' => 'txtto', 'readonly' => 'true', 'style' => 'width: 120px;')) .
                CHtml::image(Yii::app()->request->baseUrl . "/images/calendar.png", "calendar", array("id" => "calbutton2", "class" => "pointer", "style" => "cursor: pointer;"));
                $this->widget('application.extensions.calendar.SCalendar', array(
                    'inputField' => 'txtto',
                    'button' => 'calbutton2',
                    //'showsTime'=>true,
                    'ifFormat' => '%Y-%m-%d',
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
    <?php $this->actionVoucherUsageDataTable(Yii::app()->session['rawData']); ?>
</div>
<?php $this->endWidget(); ?>
