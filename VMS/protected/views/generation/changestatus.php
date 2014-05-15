<?php
/**
 * Change Coupon/Ticket Status
 * @author Mark Kenneth Esguerra
 * @date November 4, 2013
 * @copyright (c) 2013, Philweb Corporation
 */

?>
<script type="text/javascript">
    $(document).ready(function(){   
       $("#ticketcode").val("");
       $("#batch").hide();
       $("#statusfield").hide();
       $("#validfrom").hide();
       $("#validto").hide();
       $("#submit").hide();
       $("#ticketcode").hide();
       
       var ticketcode = $("#ChangeStatusModel_ticketcode").val();
       if (ticketcode != ""){
           loadTicketStatus(ticketcode);
       }
    <?php
    if (isset($this->refresh)):
        if ($this->vtype == 1){
            ?>
               $("#batch").show();
               $("#statusfield").show();
               $("#validfrom").show();
               $("#validto").show();
               $("#submit").show();
               $("#ticketcode").hide();
            <?php
        }
        else if ($this->vtype == 2)
        {
    ?>
           $("#submit").show();
           $("#ticketcode").show();  
           $("#statusfield").show();
           $("#ticketcode").show();
    <?php
        }
    endif;
    ?>
    });
</script>
<script type="text/javascript">
    $(document).ready(function(){
        var vtype = $("#vouchertype").val();
        $("#ChangeStatusModel_validfrom").val("");
        $("#ChangeStatusModel_validto").val("");
        $("#submit").show();
        if (vtype == 1){ //if coupon
            $("#batch").show();
            $("#statusfield").show();
            $("#validfrom").show();
            $("#validto").show();
            $("#submit").show();
            $("#ticketcode").hide();    
            
            $.ajax({
               url: 'loadBatch',
               type: 'post', 
               data: {
                   vouchertype: function(){
                       return vtype;
                   }
               }, 
               success: function(data){
                    $("#ChangeStatusModel_batch").append(data);
               }, 
               error: function(xhr, status, error) {
                    alert(error);
               }
            });
        }
        else if (vtype == 2){ //if ticket
            $("#ticketcode").show();
            $("#statusfield").show();
            $("#submit").show();
            $("#validfrom").hide();
            $("#validto").hide();
            $("#batch").hide();
        }
        else if (vtype == ""){
            $("#batch").hide();
            $("#statusfield").hide();
            $("#validfrom").hide();
            $("#validto").hide();
            $("#submit").hide();
        }        
        $("#status").html("");
        $("#ChangeStatusModel_status").html("");
        var vtype = $("#vouchertype").val();

        $("#ChangeStatusModel_status").append(new Option("Please Select", -1));
        if (vtype == 1){ //Coupon
            $("#ChangeStatusModel_status").append(new Option("Active", 1));
            $("#ChangeStatusModel_status").append(new Option("Deactivated", 2));
            $("#ChangeStatusModel_status").append(new Option("Cancelled", 4));
        }
       $("#ChangeStatusModel_status").change(function(){
          var status = $("#ChangeStatusModel_status").val();
          var currentstat = $("#status").html();
              if (status == 1){
                  $("#ChangeStatusModel_validfrom").removeAttr("disabled");
                  $("#ChangeStatusModel_validto").removeAttr("disabled");
              }
              else if (status == 2 || status == 4){
                  $("#ChangeStatusModel_validfrom").attr("disabled","disabled");
                  $("#ChangeStatusModel_validto").attr("disabled","disabled");
              }
       });
       $("#ChangeStatusModel_batch").live('change', function(){
            $("#ChangeStatusModel_status").empty();
            $("#ChangeStatusModel_status").append(new Option("Please Select", -1));
            $("#ChangeStatusModel_status").append(new Option("Active", 1));
            $("#ChangeStatusModel_status").append(new Option("Deactivated", 2));
            $("#ChangeStatusModel_status").append(new Option("Cancelled", 4));
            $.ajax({
               url : 'getVoucherInfo',
               type : 'post',
               dataType : 'json',
               data : {batch : $("#ChangeStatusModel_batch").val()},
               success : function(data){
                 $.each(data, function(index, info){
                     if (data.Status == "Deactivated" || data.Status == "Cancelled"){
                         $("#ChangeStatusModel_validfrom").attr("disabled","disabled");
                         $("#ChangeStatusModel_validto").attr("disabled","disabled");

                     }
                     else {
                         $("#ChangeStatusModel_validfrom").removeAttr("disabled");
                         $("#ChangeStatusModel_validto").removeAttr("disabled");
                     }
                     $("#status").html(data.Status);
                     $("#ChangeStatusModel_validfrom").val(data.ValidFromDate);
                     $("#ChangeStatusModel_validto").val(data.ValidToDate);
                 });
               },
               error : function(XMLHttpRequest, e){
                 alert(XMLHttpRequest.responseText);
                 if(XMLHttpRequest.status == 401)
                 {
                     window.location.reload();
                 }
               }
            });
        });
        $("#ChangeStatusModel_ticketcode").live("blur", function(){
            var ticketcode = $("#ChangeStatusModel_ticketcode").val();
            
            loadTicketStatus(ticketcode);
        });
        
        
    });
    function loadTicketStatus(ticketcode)
    {
        $.ajax({
            url: 'loadTicketStatus', 
            type: 'post', 
            dataType: 'json', 
            data: {
                ticketcode : function(){
                    return ticketcode;
                }
            }, 
            success: function(data){
                $("#ChangeStatusModel_status").empty();
                $.each(data, function(index, keys){
                    if (data.TransCode == 1){
                        $("#ChangeStatusModel_status").html("<option value='-1'>Please Select</option>");
                        $("#ChangeStatusModel_status").append(data.Result);
                    }
                    else{
                        $("#dialog_box").dialog('open');
                        $("#dialog_box").dialog('option','title','MESSAGE');
                        $("#dlg_msg").html(data.Result);
                        $("#ChangeStatusModel_status").html("<option value='-1'>Please Select</option>");
                    }
                });
            }, 
            error : function(XMLHttpRequest, e){
                alert(XMLHttpRequest.responseText);
                if(XMLHttpRequest.status == 401)
                {
                    window.location.reload();
                }
           }
        });
    }
