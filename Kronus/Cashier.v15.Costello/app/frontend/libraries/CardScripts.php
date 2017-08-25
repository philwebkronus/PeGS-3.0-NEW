<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
?>

<script type="text/javascript">
var isEwalletSessionMode = false;
var isValidated = false;
// CCT - BEGIN comment
/*
function zeroPad(num, numZeros) 
{
    var n = Math.abs(num);
    var zeros = Math.max(0, numZeros - Math.floor(n).toString().length );
    var zeroString = Math.pow(10,zeros).toString().substr(1);
    if( num < 0 ) 
    {
        zeroString = '-' + zeroString;
    }
    return zeroString+n;
}
*/
// CCT - END comment
function identifyCard()
{
    var url = '<?php echo Mirage::app()->createUrl('loyalty/cardinquiry') ?>';
    var urlGetCasinoServices = '<?php echo Mirage::app()->createUrl('terminal/getCasinoServices') ?>';
    var urlcheckSession = '<?php echo Mirage::app()->createUrl('terminal/checkSession') ?>';
    var url_transfer = '<?php echo Mirage::app()->createUrl('loyalty/transferpoints') ?>'; 
    var url_activate = '<?php echo Mirage::app()->param['member_activation']?>'; 
    var url_tempactivate = '<?php echo Mirage::app()->param['temp_activation']?>'; 
    var servertime = '<?php echo date('m-d-Y H:i:s'); ?>';
    var AID = $('#acc_id').val();
    var siteid = <?php echo $_SESSION['AccountSiteID'] ?>;
    var sitecode = $('#sitecode').val();
    var card_number = $('#StartSessionFormModel_loyalty_card').val();
    // CCT - BEGIN uncomment
    var data = 'card_number='+card_number+'&isreg=0'+'&siteid='+siteid;
    // CCT - END uncomment
    var response = '';
    var tid = $("#StartSessionFormModel_terminal_id").val();
    var servicesCount = '';
    var serviceID = '';
    // CCT - BEGIN comment
    //card_number = 'UBT' + zeroPad(tid, 5);
    //var data = 'card_number='+card_number+'&isreg=0'+'&siteid='+siteid;
    // CCT - END comment

    if(tid =='')
    {
        $('.hideControls').hide();
        // CCT - BEGIN added
        //$('.hideControlsVIP').hide();
        // CCT - END added                       
        alert('Please select terminal');
        return false;
    }

    $.ajax(
    {
        type : 'post',
        async: false,
        url : urlGetCasinoServices,
        data : {'tid':tid},
        success : function(data) 
        {
            var obj = JSON.parse(data);
            servicesCount = obj.ServicesCount;
            serviceID = obj.ServiceID;
        },
        error : function(e) 
        {
            displayError(e);
        }

    });

    $.ajax(
    {
        type : 'post',
        async: false,
        url : url,
        data : data,
        success : function(data) 
        {
            try 
            {
                var json = $.parseJSON(data);

                //get status value
                var StatusValue = getStatusValue(json.CardInfo.StatusCode);

                //get isewallet value
                var IsEwallet = json.CardInfo.IsEwallet;

                if(StatusValue == "Active" || StatusValue == "Active Temporary" || StatusValue == "New Migrated"){

                    if(servicesCount == 1){
                        if(IsEwallet==1 && (serviceID==19 || serviceID==20))
                        {
                           isEwalletSessionMode = true;
                           $('.hideControls').hide();
                           // CCT - BEGIN added
                           //$('.hideControlsVIP').hide();
                           // CCT - END added                                          
//                                    }else if(IsEwallet==0 && serviceID==20){ //if card is not e-SAFE
//                                       $('.hideControls').hide();
//                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Only e-SAFE card can start a session on this terminal.</label>' +
//                                                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
//                                                                            ''          
//                                            ); 
                        }
                        else
                        {
                            isEwalletSessionMode = false;
                            isValidated = true;
                            $('.hideControls').show(); 
                            // CCT - BEGIN added
                            //if($('#viptypeVIP').is(':checked') || $('#viptypeSVIP').is(':checked'))
                            //{
                            //    $('.hideControlsVIP').show();
                            //}    
                            //else
                            //{
                            //    $('.hideControlsVIP').hide();
                            //}
                            // CCT - END added                                          
                        }
                        response = "false";
                    }
                    else
                    {
                        $('.hideControls').hide();
                        // CCT - BEGIN added
                        //$('.hideControlsVIP').hide();
                        // CCT - END added                                       
                        alert('More than 1 casinos are mapped in this terminal');
                    }
                } 
                else if (StatusValue == 'Banned Card')
                {
                    $('.hideControls').hide();
                    // CCT - BEGIN added
                    //$('.hideControlsVIP').hide();
                    // CCT - END added                                 
                    updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Player is on the National Database of Restricted Persons List.</label>' + 
                                    '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please ask Player to contact Customer Service Hotline.</label>' + 
                                    '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                    ''          
                    ); 
                } 
                else 
                {
                    $('.hideControls').hide();
                    // CCT - BEGIN added
                    //$('.hideControlsVIP').hide();
                    // CCT - END added                                   
                    showLightbox(function()
                    {
                        if(StatusValue == '')
                        {
                            updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[000]: Card Number is INVALID.</label>' + 
                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                            ''          
                            );    
                        }

                        if(StatusValue == undefined)
                        {
                            updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[001]: Card Number is INVALID.</label>' + 
                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                            ''          
                            );    
                        }

                        if(json.CardInfo.StatusCode == 100)
                        {
                            updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[002]: Card Number is INVALID.</label>' + 
                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                            ''          
                            );    
                        }

                        //check if the card number has a value, if none return invalid message
                        if(json.CardInfo.StatusCode != 8 )
                        {
                            if(StatusValue == "Old")
                            {
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
                            } 
                            else if(StatusValue == "Inactive Temporary")
                            {

                                if(json.CardInfo.DateVerified != null && json.CardInfo.DateVerified != undefined && json.CardInfo.DateVerified != "") 
                                {
                                    //formatting dateverified
                                    var dateStr= json.CardInfo.DateVerified;
                                    var a=dateStr.split(" ");
                                    var d=a[0].split("-");
                                    var t=a[1].split(":");
                                    var date = new Date(d[0],(d[1]-1),d[2],t[0],t[1],t[2]);
                                    var cooling_period =  json.CardInfo.CoolingPeriod;

                                    //get date difference between date today and the dateverified
                                    var datediff = getDateDiff(date,servertime, cooling_period);

                                    if(datediff != "false" && datediff != "true") 
                                    {
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Temporary Account is INACTIVE.</label>' + 
                                                        '<br /><br /><label style="font-size: 20px;">PAGCOR requires '+cooling_period+'-hour Cooling Period.</label>' + 
                                                        '<br /><label style="font-size: 20px;">Account <b>' + json.CardInfo.CardNumber + '</b> will be activated on</label>' + 
                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">'+ datediff +'.</label></center>' +
                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                        ''          
                                        );  
                                    } 
                                    else if(datediff == "false") 
                                    {
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is INVALID.</label>' + 
                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                        ''          
                                        ); 
                                    }
                                } 
                                else 
                                {
                                    updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is INVALID.</label>' + 
                                                    '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                    '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                    '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                    ''          
                                    ); 
                                }
                            } 
                            else if(StatusValue == "Inactive")
                            {
                                updateLightbox( '<div id = "popup"><br/><br />Please input Temporary Account Code. '  + 
                                                '<br /> Temporary Account:  <input type="text"  style="margin-left: 5px;text-align: center;font-size: 10px;font-weight: bold; padding: 3px;" id= "txtCardNumber"  />' +
                                                '<input type= "hidden" id="status" value ="' + StatusValue + '" />' +
                                                '<input type= "hidden" id="memcard" value ="' + card_number + '" />' +
                                                '<input type= "hidden" id="url" value ="' + url + '" />' +
                                                '<input type= "hidden" id="aid" value ="' + AID + '" />' +
                                                '<input type= "hidden" id="servertime" value ="' + servertime + '" />' +
                                                '<input type= "hidden" id="sitecode" value ="' + sitecode + '" />' +
                                                '<input type= "hidden" id="urlcheckSession" value ="' + urlcheckSession + '" />' +
                                                '<input type= "hidden" id="url_tempactivate" value ="' + url_tempactivate + '" />' +
                                                '<br /><br /><input type="button" style="font-size: 14px; margin-left:80px; width: 80px; height: 27px;"  value="Clear" class="btnClear"/>' +
                                                '<input type="button" style="font-size: 14px; margin-left:10px; width: 80px; height: 27px;"  value="Submit" onclick="javascript: btnSubmit();" class="btnSubmit"/></div>',
                                                '<a  class = "btnClose" id="fancyClose"></a>' , function(){ $("#txtCardNumber").focus(); }         
                                ); 
                            } 
                            else if(StatusValue == "Deactivated")
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is DEACTIVATED.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                ); 
                            } 
                            else if(StatusValue == "Old Migrated")
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; font-weight: bold;">VIP Rewards Card has been</label>' + 
                                                '<br /><label  style="font-size: 24px; font-weight: bold;">deactivated.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClr" />',
                                                ''          
                                ); 
                            } 
                        } 
                        else 
                        {
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
            } 
            catch(e) 
            {
                alert(e);
                //alert('Oops! Something went wrong');
            }
        },
        error : function(e) 
        {
            displayError(e);
        }
    });
    return response;
}

