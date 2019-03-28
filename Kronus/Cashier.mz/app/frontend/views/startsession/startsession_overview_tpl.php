<?php Mirage::loadLibraries(array('CardScripts', 'LoyaltyScripts')); ?>
<script type="text/javascript" src="jscripts/validation.js"></script> 
<script type="text/javascript" src="jscripts/check_partner.js"></script>
<script type="text/javascript">
    $(document).ready(function()
    {
        if ($('#siteamountinfo').val() == 0)
        {
            showLightbox(function()
            {
                updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                        ''
                        );
            });
        }

        if ($('#siteclassification').val() == 3)
        {
            $('#loyalty_card_tr').css('display', 'none');
        }

        // CCT - BEGIN uncomment
        $('.hideControls').hide();
        // CCT - END uncomment
        // CCT - BEGIN added VIP
        //$('.hideControlsVIP').hide();
        //$('#StartSessionFormModel_vip_type').val(0);           
        //$('#StartSessionFormModel_lvip_type').val(0);  
        // CCT - END added VIP    

        $('#StartSessionFormModel_terminal_id').focus();
        $('#chkbancnet').removeAttr('disabled');
        $('#StartSessionFormModel_amount').autoNumeric();
        $('#btnstartsessionsa').click(function()
        {

            if ($('#siteamountinfo').val() == 0)
            {
                showLightbox(function()
                {
                    updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                            '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                            '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                            '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                            ''
                            );
                });
            }
            else
            {
                var usermode = $('#mode').val();
                var voucher = $("#StartSessionFormModel_voucher_code").val();
                if (usermode != 2)
                {

                    if (!startSessionStandAloneChecking())
                    {
                        return false;
                    }
                    else
                    {
                        var issuccess = identifyCard();
                    }
                }
                else
                {
                    if (!startSessionAloneeBingoChecking())
                    {
                        return false;
                    }
                    else
                    {
                        var issuccess = "false";
                        isEwalletSessionMode = false;
                    }
                }

                if (issuccess == "false")
                {
                    //check voucher length
                    if (isEwalletSessionMode == false)
                    {
                        if (voucher.length == 0)
                        {
                            //alert(toMoney($('#StartSessionFormModel_amount').val()));return false;
                            if (!confirm('Are you sure you want to start a new session with the initial playing balance of  ' + toMoney($('#StartSessionFormModel_amount').val()) + '?'))
                            {
                                return false;
                            }
                        }
                        else
                        {
                            $("#StartSessionFormModel_voucher_code").val(voucher);
                            if (!confirm('Are you sure you want to start a new session using a voucher?'))
                            {
                                return false;
                            }
                        }
                    }
                    else
                    {
                        if (!confirm('Are you sure you want to start a session in this terminal?'))
                        {
                            return false;
                        }
                    }

                    //get terminal code for blocking
                    var preffixCode = '<?php echo (!empty($_SESSION['last_code']) ? $_SESSION['last_code'] : ""); ?>';
                    var terminalCode = $('#StartSessionFormModel_terminal_id > option:selected').html();

                    terminalCode = terminalCode.replace(/vip/i, '');
                    terminalCode = preffixCode + terminalCode;

                    // CCT BEGIN Addded
                    $('#btnstartsessionsa').attr('disabled', 'disabled');
                    // CCT END Added

                    showLightbox(function()
                    {
                        url = '<?php echo Mirage::app()->createUrl('startsession') ?>';
                        data = $('#frmstartsessionsa').serialize();
                        $.ajax(
                                {
                                    type: 'post',
                                    data: data,
                                    url: url,
                                    success: function(data)
                                    {
                                        try
                                        {
                                            var json = $.parseJSON(data);
<?php if ($_SESSION['spyder_enabled'] == 0): ?>
                                                try
                                                {
                                                    var oaxPSMAC = new ActiveXObject("PEGS.StationManager.ActiveX.Controller");
                                                    if (oaxPSMAC.UnlockScreen(terminalCode,<?php echo Mirage::app()->param['port'] ?>) != 1)
                                                    {
                                                        alert('<?php echo Mirage::app()->param['failed_unlock'] ?> ' + terminalCode);
                                                    }
                                                }
                                                catch (e)
                                                {
                                                    alert('<?php echo Mirage::app()->param['pegsstationerrormsg'] ?>');
                                                }
<?php endif; ?>
                                            var unlock = json.Unlock;
                                            if (unlock == 1)
                                            {
                                                alert('Transaction Successful Terminal is now unlocked.');
                                            }
                                            else
                                            {
                                                alert('Transaction Successful \n New player session started. The player initial playing balance is PhP ' + json.initial_deposit);
                                            }
                                        }
                                        catch (e)
                                        {
                                            alert('Oops! Something went wrong');
                                        }
                                        //revised CCT BEGIN
                                        //location.reload(true);
                                        window.location.reload(true);
                                        //revised CCT END                                
                                    },
                                    error: function(e)
                                    {
                                        displayError(e);
                                    }
                                });
                    });
                }
                return false;
            }
        });

        // change denomination
        $('#StartSessionFormModel_sel_amount').live('change', function()
        {
            if ($(this).val() != '--')
            {
                $('#StartSessionFormModel_amount').val($(this).children('option:selected').html());
                if ($(this).val() == '')
                    $('#StartSessionFormModel_amount').val('');
            }
        });

        $('#StartSessionFormModel_terminal_id').change(function()
        {
            $('#StartSessionFormModel_casino').html('<option value="">Select Casino</option>');
            $('#StartSessionFormModel_sel_amount').html('<option value="">Select Amount</option>');
            $('#StartSessionFormModel_amount').val('');
            $('#StartSessionFormModel_max_deposit').val('');
            $('#StartSessionFormModel_min_deposit').val('');
            $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
            $('#chkotheramount').attr('disabled', 'disabled');
            $('#chkotheramount').removeAttr('checked');
            $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
            if ($(this).val() == '')
            {
                return false;
            }

            if (!checkPartner($(this).children('option:selected').html()))
            {
                return false;
            }

            showLightbox(function()
            {
                url = '<?php echo $this->createUrl('terminal/denomination'); ?>';
                data = {terminal_id: $('#StartSessionFormModel_terminal_id').val()};
                $.ajax(
                        {
                            type: 'post',
                            url: url,
                            data: data,
                            success: function(data)
                            {
                                $('#chkotheramount').removeAttr('disabled');
                                try
                                {
                                    var json = $.parseJSON(data);
                                    var casopt = '';
                                    var opt = '';

                                    if (json.casinousermode == 4)
                                    {
                                        $('#loyalty_card_tr').css('display', 'block');
                                        $('#mode').val(json.casinousermode);
                                        $('.hideControls').hide();
                                        // CCT - BEGIN added VIP
                                        //if($('#viptypeVIP').is(':checked') || $('#viptypeSVIP').is(':checked'))
                                        //{
                                        //    $('.hideControlsVIP').show();
                                        //}    
                                        //else
                                        //{
                                        //    $('.hideControlsVIP').hide();
                                        //}
                                        // CCT - END added VIP          
                                        var ebingo_denom = <?php echo $eBingoDenomination ?>;

                                        $.each(ebingo_denom, function(k, v)
                                        {
                                            opt += '<option value="' + k + '" >' + v + '</option>';
                                        });
                                        $('#StartSessionFormModel_sel_amount').append(opt);
                                        $.each(json.casino, function(k, v)
                                        {
                                            casopt += '<option value="' + k + '" >' + v + '</option>';
                                        });
                                    }
                                    else if (json.casinousermode == 2)
                                    {
                                        $('#loyalty_card_tr').css('display', 'none');
                                        $('#mode').val(json.casinousermode);
                                        $('.hideControls').show();
                                        // CCT - BEGIN added VIP
                                        //if($('#viptypeVIP').is(':checked') || $('#viptypeSVIP').is(':checked'))
                                        //{
                                        //    $('.hideControlsVIP').show();
                                        //}    
                                        //else
                                        //{
                                        //    $('.hideControlsVIP').hide();
                                        //}
                                        // CCT - END added VIP       

                                        $.each(json.denomination, function(k, v)
                                        {
                                            opt += '<option value="' + k + '" >' + v + '</option>';
                                        });
                                        $('#StartSessionFormModel_sel_amount').append(opt);
                                        $.each(json.casino, function(k, v)
                                        {
                                            casopt += '<option value="' + k + '" >' + v + '</option>';
                                        });
                                    }
                                    else
                                    {
                                        $('#loyalty_card_tr').css('display', 'block');
                                        // CCT - BEGIN uncomment
                                        $('.hideControls').hide();
                                        // CCT - END uncomment
                                        // CCT - BEGIN added VIP
                                        //if($('#viptypeVIP').is(':checked') || $('#viptypeSVIP').is(':checked'))
                                        //{
                                        //    $('.hideControlsVIP').show();
                                        //}    
                                        //else
                                        //{
                                        //    $('.hideControlsVIP').hide();
                                        //}
                                        // CCT - END added VIP 

                                        $.each(json.denomination, function(k, v)
                                        {
                                            opt += '<option value="' + k + '" >' + v + '</option>';
                                        });
                                        $('#StartSessionFormModel_sel_amount').append(opt);
                                        $.each(json.casino, function(k, v)
                                        {
                                            casopt += '<option value="' + k + '" >' + v + '</option>';
                                        });
                                    }

                                    $('#StartSessionFormModel_casino').html(casopt);
                                    // console.log(json.max_denomination);
                                    $('#StartSessionFormModel_max_deposit').val(json.max_denomination);
                                    $('#StartSessionFormModel_min_deposit').val(json.min_denomination);
                                    hideLightbox();
                                }
                                catch (e)
                                {
                                    alert('Oops! Something went wrong');
                                    //revised CCT BEGIN
                                    //location.reload(true);
                                    window.location.reload(true);
                                    //revised CCT END                            
                                }
                            },
                            error: function(e)
                            {
                                displayError(e);
                            }
                        });
            });

            $('#StartSessionFormModel_loyalty_card').val('');

            // CCT - BEGIN uncomment
            $('.hideControls').hide();
            // CCT - END uncomment
            // CCT - BEGIN added VIP
            //$('.hideControlsVIP').hide();
            // CCT - END added VIP   
        });

        $('#lblotheramount').click(function()
        {
            if ($('#StartSessionFormModel_terminal_id').val() == '')
            {
                return false;
            }
            $('#StartSessionFormModel_amount').val('');
            $('#StartSessionFormModel_sel_amount').val('');
            if ($('#chkotheramount').is(':checked'))
            {
                $('#chkotheramount').removeAttr('checked');
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
            }
            else
            {
                $('#chkotheramount').attr('checked', 'checked');
                $('#StartSessionFormModel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').val(0.00);
                $('#StartSessionFormModel_sel_amount').attr('disabled', 'disabled');
            }
        });

        $('#chkotheramount').click(function()
        {
            if ($(this).is(':checked'))
            {
                $('#StartSessionFormModel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').val(0.00);
                $('#StartSessionFormModel_sel_amount').attr('disabled', 'disabled');
                $('#StartSessionFormModel_voucher_code').attr('disabled', 'disabled');
                $('#StartSessionFormModel_sel_amount').val('');
                $('#StartSessionFormModel_voucher_code').val('');
            }
            else
            {
                $('#StartSessionFormModel_amount').val('');
                $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
            }
        });

        //for mouseout event for initial deposit and voucher fields
        $('#StartSessionFormModel_sel_amount').mouseout(function()
        {
            if ($('#chkotheramount').is(':checked'))
            {
                //voucher code textbox  will stay disabled..
            }
            else
            {
                $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
            }
        });

        $('#StartSessionFormModel_voucher_code').mouseout(function()
        {
            if ($('#chkotheramount').is(':checked'))
            {
                //sel_amount drop down  will stay disabled..
            } else
            {
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                if ($('#StartSessionFormModel_voucher_code').val() != '')
                {
                    $('#StartSessionFormModel_amount').val('');
                }
            }
        });
        //-------------------------end code for mouseout event------------------------------//

        //onfocus event for initial deposit, other amount and voucher fields
        $('#StartSessionFormModel_voucher_code').focus(function()
        {
            $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
            $('#StartSessionFormModel_sel_amount').attr('disabled', 'disabled');
            //for keypress event, allow alphanumeric values in voucher code
            $("#StartSessionFormModel_voucher_code").bind("keypress", function(event)
            {
                if (event.charCode != 0)
                {
                    var regex = new RegExp("^[a-zA-Z0-9]+$");
                    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                    if (!regex.test(key))
                    {
                        event.preventDefault();
                        return false;
                    }
                }

                //clear amount textbox when keypress event start in voucher code textbox
                if ($('#StartSessionFormModel_voucher_code').val() != '')
                {
                    $('#StartSessionFormModel_sel_amount').val('');
                    $('#StartSessionFormModel_amount').val('');
                    $('#chkotheramount').removeAttr('checked');
                    $('#StartSessionFormModel_amount').removeAttr('disabled');
                    $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
                }
            });

            //clear amount textbox when voucher code textbox has value
            if ($('#StartSessionFormModel_voucher_code').val() != '')
            {
                $('#StartSessionFormModel_sel_amount').val('');
                $('#StartSessionFormModel_amount').val('');
                $('#chkotheramount').removeAttr('checked');
                $('#StartSessionFormModel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
            }
            //-------------------end code for keypress event---------------------//
        });

        $('#StartSessionFormModel_sel_amount').focus(function()
        {
            $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
            $('#StartSessionFormModel_voucher_code').attr('disabled', 'disabled');
            $('#StartSessionFormModel_sel_amount').change(function()
            {
                if ($('#StartSessionFormModel_sel_amount').val() != '')
                {
                    $('#StartSessionFormModel_voucher_code').val('');
                }
            });
        });

        $('#StartSessionFormModel_amount').focus(function()
        {
            $('#StartSessionFormModel_voucher_code').attr('disabled', 'disabled');
            $('#StartSessionFormModel_amount').change(function()
            {
                if ($('#StartSessionFormModel_amount').val() != '')
                {
                    $('#StartSessionFormModel_voucher_code').val('');
                }
            });
        });
        //-------------------------end code for onfocus event------------------------------//

        $('#chkbancnet').click(function()
        {
            if ($(this).is(':checked'))
            {
                $('#StartSessionFormModel_reference_number').val('');
                $('#StartSessionFormModel_reference_number').removeAttr('disabled');
                $('#StartSessionFormModel_bankid').removeAttr('disabled');
                $('#StartSessionFormModel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').val(0.00);
                $('#StartSessionFormModel_sel_amount').attr('disabled', 'disabled');
                $('#chkotheramount').attr('disabled', 'disabled');
                $('#StartSessionFormModel_voucher_code').attr('disabled', 'disabled');
                $('#StartSessionFormModel_sel_amount').val('');
                $('#StartSessionFormModel_voucher_code').val('');
                $('#StartSessionFormModel_trace_number').removeAttr('disabled');
                $('#StartSessionFormModel_trace_number').focus(function()
                {
                    $("#StartSessionFormModel_trace_number").bind("keypress", function(event)
                    {
                        if (event.charCode != 0)
                        {
                            var regex = new RegExp("^[a-zA-Z0-9]+$");
                            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                            if (!regex.test(key))
                            {
                                event.preventDefault();
                                return false;
                            }
                        }
                    });
                });

                $('#StartSessionFormModel_reference_number').focus(function()
                {
                    $("#StartSessionFormModel_reference_number").bind("keypress", function(event)
                    {
                        if (event.charCode != 0)
                        {
                            var regex = new RegExp("^[a-zA-Z0-9]+$");
                            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                            if (!regex.test(key))
                            {
                                event.preventDefault();
                                return false;
                            }
                        }
                    });
                });
            }
            else
            {
                $('#StartSessionFormModel_amount').val('');
                $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
                $('#StartSessionFormModel_bankid').attr('disabled', 'disabled');
                $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
                $('#chkotheramount').removeAttr('disabled');
                $('#StartSessionFormModel_reference_number').val('');
                $('#StartSessionFormModel_reference_number').attr('disabled', 'disabled');
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
                $('#StartSessionFormModel_trace_number').attr('disabled', 'disabled');
                $('#StartSessionFormModel_trace_number').val('');
            }
        });

        //CCT - BEGIN uncomment
        $('#StartSessionFormModel_loyalty_card').bind('keydown', function(event)
        {
            if (event.keyCode == 13 || event.charCode == 13 || event.keyCode == 9)
            {
                var cardNumber = $('#StartSessionFormModel_loyalty_card').val();
                if (cardNumber == '')
                {
                    alert('Please enter loyalty card number.');
                    return false;
                }
                var issuccess = identifyCard();

                Email = Email.slice(0, 7) + Email.slice(-12);
                if (FirstName == "TST MarkEng" || MiddleName == "" || LastName == "Salva" || Email == "MarkEng@yopmail.com" || Birthdate == "1989-05-05"
                        || MobileNumber == "9091111111" || IdentificationNumber == "123456789") {
                    alert('Player needs to update member details. Please contact Customer Support.');
                }

                var usermode = $('#mode').val();
                if (issuccess === "false")
                {
                    if (usermode === 4)
                    {
                        $('.hideControls').hide();
                        $('#btnstartsessionsa').focus();
                    }
                    else {
                        $('#StartSessionFormModel_sel_amount').focus();
                        $('.btnSubmit').focus();
                    }

                    return false;
                }

            }

            if (event.keyCode != 9)
            {
                $('.hideControls').hide();
                $('.bankContainer').hide();
                // CCT - BEGIN added VIP
                //$('.hideControlsVIP').hide();
                //$('#StartSessionFormModel_vip_type').val(0);                
                //$('#StartSessionFormModel_lvip_type').val(0);  
                // CCT - END added VIP         
                isEwalletSessionMode = false;
                isValidated = false;

                $('#StartSessionFormModel_amount').val('');
                $('#StartSessionFormModel_voucher_code').val('');
                $('#StartSessionFormModel_amount').val('');
                $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
                $('#StartSessionFormModel_bankid').attr('disabled', 'disabled');
                $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
                $('#chkotheramount').removeAttr('disabled');
                $('#chkbancnet').attr('checked', false);
                $('#StartSessionFormModel_reference_number').val('');
                $('#StartSessionFormModel_reference_number').attr('disabled', 'disabled');
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
                $('#StartSessionFormModel_trace_number').attr('disabled', 'disabled');
                $('#StartSessionFormModel_trace_number').val('');
                $('#StartSessionFormModel_sel_amount').val(0);
                $('#StartSessionFormModel_amount').autoNumeric();
                document.getElementById('StartSessionFormModel_sel_amount').selectedIndex = 0;
            }
        });
        // CCT - END uncomment
    })