</script>
<h2><?php echo $title; ?></h2>
<br/>
<hr style="color:#000;background-color:#000;">
<br />
<?php
$form = $this->beginWidget('CActiveForm', array(
        'id' => 'changestat-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ), 
        'action' => 'changeTicketStatus'
    ));
?>
<div id="gentool">
    <table id="gentooltbl">
        <tr id="vtype">
            <td id="label">
                Voucher Type
            </td>
            <td>
                <?php
                    echo $form->dropDownList($model, 'vouchertype', $vouchers, 
                                                                    array('id' => 'vouchertype'));
                ?>
            </td> 
        </tr>
        <tr id="batch">
            <td id="label">
                Batch
            </td>
            <td id="drpbatch">
                <?php
                    echo $form->dropDownList($model, 'batch', array(), array('prompt' => 'Please Select'));
                ?>
                <span id="status"></span>
            </td>
        </tr>
        <tr id="ticketcode">
            <td id="label">
                Ticket Code: 
            </td>
            <td id="txtticketcode">
                <?php
                    echo $form->textField($model, 'ticketcode', array('size' => 15, 'maxlength' => 7));
                ?>
            </td>
        </tr>
        <tr id="statusfield">
            <td id="label">
                Status
            </td>
            <td>
                <?php
                    echo $form->dropDownList($model, 'status', array('prompt' => 'Please Select'))
                ?>
            </td>
        </tr>
        <tr id="validfrom">
            <td>
                Valid From:
            </td>    
            <td>
                <?php
                    $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'model' => $model,
                        'attribute' => 'validfrom',
                        'htmlOptions' => array(
                            'size' => '10',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true,
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
        <tr id="validto">
            <td>
                Valid To:
            </td>    
            <td>
                <?php
                    $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                        'model' => $model,
                        'attribute' => 'validto',
                        'htmlOptions' => array(
                            'size' => '10',         // textField size
                            'maxlength' => '10',    // textField maxlength
                            'readonly' => true,
                            'disabled' => 'disabled'
                        ),
                        'options' => array(
                            'showOn'=>'button',
                            'buttonImageOnly' => true,
                            'changeMonth' => true,
                            'changeYear' => true,
                            'buttonText'=> 'Select Date To',
                            'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.gif',
                            'dateFormat'=>'yy-mm-dd',
                            'beforeShow' => 'js:function(){
                                var selectedDate = $("#'.CHtml::activeId($model,'validfrom').'").datepicker("getDate");
                                selectedDate.setDate(selectedDate.getDate() + 1);
                                $(this).datepicker("option","minDate",selectedDate);

                            }'
                        )
                    ));
                 ?>
            </td>    
        </tr>
    </table>
    <div id="submit">
        <?php echo CHtml::submitButton('Submit', array('id' =>'btnsubmit', 'style' => 'margin-left: 600px;')); ?>
    </div>    
</div>    
<?php
$this->endWidget();
?>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog_box',
    // additional javascript options for the dialog plugin
    'options'=>array(
        'title'=> $this->title,
        'autoOpen' => $this->showdialog,
        'resizable' => false,
        'draggable' => false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'modal'=>true,
        'buttons' => array(
            'OK'=>'js:function(){
                        $(this).dialog("close");
                   }',
        ),
    ),
));
?>
<p id="dlg_msg" style="text-align: left;"><?php echo $this->message; ?></p>
    
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'confirmation_dialog',
    // additional javascript options for the dialog plugin
    'options'=>array(
        'title'=> 'CONFIRM',
        'autoOpen' => $this->showconfirmdlg,
        'resizable' => false,
        'draggable' => false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'modal'=>true,
        'buttons' => array(
            'Proceed' => 'js:function(){
                        $("#confirm-sform").submit();
                   }',
            'Cancel' => 'js:function(){
                        $(this).dialog("close");
                    }'
        ),
    ),
));
?>
<p id="confirm" style="text-align: left;"><?php echo $this->confirmmsg; ?></p>
    
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
<?php
/***************************************
 * Confirmation Submit Form
 * Added by: Mark Kenneth Esguerra
 *************************************/
$form = $this->beginWidget('CActiveForm', array(
        'id' => 'confirm-sform',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => 'confChangeTicketStatus'
    ));
echo $form->hiddenField($model, 'ticketcode', array('value' => $this->ticketcode));
echo $form->hiddenField($model, 'status', array('value' => $this->status));

$this->endWidget();
?>

<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'alert-box',
    'options'=>array(
        'autoOpen' => $this->showalert,
        'modal' => true,
        'resizable' => false,
        'draggable' => false,
        'show' => 'fade',
        'hide' => 'fade',
        'width' => 350,
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'OK'=>'js:function(){ window.location = "'.$this->createUrl('site/index').'"}',
        ),
    ),
));
echo $this->messagealert;

$this->endWidget('zii.widgets.jui.CJuiDialog');
?>

