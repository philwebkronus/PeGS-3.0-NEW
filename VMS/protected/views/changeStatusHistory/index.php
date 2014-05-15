<?php
/**
 * Change Voucher Status History View
 * @author Mark Kenneth Esguerra
 * @date Febraury 27, 2014
 */ 
$this->pageTitle = Yii::app()->name." - Change Voucher Status History";
?>
<script>
    function loadJqGrid(vtype, vcode, datefrom, dateto){
        jQuery("#list1").jqGrid("GridUnload");
        jQuery("#list1").jqGrid({ 
             url:'getChangeStatusHistory', 
             mtype: 'POST',
             postData: {
                 vouchertype: function(){
                     return vtype;
                 },
                 vouchercode: function(){
                    return vcode;
                 }, 
                 date_from: function(){
                     return datefrom;
                 }, 
                 date_to: function(){
                     return dateto;
                 }
             },
             datatype: "json",
             colNames:['Date Created','Ticket Code', 'Original Status', 'Updated Status','Processsed By'], 
             colModel:[ 
                 {name:'DateCreated',index:'DateCreated', width:180, align:"right"}, 
                 {name:'TicketCode',index:'TicketCode', width:180, align:"center"}, 
                 {name:'OriginalStatus',index:'OriginalStatus', width:150, align:"center"}, 
                 {name:'UpdatedStatus',index:'UpdatedStatus', width:150, align:"center"}, 
                 {name:'ProcesssedBy',index:'ProcesssedBy', width:180, align:"center"}], 
             rowNum:10, 
             rowList:[10,20,30], 
             height: '300', 
             pager: '#pager1', 
             sortname: 'id', 
             viewrecords: true, 
             loadonce: true, 
             sortorder: "desc", 
             caption: "Change Voucher Status History"
        });
        jQuery("#list1").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false,search:false});
    }
    $(document).ready(function(){
       $("#exporttoexcel").hide();
       $("#btnsubmit").live('click', function(){         
           var vtype    = $("#ChangeStatusHistoryForm_vouchertype").val();
           var vcode    = $("#ChangeStatusHistoryForm_vouchercode").val();
           var datefrom = $("#ChangeStatusHistoryForm_datefrom").val();
           var dateto   = $("#ChangeStatusHistoryForm_dateto").val();

           //Put entered values in hidden fields for excel generation
           $("#ChangeStatusHistoryForm_hdn_vouchertype").val(vtype);
           $("#ChangeStatusHistoryForm_hdn_vouchercode").val(vcode);
           $("#ChangeStatusHistoryForm_hdn_datefrom").val(datefrom);
           $("#ChangeStatusHistoryForm_hdn_dateto").val(dateto);
           
           if (datefrom !== "" && dateto !== ""){
               if (datefrom < dateto){
                   loadJqGrid(vtype, vcode, datefrom, dateto);
                   $(document).scrollTop($(document).height());
                   $("#exporttoexcel").show();
               }
               else{
                   $("#dialog-msg").dialog('open');
                   $("#dialog-msg").dialog('option', 'title', 'ERROR MESSAGE');
                   $("#message").html("The Start Date is greater than the End Date");
               }
           }
           else{
               $("#dialog-msg").dialog('open');
               $("#dialog-msg").dialog('option', 'title', 'ERROR MESSAGE');
               $("#message").html("Please fill up all fields");
           }
           
       });
    });
</script>    
<style>
    #changestathist{
        width: 300px;
    }
</style>    
<h2><?php echo $headertitle; ?></h2>
<br/>
<hr style="color:#000;background-color:#000;">

<?php
$form = $this->beginWidget('CActiveForm', array(
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
    ));
?>
<table id="changestathist">
    <tr>
        <td>
            Voucher Type: 
        </td>    
        <td>
            <?php echo $form->dropdownlist($model, 'vouchertype', $vouchertype, array('style' => 'width: 110px;')); ?>
        </td>
    </tr>
    <tr>
        <td>
            Voucher Code: 
        </td>    
        <td>
            <?php echo $form->textField($model, 'vouchercode', array('size' => 15, 'maxlength' => 7)); ?>
        </td>
    </tr>
    <tr>
        <td>
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
        <td>
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
<?php
$this->endWidget();
?>
<?php
$form = $this->beginWidget('CActiveForm', array(
                    'enableClientValidation' => true,
                    'clientOptions' => array(
                        'validateOnSubmit' => true,
                     ),
        ));
?>
<div id="exporttoexcel" align="right" style="background-color: activecaption; padding-bottom: 10px; padding-right: 10px; display: block; width: 857px;">
    <br />
    <?php
        echo $form->hiddenField($model, 'hdn_vouchertype');
        echo $form->hiddenField($model, 'hdn_datefrom');
        echo $form->hiddenField($model, 'hdn_dateto');
        echo $form->hiddenField($model, 'hdn_vouchercode');
        echo CHtml::button('Export to Excel File', array('submit'=>'exportXls', 'id'=>'btnExportXls'));
    ?>
</div>
<?php $this->endWidget(); ?>
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'dialog-msg',
    'options'=>array(
        'autoOpen' => false,
        'modal' => true,
        'resizable' => false,
        'draggable' => false,
        'show' => 'fade',
        'hide' => 'fade',
        'width' => 350,
        'buttons' => array
        (
            'OK'=>'js:function(){$(this).dialog("close");}',
        ),
    ),
));
?>
<span id="message"></span>
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
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