</script>
<br />
<?php if ($startSessionFormModel->error_count): ?>
    <?php echo $startSessionFormModel->getErrorMessages(); ?>
<?php endif; ?>
<form id="frmstartsessionsa">
    <input type="hidden" name="mode" id="mode" />
    <input type="hidden" name="acc_id" id="acc_id" value="<?php echo $_SESSION['accID'] ?>" />
    <input type="hidden" name="sitecode" id="sitecode" value="<?php echo $_SESSION['site_code'] ?>" />
    <input type="hidden" name="siteclassification" id="siteclassification" value="<?php echo $siteClassification; ?>" />
    <input type="hidden" name="siteamountinfo" id="siteamountinfo" value="<?php echo $siteAmountInfo; ?>" />
    <input type="hidden" name="eBingoDivisibleBy" id="eBingoDivisibleBy" value="<?php echo $eBingoDivisibleBy; ?>"/>
    <input type="hidden" name="eBingoMaxDeposit" id="eBingoMaxDeposit" value="<?php echo $eBingoMaxDeposit; ?>"/>
    <input type="hidden" name="eBingoMinDeposit" id="eBingoMinDeposit"  value="<?php echo $eBingoMinDeposit; ?>"/>
    <?php echo MI_HTML::inputHidden($startSessionFormModel, 'max_deposit') ?>
    <?php echo MI_HTML::inputHidden($startSessionFormModel, 'min_deposit') ?>
    <!-- CCT BEGIN added VIP -->
    <!-- <?php //echo MI_HTML::inputHidden($startSessionFormModel, 'lvip_type')                              ?> -->
    <!-- CCT END added VIP -->    
    <table class="standalonetbl">
        <tr>
            <th><?php echo MI_HTML::label($startSessionFormModel, 'terminal_id', 'Gaming Terminal'); ?></th>
            <td><?php echo MI_HTML::dropDownArray($startSessionFormModel, 'terminal_id', $terminals, 'id', 'code', array('' => '--Select Terminal--'), array(), array('class' => 'width204')) ?></td>
        </tr>
        <!--
        // CCT - BEGIN uncomment
        -->
        <tr id="loyalty_card_tr">
            <th><?php echo MI_HTML::label($startSessionFormModel, 'loyalty_card', 'Membership Card') ?></th>
            <td><?php echo MI_HTML::inputPassword($startSessionFormModel, 'loyalty_card', array('class' => 'width200')) ?></td>
            <!-- 
            // CCT - END uncomment
            -->        
                <!--<td><a href="javascript:void(0);" id="get_info_card">Get Card Info</a><a style="display: none;" href="javascript:void(0);" id="register">Register</a></td>-->
            <!--
            // CCT - BEGIN uncomment
            -->
        </tr>
        <!-- 
        // CCT - END uncomment
        -->
        <!-- CCT BEGIN ADDED VIP -->
        <!--
        <tr class='hideControlsVIP'>
            <th><?php //echo MI_HTML::label($startSessionFormModel, 'vip_type', 'VIP Type')                              ?></th>
            <td>
                <div>
                    <input type="radio" id="viptypeVIP" name="vip_type" value="1"/> VIP <input type="radio" id="viptypeSVIP" name="vip_type" value="2"/> SVIP   
                </div>
            </td>
        </tr>
        -->
        <!-- CCT END ADDED VIP -->         
        <tr class='hideControls'>
            <th><?php echo MI_HTML::label($startSessionFormModel, 'casino', 'Casino'); ?></th>
            <td><?php echo MI_HTML::dropDown($startSessionFormModel, 'casino', array('' => 'Select Casino'), array(), array(), array('class' => 'width204')) ?></td>
        </tr>
        <tr class='hideControls'>
            <th><?php echo MI_HTML::label($startSessionFormModel, 'sel_amount', 'Initial Deposit'); ?></th>
            <td><?php echo MI_HTML::dropDown($startSessionFormModel, 'sel_amount', array('' => 'Select Amount'), array(), array(), array('class' => 'width204')) ?></td>
            <th>
                <input type="checkbox" id="chkotheramount" disabled="disabled" name="chkotheramount"/>
                <?php echo MI_HTML::label($startSessionFormModel, 'amount', 'Other Amount', array('id' => 'lblotheramount')) ?>
            </th>
            <td><?php echo MI_HTML::inputText($startSessionFormModel, 'amount', array('disabled' => 'disabled', 'class' => 'auto', 'maxlength' => 8, 'class' => 'width200')) ?></td>
        </tr>
        <tr class="bankContainer hideControls">
            <th>
                <input type="checkbox" id="chkbancnet" disabled="disabled" name="chkbancnet"/>
                <?php echo MI_HTML::label($startSessionFormModel, 'lblbancnet', 'Bancnet', array('id' => 'lblbancnet')) ?>
            </th>
        </tr>
        <tr class="bankContainer hideControls">
            <th>
                <?php echo MI_HTML::label($startSessionFormModel, 'lbl_traceNumber', 'Trace Number') ?>
            </th>
            <td><?php echo MI_HTML::inputText($startSessionFormModel, 'trace_number', array('class' => 'width200', 'maxlength' => 20, 'disabled' => 'disabled')); ?> <td>
        </tr>
        <tr class="bankContainer hideControls">
            <th><?php echo MI_HTML::label($startSessionFormModel, 'lbl_refNumber', 'Reference Number') ?></th>
            <td><?php echo MI_HTML::inputText($startSessionFormModel, 'reference_number', array('class' => 'width200', 'maxlength' => 20, 'disabled' => 'disabled')); ?><td>
        </tr>
        <tr class='hideControls'>
            <th><?php echo MI_HTML::label($startSessionFormModel, 'voucher_code', 'Voucher Code') ?></th>
            <td><?php echo MI_HTML::inputText($startSessionFormModel, 'voucher_code', array('maxlength' => 20, 'class' => 'width200')) ?></td>
        </tr>
        <tr>
            <!--<td><input type="button" value="Start Session" id="btnstartsessionsa" class="btnSubmit"/></td>-->
            <td><input type="button" value="Start Session" id="btnstartsessionsa"/></td>
        </tr>
    </table>
</form>
