<?php
/**
 * Ticket Auto-Generation Configuration View
 * @author Mark Kenneth Esguerra
 * 03-04-14
 */
$this->pageTitle = Yii::app()->name." - Configure Ticket Auto-Generation"
?>
<h2>Configure Ticket Auto-Generation</h2>
<br />
<hr style="color:#000;background-color:#000;">
<br />
<script type="text/javascript">
    $(document).ready(function(){
       if ($("#GenerationToolModel_autogenjob_1").is(':checked')){
           $("#GenerationToolModel_thresholdlimit").attr('disabled','disabled');
           $("#GenerationToolModel_autoticketcount").attr('disabled','disabled');
       }
       else if($("#GenerationToolModel_autogenjob_0").is(':checked')){
           $("#GenerationToolModel_thresholdlimit").removeAttr('disabled');
           $("#GenerationToolModel_autoticketcount").removeAttr('disabled');
       }
       $("#GenerationToolModel_autogenjob").click(function(){
           if ($("#GenerationToolModel_autogenjob_1").is(':checked')){
               $("#GenerationToolModel_thresholdlimit").attr('disabled','disabled');
               $("#GenerationToolModel_autoticketcount").attr('disabled','disabled');
           }
           else if($("#GenerationToolModel_autogenjob_0").is(':checked')){
               $("#GenerationToolModel_thresholdlimit").removeAttr('disabled');
               $("#GenerationToolModel_autoticketcount").removeAttr('disabled');
           }
       });
       $("#btnproceed").live("click", function(){
           var autogenjob   = $("input[type='radio'][name='GenerationToolModel[autogenjob]']:checked").val();
           var threshold    = $("#GenerationToolModel_thresholdlimit").val();
           var ticketcount  = $("#GenerationToolModel_autoticketcount").val();
           
           $.ajax({
              url: 'ticketAutoGenConf', 
              type: 'post', 
              dataType: 'json', 
              data: {
                  autogenjob : function(){
                      return autogenjob;
                  },
                  thresholdlimit : function(){
                      return threshold;
                  }, 
                  autoticketcount : function(){
                      return ticketcount;
                  }
              },
              success: function(data){
                  if (data.ResultCode == 1){
                      $("#dlgconfimation").dialog('open');
                      $("#dlg_confirmsg").html(data.Message);
                      $("#GenerationToolModel_hdn_autogenjob").val(data.AutoGenJob);
                      $("#GenerationToolModel_hdn_threshold").val(data.Threshold);
                      $("#GenerationToolModel_hdn_ticketcount").val(data.TicketCount);
                  }
                  else{
                      $("#dialog_box").dialog('open');
                      $("#dlg_msg").html(data.Message);
                  }
              }, 
              error: function(e) {
                alert(e);
              }
           });
       });
    });
</script>    
<style>
    .lbl{
        width: 200px;
    }
</style> 
<?php
$form = $this->beginWidget('CActiveForm', array(
            'id' => 'regenerate-form',
            'enableClientValidation' => true,
            'enableAjaxValidation' => true,
            'clientOptions' => array(
                'validateOnSubmit' => true,
            )
        ));
?>
<table id="autogenconf">
    <tr>
        <td class="lbl">
            Auto-Generation: 
        </td>
        <td>
            <?php
                $autooptions = array(1 => 'Enable', 2 => 'Disable');
                echo $form->radioButtonList($model, 'autogenjob', $autooptions, array('separator'=>' ', 
                                                                                        'labelOptions'=>array('style'=>'float:left;'), 'style'=>'float:left;'))?>
        </td>    
    </tr>
    <tr>
        <td class="lbl">
            Threshold Limit:  
        </td>
        <td>
            <?php
                echo $form->dropdownlist($model, 'thresholdlimit', $thresholds, array('prompt' => '-Please Select-', '2500' => 'selected'));
            ?>
        </td>    
    </tr>
    <tr>
        <td class="lbl">
            Auto-Generation Ticket Count:  
        </td>
        <td>
            <?php
                echo $form->dropdownlist($model, 'autoticketcount', $ticketcounts, array('prompt' => '-Please Select-'));
            ?>
        </td>    
    </tr>
</table>  
<div id="submit">
    <?php echo CHtml::button('Proceed', array('id' => 'btnproceed','style' => 'margin-left: 600px;')); ?>
</div>
<?php
$this->endWidget();
//Confirmation Dialog Form
$form = $this->beginWidget('CActiveForm', array(
            'id' => 'confirmation-form',
            'enableClientValidation' => true,
            'enableAjaxValidation' => true,
            'clientOptions' => array(
                'validateOnSubmit' => true,
            ), 
            'action' => 'confirmConfigSetting'
        ));
echo $form->hiddenField($model, 'hdn_autogenjob', array('value' => $this->autogenjob));
echo $form->hiddenField($model, 'hdn_threshold', array('value' => $this->threshold));
echo $form->hiddenField($model, 'hdn_ticketcount', array('value' => $this->ticketcount));

$this->endWidget();

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
        'open' => 'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array(
            'OK' => 'js:function(){
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
    'id'=>'dlgconfimation',
    // additional javascript options for the dialog plugin
    'options'=>array(
        'title'=> "CONFIRM",
        'autoOpen' => $this->showconfirmdlg,
        'resizable' => false,
        'draggable' => false,
        'show'=>'fade',
        'hide'=>'fade',
        'width'=>350,
        'modal'=>true,
        'open' => 'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array(
            'Proceed' => 'js:function(){
                        $("#confirmation-form").submit();
                   }',
            'Cancel' => 'js:function(){
                        $(this).dialog("close");
                   }'
        ),
    ),
));
?>
<p id="dlg_confirmsg" style="text-align: left;"><?php echo $this->confirmmsg; ?></p>

<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>