function identifyCard2()
{
    var url = '<?php echo Mirage::app()->createUrl('loyalty/cardinquiry') ?>'; 
    var url_transfer = '<?php echo Mirage::app()->createUrl('loyalty/transferpoints') ?>'; 
    var url_activate = '<?php echo Mirage::app()->param['member_activation']?>'; 
    var url_tempactivate = '<?php echo Mirage::app()->param['temp_activation']?>'; 

    var servertime = '<?php echo date('m-d-Y H:i:s'); ?>';
    var AID = $('#acc_id').val();
    var siteid = <?php echo $_SESSION['AccountSiteID'] ?>;
    var sitecode = $('#sitecode').val();
    var card_number = $('#UnlockTerminalFormModel_loyalty_card').val();
    // CCT - BEGIN uncomment
    var data = 'card_number='+card_number+'&isreg=0'+'&siteid='+siteid;                
    // CCT - END uncomment
    var response = '';
    // CCT - BEGIN comment
    //card_number = 'UBT' + zeroPad(tid, 5);
    //var data = 'card_number='+card_number+'&isreg=0'+'&siteid='+siteid;
    // CCT - END comment

    $.ajax(
    {
        type : 'post',
        async: false,
        url : url,
        data : data,
        success : function(data) 
        {
            try 
            {
                var json = $.parseJSON(data);

                //get status value
                 var StatusValue = getStatusValue(json.CardInfo.StatusCode);

                if(StatusValue == "Active" || StatusValue == "Active Temporary" || StatusValue == "New Migrated")
                {
                    response = "false";
                } 
                else if (StatusValue == 'Banned Card')
                {
                    updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Player is on the National Database of Restricted Persons List.</label>' + 
                                    '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please ask Player to contact Customer Service Hotline.</label>' + 
                                    '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                    ''          
                    ); 
                } 
                else 
                {
                    showLightbox(function()
                    {
                        if(StatusValue == '')
                        {
                            updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[000]: Card Number is INVALID.</label>' + 
                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                            ''          
                            );    
                        }

                        if(StatusValue == undefined)
                        {
                            updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[001]: Card Number is INVALID.</label>' + 
                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                            ''          
                            );    
                        }

                        if(json.CardInfo.StatusCode == 100)
                        {
                            updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[002]: Card Number is INVALID.</label>' + 
                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                            ''          
                            );    
                        }

                        //check if the card number has a value, if none return invalid message
                        if(json.CardInfo.StatusCode != 8 )
                        {
                            if(StatusValue == "Old")
                            {
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
                            } 
                            else if(StatusValue == "Inactive Temporary")
                            {

                                if(json.CardInfo.DateVerified != null && json.CardInfo.DateVerified != undefined && json.CardInfo.DateVerified != "") 
                                {
                                        //formatting dateverified
                                        var dateStr= json.CardInfo.DateVerified;
                                        var a=dateStr.split(" ");
                                        var d=a[0].split("-");
                                        var t=a[1].split(":");
                                        var date = new Date(d[0],(d[1]-1),d[2],t[0],t[1],t[2]);
                                        var cooling_period =  json.CardInfo.CoolingPeriod;

                                        //get date difference between date today and the dateverified
                                        var datediff = getDateDiff(date,servertime, cooling_period);

                                        if(datediff != "false" && datediff != "true") 
                                        {
                                            updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Temporary Account is INACTIVE.</label>' + 
                                                            '<br /><br /><label style="font-size: 20px;">PAGCOR requires '+cooling_period+'-hour Cooling Period.</label>' + 
                                                            '<br /><label style="font-size: 20px;">Account <b>' + json.CardInfo.CardNumber + '</b> will be activated on</label>' + 
                                                            '<br /><label style="font-size: 20px;  font-weight: bold;">'+ datediff +'.</label></center>' +
                                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                            ''          
                                            );  
                                        } 
                                        else if(datediff == "false") 
                                        {
                                            updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is INVALID.</label>' + 
                                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                            ''          
                                            ); 
                                        }
                                } 
                                else 
                                {
                                    updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                    ); 
                                }
                            } 
                            else if(StatusValue == "Inactive")
                            {
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
                            } 
                            else if(StatusValue == "Deactivated")
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is DEACTIVATED.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                ); 
                            } 
                            else if(StatusValue == "Old Migrated")
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; font-weight: bold;">VIP Rewards Card has been</label>' + 
                                                '<br /><label  style="font-size: 24px; font-weight: bold;">deactivated.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClr" />',
                                                ''          
                                ); 
                            } 
                        } 
                        else 
                        {
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
            } 
            catch(e) 
            {
                alert(e);
                //alert('Oops! Something went wrong');
            }
        },
        error : function(e) 
        {
            displayError(e);
        }
    });
    return response;
} 

