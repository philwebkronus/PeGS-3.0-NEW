<?php

Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseURL . '/js/jquery-1.7.2.min.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/validations.js');
?>
<script type="text/javascript">
    $(document).ready(function(){
       <?php
       //Check if has result, no result, hide grid
       if ($this->hasResult)
       {
       ?>
        $("#rawdatatbl").show();
       <?php } else {
           ?>$("#rawdatatbl").hide();<?php
       }?>
       $("#dpickerfrom").hide();
       $("#dpickerto").hide();
       var vtype = $("#vouchertype").val(); 
       if (vtype == 1){ //Ticket
           $("#dpickerfrom").show();
           $("#dpickerto").show();
       }
       else if(vtype == 0){ //Coupon
           $("#dpickerfrom").hide();
           $("#dpickerto").hide();
       }
        <?php
        if ($this->hasError):
            ?>
                $("#dpickerfrom").show();
                $("#dpickerto").show();
            <?php
        endif;
        ?>
        
        
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
        $("#vouchertype").change(function(){
           var vtype = $("#vouchertype").val();
           if (vtype == 1){ //Ticket
                $("#dpickerfrom").show();
                $("#dpickerto").show();
                $("#date_from").val("");
                $("#date_to").val("");
           }
           else{ //Coupon
               $("#dpickerfrom").hide();
               $("#dpickerto").hide();
           }
        });
    });
</script>
<h2><?php echo $title; ?></h2>
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
            <td><?php echo $form->labelEx($model, 'voucher type:') ; ?></td>
            <td>
                <?php
                echo $form->dropDownList($model, 'vouchertype', $vouchers, 
                                                                array('id' => 'vouchertype', 'style' => 'width: 135px;'));
                ?>
            </td>
        </tr> 
        <tr id="dpickerfrom">
            <td><?php echo $form->labelEx($model, 'date from:') ; ?></td>
            <td>
                <?php
                    $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'model' => $model,
                        'attribute' => 'datefrom',
                        'htmlOptions' => array(
                            'size' => '16',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true,
                            'id' => 'date_from'
                        ),
                        'options' => array(
                            'showOn'=>'button',
                            'buttonImageOnly' => true,
                            'changeMonth' => true,
                            'changeYear' => true,
                            'buttonText'=> 'Select Date From',
                            'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.gif',
                            'dateFormat'=>'yy-mm-dd',
                        )
                    ));
                 ?>
            </td>
        </tr>
        <tr id="dpickerto">
            <td><?php echo $form->labelEx($model, 'date to:') ; ?></td>
            <td>
                <?php
                    $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'model' => $model,
                        'attribute' => 'dateto',
                        'htmlOptions' => array(
                            'size' => '16',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true,
                            'id' => 'date_to'
                        ),
                        'options' => array(
                            'showOn'=>'button',
                            'buttonImageOnly' => true,
                            'changeMonth' => true,
                            'changeYear' => true,
                            'buttonText'=> 'Select Date To',
                            'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.gif',
                            'dateFormat'=>'yy-mm-dd',
                        )
                    ));
                 ?>
            </td>
        </tr>
    </table>
    <div style="width: 100%; text-align: center; margin-left: 250px;">
            <?php echo CHtml::submitButton("Submit", array('id' => 'submitbtn', 'name'=>'submitbtn')); ?>
    </div>    

<div id="rawdatatbl" style="width: 700px; margin: 0 auto">
    <?php $this->actionMonitoringVoucherDataTable($rawdata); ?>
</div>
<?php $this->endWidget(); ?>

<?php
/** Start Widget **/
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'mydialog',
    'options'=>array(
        'title'=>'MESSAGE',
        'modal'=>true,
        'autoOpen'=>$this->showDialog,
        'width'=>300,
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
echo "<p>";
echo $this->dialogMsg;
echo "<p/>";
    
$this->endWidget('zii.widgets.jui.CJuiDialog');
/** End Widget **/

?>