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

    $('#submit').live('click', function(event) {
        var ticketCode = $('#ticketcode').val();
        if(ticketCode == '') {
            var dateFrom = $('#dateFrom').val();
            var dateTo = $('#dateTo').val();
            compareDates(dateFrom, dateTo, event);
        }
    });
    
    function validateDateTime (date, event) {
            var time1 = " <?php echo Yii::app()->params["cutofftimestart"];?>";
            var time2 = " <?php echo Yii::app()->params["cutofftimeend"];?>";
            var date1 = $("#dateFrom").val().concat(time1);
            var date2 = $("#dateTo").val().concat(time2);

            var fromDateTime = date1.split(" ");
            var toDateTime = date2.split(" ");
            var fromTimeArray = fromDateTime[1].split(":");
            var fromTime = parseInt("".concat(fromTimeArray[0]).concat(fromTimeArray[1]).concat(fromTimeArray[2]), 10);
            var toTimeArray = toDateTime[1].split(":");
            var toTime = parseInt("".concat(toTimeArray[0]).concat(toTimeArray[1]).concat(toTimeArray[2]),10);
            var fromDate = fromDateTime[0].split("-");
            var toDateArray = toDateTime[0].split("-");
            var toDate = parseInt("".concat(toDateArray[0]).concat(toDateArray[1]).concat(toDateArray[2]));
            var fromDateAsInt = parseInt("".concat(fromDate[0]).concat(fromDate[1]).concat(fromDate[2]));
            var toDatez = toDateTime[0].split("-");
            
            var year = parseInt(fromDate[0], 10);
            var year2 = parseInt(toDatez[0], 10);
            var month = parseInt(fromDate[1], 10);
            var month2 = parseInt(toDatez[1], 10);
            var day = parseInt(fromDate[2], 10);
            var day2 = parseInt(toDatez[2], 10);
            var monthsum = month2 - month;

            var theNextDate = "";
            var leadingZero = "0";

            var currentDate = date;
            
            /**
             * @Code Block to check validity of date and time parameters
             * 
             */    
            if(month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12) { //31 Days

                if(month == 12) {

                    if(day == 31) {
                        theNextDate = theNextDate.concat((year+1),'01','01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, '12', (leadingZero.concat((day+1).toString())).substr(-2));
                    }

                }
                else {

                    if(day == 31) {
                        theNextDate = theNextDate.concat(year, (leadingZero.concat((month+1).toString())).substr(-2),'01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, (leadingZero.concat((month).toString())).substr(-2), (leadingZero.concat((day+1).toString())).substr(-2));
                    }

                }

            }
            else if (month == 4 || month == 6 || month == 9 || month == 11) { //30 Days

                 if(day == 30) {
                     theNextDate = theNextDate.concat(year, (leadingZero.concat((month+1).toString())).substr(-2),'01');
                 }
                 else {
                     theNextDate = theNextDate.concat(year, (leadingZero.concat((month).toString())).substr(-2), (leadingZero.concat((day+1).toString())).substr(-2));
                 }

            }
            else { //February

                if((year%4) == 0) {

                    if(day == 29) {
                         theNextDate = theNextDate.concat(year, '03','01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, '02', (leadingZero.concat((day+1).toString())).substr(-2));
                    }

                }
                else {

                    if(day == 28) {
                         theNextDate = theNextDate.concat(year, '03','01');
                    }
                    else {
                        theNextDate = theNextDate.concat(year, '02', (leadingZero.concat((day+1).toString())).substr(-2));
                    }

                }

            }
            
           
                var monthresult = month2 - month;
                var msg = '';
                if(monthresult > 1){
                    msg = "Your Starting and Ending Date and Time must be within 1-Week Frame";
                }
                else{                
                            
                if(year == year2 ){
                    
                    if(month == month2){
                        var daydiff = day2 - day;
                  
                        if(daydiff < 7){
                            return true;
                        }
                        else{
                            msg = "Your Starting and Ending Date and Time must be within 1-Week Frame";
                        }
                    }
                    else{
                        if(month == 1 || month == 3 || month == 5 || month == 7 || month == 8 || month == 10 || month == 12)
                        {
                            //31
                            var result = 31 - day;
                            var result2 = result + day2;
                            if(result2 <= 7){
                                return true;
                            }
                            
                            else
                            {
                                msg = "Your Starting and Ending Date and Time must be within 1-Week Frame";
                            }
                        }
                        else if(month == 4 || month == 6 || month == 9 || month == 11)
                        {
                            //30
                            var result = 30 - day;
                            var result2 = result + day2;
                            if(result2 < 7){
                                return true;
                            }
                            else{
                                msg = "Your Starting and Ending Date and Time must be within 1-Week Frame";
                            }

                        }
                        else{
                            msg = "Your Starting and Ending Date and Time must be within 1-Week Frame";
                        } 
                    }
                    
                       
                  }
                  else{
                      
                      var yeardiff = year2 - year;
                      
                      if(yeardiff > 1){
                          msg = "Your Starting and Ending Date and Time must be within 1-Week Frame";
                      }
                      else{
                          if(month == 12 && month2 == 1){
                              //31
                            var result = 31 - day;
                            var result2 = result + day2;
                            if(result2 < 7){
                                return true;
                            }
                            
                            else
                            {
                                msg = "Your Starting and Ending Date and Time must be within 1-Week Frame";
                            }
                          }
                          else{
                              msg = "Your Starting and Ending Date and Time must be within 1-Week Frame";
                          }
                      }
                      
                  }
               }
            
            

                if((fromDateAsInt > toDate) ) {

                    msg = "Invalid Date";
                }
                else if((toDate > currentDate || fromDateAsInt > currentDate)){
                    msg = "Queried date must not be greater than today";
                }
                else {

                    msg = "Your Starting and Ending Date and Time must be within 1-Week Frame";
                }

                if (msg !== '') {
            event.preventDefault();
            $('#ticketmonitoringgrid').css('display','none');
            alert(msg);
            return false;
        } else {
            return true;
        }
            
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
        if (msg !== '') {
            event.preventDefault();
            $('#ticketmonitoringgrid').css('display','none');
            alert(msg);
            return false;
        } else {
            validateDateTime (dateToday, event);
            return true;
        }
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
} else {
    if (isset($_GET['page'])) {
        $model->dateFrom = substr(Yii::app()->session['dateFrom'], 0, 10);
        $model->dateTo = substr(Yii::app()->session['dateTo'], 0, 10);
        $model->site = Yii::app()->session['site'];
    } else {
        $model->dateFrom = substr(Yii::app()->session['dateFrom'], 0, 10);
        $model->dateTo = substr(Yii::app()->session['dateTo'], 0, 10);
        $model->site = Yii::app()->session['site'];
    }
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
                $list = array('All' => 'All', 3 => 'Used', 4 => 'Encashed', 2 => 'Cancelled', 7 => 'Expired');
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
        <?php echo CHtml::submitButton('Submit', array('id' => 'submit')); ?>
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