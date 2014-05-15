<?php
/**
 * Ticket Auto-Generation Configuration View
 * @author Mark Kenneth Esguerra
 * @date March 6, 2014
 */
$this->pageTitle = Yii::app()->name." - Ticket Auto-Generation Configuration History";
?>
<h2>Ticket Auto-Generation Configuration History</h2>
<br />
<hr style="color:#000;background-color:#000;">
<br />
<script type="text/javascript">
    function loadJqGrid(vtype, datefrom, dateto){
        jQuery("#list1").jqGrid("GridUnload");
        jQuery("#list1").jqGrid({ 
             url:'getConfigurationHistory', 
             mtype: 'POST',
             postData: {
                 vouchertype: function(){
                     return vtype;
                 },
                 date_from: function(){
                     return datefrom;
                 }, 
                 date_to: function(){
                     return dateto;
                 }
             },
             datatype: "json",
             colNames:['Date and Time Configured','Auto Generate', 'Threshold Limit', 'Auto-Generation Count', 'Configured by'], 
             colModel:[ 
                 {name:'DateCreated',index:'DateCreated', width:210, align:"right"}, 
                 {name:'AutoGenerate',index:'AutoGenerate', width:150, align:"center"}, 
                 {name:'ThresholdLimit',index:'ThresholdLimit', width:170, align:"right"}, 
                 {name:'AutoGenCount',index:'AutoGenCount', width:160, align:"right"}, 
                 {name:'ConfiguredBy',index:'ConfiguredBy', width:170, align:"center"}], 
             rowNum:10, 
             rowList:[10,20,30], 
             height: '300', 
             pager: '#pager1', 
             sortname: 'id', 
             viewrecords: true, 
             loadonce: true, 
             sortorder: "desc", 
             caption: "Ticket Auto-Generation Configuration History"
        });
        jQuery("#list1").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false,search:false});
    }
    $(document).ready(function(){
        $("#btnsubmit").live("click", function(){
            var vtype       = $("#TicketAutoGenConfHistoryForm_vouchertype").val();
            var datefrom    = $("#TicketAutoGenConfHistoryForm_datefrom").val();
            var dateto      = $("#TicketAutoGenConfHistoryForm_dateto").val();
            
            if (datefrom != "" && dateto != ""){
               if (datefrom < dateto){
                    loadJqGrid(vtype, datefrom, dateto);
                    $(document).scrollTop($(document).height());
                }
                else{
                    $("#dialog_box").dialog("open");
                    $("#dialog_box").dialog("option", "title", "ERROR MESSAGE");
                    $("#dlg_msg").html("The Start Date is greater than the End Date.");
                } 
            }
            else{
                $("#dialog_box").dialog("open");
                $("#dialog_box").dialog("option", "title", "ERROR MESSAGE");
                $("#dlg_msg").html("Please fill up all fields.");
            }
            
        });
        
    });
</script>    
<style>
    .label{
        width: 100px;
    }
</style>    
<?php
$form = $this->beginWidget('CActiveForm', array(
        'id' => 'generate-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        )
    ));
?>
<table id="autogenconfhist">
    <tr>
        <td class="label">
            Voucher Type:
        </td>    
        <td>
            <?php echo $form->dropdownlist($model, 'vouchertype', $vouchertype, array('style' => 'width: 110px;')); ?>
        </td>
    </tr>
    <tr>
        <td class="label">
            Date From:
        </td>    
        <td>
           <?php
                $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                    'model' => $model,
                    'attribute' => 'datefrom',
                    'htmlOptions' => array(
                        'size' => '12',         // textField size
                        'maxlength' => '10',    // textField maxlength
                        'readonly' => true,
                    ),
                    'options' => array(
                        'showOn'=>'button',
                        'buttonImageOnly' => true,
                        'changeMonth' => true,
                        'changeYear' => true,
                        'maxDate' => '0', 
                        'buttonText'=> 'Select Date From',
                        'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.gif',
                        'dateFormat'=>'yy-mm-dd',
                    )
                ));
             ?>
        </td>
    </tr>
    <tr>
        <td class="label">
            Date To:
        </td>    
        <td>
           <?php
                $this->widget('zii.widgets.jui.CJuiDatePicker', array(
                    'model' => $model,
                    'attribute' => 'dateto',
                    'htmlOptions' => array(
                        'size' => '12',         // textField size
                        'maxlength' => '10',    // textField maxlength
                        'readonly' => true,
                    ),
                    'options' => array(
                        'showOn'=>'button',
                        'buttonImageOnly' => true,
                        'changeMonth' => true,
                        'changeYear' => true,
                        'maxDate' => '0', 
                        'buttonText'=> 'Select Date From',
                        'buttonImage'=>Yii::app()->request->baseUrl.'/images/calendar.gif',
                        'dateFormat'=>'yy-mm-dd',
                    )
                ));
             ?>
        </td>
    </tr>
</table>  
<?php echo CHtml::button('Submit', array('id' => 'btnsubmit','style' => 'margin-left: 600px;')); ?>
<br />
<br />
<table id="list1"></table> 
<div id="pager1"></div>
<?php $this->endWidget(); ?>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog_box',
    // additional javascript options for the dialog plugin
    'options'=>array(
        'autoOpen' => false,
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
<p id="dlg_msg" style="text-align: left;"></p>
    
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>