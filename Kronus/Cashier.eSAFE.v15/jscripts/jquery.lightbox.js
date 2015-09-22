/* 
 * Author: Bryan Salazar
 * Date: Nov. 2, 2011
 * Requirements: jqDnR.js, jqModal.js
 * Description: 
 * 
 */

function updateLightbox(data,header,oncomplete) {
    $('#fancybox-content').html('<h2>Loading ...</h2>');
    var title = '';
    if(header != undefined && header != null) {
        title = '<h2>'+header+'</h2>';
    }
    $.fancybox(title+data,{
            'autoDimensions'	: true,
            'width'             : 370,
            'height'        	: 'auto',
//            'transitionIn'		: 'none',
//            'transitionOut'		: 'none',
            'modal'             : true,
            'autoScale'         : false,
            'scrolling'         : true,
            onComplete : function(){
                if(oncomplete != undefined) {
                    oncomplete();
                }

            }            
        }
    );
    $.fancybox.center();    
}

function displayError(e) {
    if(e.responseText == '') {
        updateLightbox('<h1 style="color:red">Oops! Something went wrong</h1>');
    } else {
        updateLightbox('<h1 style="color:red">'+e.responseText+'</h1>');
    }
    setTimeout(function(){location.reload(true)}, 2000);
}

function messageLigthbox(message) {
    $('#innerLightbox').html('<h1>'+message+'</h1>');
    $( "#innerLightbox" ).draggable({ handle: 'h1',containment:'body'});
    $('#innerLightbox').jqResize('.jqResize');    
}

$('.btnClose').live('click',function(){
    hideLightbox();
});

//use for clearing textfield
$('.btnClear').live('click',function(){
    $("#txtCardNumber").removeAttr('disabled');
    $('#txtCardNumber').val('');
    $("#cardStatus").html("");
    $(".btnProceed").attr('disabled','disabled');
});

//use for clearing loyalty_card field
$('.btnClr').live('click',function(){
    $('#StartSessionFormModel_loyalty_card').val('');
    hideLightbox();
});

/*
*Description: Function for verifying membership card availability.
*/
function getMemInfo(){
        $("#txtCardNumber").attr('disabled', 'disabled');
        var card_number = $("#txtCardNumber").val();
        var url = $("#url").val();
        var data = 'card_number='+card_number+'&isreg=0';
        var message = "";

        $.ajax({
        type : 'post',
        url : url,
        data : data,
        success : function(data) {
            try {
                var json = $.parseJSON(data);
                var StatusValue = getStatusValue(json.CardInfo.StatusCode);
                if( StatusValue == "Deactivated" ){
                    showLightbox(function(){
                        updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is DEACTIVATED.</label>' + 
                                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                            ''
                        );
                    });
                } else if(StatusValue == "Banned Card") {
                        message = "<p style='color:red;'>Card is BANNED.</p>";
                        $(".btnProceed").attr('disabled','disabled');
                        $("#statusvalue").val(StatusValue);
                        $("#cardStatus").html(message);
                } else {
                    if(StatusValue == "Active"){
                            message = "<p style='color:green;'>Card is already active. Do you wish to transfer points from VIP Rewards Card to Membership Card?</p>";
                            $(".btnProceed").removeAttr("disabled");
                            $("#statusvalue").val(StatusValue);
                            $("#Mempoints").val(json.CardInfo.CurrentPoints);
                            $("#cardStatus").html(message);
                    } else if(StatusValue == "Inactive"){
                            message = "<p style='color:green;'>Card is Available.</p>";
                            $(".btnProceed").removeAttr("disabled");
                            $("#statusvalue").val(StatusValue);
                            $("#cardStatus").html(message);
                    } else {
                            message = "<p style='color:red;'>Invalid.</p>"+
                                                "<p style='color:red;'>Please input a valid card number.</p>";
                            $(".btnProceed").attr('disabled','disabled');
                            $("#cardStatus").html(message);
                    }
                }

            } catch(e) {
                alert(e);
                //alert('Oops! Something went wrong');
            }

        },
        error : function(e) {
            displayError(e);
        }
    })
}

