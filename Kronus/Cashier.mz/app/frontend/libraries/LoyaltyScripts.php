<script type="text/javascript">
    function registerCard() 
    {
        window.showModalDialog("<?php echo Mirage::app()->param['register_account']; ?>?actno=<?php echo $_SESSION['AccountSiteID']?>","CardReg","dialogHeight:550px; dialogWidth:650px; resizable:0");
    }
    
    $(document).ready(function()
    {
        $('#register').live('click',function(){
            registerCard();
        });
        
        $('#get_info_card').live('click',function()
        {
            var url = '<?php echo Mirage::app()->createUrl('loyalty/cardinquiry') ?>'; 
            var siteid = <?php echo $_SESSION['AccountSiteID'] ?>;
            
            var card_number = $('#StartSessionFormModel_loyalty_card').val();
            if(card_number == '') 
            {
                alert('Please barcode scan the VIP Reward Card.');
                return false;
            }

            var data = 'card_number='+card_number+'&isreg=0'+'&siteid='+siteid;
            
            showLightbox(function()
            {
                $.ajax(
                {
                    type : 'post',
                    url : url,
                    data : data,
                    success : function(data) 
                    {
                        try 
                        {
                            var json = $.parseJSON(data);
                            if(json.CardInfo.StatusCode == '' || json.CardInfo.StatusCode == null)
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[004]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            if(json.CardInfo.StatusCode == undefined )
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[005]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            if(json.CardInfo.StatusCode == 100)
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[006]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            if(json.CardInfo.StatusCode == 4)
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[007]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            //check if the card number has a value, if none return invalid message
                            if(json.CardInfo.StatusCode != 8)
                            {
                                var StatusValue = getStatusValue(json.CardInfo.StatusCode);
                                var cardtype = getCardType(json.CardInfo.CardType);

                                if(StatusValue == "Active Temporary")
                                {
                                    updateLightbox( '<b>Temporary Account: </b>' + json.CardInfo.MemberUsername + 
                                                    '<br /> <b>Account Status: </b>Active' + 
                                                    '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                    '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                    '<br /> <b>Current Points Balance: </b>' + json.CardInfo.CurrentPoints +
                                                    '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                    ''          
                                    );
                                } 
                                else if(StatusValue == "Inactive Temporary")
                                {
                                    updateLightbox( '<b>Temporary Account: </b>' + json.CardInfo.MemberUsername + 
                                                    '<br /> <b>Account Status: </b>Inactive' + 
                                                    '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                    '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                    '<br /> <b>Current Points Balance: </b>' + json.CardInfo.CurrentPoints +
                                                    '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                    ''          
                                    );
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
                                    updateLightbox( '<b>Card Number: </b>' + json.CardInfo.CardNumber + 
                                                    '<br /> <b>Card Status: </b>' + StatusValue + 
                                                    //'<br /> <b>Card Type: </b>' + cardtype +
                                                    '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                    '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                    '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                    ''          
                                    );
                                }
                            } 
                            else 
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[008]: Card Number is INVALID.</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }
                        } 
                        catch(e) 
                        {
                            alert(e);
                            //alert('Oops! Something went wrong');
                        }
                        hideLightbox();
                    },
                    error : function(e) 
                    {
                        displayError(e);
                    }
                })
            });
        });
        
        $('#get_info_card2').live('click',function()
        {
            var url = '<?php echo Mirage::app()->createUrl('loyalty/cardinquiry') ?>'; 
            var siteid = <?php echo $_SESSION['AccountSiteID'] ?>;
            
            var card_number = $('#UnlockTerminalFormModel_loyalty_card').val();
            if(card_number == '') 
            {
                alert('Please barcode scan the VIP Reward Card.');
                return false;
            }

            var data = 'card_number='+card_number+'&isreg=0'+'&siteid='+siteid;
            
            showLightbox(function()
            {
                $.ajax(
                {
                    type : 'post',
                    url : url,
                    data : data,
                    success : function(data) 
                    {
                        try 
                        {
                            var json = $.parseJSON(data);
                            if(json.CardInfo.StatusCode == '' || json.CardInfo.StatusCode == null)
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[004]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            if(json.CardInfo.StatusCode == undefined )
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[005]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            if(json.CardInfo.StatusCode == 100)
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[006]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            if(json.CardInfo.StatusCode == 4)
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[007]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            //check if the card number has a value, if none return invalid message
                            if(json.CardInfo.StatusCode != 8)
                            {
                                var StatusValue = getStatusValue(json.CardInfo.StatusCode);
                                var cardtype = getCardType(json.CardInfo.CardType);

                                if(StatusValue == "Active Temporary")
                                {
                                    updateLightbox( '<b>Temporary Account: </b>' + json.CardInfo.MemberUsername + 
                                                    '<br /> <b>Account Status: </b>Active' + 
                                                    '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                    '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                    '<br /> <b>Current Points Balance: </b>' + json.CardInfo.CurrentPoints +
                                                    '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                    ''          
                                    );
                                } 
                                else if(StatusValue == "Inactive Temporary")
                                {
                                    updateLightbox( '<b>Temporary Account: </b>' + json.CardInfo.MemberUsername + 
                                                    '<br /> <b>Account Status: </b>Inactive' + 
                                                    '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                    '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                    '<br /> <b>Current Points Balance: </b>' + json.CardInfo.CurrentPoints +
                                                    '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                    ''          
                                    );
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
                                    updateLightbox( '<b>Card Number: </b>' + json.CardInfo.CardNumber + 
                                                    '<br /> <b>Card Status: </b>' + StatusValue + 
                                                    //'<br /> <b>Card Type: </b>' + cardtype +
                                                    '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                    '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                    '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                    ''          
                                    );
                                }
                            } 
                            else 
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[008]: Card Number is INVALID.</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }
                        } 
                        catch(e) 
                        {
                            alert(e);
                            //alert('Oops! Something went wrong');
                        }
                        hideLightbox();
                    },
                    error : function(e) 
                    {
                        displayError(e);
                    }
                });
            });
        });
        
        $('#get_info_card3').live('click',function()
        {
            var url = '<?php echo Mirage::app()->createUrl('loyalty/cardinquiry') ?>'; 
            var siteid = <?php echo $_SESSION['AccountSiteID'] ?>;
            
            var card_number = $('#ForceTFormModel_loyalty_card').val();
            if(card_number == '') 
            {
                alert('Please barcode scan the VIP Reward Card.');
                return false;
            }

            var data = 'card_number='+card_number+'&isreg=0'+'&siteid='+siteid;
            
            showLightbox(function()
            {
                $.ajax(
                {
                    type : 'post',
                    url : url,
                    data : data,
                    success : function(data) 
                    {
                        try 
                        {
                            var json = $.parseJSON(data);
                            if(json.CardInfo.StatusCode == '' || json.CardInfo.StatusCode == null)
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[004]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            if(json.CardInfo.StatusCode == undefined )
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[005]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            if(json.CardInfo.StatusCode == 100)
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[006]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            if(json.CardInfo.StatusCode == 4)
                            {
                                updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[007]: Card Number is INVALID.</label>' + 
                                                '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }

                            //check if the card number has a value, if none return invalid message
                            if(json.CardInfo.StatusCode != 8)
                            {
                                var StatusValue = getStatusValue(json.CardInfo.StatusCode);
                                var cardtype = getCardType(json.CardInfo.CardType);

                                if(StatusValue == "Active Temporary")
                                {
                                    updateLightbox( '<b>Temporary Account: </b>' + json.CardInfo.MemberUsername + 
                                                    '<br /> <b>Account Status: </b>Active' + 
                                                    '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                    '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                    '<br /> <b>Current Points Balance: </b>' + json.CardInfo.CurrentPoints +
                                                    '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                    ''          
                                    );
                                } 
                                else if(StatusValue == "Inactive Temporary")
                                {
                                    updateLightbox( '<b>Temporary Account: </b>' + json.CardInfo.MemberUsername + 
                                                    '<br /> <b>Account Status: </b>Inactive' + 
                                                    '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                    '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                    '<br /> <b>Current Points Balance: </b>' + json.CardInfo.CurrentPoints +
                                                    '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                    ''          
                                    );
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
                                    updateLightbox( '<b>Card Number: </b>' + json.CardInfo.CardNumber + 
                                                    '<br /> <b>Card Status: </b>' + StatusValue + 
                                                    //'<br /> <b>Card Type: </b>' + cardtype +
                                                    '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                    '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                    '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                    ''          
                                    );
                                }
                            } 
                            else 
                            {
                                 updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Error[008]: Card Number is INVALID.</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                ''          
                                );    
                            }
                        } 
                        catch(e) 
                        {
                            alert(e);
                            //alert('Oops! Something went wrong');
                        }
                        hideLightbox();
                    },
                    error : function(e) 
                    {
                        displayError(e);
                    }
                });
            });
        });
    });
    
    /*
   **@Description: Event after the barcode was scanned
   */
    $("#StartSessionFormModel_loyalty_card").focus(function()
    {
        $("#StartSessionFormModel_loyalty_card").bind("keydown", function (event) 
        { 
//                if (event.keyCode == 13 || event.charCode == 13 || event.keyCode==9) {
//                    var cardNumber = $('#StartSessionFormModel_loyalty_card').val();
//                    if(cardNumber==''){
//                        alert('Please enter loyalty card number.');
//                        return false;
//                    }
//                    var issuccess = identifyCard();
//                    if(issuccess == "false"){
//                        
//                        $('.btnSubmit').focus();
//                        $('#StartSessionFormModel_sel_amount').focus();
//                        return false;
//                    }
//                }
                
            if (event.keyCode == 8 || event.charCode == 8) 
            {
                 // CCT - BEGIN uncomment
                $('.hideControls').hide();
                // CCT - END uncomment
                $('.bankContainer').hide();
                // CCT - BEGIN added
                //$('.hideControlsVIP').hide();
                //$('#StartSessionFormModel_vip_type').val(0);  
                //$('#StartSessionFormModel_lvip_type').val(0);                   
                // CCT - END added
                isEwalletSessionMode = false;
                isValidated = false;
                $('#StartSessionFormModel_sel_amount').val(0);
                $('#StartSessionFormModel_amount').val('');
                $('#StartSessionFormModel_voucher_code').val('');
                $('#StartSessionFormModel_trace_number').val('');
                $('#StartSessionFormModel_reference_number').val('');
            }
        });  
    });
    
    $("#UnlockTerminalFormModel_loyalty_card").focus(function()
    {
        $("#UnlockTerminalFormModel_loyalty_card").bind("keydown", function (event) 
        { 

            if (event.keyCode == 13 || event.charCode == 13) 
            {

                var issuccess = identifyCard2();
                if(issuccess == "false")
                {
                    return false;
                }
                event.preventDefault();
            }
        });  
    });
    
</script>