<script type="text/javascript">
    $(document).ready(function(){
        $("#transpercutoffgrid").hide();
        
        $('#submit').live("click", function(e) {
            e.preventDefault();
            var transactiondate = $("#transactiondate").val();
            var sitecode = $("#site").val();
            var vouchertype = $("#vouchertype").val();

            $.ajax({
               url: 'getTicketCuOffSummary', 
               type: 'post', 
               dataType: 'json',
               data: {transactiondate: transactiondate, 
                      sitecode : sitecode, 
                      vouchertype :  vouchertype}, 
               success: function(data) {
                   if (data.ErrorCode == 0) {
                       $("#printedticketscount").html(data.PrintedTickets);
                       $("#printedticketsvalue").html(data.PrintedTicketsValue);

                       $("#unusedticketscount").html(data.UnusedTickets);
                       $("#unusedticketsvalue").html(data.UnusedTicketsValue);

                       $("#usedticketscount").html(data.UsedTickets);
                       $("#usedticketsvalue").html(data.UsedTicketsValue);

                       $("#encashedticketscount").html(data.EncashedTickets);
                       $("#encashedticketsvalue").html(data.EncashedTicketsValue);

                       $("#runningactivecount").html(data.RunningActiveCount);
                       $("#runningactivevalue").html(data.RunningActiveValue);

                       loadJqGrid(transactiondate, sitecode, vouchertype);
                       $("#transpercutoffgrid").show();
                       $("#site-code").val(sitecode);
                       $("#trans-date").val(transactiondate);
                   }
                   else {
                       $("#alert-box").dialog('open');
                       $("#alertmessage").html(data.Message);
                   }
               }
            });
        });
    });
    
    function loadJqGrid(transactiondate, sitecode, vouchertype){
        jQuery("#list1").jqGrid("GridUnload");
        jQuery("#list1").jqGrid({ 
             url:'getTicketRedemptions', 
             mtype: 'POST',
             postData: {
                 _sitecode: function(){
                    return sitecode;
                 }, 
                 _transdate: function(){
                    return transactiondate;
                 }, 
                 _vouchertype: function(){
                    return vouchertype;
                 }
             },
             datatype: "json",
             colNames:['Site/PeGS Code', 'Terminal Name', 'Ticket Code','Date and Time Printed', 'Amount', 'Expiration Date', 'Status', 'DateTime Processed'], 
             colModel:[ 
                 {name:'Site/PeGSCode',index:'PeGSCode', width:150, align:"center"}, 
                 {name:'TerminalName',index:'TerminalName', width:150, align:"center"},
                 {name:'TicketCode',index:'TicketCode', width:210, align:"center"},
                 {name:'DateTimePrinted',index:'UpdatedStatus', width:180, align:"center"},
                 {name:'Amount',index:'UpdatedStatus', width:130, align:"right"},
                 {name:'ExpirationDate',index:'UpdatedStatus', width:160, align:"center"},
                 {name:'Status',index:'Status', width:100, align:"center"},
                 {name:'DateTimeProcessed',index:'DateTimeProcessed', width:170, align:"center"}], 
             rowNum:10, 
             rowList:[10,20,30], 
             height: '300', 
             pager: '#pager1', 
             sortname: 'id', 
             viewrecords: true, 
             loadonce: true, 
             sortorder: "desc", 
             shrinkToFit: true,  
             caption: "Transaction Details"
        });
        jQuery("#list1").jqGrid('setGridWidth', '920'); 
        jQuery("#list1").jqGrid('navGrid','#pager1',{edit:false,add:false,del:false,search:false});
    }
</script>
<?php
$this->breadcrumbs=array(
	'Transaction Per Cut-off',
);?>
<?php

$sitesModel = new SitesModel();
if(isset($_POST['TransactionpercutoffForm']))
{
    $model->attributes=$_POST['TransactionpercutoffForm'];
}
else
{
    if(isset($_GET['page']))
    {
        $model->transactiondate=substr(Yii::app()->session['transactiondate'], 0, 10); 
        $model->site=Yii::app()->session['site'];
    }
    else
    {
        $model->transactiondate = date('Y-m-d');

    }
}