/*
*Description: Function for displaying points of loyaltycard and membershipcard before transferring points.
*/
function btnProceed(){
    var status = $('#statusvalue').val();
    var loyaltycard = $('#loyaltycard').val();
    var membershipcard = $('#txtCardNumber').val();
    var loyaltypoints = $('#LYpoints').val();
    var mempoints = $("#Mempoints").val();
    var url_transfer = $("#url_transfer").val();
    var url_activate = $("#url_activate").val();
    var aid = $("#aid").val();
    var sitecode = $("#sitecode").val();
    var total = addCommas(Number(loyaltypoints) + Number(mempoints));

    if(status == "Active"){
        updateLightbox('<table style="top: 50%; left: 50%; margin-top: -100px; margin-left: -250px; position: fixed; background-color:#FFFFFF;"><tr></tr><tr></tr><tr><td><tr></tr><tr></tr><tr><td>&nbsp;VIP Rewards Card &nbsp;<input type="text" readonly  style="width: 180px;left:2px;margin-left: 5px;font-size: 10px;text-align: center;font-weight: bold; padding: 3px;" id= "rewardcard" value="' + loyaltycard + '" />' +
                                        '<input type = "text" style="width: 60px;left:2px;margin-left: 5px;font-size: 10px;text-align: center;font-weight: bold; padding: 3px; text-align: right;" id="loyaltypoints" readonly value = "'+  addCommas(loyaltypoints) +'" />&nbsp;</td></tr>' +
                                        '<tr><td><br /><div style="margin-top:10px;">&nbsp;Membership Card &nbsp;<input type="text" readonly style=" width: 180px;left:2px;margin-left: 5px;font-size: 10px;text-align: center;font-weight: bold; padding: 3px;" id= "membershipcard" value="' + membershipcard + '" />' +
                                        '<input type = "text" style="width: 60px;left:2px;margin-left: 5px;font-size: 10px;text-align: center;font-weight: bold; padding: 3px; text-align: right;" id="mempoints" readonly value = "'+  addCommas(mempoints) +'" />&nbsp;&nbsp;</td></tr>' + 
                                        '<tr><td><input type= "hidden" id="url_transfer" value ="' + url_transfer + '" />' +
                                        '<input type= "hidden" id="aid" value ="' + aid + '" />' +
                                        '<hr style ="margin-right: 5px; margin-top: 1px; width: 17%; display: block; float: right; font-weight: bold;" />' +
                                        '<br /><br />&nbsp;<input type = "text" style ="margin-top: -30px; margin-right: 7px;float: right; font-size: 10px;width: 60px;font-weight: bold; text-align: right;padding: 3px;" readonly value = "'+ total +'" /></td></tr>' + 
                                        '<tr><td><br /><br /><br /><input type="button" style="font-size: 14px; margin-left:120px; width: 80px; height: 27px;"  value="Back" class = "btnClr"/>' +
                                        '<input type="button" style="font-size: 14px; margin-left:10px; width: 80px; height: 27px;"  value="Transfer" onClick="javascript: btnTransfer();" /></td></tr><tr></tr><tr></tr><tr></tr><tr></tr><tr><td></table>', '');
    } else if(status == "Inactive"){
            hideLightbox();
            window.showModalDialog(url_activate+'?oldnumber='+loyaltycard+'&newnumber='+membershipcard+
                                                                '&site='+sitecode+'&AID='+aid,"MigrateAccount",'scroll: no;resizable:no; dialogHeight:380px; dialogWidth:600px;');
    }
}

