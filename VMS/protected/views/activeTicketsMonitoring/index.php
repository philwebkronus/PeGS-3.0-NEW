<?php
/******************************
 * Active Tickets Monitoring View
 * Mark Kenneth Esguerra
 * March 26, 2014
 */

$this->pageTitle = Yii::app()->name." - Active Tickets Monitoring";
?>
<script type="text/javascript">
    
    $(document).ready(function(){
        $("#disptotal").hide();
        $("#btnsubmit").live("click", function(){
            var sitecode = $("#ActiveTicketsMonitoringForm_sitecode").val();
            if (sitecode != ""){
                $.ajax({
                   url: 'getTotalTickets', 
                   type: 'post', 
                   dataType: 'json', 
                   data: {
                       _sitecode: function(){
                           return sitecode;
                       }
                   }, 
                   success: function(data){
                       $("#disptotal").show();
                       $("#totaltickets").html(data.TotalCount);
                       $("#totalamount").html(data.TotalAmount);
                       loadJqGrid(sitecode);
                   }
                });
            }
            else{
                $("#dialog-msg").dialog('open');
                $("#dialog-msg").dialog('option', 'title', 'MESSAGE');
                $("#message").html("Please select a Site/PeGS Code.");
            }
        });
    });
    
    function loadJqGrid(sitecode){
        jQuery("#list1").jqGrid("GridUnload");
        jQuery("#list1").jqGrid({ 
             url:'loadAllTicketInfo', 
             mtype: 'POST',
             postData: {
                 _sitecode: function(){
                     return sitecode;
                 }
             },
             datatype: "json",
             colNames:['Site/PeGS Code', 'Ticket Code','Date and Time', 'Amount', 'Expiration Date', 'Validity Status'], 
             colModel:[ 
                 {name:'Site/PeGSCode',index:'TicketCode', width:140, align:"center"}, 
                 {name:'TicketCode',index:'UpdatedStatus', width:190, align:"center"},
                 {name:'DateTimePrinted',index:'UpdatedStatus', width:200, align:"center"},
                 {name:'Amount',index:'UpdatedStatus', width:130, align:"right"},
                 {name:'ExpirationDate',index:'UpdatedStatus', width:140, align:"center"},
                 {name:'ValidityStatus',index:'ProcesssedBy', width:160, align:"center"}], 
             rowNum:10, 
             rowList:[10,20,30], 
             height: '300', 
             pager: '#pager1', 
             sortname: 'id', 
             viewrecords: true, 
             loadonce: true, 
             sortorder: "desc", 
             caption: "Ticket Details"
        });
        jQuery("#list1").jqGrid('setGridWidth', '900'); 
        jQuery("#list1").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false,search:false});
    }
</script>    
<h2>Active Tickets Monitoring</h2>
<br/>
<hr style="color:#000;background-color:#000;">
<br />

<?php
$form = $this->beginWidget('CActiveForm', array(
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
    ));
?>
<style>
    .field{
        width: 100px;
    }
    .view_div{
        background: #eee;
        line-height: normal;
    }
</style>    
<table>
    <tr>
        <td class="field">
            Site/PeGS Code: 
        </td>    
        <td>
            <?php echo $form->dropdownlist($model, 'sitecode', $sitecodes); ?>
        </td>    
    </tr>
</table>
<div id="submit">
    <?php echo CHtml::button('Submit', array('id' =>'btnsubmit', 'style' => 'margin-left: 600px;')); ?>
</div>
<?php
$this->endWidget();
?>
<br />
<br />
<div id="disptotal" class="view_div">
    <h4><b>Total Active Tickets: </b><span id="totaltickets"></span><br /></h4>
    <h4><b>Total Amount of Active Tickets: </b><span id="totalamount"></span><br /><br /><h4>
</div>
<br />
<?php
if (Yii::app()->session['AccountType'] == 8  || 
    Yii::app()->session['AccountType'] == 12 || 
    Yii::app()->session['AccountType'] == 5  || 
    Yii::app()->session['AccountType'] == 16 || 
    Yii::app()->session['AccountType'] == 6)
{
    
?>
    <table id="list1"></table> 
    <div id="pager1"></div>
<?php 
}
?>
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