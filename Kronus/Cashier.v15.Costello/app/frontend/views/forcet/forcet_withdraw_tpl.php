<?php ?>
<?php Mirage::loadLibraries(array('CardScripts', 'LoyaltyScripts')); ?>
<script type="text/javascript" src="jscripts/validation.js"></script> 
<script type="text/javascript" src="jscripts/check_partner.js"></script>
<script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<script type="text/javascript">
    $('document').ready(function() {
        $('#ForceTFormModel_amount').autoNumeric();
    });
</script>

<table class="standalonetbl">
    <!--Added March 2016 @@ John Aaron Vida -->
    <input type="hidden" name="siteamountinfo" id="siteamountinfo" value="<?php echo $siteAmountInfo; ?>" />
    <!--Added June 13, 2016 @@ John Aaron Vida -->
    <input type="hidden" name="iswithdraw" id="iswithdraw" value="1" />
    <tr>
        <th><?php echo MI_HTML::label($FTModel, 'loyalty_card', 'Membership Card') ?></th>
        <td><?php echo MI_HTML::inputPassword($FTModel, 'loyalty_card') ?></td>
        <!--<td><a href="javascript:void(0);" id="get_info_card3">Get Card Info</a><a style="display: none;" href="javascript:void(0);" id="register">Register</a></td>-->
    </tr>

    <tr>
        <th>Current Playing Balance </th>
        <td id="cur_playing_bal_ub"></td>
    </tr>

    <tr>
        <th>Amount to be withdrawn </th>
        <td><?php echo MI_HTML::inputText($FTModel, 'amount', array('class' => 'auto', 'maxlength' => 10, 'value' => '0.00')) ?></td>
    </tr>

    <tr id="row_player_name">
        <th>Player Name</th>
        <td id="player_name"></td>
    </tr>

    <tr id="row_id_checked">
        <th>Presented a Valid ID </th>
        <td><input type="checkbox" name="id_checked" id="id_checked" /></td>
    </tr>

    <tr id="row_cs_validated">
        <th>Customer Service Validation </th>
        <td><input type="checkbox" name="cs_validated" id="cs_validated" /></td>
    </tr>

    <tr>
        <th><?php echo MI_HTML::label($FTModel, 'pin', 'PIN :') ?></th>
        <td><?php echo MI_HTML::inputPassword($FTModel, 'pin', array('maxlength' => 6)) ?></td>
    </tr>

    <tr>
        <td><input type="button" value="Withdraw" id="btnWithdraw2"/></td>
    </tr>                
</table>
<input type="hidden" id="getAmount"/>