//modified 11-13-2015 10:30 AM
function identifyCard3()
{
    var url = '<?php echo Mirage::app()->createUrl('loyalty/cardinquiry') ?>'; 
    var url_transfer = '<?php echo Mirage::app()->createUrl('loyalty/transferpoints') ?>'; 
    var url_activate = '<?php echo Mirage::app()->param['member_activation']?>'; 
    var url_tempactivate = '<?php echo Mirage::app()->param['temp_activation']?>'; 

    var servertime = '<?php echo date('m-d-Y H:i:s'); ?>';
    var AID = $('#acc_id').val();
    var siteid = <?php echo $_SESSION['AccountSiteID'] ?>;
    var sitecode = $('#sitecode').val();
    var card_number = $('#ForceTFormModel_loyalty_card').val();
    // CCT - BEGIN comment
    //card_number = 'UBT' + zeroPad(tid, 5);
    // CCT - END comment
    var data = 'card_number='+card_number+'&isreg=0'+'&siteid='+siteid;
    var response = '';
    var iswithdraw = $('#iswithdraw').val();

    //$('#btnLoad').attr('disabled','disabled'); //added 11-13-2015 10:30 AM
    //$('#btnWithdraw2').attr('disabled','disabled'); //added 11-13-2015 10:30 AM

    $.ajax(
    {
        type : 'post',
        async: false,
        url : url,
        data : data,
        success : function(data) 
        {
            try 
            {
                var json = $.parseJSON(data);

                //get status value
                 var StatusValue = getStatusValue(json.CardInfo.StatusCode);

                if(StatusValue == "Active" || StatusValue == "Active Temporary" || StatusValue == "New Migrated")
                {
                    if(iswithdraw == 1)
                    {
                        document.getElementById('player_name').innerHTML = json.CardInfo.MemberName;
                    }
                    response = "false";
                } 
                else if (StatusValue == 'Banned Card')
                {
                    updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Player is on the National Database of Restricted Persons List.</label>' + 
                                    '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please ask Player to contact Customer Service Hotline.</label>' + 
                                    '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" onclick="javascript: removeDisabled();" class="btnClose" />', 
                                    ''          
                    );  
                } 
                else 
                {
                    showLightbox(function()
                    {
                        if(StatusValue == '')
                        {
                            updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[000]: Card Number is INVALID.</label>' + 
                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" onclick="javascript: removeDisabled();" class="btnClose" />',
                                            ''          
                            );    
                        }

                        if(StatusValue == undefined)
                        {
                            updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[001]: Card Number is INVALID.</label>' + 
                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" onclick="javascript: removeDisabled();" class="btnClose" />',
                                            ''          
                            );    
                        }

                        if(json.CardInfo.StatusCode == 100)
                        {
                            updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[002]: Card Number is INVALID.</label>' + 
                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" onclick="javascript: removeDisabled();" class="btnClose" />',
                                            ''          
                            );    
                        }

                        //check if the card number has a value, if none return invalid message
                        if(json.CardInfo.StatusCode != 8 )
                        {
                            if(StatusValue == "Old")
                            {
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
                            } 
                            else if(StatusValue == "Inactive Temporary")
                            {
                                if(json.CardInfo.DateVerified != null && json.CardInfo.DateVerified != undefined && json.CardInfo.DateVerified != "") 
                                {
                                    //formatting dateverified
                                    var dateStr= json.CardInfo.DateVerified;
                                    var a=dateStr.split(" ");
                                    var d=a[0].split("-");
                                    var t=a[1].split(":");
                                    var date = new Date(d[0],(d[1]-1),d[2],t[0],t[1],t[2]);
                                    var cooling_period =  json.CardInfo.CoolingPeriod;

                                    //get date difference between date today and the dateverified
                                    var datediff = getDateDiff(date,servertime, cooling_period);

                                    if(datediff != "false" && datediff != "true") 
                                    {
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Temporary Account is INACTIVE.</label>' + 
                                                        '<br /><br /><label style="font-size: 20px;">PAGCOR requires '+cooling_period+'-hour Cooling Period.</label>' + 
                                                        '<br /><label style="font-size: 20px;">Account <b>' + json.CardInfo.CardNumber + '</b> will be activated on</label>' + 
                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">'+ datediff +'.</label></center>' +
                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" onclick="javascript: removeDisabled();" class="btnClose" />',
                                                        ''          
                                        );  
                                    } 
                                    else if(datediff == "false") 
                                    {
                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is INVALID.</label>' + 
                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" onclick="javascript: removeDisabled();" class="btnClose" />',
                                                        ''          
                                        ); 
                                    }
                                } 
                                else 
                                {
                                    updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is INVALID.</label>' + 
                                                    '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                    '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                    '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" onclick="javascript: removeDisabled();" class="btnClose" />',
                                                    ''          
                                    ); 
                                }
                            } 
                            else if(StatusValue == "Inactive")
                            {
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
                            } 
                            else if(StatusValue == "Deactivated")
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is DEACTIVATED.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" onclick="javascript: removeDisabled();" class="btnClose" />',
                                                ''          
                                ); 
                            } 
                            else if(StatusValue == "Old Migrated")
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; font-weight: bold;">VIP Rewards Card has been</label>' + 
                                                '<br /><label  style="font-size: 24px; font-weight: bold;">deactivated.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" onclick="javascript: removeDisabled();" class="btnClr" />',
                                                ''          
                                ); 
                            } 
                        } 
                        else 
                        {
                             updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[003]: Card Number is INVALID.</label>' + 
                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" onclick="javascript: removeDisabled();" class="btnClose" />',
                                            ''          
                            );    
                        }
                    });  
                   response = "true";
                }
            } 
            catch(e) 
            {
                alert(e);
                //alert('Oops! Something went wrong');
            }
        },
        error : function(e) 
        {
            displayError(e);
        }
    });
    return response;
}

//added 11-13-2015 10:30 AM
function removeDisabled() 
{
    $('#btnLoad').removeAttr('disabled');
    $('#btnWithdraw2').removeAttr('disabled');
}
</script>
