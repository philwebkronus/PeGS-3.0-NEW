<?php
$this->breadcrumbs = array(
    'Monitoring of Voucher',
);
?>
<?php

Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseURL . '/js/jquery-1.7.2.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/validations.js');
$monitoringvoucherform = new MonitoringVoucherForm();
if(isset($_POST['MonitoringVoucherForm']))
{
    $model->attributes=$_POST['MonitoringVoucherForm'];
}

?>
<script type="text/javascript">
    $(document).ready(function(){
        $('#submitbtn').live('click', function() {
            var vouchertype = $("#vouchertype").val();
            var amount = $("#amount").val();
            var voucherquantity = $("#voucherquantity").val();
            var divbyhundered = amount % 100 === 0; 
            
            if(vouchertype < 1){
                alert('Please Select a Voucher Type');
                return false;
            }
            else{
                return true;
            }
        });
        
    });
</script>
<h2>Monitoring of Vouchers</h2>
<br/>
<hr style="color:#000;background-color:#000;">
<br />

    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id'=>'MonitoringVoucherForm',
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
       

            ));
    ?>
    <?php echo $form->errorSummary($model); ?>
    <table style="width: 300px">
        <tr>
            <td><?php echo $form->labelEx($model, 'vouchertype:') ; ?></td>
            <td>
                <?php
                echo $form->dropDownList($model, 'vouchertype', array('-1' => 'Please Select','1' => 'Ticket','2' => 'Coupon'), array('id' => 'vouchertype', 'style' => 'width: 135px;'));
                ?>
            </td>
        </tr> 
    </table>
    <div style="width: 100%; text-align: center; margin-left: 250px;">
            <?php echo CHtml::submitButton("Submit", array('id' => 'submitbtn', 'name'=>'submitbtn')); ?>
    </div>    

<div>
    <?php $this->actionMonitoringVoucherDataTable(Yii::app()->session['rawData']); ?>
</div>
<?php $this->endWidget(); ?>

<?php
/** Start Widget **/
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'mydialog',
    'options'=>array(
        'title'=>'Generation of Voucher',
        'modal'=>true,
        'autoOpen'=>$this->showDialog,
        'width'=>350,
        'height'=>200,
        'closeOnEscape' => false,
        'resizable'=>false,
        'draggable'=>false,
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'OK'=>'js:function(){
                $(this).dialog("close");
            }',
        ),
    ),
));
echo "<center>";
echo $this->dialogMsg;
echo "<br/>";
echo "</center>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');
/** End Widget **/

?>