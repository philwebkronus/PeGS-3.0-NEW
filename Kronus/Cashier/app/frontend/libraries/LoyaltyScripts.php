<script type="text/javascript">
    function registerCard() {
        window.showModalDialog("<?php echo Mirage::app()->param['register_account']; ?>?actno=<?php echo $_SESSION['AccountSiteID']?>","CardReg","dialogHeight:550px; dialogWidth:650px; resizable:0");
    }
    
    $(document).ready(function(){
        $('#register').live('click',function(){
            registerCard();
        });
        
        $('#get_info_card').live('click',function(){
            var url = '<?php echo Mirage::app()->createUrl('loyalty/cardinquiry') ?>'; 
            var isbgi = <?php echo $_SESSION['isbgi'] ?>;
            
            var card_number = $('#StartSessionFormModel_loyalty_card').val();
            if(card_number == '') {
                alert('Please barcode scan the Loyalty Card.');
                return false;
            }

            var data = 'card_number='+card_number+'&isreg=0';
            showLightbox(function(){
                $.ajax({
                    type : 'post',
                    url : url,
                    data : data,
                    success : function(data) {
                        try {
                            var json = $.parseJSON(data);
                            
                                //check if the card number has a value, if none return invalid message
                                if(json.CardInfo.StatusCode != '' && json.CardInfo.StatusCode != null && json.CardInfo.StatusCode != undefined && json.CardInfo.StatusCode != 100 &&  json.CardInfo.StatusCode != 8 &&  json.CardInfo.StatusCode != 4){
                                    var StatusValue = getStatusValue(json.CardInfo.StatusCode);
                                    var cardtype = getCardType(json.CardInfo.CardType);
                                    
                                    if(StatusValue == "Active Temporary"){
                                            updateLightbox( '<b>Temporary Account: </b>' + json.CardInfo.MemberUsername + 
                                                                            '<br /> <b>Account Status: </b>Active' + 
                                                                            '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                                            '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                                            '<br /> <b>Current Points Balance: </b>' + json.CardInfo.CurrentPoints +
                                                                            '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                                            ''          
                                            );
                                    } else if(StatusValue == "Inactive Temporary"){
                                                updateLightbox( '<b>Temporary Account: </b>' + json.CardInfo.MemberUsername + 
                                                                                '<br /> <b>Account Status: </b>Inactive' + 
                                                                                '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                                                '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                                                '<br /> <b>Current Points Balance: </b>' + json.CardInfo.CurrentPoints +
                                                                                '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                                                ''          
                                                );
                                    }else {
                                        updateLightbox( '<b>Card Number: </b>' + json.CardInfo.CardNumber + 
                                                                        '<br /> <b>Card Status: </b>' + StatusValue + 
                                                                        '<br /> <b>Card Type: </b>' + cardtype +
                                                                        '<br /> <b>Account Name: </b>' + json.CardInfo.MemberName +
                                                                        '<br /> <b>Birthday: </b>' + json.CardInfo.Birthdate +
                                                                        '<br /><input type="button" style=" margin-left:220px; width: 50px; height: 25px;"  value="Ok" class="btnClose"/>',
                                                                        ''          
                                        );
                                    }
                                } else {
                                     updateLightbox( '<center><label  style="font-size: 24px; color: red; font-weight: bold;">Card Number is INVALID.</label>' + 
                                                                    '<br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' + 
                                                                    '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' + 
                                                                    '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                                                    ''          
                                    );    
                                }
                                 
                        } catch(e) {
                            alert(e);
                            //alert('Oops! Something went wrong');
                        }
                        
                        hideLightbox();
                    },
                    error : function(e) {
                        displayError(e);
                    }
                })
            });
        });
    });
    
    /*
   **@Description: Event after the barcode was scanned
   */
    $("#StartSessionFormModel_loyalty_card").focus(function(){
            $("#StartSessionFormModel_loyalty_card").bind("keydown", function (event) { 
                if (event.keyCode == 13 || event.charCode == 13) {
                    var issuccess = identifyCard();
                    if(issuccess == "false"){
                        return false;
                    }
                }
            });  
    });
    
</script>