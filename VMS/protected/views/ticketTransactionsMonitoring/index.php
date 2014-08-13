<script type="text/javascript">
    $(document).ready(function() {
        
        $('ticketcode').bind('keypress', function (e) {
        return !(e.which != 8 && e.which != 0 &&
                (e.which < 48 || e.which > 57) && e.which != 46);
    });
<?php
if (isset($_POST['TicketTransactionsMonitoringForm']) == '') {
    ?>
            $('#ticketmonitoringgrid').hide();
    <?php
} else {
    ?>
            $('#ticketmonitoringgrid').show();
    <?php
}
?>
    });

    $('#btnSubmit').live('click', function(event) {
        var ticketCode = $('#ticketcode').val();
        if(ticketCode == '') {
            var dateFrom = $('#dateFrom').val();
            var dateTo = $('#dateTo').val();
            getDateDiff(dateFrom, dateTo);
        }
        else {
            submit();
        }
    });
    function submit(){
        $("#tickettransacform").submit();
    }
    function getDateDiff (dateFrom, dateTo, event) {
        $.ajax({
           url: 'checkDateRange', 
           type: 'post',
           dataType: 'json',
           data: {dateFrom : dateFrom, dateTo : dateTo}, 
           success: function(data){
               if (data >= 31) {
                   alert("Your starting date and end date must be in 31 days frame.");
               }
               else {
                   var msg = compareDates(dateFrom, dateTo, event);
                   if (msg == '') {
                        submit();
                   }
                   else {
                       alert(msg);
                   }
               }
           }
        });
    }
    
    function compareDates(date1, date2, event) {
        var dateToday = "<?php echo date("Y-m-d H:i:s"); ?>";
        var msg = '';
        
        if (date1 != '' && date1 != '') {
            if (date1 != '') {
                if (date2 != '') {
                    if (date1 > date2) {
                        msg = 'Date From must not be greater than Date To!';
                    } else if ((date1 > dateToday) && (date1 > dateToday)) {
                        msg = 'Date range must not be greater than Date Today!';
                    } else if (date1 > dateToday) {
                        msg = 'Date From must not be greater than Date Today!';
                    } else if (date2 > dateToday) {
                        msg = 'Date To must not be greater than Date Today!';
                    }
                } else {
                    msg = 'Please fill up the Date To!';
                }
            } else {
                msg = 'Please fill up the Date From!';
            }
        } else {
            msg = 'Please fill up the Dates!';
        }
        return msg;
    }
    
    function isNumberKey(evt)
      {
         var charCode = (evt.which) ? evt.which : event.keyCode
         if (charCode > 31 && (charCode < 48 || charCode > 57))
            return false;

         return true;
      }
    ;
</script>
<?php
$this->breadcrumbs = array(
    'Ticket Transactions Monitoring',
);
?>
<?php
$sitesModel = new SitesModel();
if (isset($_POST['TicketTransactionsMonitoringForm'])) {
    $model->attributes = $_POST['TicketTransactionsMonitoringForm'];
}
?>
<h2>Ticket Transactions Monitoring</h2>
<hr color="black" />
<div class="row" style="padding: 10px 5px; background: #EFEFEF;">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'enableClientValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'id' => 'tickettransacform'
    ));
    ?>
    <?php echo $form->errorSummary($model); ?>
    <table style="width:600px">
        <tr>
            <td><?php echo $form->labelEx($model, 'Date From : '); ?></td>    
            <td>

                <?php
                echo $form->textField($model, 'dateFrom', array('id' => 'dateFrom', 'readonly' => 'true', /* 'value'=>date('Y-m-d'), */ 'style' => 'width: 120px;')) .
                CHtml::image(Yii::app()->request->baseUrl . "/images/calendar.png", "calendar", array("id" => "calbutton1", "class" => "pointer", "style" => "cursor: pointer;"));
                $this->widget('application.extensions.calendar.SCalendar', array(
                    'inputField' => 'dateFrom',
                    'button' => 'calbutton1',
                    'showsTime' => false,
                    'ifFormat' => '%Y-%m-%d',
                ));
                ?>
            </td>
            <td><?php echo $form->labelEx($model, 'Date To: '); ?></td>    
            <td>

                <?php
                echo $form->textField($model, 'dateTo', array('id' => 'dateTo', 'readonly' => 'true', /* 'value'=>date('Y-m-d'), */ 'style' => 'width: 120px;')) .
                CHtml::image(Yii::app()->request->baseUrl . "/images/calendar.png", "calendar", array("id" => "calbutton2", "class" => "pointer", "style" => "cursor: pointer;"));
                $this->widget('application.extensions.calendar.SCalendar', array(
                    'inputField' => 'dateTo',
                    'button' => 'calbutton2',
                    'showsTime' => false,
                    'ifFormat' => '%Y-%m-%d',
                ));
                ?>
            </td>
        </tr> 
        <tr>
            <td><?php echo CHtml::label("Site/PeGs Code : ", "site"); ?></td>    
            <td>
                <?php echo $form->dropDownList($model, 'site', $sitesModel->fetchAllActiveSites(), array('id' => 'site')); ?>
                <?php echo $form->hiddenField($model, 'vouchertype', array('value' => 1), array('id' => 'vouchertype')); ?>
            </td>
        </tr>
        <tr>
            <td>Status: </td>
            <td>
                <?php
                $list = array('All' => 'All', 3 => 'Used', 4 => 'Encashed', 7 => 'Expired');
                echo $form->dropDownList($model, 'status', $list, array('id' => 'status'));
                ?>
            </td>
        </tr>
        <tr>
            <td>Ticket Code: </td>
            <td>
                <?php
                echo $form->textField($model, 'ticketcode', array('id' => 'ticketcode', /* 'value'=>date('Y-m-d'), */ 'style' => 'width: 180px;', 'onkeypress'=>"return isNumberKey(event)"))
                ?>
            </td>
        </tr>
    </table>
    <div style="width: 100%; text-align: center; margin-left: 250px;">
        <?php echo CHtml::button('Submit', array('id' => 'btnSubmit')); ?>
    </div> 
</div>
<div>
    <?php
    $this->actionTicketMonitoringDataTable(Yii::app()->session['rawData']);
    ?>
</div>
<?php $this->endWidget(); ?>
<?php
//$this->renderPartial('siteconversion', array('arrayDataProvider'=>$arrayDataProvider)) ?>