<script type="text/javascript">
    function CommaFormatted(num)
    {
        num = num.toString().replace(/\$|\,/g, '');
        if (isNaN(num))
            num = "0";
        var sign = (num == (num = Math.abs(num)));
        num = Math.floor(num * 100 + 0.50000000001);
        var cents = num % 100;
        num = Math.floor(num / 100).toString();
        if (cents < 10)
            cents = "0" + cents;
        for (var i = 0; i < Math.floor((num.length - (1 + i)) / 3); i++)
            num = num.substring(0, num.length - (4 * i + 3)) + ',' + num.substring(num.length - (4 * i + 3));
        return (((sign) ? '' : '-') + num + '.' + cents);
    }
    $(document).ready(function() {
        /*
         * Added June 13, 2016
         * John Aaron Vida
         */
        var minAmount = '<?php echo Mirage::app()->param['minAmountWithdrawn'] ?>';
        $('#row_cs_validated').hide();
        $('#row_id_checked').hide();
        $('#row_player_name').hide();
        $('#ForceTFormModel_pin').attr('disabled', 'disabled');
        $('#ForceTFormModel_amount').attr('disabled', 'disabled');
        $('#btnWithdraw2').attr('disabled', 'disabled');

        if ($('#siteamountinfo').val() == 0) {
            showLightbox(function() {
                updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                        ''
                        );
            });
        }

        /*
         * Added June 13, 2016
         * John Aaron Vida
         */

        $("#ForceTFormModel_pin").keydown(function(e) {
            // Allow: backspace, delete, tab, escape, enter and .
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
                    // Allow: Ctrl+A
                            (e.keyCode == 65 && e.ctrlKey === true) ||
                            // Allow: Ctrl+C
                                    (e.keyCode == 67 && e.ctrlKey === true) ||
                                    // Allow: Ctrl+X
                                            (e.keyCode == 88 && e.ctrlKey === true) ||
                                            // Allow: home, end, left, right
                                                    (e.keyCode >= 35 && e.keyCode <= 39)) {
                                        // let it happen, don't do anything
                                        return;
                                    }
                                    // Ensure that it is a number and stop the keypress
                                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                                        e.preventDefault();
                                    }
                                });
                        /*
                         * Added June 13, 2016
                         * John Aaron Vida
                         */

                        $("#ForceTFormModel_amount").keydown(function() {
                            var amount = $("#ForceTFormModel_amount").val();
                            var convAmount = Number(amount.replace(/[^0-9\.]+/g, ""));
                            var getAmount = $("#getAmount").val();
                            var playingBal = parseFloat(getAmount.replace(/,/g, ''));
                            if (convAmount != '') {
                                if (playingBal < convAmount && convAmount >= minAmount) {
                                    $('#row_cs_validated').show();
                                    $('#row_id_checked').show();
                                    $('#row_player_name').show();
                                    $('#ForceTFormModel_pin').attr('disabled', 'disabled');
                                }
                                else if (convAmount >= minAmount && playingBal >= convAmount) {

                                    $('#row_cs_validated').show();
                                    $('#row_id_checked').show();
                                    $('#row_player_name').show();
                                    $('#ForceTFormModel_pin').attr('disabled', 'disabled');
                                }
                                else {
                                    $('#row_cs_validated').hide();
                                    $('#row_id_checked').hide();
                                    $('#row_player_name').hide();
                                    $('#ForceTFormModel_pin').attr('disabled', false);
                                }
                            } else {
                                $('#row_cs_validated').hide();
                                $('#row_id_checked').hide();
                                $('#row_player_name').hide();
                                $('#ForceTFormModel_pin').attr('disabled', 'disabled');
                                $('#btnWithdraw2').attr('disabled', 'disabled');
                            }
                        });
                        /*
                         * Added June 13, 2016
                         * John Aaron Vida
                         */
                        $('#id_checked').click(function() {
                            var id_checked = $("#id_checked").is(':checked');
                            var cs_validated = $("#cs_validated").is(':checked');
                            var amount = $("#ForceTFormModel_amount").val();
                            var convAmount = Number(amount.replace(/[^0-9\.]+/g, ""));

                            if (id_checked || cs_validated) {
                                $('#ForceTFormModel_pin').val('');
                                if (convAmount >= minAmount) {
                                    $('#ForceTFormModel_pin').attr('disabled', false);
                                    $('#btnWithdraw2').attr('disabled', 'disbaled');
                                }
                                else {
                                    $('#ForceTFormModel_pin').attr('disabled', 'disbaled');
                                    $('#btnWithdraw2').attr('disabled', 'disabled');
                                }
                            }
                            else {
                                $('#ForceTFormModel_pin').attr('disabled', 'disbaled');
                                $('#btnWithdraw2').attr('disabled', 'disabled');
                                $('#ForceTFormModel_pin').val('');
                            }

                        });
                        /*
                         * Added June 23, 2016
                         * John Aaron Vida
                         */
                        $('#cs_validated').click(function() {
                            var id_checked = $("#id_checked").is(':checked');
                            var cs_validated = $("#cs_validated").is(':checked');
                            var amount = $("#ForceTFormModel_amount").val();
                            var convAmount = Number(amount.replace(/[^0-9\.]+/g, ""));

                            if (id_checked || cs_validated) {
                                $('#ForceTFormModel_pin').val('');
                                if (convAmount >= minAmount) {
                                    $('#ForceTFormModel_pin').attr('disabled', false);
                                    $('#btnWithdraw2').attr('disabled', 'disbaled');
                                }
                                else {
                                    $('#ForceTFormModel_pin').attr('disabled', 'disbaled');
                                    $('#btnWithdraw2').attr('disabled', 'disabled');
                                }
                            }
                            else {
                                $('#ForceTFormModel_pin').attr('disabled', 'disbaled');
                                $('#btnWithdraw2').attr('disabled', 'disabled');
                                $('#ForceTFormModel_pin').val('');
                            }

                        });                        
                        /*
                         * Added June 13, 2016
                         * John Aaron Vida
                         */
                        $('#ForceTFormModel_loyalty_card').bind('keyup', function() {
                            $('#btnWithdraw2').removeAttr('disabled');
                            $('#ForceTFormModel_amount').val('');
                            $('#ForceTFormModel_pin').val('');
                            $('#row_player_name').hide();
                            $('#cs_validated').removeAttr('checked');
                            $('#id_checked').removeAttr('checked');

                        });
                        /*
                         * Added June 13, 2016
                         * John Aaron Vida
                         */
                        $('#ForceTFormModel_amount').bind('keyup', function() {
                            $('#btnWithdraw2').attr('disabled', 'disbaled');
                            $('#ForceTFormModel_pin').attr('disabled', 'disbaled');
                            //$('#ForceTFormModel_amount').val('');
                            $('#ForceTFormModel_pin').val('');
                            //$('#row_player_name').hide();
                            $('#cs_validated').removeAttr('checked');
                            $('#id_checked').removeAttr('checked');

                        });
                        /*
                         * Added June 13, 2016
                         * John Aaron Vida
                         */
                        function ShowHide() {
                            var amount = $("#ForceTFormModel_amount").val();
                            var convAmount = Number(amount.replace(/[^0-9\.]+/g, ""));
                            var getAmount = $("#getAmount").val();
                            var playingBal = parseFloat(getAmount.replace(/,/g, ''));
                            if (convAmount != '') {
                                if (playingBal < convAmount && convAmount >= minAmount) {
                                    if ($("#ForceTFormModel_amount").val() != '') {
                                        $('#row_cs_validated').show();
                                        $('#row_id_checked').show();
                                        $('#row_player_name').show();
                                    }
                                    else {
                                        $('#row_cs_validated').show();
                                        $('#row_id_checked').show();
                                        $('#row_player_name').show();
                                        $('#ForceTFormModel_pin').attr('disabled', 'disabled');
                                    }
                                }
                                else if (convAmount >= minAmount && playingBal >= convAmount) {
                                    if ($("#ForceTFormModel_amount").val() != '') {
                                        $('#row_cs_validated').show();
                                        $('#row_id_checked').show();
                                        $('#row_player_name').show();
                                    }
                                    else {
                                        $('#row_cs_validated').show();
                                        $('#row_id_checked').show();
                                        $('#row_player_name').show();
                                        $('#ForceTFormModel_pin').attr('disabled', 'disabled');
                                    }
                                }
                                else {
                                    $('#row_cs_validated').hide();
                                    $('#row_id_checked').hide();
                                    $('#row_player_name').hide();
                                    $('#ForceTFormModel_pin').attr('disabled', false);
                                }
                            }
                            else {
                                $('#row_cs_validated').hide();
                                $('#row_id_checked').hide();
                                $('#row_player_name').hide();
                                $('#ForceTFormModel_pin').attr('disabled', 'disabled');
                                $('#btnWithdraw2').attr('disabled', 'disabled');
                            }
                        }
                        /*
                         * Added June 13, 2016
                         * John Aaron Vida
                         */

                        $('#ForceTFormModel_loyalty_card, #ForceTFormModel_amount, #ForceTFormModel_pin').bind('keyup', function() {
                            ShowHide();
                            var balance = $("#cur_playing_bal_ub").text();
                            if(balance != ''){
                                $('#ForceTFormModel_amount').attr('disabled', false);
                            }else{
                                $('#ForceTFormModel_amount').attr('disabled', 'disabled');
                            }
                            var amount = $("#ForceTFormModel_amount").val();
                            var convAmount = Number(amount.replace(/[^0-9\.]+/g, ""));
                            var getAmount = $("#getAmount").val();
                            var playingBal = parseFloat(getAmount.replace(/,/g, ''));
                            if (convAmount != '') {
                                if (playingBal < convAmount && convAmount >= minAmount) {
                                    var card = $("#ForceTFormModel_loyalty_card").val();
                                    var pin = $("#ForceTFormModel_pin").val();
                                    var bal = $("#ForceTFormModel_amount").val();
                                    var id = $("#id_checked").is(':checked');
                                    var cs = $("#cs_validated").is(':checked');

                                    if (( id != ''|| cs != '') && card != '' && bal != '' && pin != '') {
                                        $('#btnWithdraw2').removeAttr('disabled');
                                    }
                                    else {
                                        $('#btnWithdraw2').attr('disabled', 'disabled');
                                    }
                                }
                                else if (convAmount >= minAmount && playingBal >= convAmount) {
                                    var card = $("#ForceTFormModel_loyalty_card").val();
                                    var pin = $("#ForceTFormModel_pin").val();
                                    var bal = $("#ForceTFormModel_amount").val();
                                    var id = $("#id_checked").is(':checked');
                                    var cs = $("#cs_validated").is(':checked');

                                    if (( id != ''|| cs != '') && card != '' && bal != '' && pin != '') {
                                        $('#btnWithdraw2').removeAttr('disabled');
                                    }
                                    else {
                                        $('#btnWithdraw2').attr('disabled', 'disabled');
                                    }
                                }
                                else {
                                    card = $("#ForceTFormModel_loyalty_card").val();
                                    pin = $("#ForceTFormModel_pin").val();

                                    if (card != '' && pin != '') {
                                        $('#btnWithdraw2').removeAttr('disabled');
                                    }
                                    else {
                                        $('#btnWithdraw2').attr('disabled', 'disabled');
                                    }
                                }
                            } else {
                                $('#ForceTFormModel_pin').attr('disabled', 'disabled');
                                $('#btnWithdraw2').attr('disabled', 'disabled');
                            }
                        });

                        $('#btnWithdraw2').click(function() {

                            if ($('#siteamountinfo').val() == 0) {
                                showLightbox(function() {
                                    updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                                            ''
                                            );
                                });
                            } else {

                                if ($('#ForceTFormModel_loyalty_card').val() == '') {
                                    alert('Please input membership card');
                                    return false;
                                }

                                var issuccess = identifyCard3();

                                if (issuccess == "false") {

                                    if ($('#ForceTFormModel_amount').val() == '') {
                                        alert('Please Enter Amount to be withdrawn');
                                        return false;
                                    }

                                    if ($('#ForceTFormModel_pin').val() == '') {
                                        alert('Please Enter PIN');
                                        return false;
                                    }

                                    if ($('#ForceTFormModel_amount').val() <= 0) {
                                        alert('Invalid Amount to be withdrawn');
                                        return false;
                                    }


                                    var amount = toMoney($("#ForceTFormModel_amount").val(), true);
                                    amount = Number(amount.replace(/[^0-9\.]+/g, ""));
                                    //get terminal code for blocking

                                    if (amount == '0.00') {
                                        alert('Indicated amount cannot be withdrawn.');
                                        return false;
                                    }

                                    if (parseFloat(amount) > 999999) {
                                        alert('Indicated amount cannot be withdrawn.');
                                        return false;
                                    }

                                    if (!confirm('Are you sure you want to withdraw from this account the amount of ' + toMoney(amount) + '?')) {
                                        return false;
                                    }

                                    $('#btnWithdraw2').attr('disabled', 'disabled'); //added 11-05-2015 2:40 PM

                                    var id = $("#id_checked").is(':checked');
                                    if (id) {
                                        var id_checked = 1;
                                    } else {
                                        id_checked = 0;
                                    }

                                    var cs = $("#cs_validated").is(':checked');
                                    if (cs) {
                                        var cs_validated = 1;
                                    } else {
                                        cs_validated = 0;
                                    }

                                    showLightbox(function() {
                                        var url = '<?php echo Mirage::app()->createUrl('redeem/redeemForcet') ?>';
                                        var data = {
                                            amount: function() {
                                                return $("#ForceTFormModel_amount").val();
                                            },
                                            pin: function() {
                                                return $("#ForceTFormModel_pin").val();
                                            },
                                            cardnumber: function() {
                                                return $("#ForceTFormModel_loyalty_card").val();
                                            },
                                            idchecked: id_checked,
                                            csvalidated: cs_validated
                                        };
                                        $.ajax({
                                            url: url,
                                            type: 'post',
                                            data: data,
                                            success: function(data) {
                                                try {
                                                    var json = $.parseJSON(data);
                                                    var msg = json.message;
                                                    if (msg.indexOf('successful') !== -1) {
                                                        alert(json.message);
                                                        $('#btnWithdraw2').removeAttr('disabled'); //added 11-05-2015 2:40 PM
                                                        location.reload(true);
                                                    }
                                                    else {
                                                        updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold;">' + json.message + '</label>' +
                                                                '<br /></center>' +
                                                                '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClr" />',
                                                                ''
                                                                );
                                                        $('#btnWithdraw2').removeAttr('disabled'); //added 11-05-2015 2:40 PM
                                                    }
                                                } catch (e) {
                                                    alert('Oops! Something went wrong');
                                                    $('#btnWithdraw2').removeAttr('disabled'); //added 11-05-2015 2:40 PM
                                                    location.reload(true);
                                                }

                                            },
                                            error: function(e) {
                                                displayError(e);
                                            }
                                        });
                                    });

                                <?php if ($_SESSION['spyder_enabled'] == 0): ?>
                                        try {
                                            var oaxPSMAC = new ActiveXObject("PEGS.StationManager.ActiveX.Controller");
                                            if (oaxPSMAC.LockScreen(terminalCode,<?php echo Mirage::app()->param['port'] ?>) != 1) {
                                                alert('<?php echo Mirage::app()->param['failed_lock'] ?> ' + terminalCode);
                                                if (!confirm('<?php echo Mirage::app()->param['failed_lock'] ?> ' + terminalCode + ". \n Do you want to continue?")) {
                                                    return false;
                                                }
                                            }
                                        } catch (e) {
                                            alert('<?php echo Mirage::app()->param['pegsstationerrormsg'] ?>');
                                        }
                                <?php endif; ?>

                                }
                                return false;
                            }
                        });

                        $('#ForceTFormModel_loyalty_card').bind("enterKey", function(e) {
                            var issuccess = identifyCard3();

                            var url = '<?php echo Mirage::app()->createUrl('forcet/getbalance') ?>';
                            var data = {loyalty_card: function() {
                                    return $("#ForceTFormModel_loyalty_card").val();
                                }};
                            $.ajax({
                                url: url,
                                type: 'post',
                                data: data,
                                success: function(data) {
                                    try {
                                        $('#cur_playing_bal_ub').html("");
                                        $('#cur_playing_bal_ub').html(data);
                                        $('#getAmount').val(data);
                                        $('#ForceTFormModel_amount').attr('disabled', false);

                                    } catch (e) {
                                        alert('Oops! Something went wrong');
                                        location.reload(true);
                                    }

                                },
                                error: function(e) {
                                    displayError(e);
                                }
                            });

//                if(issuccess == 'false'){
//                    var url = '<?php //echo Mirage::app()->createUrl('redeem/getbalance')      ?>';
//                    var data = 'loyalty_card='+$('#ForceTFormModel_loyalty_card').val();
//                    var tbody = '';
////                    var total_reload = 0;
//                    showLightbox(function(){
//                        $.ajax({
//                            type : 'post',
//                            url : url,
//                            data : data,
//                            success : function(data) {
//                                try {
//                                    var json = $.parseJSON(data);
//                                    $('#cur_playing_bal').html('PhP ' + json.amount);
//                                } catch(e) {
//                                    alert('Oops! Something went wrong');
//                                    location.reload(true);
//                                }
//                                hideLightbox();
//                            },
//                            error : function(e) {
//                                displayError(e);
//                            }
//                        });
//                    });
//                }
//                return false
                        });
                        $('#ForceTFormModel_loyalty_card').keyup(function(e) {
                            if (e.keyCode == 13)
                            {
                                $(this).trigger("enterKey");
                            } else {
                                $('#cur_playing_bal_ub').html("");
                            }
                        });

                        $('#showdetails').click(function() {
                            $('#reloadtbody').html('');
                            if ($(this).is(':checked') && $('#ForceTFormModel_terminal_id').val() != '') {
                                showLightbox(function() {
                                    var url = '<?php echo Mirage::app()->createUrl('withdraw/getdetail') ?>';
                                    var data = 'terminal_id=' + $('#ForceTFormModel_terminal_id').val();
                                    var tbody = '';
//                    var total_reload = 0;
                                    $.ajax({
                                        type: 'post',
                                        url: url,
                                        data: data,
                                        success: function(data) {
                                            try {
                                                var json = $.parseJSON(data);
                                                for (i = 0; i < json.trans_details.length; i++) {
                                                    tbody += '<tr>';
                                                    tbody += '<td>' + json.trans_details[i].TransType + '</td>';
                                                    tbody += '<td class="amount">' + toMoney(json.trans_details[i].Amount, 'no') + '</td>';
                                                    tbody += '<td>' + json.trans_details[i].DateCreated + '</td>';
//                                    tbody+='<td>'+json.trans_details[i].TerminalType+'</td>';
//                                    tbody+='<td>'+json.trans_details[i].Name+'</td>';
                                                    tbody += '</tr>';
                                                }
                                                $('#reloadtbody').html(tbody);
                                                hideLightbox();
                                            } catch (e) {
                                                alert('Oops! Something went wrong');
                                                location.reload(true);
                                            }
                                        },
                                        error: function(e) {
                                            displayError(e);
                                        }
                                    });
                                });
                            }
                        });
                    });
</script>