?>
<h2>Transaction Per Cut-off for Tickets</h2>
<hr color="black" />
<div class="row" style="padding: 10px 5px; background: #EFEFEF;">
<?php $form=$this->beginWidget('CActiveForm', array(
    'enableClientValidation'=>true,
    'clientOptions'=>array(
    'validateOnSubmit'=>true,
    ),
)); ?>
    <?php echo $form->errorSummary($model); ?>
    <table style="width:500px">
        <tr>
            <td><?php echo $form->labelEx($model,'Transaction Date : ');?></td>    
            <td>
                
                <?php echo $form->textField($model,'transactiondate', array('id'=>'transactiondate','readonly'=>'true', /*'value'=>date('Y-m-d'),*/ 'style'=>'width: 120px;')).
                      CHtml::image(Yii::app()->request->baseUrl."/images/calendar.png","calendar", array("id"=>"calbutton","class"=>"pointer","style"=>"cursor: pointer;"));
                      $this->widget('application.extensions.calendar.SCalendar',
                      array(
                      'inputField'=>'transactiondate',
                      'button'=>'calbutton',
                      'showsTime'=>false,
                      'ifFormat'=>'%Y-%m-%d',
                      ));                
                ?>
            </td>
        </tr> 
        <tr>
        <td><?php echo CHtml::label("Site/PeGs Code : ", "site");?></td>    
        <td>
                <?php echo $form->dropDownList($model, 'site', $sitecodes, array('id'=>'site')); ?>
                <?php echo $form->hiddenField($model, 'vouchertype', array('value' => 1, 'id'=>'vouchertype')); ?>
        </td>
        </tr>
    </table>
    <div style="width: 100%; text-align: center; margin-left: 250px;">
            <?php echo CHtml::button('Submit', array('id' => 'submit')); ?>
    </div> 
</div>
<?php $this->endWidget(); ?>
<div id="transpercutoffgrid">
    <br /><br />
    <center>
        <table style="border: 2px #DEDEDE solid; min-width: 30%; max-width: 50%;">
            <tr>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; text-align: center; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;;">NO. OF TICKETS</td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; text-align: center; background: #D0E3EF;">VALUE</td>
            </tr>
            <tr><td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Printed Tickets Total</td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #f4f9fb;">
                    <span id="printedticketscount"></span>
                </td>
                <td  style="border-bottom: 0.1px #DEDEDE solid; min-width: 30%; max-width: 50%; text-align: right; background: #f4f9fb;">
                    <span id="printedticketsvalue"></span>
                </td>
            </tr>
            <tr><td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td><td colspan="2" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Active Tickets for the Day</td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid;">
                    <span id="unusedticketscount"></span>
                </td>
                <td style="border-bottom: 0.1px #DEDEDE solid; text-align: right;">
                    <span id="unusedticketsvalue"></span>
                </td>
            </tr>
            <tr><td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td><td colspan="2" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Running Active Tickets</td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid;">
                    <span id="runningactivecount"></span>
                </td>
                <td style="border-bottom: 0.1px #DEDEDE solid; text-align: right;">
                    <span id="runningactivevalue"></span>
                </td>
            </tr>
            <tr><td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td colspan="2" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Ticket Redemptions</td>
                <td colspan="2" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #f4f9fb;"></td>
                <td colspan="2" style="border-bottom: 0.1px #DEDEDE solid; background: #f4f9fb;"></td>
            </tr>
            <tr><td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Used (Deposit/Reload)</td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid;">
                    <span id="usedticketscount"></span>
                </td>
                <td  style="border-bottom: 0.1px #DEDEDE solid; text-align: right;">
                    <span id="usedticketsvalue"></span>
                </td>
            </tr>
            <tr><td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td style="border-bottom: 0.1px #DEDEDE solid; background: #D0E3EF;"></td>
                <td style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #D0E3EF;">Encashed</td>
                <td colspan="3" style="border-bottom: 0.1px #DEDEDE solid; border-right: 0.1px #DEDEDE solid; background: #f4f9fb;">
                    <span id="encashedticketscount"></span>
                </td>
                <td style="border-bottom: 0.1px #DEDEDE solid; text-align: right; background: #f4f9fb;">
                    <span id="encashedticketsvalue"></span>
                </td>
            </tr>
        </table>
    </center>    
    <table id="list1"></table> 
    <div id="pager1"></div>
    <br />
    <br />
    <?php $form=$this->beginWidget('CActiveForm', array(
        'enableClientValidation'=>true,
        'clientOptions'=>array(
            'validateOnSubmit'=>true,
        ),
        'action' => $this->createUrl('exporttoexcelticket')
    )); ?>
    <div style="float:right">
        <?php 
            echo CHtml::hiddenField('sitecode', '', array('id' => 'site-code'));
            echo CHtml::hiddenField('transdate', '', array('id' => 'trans-date'));
            
            echo CHtml::submitButton('Export To Excel');
        ?>
    </div>
    <div class="clear"></div>
    <br />
    <br />
    <?php $this->endWidget(); ?>
</div>

<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog',array(
    'id'=>'alert-box',
    'options'=>array(
        'autoOpen' => false,
        'modal' => true,
        'title' => 'MESSAGE',
        'resizable' => false,
        'draggable' => false,
        'show' => 'fade',
        'hide' => 'fade',
        'width' => 350,
        'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
        (
            'OK'=>'js:function(){$(this).dialog("close");}',
        ),
    ),
));
?><p id="alertmessage"></p><?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>