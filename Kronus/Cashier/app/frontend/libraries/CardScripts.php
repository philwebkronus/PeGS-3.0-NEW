<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<script type="text/javascript">

function identifyCard()
{
                var url = '<?php echo Mirage::app()->createUrl('loyalty/cardinquiry') ?>'; 
                var url_transfer = '<?php echo Mirage::app()->createUrl('loyalty/transferpoints') ?>'; 
                var url_activate = '<?php echo Mirage::app()->param['member_activation']?>'; 
                var url_tempactivate = '<?php echo Mirage::app()->param['temp_activation']?>'; 
                  
                var isbgi = <?php echo $_SESSION['isbgi'] ?>;
                var servertime = '<?php echo date('m-d-Y H:i:s'); ?>';
                var AID = $('#acc_id').val();
                var sitecode = $('#sitecode').val();
                var card_number = $('#StartSessionFormModel_loyalty_card').val();
                var data = 'card_number='+card_number+'&isreg=0';
                var response = '';

                $.ajax({
                    type : 'post',
                    async: false,
                    url : url,
                    data : data,
                    success : function(data) {
                        try {
                            var json = $.parseJSON(data);
                            
                            //get status value
                             var StatusValue = getStatusValue(json.CardInfo.StatusCode);

                            if(StatusValue == "Active" || StatusValue == "Active Temporary" || StatusValue == "New Migrated"){
                                    response = "false";
                            } else if (StatusValue == 'Banned Card'){
                                    updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card is BANNED.</label>' + 
                                                                    '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                    '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                    '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                    ''          
                                    ); 
                            } else {

                                showLightbox(function(){
                                    if(StatusValue == ''){
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[000]: Card Number is INVALID.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        );    
                                    }
                                    
                                    if(StatusValue == undefined){
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[001]: Card Number is INVALID.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        );    
                                    }
                                    
                                    if(json.CardInfo.StatusCode == 100){
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[002]: Card Number is INVALID.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        );    
                                    }
                                    
                                    //check if the card number has a value, if none return invalid message
                                    if(json.CardInfo.StatusCode != 8 ){
                                        
                                        if(StatusValue == "Old"){
                                            updateLightbox( '<div id = "popup">Migration Account: '  + 
                                                                            '<br /> Please tap Membership Card' + 
                                                                            '<br /><br /> Membership Card <input type="text" onkeydown = "if(event.keyCode == 13){ getMemInfo();  }" style="margin-left: 5px;text-align: center;font-size: 10px;font-weight: bold; padding: 3px;" id= "txtCardNumber"  />' +
                                                                            '<input type= "hidden" id="statusvalue" />' +
                                                                            '<input type= "hidden" id="loyaltycard" value ="' + json.CardInfo.CardNumber + '" />' +
                                                                            '<input type= "hidden" id="LYpoints" value ="' + json.CardInfo.CurrentPoints + '" />' +
                                                                            '<input type= "hidden" id="Mempoints" />' +
                                                                            '<input type= "hidden" id="url" value ="' + url + '" />' +
                                                                            '<input type= "hidden" id="url_transfer" value ="' + url_transfer + '" />' +
                                                                            '<input type= "hidden" id="url_activate" value ="' + url_activate + '" />' +
                                                                            '<input type= "hidden" id="aid" value ="' + AID + '" />' +
                                                                            '<input type= "hidden" id="sitecode" value ="' + sitecode + '" />' +
                                                                            '<br /><br /><center><label id="cardStatus" style="display:block;"></div></label>' + 
                                                                            '<input type="button" style="font-size: 14px; margin-left:60px; width: 80px; height: 27px;"  value="Clear" class="btnClear"/>' +
                                                                            '<input type="button" style="font-size: 14px; margin-left:10px; width: 80px; height: 27px;"  disabled="disabled" value="Proceed" onClick="javascript: btnProceed();" class="btnProceed"/></div>',
                                                                        '<a  class = "btnClose" id="fancyClose"></a>',function(){ $("#txtCardNumber").focus(); }
                                            ); 
                                        } else if(StatusValue == "Inactive Temporary"){

                                            if(json.CardInfo.DateVerified != null && json.CardInfo.DateVerified != undefined && json.CardInfo.DateVerified != "") {
                                                    //formatting dateverified
                                                    var dateStr= json.CardInfo.DateVerified;
                                                    var a=dateStr.split(" ");
                                                    var d=a[0].split("-");
                                                    var t=a[1].split(":");
                                                    var date = new Date(d[0],(d[1]-1),d[2],t[0],t[1],t[2]);
                                                    var cooling_period =  json.CardInfo.CoolingPeriod;
                                                    
                                                    //get date difference between date today and the dateverified
                                                    var datediff = getDateDiff(date,servertime, cooling_period);

                                                    if(datediff != "false" && datediff != "true") {
                                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Temporary Account is INACTIVE.</label>' + 
                                                                                        '<br /><br /><label style="font-size: 20px;">PAGCOR requires '+cooling_period+'-hour Cooling Period.</label>' + 
                                                                                        '<br /><label style="font-size: 20px;">Account <b>' + json.CardInfo.CardNumber + '</b> will be activated on</label>' + 
                                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">'+ datediff +'.</label></center>' +
                                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                                        ''          
                                                        );  
                                                    } else if(datediff == "false") {
                                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is INVALID.</label>' + 
                                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                                        ''          
                                                        ); 
                                                    }
                                            } else {
                                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is INVALID.</label>' + 
                                                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                                ''          
                                                ); 
                                            }


                                        } else if(StatusValue == "Inactive"){
                                                updateLightbox( '<div id = "popup"><br/><br />Please input Temporary Account Code. '  + 
                                                                                '<br /> Temporary Account:  <input type="text"  style="margin-left: 5px;text-align: center;font-size: 10px;font-weight: bold; padding: 3px;" id= "txtCardNumber"  />' +
                                                                                '<input type= "hidden" id="status" value ="' + StatusValue + '" />' +
                                                                                '<input type= "hidden" id="memcard" value ="' + card_number + '" />' +
                                                                                '<input type= "hidden" id="url" value ="' + url + '" />' +
                                                                                '<input type= "hidden" id="aid" value ="' + AID + '" />' +
                                                                                '<input type= "hidden" id="servertime" value ="' + servertime + '" />' +
                                                                                '<input type= "hidden" id="sitecode" value ="' + sitecode + '" />' +
                                                                                '<input type= "hidden" id="url_tempactivate" value ="' + url_tempactivate + '" />' +
                                                                                '<br /><br /><input type="button" style="font-size: 14px; margin-left:80px; width: 80px; height: 27px;"  value="Clear" class="btnClear"/>' +
                                                                                '<input type="button" style="font-size: 14px; margin-left:10px; width: 80px; height: 27px;"  value="Submit" onclick="javascript: btnSubmit();" class="btnSubmit"/></div>',
                                                                                '<a  class = "btnClose" id="fancyClose"></a>' , function(){ $("#txtCardNumber").focus(); }         
                                                ); 
                                        } else if(StatusValue == "Deactivated"){
                                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is DEACTIVATED.</label>' + 
                                                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                                ''          
                                                ); 
                                        } else if(StatusValue == "Old Migrated"){
                                                updateLightbox( '<center><label  style="font-size: 24px; font-weight: bold;">VIP Rewards Card has been</label>' + 
                                                                                '<br /><label  style="font-size: 24px; font-weight: bold;">deactivated.</label></center>' + 
                                                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClr" />',
                                                                                ''          
                                                ); 
                                        } 
                                    } else {
                                         updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[003]: Card Number is INVALID.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        );    
                                    }

                                    });  
                                    response = "true";
                            } 

                        } catch(e) {
                            alert(e);
                            //alert('Oops! Something went wrong');
                        }

                    },
                    error : function(e) {
                        displayError(e);
                    }
                });
                return response;
}

</script>