/*
*Description: Function call for activation web page for temporary account.
*/
function btnSubmit(){
        var temp_card = $("#txtCardNumber").val();
        var memcard = $("#memcard").val();
        var url = $("#url").val();
        var url_tempactivate = $("#url_tempactivate").val();
        var aid = $("#aid").val();
        var sitecode = $("#sitecode").val();
        var servertime = $("#servertime").val();
        var data = 'card_number='+temp_card+'&isreg=1';
        
        $.ajax({
            type : 'post',
            url : url,
            data : data,
            success : function(data) {
                try {
                    var json = $.parseJSON(data);
                    
                     //get status value
                    var StatusValue = getStatusValue(json.CardInfo.StatusCode);
                    
                    if(StatusValue == "Active Temporary")
                    {
                        hideLightbox();
                        window.showModalDialog(url_tempactivate+'?tempnumber='+temp_card+'&newnumber='+memcard+
                                                                '&mid='+json.CardInfo.MID+'&site='+sitecode+'&aid='+aid+'&isreg=1','MigrateAccount','scroll: no;resizable:no; dialogHeight:380px; dialogWidth:600px; ');
                    } else if(StatusValue == "Banned Card") {
                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card is BANNED.</label>' + 
                                                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                        ''          
                        ); 
                    } else {
                        showLightbox(function(){                            
                            if(StatusValue == "Inactive Temporary"){
                                
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

                                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Temporary Account is INACTIVE.</label>' + 
                                                                        '<br /><br /><label style="font-size: 20px;">PAGCOR requires '+cooling_period+'-hour Cooling Period.</label>' + 
                                                                        '<br /><label style="font-size: 20px;">Account <b>' + json.CardInfo.CardNumber + '</b> will be activated on</label>' + 
                                                                        '<br /><label style="font-size: 20px;  font-weight: bold;">'+ datediff +'.</label></center>' +
                                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                        ''          
                                        ); 
                                } else {
                                    updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[009]: Card Number is INVALID.</label>' + 
                                                                    '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                    '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                    '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClr" />',
                                                                    ''          
                                    );     
                                }
                            } else {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[010]: Card Number is INVALID.</label>' + 
                                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClr" />',
                                                                ''          
                                );     
                            }
                        });
                    }

                } catch(e) {
                    alert(e);
                    //alert('Oops! Something went wrong');
                }

            },
            error : function(e) {
                displayError(e);
            }
        })
}

/*
*Description: Function call for transferring of points from old loyalty card to membership card.
*/
function btnTransfer(){
        var card_number = $("#rewardcard").val();
        var memcard_number = $("#membershipcard").val();
        var url_transfer = $("#url_transfer").val();
        var aid = $("#aid").val();
        var data = 'oldnumber='+card_number+'&newnumber='+memcard_number+'&aid='+aid;
        
        $.ajax({
            type : 'post',
            url : url_transfer,
            data : data,
            success : function(data) {
                try {
                    var json = $.parseJSON(data);
                    
                    if(json.CardPoints.StatusCode == 1){
                        updateLightbox( '<center><label  style="font-size: 24px; color: green; font-weight: bold;">Points transfer successful.</label>' + 
                                                        '<br/><label  style="padding-top: 2px; font-size: 24px; color: green; font-weight: bold;">VIP Rewards Card has been</label>' + 
                                                        '<br/><label  style="padding-top: 2px; font-size: 24px; color: green; font-weight: bold;">deactivated.</label>' + 
                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClr" />',
                                                        ''          
                        ); 
                    } else {
                        updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Failed to transfer points.</label>' + 
                                                        '<br/><label  style="font-size: 24px; color: red; font-weight: bold;">Please try again.</label>' +
                                                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                        ''          
                        ); 
                    }

                } catch(e) {
                    alert(e);
                }

            },
            error : function(e) {
                displayError(e);
            }
        })
}

function formatNumber(number)
{
    number = number.toFixed(2) + '';
    x = number.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1;
}


function hideLightbox() {
    $.fancybox.close();
    
}

function showLightbox(oncomplete) {
    $.fancybox('<h2>Loading ...</h2>',{
            'autoDimensions'	: true,
            'width'             : 370,
            'height'        	: 'auto',
            'transitionIn'		: 'none',
            'transitionOut'		: 'none',
            'modal'             : true,
            'autoScale'         : false,
            'scrolling'         : true,
            onComplete : function(){
                if(oncomplete != undefined) {
                    oncomplete();
                }

            }
        }
    );
    $.fancybox.center();
}
