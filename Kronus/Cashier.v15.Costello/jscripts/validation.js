$(document).ready(function()
{
    startSessionStandAloneChecking = function()
    {
        var bcf = $('#head-bcf > div').children('span').html();
        var terminal = $('#StartSessionFormModel_terminal_id').val();
        var casino = $('#StartSessionFormModel_casino').val();
        var max_deposit = toInt($('#StartSessionFormModel_max_deposit').val());
        var min_deposit = toInt($('#StartSessionFormModel_min_deposit').val());
        var voucher_code = $('#StartSessionFormModel_voucher_code').val();
        var loyalty_card = $('#StartSessionFormModel_loyalty_card').val();
        var trace_number = $('#StartSessionFormModel_trace_number').val();
        var reference_number = $('#StartSessionFormModel_reference_number').val();
        var amount = '';
        var site_amount_info = $('#siteamountinfo').val();
        var ebingo_divisible_by = $('#eBingoDivisibleBy').val();
        var ebingo_max_deposit = $('#eBingoMaxDeposit').val();
        var ebingo_min_deposit = $('#eBingoMinDeposit').val();
        var usermode = $('#mode').val();

        if (site_amount_info == 0)
        {
            showLightbox(function()
            {
                updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                        ''
                        );
                ;
            });
        }
        else
        {
            if ($('#StartSessionFormModel_terminal_id').val() == '')
            {
                alert('Please select terminal');
                return false;
            }

            //check loyalty card if empty
            if (loyalty_card == '')
            {
                alert('Please enter VIP reward card number');
                return false;
            }

            if (isEwalletSessionMode == false)
            {
                // CCT - BEGIN uncomment
                if (isValidated == false)
                {
                    alert('Membership Card is not yet validated. Please scan again');
                    return false;
                }
                // CCT - END uncomment

                if ($('#StartSessionFormModel_sel_amount').is(':disabled') && $('#StartSessionFormModel_voucher_code').val(''))
                {
                    amount = $('#StartSessionFormModel_amount').val();
                }
                else
                {
                    amount = $('#StartSessionFormModel_sel_amount').val();
                    $('#StartSessionFormModel_amount').val(amount);
                }

                if (voucher_code == '')
                {
                    bcf = toInt(bcf);
                    amount = toInt(amount);

                    if (amount == '' && voucher_code != '')
                    {
                        alert('Amount cannot be empty');
                        return false;
                    }
                    else if (!$('#StartSessionFormModel_amount').is(':disabled') && amount == '')
                    {
                        alert('Please enter other amount');
                        return false;
                    }
                    else if (amount == '' && voucher_code == '')
                    {
                        alert('Please select amount or  enter the voucher code');
                        return false;
                    }

                    if (usermode == 4) {
                        if (parseFloat(amount) % parseInt(ebingo_divisible_by) != 0)
                        {
                            alert('Amount should be divisible by ' + toMoney(ebingo_divisible_by));
                            return false;
                        }
                    }
                    else {
                        // check if amount is divisible by 100
                        if (parseFloat(amount) % parseInt(site_amount_info) != 0)
                        {
                            alert('Amount should be divisible by ' + site_amount_info);
                            return false;
                        }
                    }


                    if (usermode == 4) {
                        // initial deposit should be greater than or equal to minimum deposit
                        if (parseFloat(amount) < ebingo_min_deposit)
                        {
                            alert('Amount should be greater than or equal to PhP ' + toMoney(ebingo_min_deposit));
                            return false;
                        }
                    } else {
                        // initial deposit should be greater than or equal to minimum deposit
                        if (parseFloat(amount) < min_deposit)
                        {
                            alert('Amount should be greater than or equal to PhP ' + $('#StartSessionFormModel_min_deposit').val());
                            return false;
                        }
                    }

                    if (usermode == 4) {
                        // initial deposit should be less than or equal to maximum deposit
                        if (parseFloat(amount) > ebingo_max_deposit)
                        {
                            alert('Amount should be less than or equal to PhP ' + toMoney(ebingo_max_deposit));
                            return false;
                        }
                    } else {
                        // initial deposit should be less than or equal to maximum deposit
                        if (parseFloat(amount) > max_deposit)
                        {
                            alert('Amount should be less than or equal to PhP ' + $('#StartSessionFormModel_max_deposit').val());
                            return false;
                        }
                    }

                    // check initial deposit is greater than bcf
                    if (parseFloat(amount) > parseFloat(bcf))
                    {
                        alert('Not enough bcf');
                        return false;
                    }

                    if ($('#chkbancnet').is(':checked'))
                    {
                        if (trace_number == '')
                        {
                            alert('Trace number cannot be empty.');
                            return false;
                        }

                        if (reference_number == '')
                        {
                            alert('Reference number cannot be empty.');
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    startSessionChecking = function()
    {
        var bcf = $('#head-bcf > div').children('span').html();
        var min_deposit = toInt($('#StartSessionFormModel_min_deposit').val());
        var max_deposit = toInt($('#StartSessionFormModel_max_deposit').val());
        var voucher_code = $('#StartSessionFormModel_voucher_code').val();
        var initial_deposit = $('#StartSessionFormModel_amount').val();
        var selected = $('#StartSessionFormModel_sel_amount').val();
        var loyalty_card = $('#StartSessionFormModel_loyalty_card').val();
        var trace_number = $('#StartSessionFormModel_trace_number').val();
        var reference_number = $('#StartSessionFormModel_reference_number').val();
        var site_amount_info = $('#siteamountinfo').val();
        var ebingo_divisible_by = $('#eBingoDivisibleBy').val();
        var ebingo_max_deposit = $('#eBingoMaxDeposit').val();
        var ebingo_min_deposit = $('#eBingoMinDeposit').val();
        var usermode = $('#mode').val();

        if (site_amount_info == 0)
        {
            showLightbox(function()
            {
                updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                        ''
                        );
                ;
            });
        }
        else
        {
            //CCT - BEGIN uncomment
            //check loyalty card if empty
            if (loyalty_card == '')
            {
                alert('Please enter loyalty card number');
                return false;
            }
            //CCT - END uncomment            

            if (isEwalletSessionMode == false)
            {
                //CCT - BEGIN uncomment
                if (isValidated == false)
                {
                    alert('Membership Card is not yet validated. Please scan again');
                    return false;
                }
                // CCT - END uncomment

                //check if the selected type of deposit is in terms of voucher or money
                if (selected != 'voucher' && selected != 'bancnet')
                {
                    bcf = toInt(bcf);
                    initial_deposit = toInt(initial_deposit);

                    if (initial_deposit == '')
                    {
                        alert('Amount cannot be empty');
                        return false;
                    }

                    if (usermode == 4) {
                        if (parseFloat(initial_deposit) % parseInt(ebingo_divisible_by) != 0)
                        {
                            alert('Amount should be divisible by ' + toMoney(ebingo_divisible_by));
                            return false;
                        }
                    }
                    else {
                        // check if amount is divisible by 100
                        if (parseFloat(initial_deposit) % parseInt(site_amount_info) != 0)
                        {
                            alert('Amount should be divisible by ' + site_amount_info);
                            return false;
                        }
                    }


                    if (usermode == 4) {
                        // initial deposit should be greater than or equal to minimum deposit
                        if (parseFloat(initial_deposit) < ebingo_min_deposit)
                        {
                            alert('Amount should be greater than or equal to PhP ' + toMoney(ebingo_min_deposit));
                            return false;
                        }
                    } else {
                        // initial deposit should be greater than or equal to minimum deposit
                        if (parseFloat(initial_deposit) < min_deposit)
                        {
                            alert('Amount should be greater than or equal to PhP ' + $('#StartSessionFormModel_min_deposit').val());
                            return false;
                        }
                    }

                    if (usermode == 4) {
                        // initial deposit should be less than or equal to maximum deposit
                        if (parseFloat(initial_deposit) > ebingo_max_deposit)
                        {
                            alert('Amount should be less than or equal to PhP ' + toMoney(ebingo_max_deposit));
                            return false;
                        }
                    } else {
                        // initial deposit should be less than or equal to maximum deposit
                        if (parseFloat(initial_deposit) > max_deposit)
                        {
                            alert('Amount should be less than or equal to PhP ' + $('#StartSessionFormModel_max_deposit').val());
                            return false;
                        }
                    }

                    // check initial deposit is greater than bcf
                    if (parseFloat(initial_deposit) > parseFloat(bcf))
                    {
                        alert('Not enough bcf');
                        return false;
                    }
                }
                else if (selected == 'bancnet')
                {
                    bcf = toInt(bcf);
                    initial_deposit = toInt(initial_deposit);

                    if (initial_deposit == '')
                    {
                        alert('Amount cannot be empty');
                        return false;
                    }

                    if (usermode == 4) {
                        if (parseFloat(initial_deposit) % parseInt(ebingo_divisible_by) != 0)
                        {
                            alert('Amount should be divisible by ' + toMoney(ebingo_divisible_by));
                            return false;
                        }
                    }
                    else {
                        // check if amount is divisible by 100
                        if (parseFloat(initial_deposit) % parseInt(site_amount_info) != 0)
                        {
                            alert('Amount should be divisible by ' + site_amount_info);
                            return false;
                        }
                    }


                    if (usermode == 4) {
                        // initial deposit should be greater than or equal to minimum deposit
                        if (parseFloat(initial_deposit) < ebingo_min_deposit)
                        {
                            alert('Amount should be greater than or equal to PhP ' + toMoney(ebingo_min_deposit));
                            return false;
                        }
                    } else {
                        // initial deposit should be greater than or equal to minimum deposit
                        if (parseFloat(initial_deposit) < min_deposit)
                        {
                            alert('Amount should be greater than or equal to PhP ' + $('#StartSessionFormModel_min_deposit').val());
                            return false;
                        }
                    }

                    if (usermode == 4) {
                        // initial deposit should be less than or equal to maximum deposit
                        if (parseFloat(initial_deposit) > ebingo_max_deposit)
                        {
                            alert('Amount should be less than or equal to PhP ' + toMoney(ebingo_max_deposit));
                            return false;
                        }
                    } else {
                        // initial deposit should be less than or equal to maximum deposit
                        if (parseFloat(initial_deposit) > max_deposit)
                        {
                            alert('Amount should be less than or equal to PhP ' + $('#StartSessionFormModel_max_deposit').val());
                            return false;
                        }
                    }


                    // check initial deposit is greater than bcf
                    if (parseFloat(initial_deposit) > parseFloat(bcf))
                    {
                        alert('Not enough bcf');
                        return false;
                    }

                    if (trace_number == '')
                    {
                        alert('Trace number cannot be empty.');
                        return false;
                    }

                    if (reference_number == '')
                    {
                        alert('Reference number cannot be empty.');
                        return false;
                    }
                }
                else
                {
                    //check if voucher code is empty
                    if (voucher_code == '')
                    {
                        alert('Please enter the voucher code');
                        return false;
                    }
                }
            }
        }
        return true;
    };

    reloadSessionChecking = function()
    {
        var bcf = $('#head-bcf > div').children('span').html();
        var min_deposit = toInt($('#StartSessionFormModel_min_deposit').val());
        var max_deposit = toInt($('#StartSessionFormModel_max_deposit').val());
        var voucher_code = $('#StartSessionFormModel_voucher_code').val();
        var initial_deposit = $('#StartSessionFormModel_amount').val();
        var selected = $('#StartSessionFormModel_sel_amount').val();
        var loyalty_card = $('#StartSessionFormModel_loyalty_card').val();
        var trace_number = $('#StartSessionFormModel_trace_number').val();
        var reference_number = $('#StartSessionFormModel_reference_number').val();
        var site_amount_info = $('#siteamountinfo').val();
        var ebingo_divisible_by = $('#eBingoDivisibleBy').val();
        var ebingo_max_deposit = $('#eBingoMaxDeposit').val();
        var ebingo_min_deposit = $('#eBingoMinDeposit').val();
        var usermode = $('#mode').val();

        if (site_amount_info == 0)
        {
            showLightbox(function()
            {
                updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                        ''
                        );
                ;
            });
        }
        else
        {
            // CCT - BEGIN uncomment
            //check loyalty card if empty
            if (loyalty_card == '')
            {
                alert('Please enter loyalty card number');
                return false;
            }
            // CCT - END uncomment

            //check if the selected type of deposit is in terms of voucher or money
            if (selected != 'voucher' && selected != 'bancnet')
            {
                bcf = toInt(bcf);
                initial_deposit = toInt(initial_deposit);

                if (initial_deposit == '')
                {
                    alert('Amount cannot be empty');
                    return false;
                }

                if (usermode == 4) {
                    // ebingo check if amount is divisible by --
                    if (parseFloat(initial_deposit) % parseInt(ebingo_divisible_by) != 0)
                    {
                        alert('Amount should be divisible by ' + toMoney(ebingo_divisible_by));
                        return false;
                    }
                }
                else {
                    // check if amount is divisible by 100
                    if (parseFloat(initial_deposit) % parseInt(site_amount_info) != 0)
                    {
                        alert('Amount should be divisible by ' + site_amount_info);
                        return false;
                    }
                }


                if (usermode == 4) {
                    // initial deposit should be greater than or equal to minimum deposit
                    if (parseFloat(initial_deposit) < ebingo_min_deposit)
                    {
                        alert('Amount should be greater than or equal to PhP ' + toMoney(ebingo_min_deposit));
                        return false;
                    }
                } else {
                    // initial deposit should be greater than or equal to minimum deposit
                    if (parseFloat(initial_deposit) < min_deposit)
                    {
                        alert('Amount should be greater than or equal to PhP ' + $('#StartSessionFormModel_min_deposit').val());
                        return false;
                    }
                }

                if (usermode == 4) {
                    // initial deposit should be less than or equal to maximum deposit
                    if (parseFloat(initial_deposit) > ebingo_max_deposit)
                    {
                        alert('Amount should be less than or equal to PhP ' + ebingo_max_deposit);
                        return false;
                    }
                } else {
                    // initial deposit should be less than or equal to maximum deposit
                    if (parseFloat(initial_deposit) > max_deposit)
                    {
                        alert('Amount should be less than or equal to PhP ' + $('#StartSessionFormModel_max_deposit').val());
                        return false;
                    }
                }


                // check initial deposit is greater than bcf
                if (parseFloat(initial_deposit) > parseFloat(bcf))
                {
                    alert('Not enough bcf');
                    return false;
                }
            }
            else if (selected == 'bancnet')
            {
                bcf = toInt(bcf);
                initial_deposit = toInt(initial_deposit);

                if (initial_deposit == '')
                {
                    alert('Amount cannot be empty');
                    return false;
                }

                if (usermode == 4) {
                    // ebingo check if amount is divisible by --
                    if (parseFloat(initial_deposit) % parseInt(ebingo_divisible_by) != 0)
                    {
                        alert('Amount should be divisible by ' + toMoney(ebingo_divisible_by));
                        return false;
                    }
                }
                else {
                    // check if amount is divisible by 100
                    if (parseFloat(initial_deposit) % parseInt(site_amount_info) != 0)
                    {
                        alert('Amount should be divisible by ' + site_amount_info);
                        return false;
                    }
                }


                if (usermode == 4) {
                    // initial deposit should be greater than or equal to minimum deposit
                    if (parseFloat(initial_deposit) < ebingo_min_deposit)
                    {
                        alert('Amount should be greater than or equal to PhP ' + toMoney(ebingo_min_deposit));
                        return false;
                    }
                } else {
                    // initial deposit should be greater than or equal to minimum deposit
                    if (parseFloat(initial_deposit) < min_deposit)
                    {
                        alert('Amount should be greater than or equal to PhP ' + $('#StartSessionFormModel_min_deposit').val());
                        return false;
                    }
                }

                if (usermode == 4) {
                    // initial deposit should be less than or equal to maximum deposit
                    if (parseFloat(initial_deposit) > ebingo_max_deposit)
                    {
                        alert('Amount should be less than or equal to PhP ' + toMoney(ebingo_max_deposit));
                        return false;
                    }
                } else {
                    // initial deposit should be less than or equal to maximum deposit
                    if (parseFloat(initial_deposit) > max_deposit)
                    {
                        alert('Amount should be less than or equal to PhP ' + $('#StartSessionFormModel_max_deposit').val());
                        return false;
                    }
                }


                // check initial deposit is greater than bcf
                if (parseFloat(initial_deposit) > parseFloat(bcf))
                {
                    alert('Not enough bcf');
                    return false;
                }

                if (trace_number == '')
                {
                    alert('Trace number cannot be empty.');
                    return false;
                }

                if (reference_number == '')
                {
                    alert('Reference number cannot be empty.');
                    return false;
                }
            }
            else
            {
                //check if voucher code is empty
                if (voucher_code == '')
                {
                    alert('Please enter the voucher code');
                    return false;
                }
            }
        }
        return true;
    };

    reloadSessionStandAloneChecking = function()
    {
        var bcf = $('#head-bcf > div').children('span').html();
        var terminal = $('#StartSessionFormModel_terminal_id').val();
        var casino = $('#StartSessionFormModel_casino').val();
        var max_deposit = toInt($('#StartSessionFormModel_max_deposit').val());
        var min_deposit = toInt($('#StartSessionFormModel_min_deposit').val());
        var voucher_code = $('#StartSessionFormModel_voucher_code').val();
        var loyalty_card = $('#StartSessionFormModel_loyalty_card').val();
        var trace_number = $('#StartSessionFormModel_trace_number').val();
        var reference_number = $('#StartSessionFormModel_reference_number').val();
        var amount = '';
        var site_amount_info = $('#siteamountinfo').val();
        var ebingo_divisible_by = $('#eBingoDivisibleBy').val();
        var ebingo_max_deposit = $('#eBingoMaxDeposit').val();
        var ebingo_min_deposit = $('#eBingoMinDeposit').val();
        var usermode = $('#mode').val();

        if (site_amount_info == 0)
        {
            showLightbox(function()
            {
                updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                        ''
                        );
                ;
            });
        }
        else
        {
            if ($('#StartSessionFormModel_terminal_id').val() == '')
            {
                alert('Please select terminal');
                return false;
            }

            //check loyalty card if empty
            if (loyalty_card == '')
            {
                alert('Please enter VIP reward card number');
                return false;
            }

            if ($('#StartSessionFormModel_sel_amount').is(':disabled') && $('#StartSessionFormModel_voucher_code').val(''))
            {
                amount = $('#StartSessionFormModel_amount').val();
            }
            else
            {
                amount = $('#StartSessionFormModel_sel_amount').val();
                $('#StartSessionFormModel_amount').val(amount);
            }

            if (voucher_code == '')
            {
                bcf = toInt(bcf);
                amount = toInt(amount);

                if (amount == '' && voucher_code != '')
                {
                    alert('Amount cannot be empty');
                    return false;
                }
                else if (!$('#StartSessionFormModel_amount').is(':disabled') && amount == '')
                {
                    alert('Please enter other amount');
                    return false;
                }
                else if (amount == '' && voucher_code == '')
                {
                    alert('Please select amount or  enter the voucher code');
                    return false;
                }


                if (usermode == 4) {
                    // ebingo check if amount is divisible by --
                    if (parseFloat(amount) % parseInt(ebingo_divisible_by) != 0)
                    {
                        alert('Amount should be divisible by ' + toMoney(ebingo_divisible_by));
                        return false;
                    }
                }
                else {
                    // check if amount is divisible by 100
                    if (parseFloat(amount) % parseInt(site_amount_info) != 0)
                    {
                        alert('Amount should be divisible by ' + site_amount_info);
                        return false;
                    }
                }


                if (usermode == 4) {
                    // initial deposit should be greater than or equal to minimum deposit
                    if (parseFloat(amount) < ebingo_min_deposit)
                    {
                        alert('Amount should be greater than or equal to PhP ' + toMoney(ebingo_min_deposit));
                        return false;
                    }
                } else {
                    // initial deposit should be greater than or equal to minimum deposit
                    if (parseFloat(amount) < min_deposit)
                    {
                        alert('Amount should be greater than or equal to PhP ' + $('#StartSessionFormModel_min_deposit').val());
                        return false;
                    }
                }

                if (usermode == 4) {
                    // initial deposit should be less than or equal to maximum deposit
                    if (parseFloat(amount) > ebingo_max_deposit)
                    {
                        alert('Amount should be less than or equal to PhP ' + ebingo_max_deposit);
                        return false;
                    }
                } else {
                    // initial deposit should be less than or equal to maximum deposit
                    if (parseFloat(amount) > max_deposit)
                    {
                        alert('Amount should be less than or equal to PhP ' + $('#StartSessionFormModel_max_deposit').val());
                        return false;
                    }
                }

                // check initial deposit is greater than bcf
                if (parseFloat(amount) > parseFloat(bcf))
                {
                    alert('Not enough bcf');
                    return false;
                }

                if ($('#chkbancnet').is(':checked'))
                {
                    if (trace_number == '')
                    {
                        alert('Trace number cannot be empty.');
                        return false;
                    }

                    if (reference_number == '')
                    {
                        alert('Reference number cannot be empty.');
                        return false;
                    }
                }
            }
        }
        return true;
    }

    unlockTerminalChecking = function()
    {
        var bcf = $('#head-bcf > div').children('span').html();
        var loyalty_card = $('#UnlockTerminalFormModel_loyalty_card').val();

        //CCT - BEGIN uncomment
        //check loyalty card if empty
        if (loyalty_card == '')
        {
            alert('Please enter loyalty card number');
            return false;
        }
        //CCT - END uncomment
        return true;
    };

    forceTLoadStandAloneChecking = function()
    {
        var bcf = $('#head-bcf > div').children('span').html();
        var terminal = $('#ForceTFormModel_terminal_id').val();
        var casino = $('#ForceTFormModel_casino').val();
        var max_deposit = toInt($('#ForceTFormModel_max_deposit').val());
        var min_deposit = toInt($('#ForceTFormModel_min_deposit').val());
        var voucher_code = $('#ForceTFormModel_voucher_code').val();
        var loyalty_card = $('#ForceTFormModel_loyalty_card').val();
        var amount = '';
        var site_amount_info = $('#siteamountinfo').val();

        if (site_amount_info == 0)
        {
            showLightbox(function()
            {
                updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                        ''
                        );
                ;
            });
        }
        else
        {
            if ($('#ForceTFormModel_terminal_id').val() == '')
            {
                alert('Please select terminal');
                return false;
            }

            if ($('#ForceTFormModel_sel_amount').is(':disabled') && $('#ForceTFormModel_voucher_code').val(''))
            {
                amount = $('#ForceTFormModel_amount').val();
            }
            else
            {
                amount = $('#ForceTFormModel_sel_amount').val();
                $('#ForceTFormModel_amount').val(amount);
            }

            if (voucher_code == '')
            {
                bcf = toInt(bcf);
                amount = toInt(amount);

                if (amount == '' && voucher_code != '')
                {
                    alert('Amount cannot be empty');
                    return false;
                }
                else if (!$('#ForceTFormModel_amount').is(':disabled') && amount == '')
                {
                    alert('Please enter other amount');
                    return false;
                }
                else if (amount == '' && voucher_code == '')
                {
                    alert('Please select amount or  enter the voucher code');
                    return false;
                }

                // check if amount is divisible by 100
                if (parseFloat(amount) % parseInt(site_amount_info) != 0)
                {
                    alert('Amount should be divisible by ' + site_amount_info);
                    return false;
                }

                // initial deposit should be greater than or equal to minimum deposit
                if (parseFloat(amount) < parseFloat(min_deposit))
                {
                    alert('Amount should be greater than or equal to PhP ' + $('#ForceTFormModel_min_deposit').val());
                    return false;
                }

                // initial deposit should be less than or equal to maximum deposit
                if (parseFloat(amount) > parseFloat(max_deposit))
                {
                    alert('Amount should be less than or equal to PhP ' + $('#ForceTFormModel_max_deposit').val());
                    return false;
                }

                // check initial deposit is greater than bcf
                if (parseFloat(amount) > parseFloat(bcf))
                {
                    alert('Not enough bcf');
                    return false;
                }
            }
            //check loyalty card if empty
            if (loyalty_card == '')
            {
                alert('Please enter VIP reward card number');
                return false;
            }
        }
        return true;
    };

    unlockStandAloneChecking = function()
    {
        var bcf = $('#head-bcf > div').children('span').html();
        var terminal = $('#UnlockTerminalFormModel_terminal_id').val();
        var casino = $('#UnlockTerminalFormModel_casino').val();
        var max_deposit = toInt($('#UnlockTerminalFormModel_max_deposit').val());
        var min_deposit = toInt($('#UnlockTerminalFormModel_min_deposit').val());
        var voucher_code = $('#UnlockTerminalFormModel_voucher_code').val();
        var loyalty_card = $('#UnlockTerminalFormModel_loyalty_card').val();
        var amount = '';
        var site_amount_info = $('#siteamountinfo').val();

        if (site_amount_info == 0)
        {
            showLightbox(function()
            {
                updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                        ''
                        );
                ;
            });
        }
        else
        {
            if ($('#UnlockTerminalFormModel_terminal_id').val() == '')
            {
                alert('Please select terminal');
                return false;
            }

            if ($('#UnlockTerminalFormModel_sel_amount').is(':disabled') && $('#UnlockTerminalFormModel_voucher_code').val(''))
            {
                amount = $('#UnlockTerminalFormModel_amount').val();
            }
            else
            {
                amount = $('#UnlockTerminalFormModel_sel_amount').val();
                $('#UnlockTerminalFormModel_amount').val(amount);
            }

            if (voucher_code == '')
            {
                bcf = toInt(bcf);
                amount = toInt(amount);

                if (amount == '' && voucher_code != '')
                {
                    alert('Amount cannot be empty');
                    return false;
                }
                else if (!$('#UnlockTerminalFormModel_amount').is(':disabled') && amount == '')
                {
                    alert('Please enter other amount');
                    return false;
                }
                else if (amount == '' && voucher_code == '')
                {
                    alert('Please select amount or  enter the voucher code');
                    return false;
                }

                // check if amount is divisible by 100
                if (parseFloat(amount) % parseInt(site_amount_info) != 0)
                {
                    alert('Amount should be divisible by ' + site_amount_info);
                    return false;
                }

                //            // initial deposit should be greater than or equal to minimum deposit
                //            if(parseFloat(amount) < parseFloat(min_deposit)) {
                //                alert('Amount should be greater than or equal to PhP ' + $('#UnlockTerminalFormModel_min_deposit').val());
                //                return false;
                //            }
                //            
                // initial deposit should be less than or equal to maximum deposit
                if (parseFloat(amount) > parseFloat(max_deposit))
                {
                    alert('Amount should be less than or equal to PhP ' + $('#UnlockTerminalFormModel_max_deposit').val());
                    return false;
                }

                // check initial deposit is greater than bcf
                if (parseFloat(amount) > parseFloat(bcf))
                {
                    alert('Not enough bcf');
                    return false;
                }
            }
            //check loyalty card if empty
            if (loyalty_card == '')
            {
                alert('Please enter VIP reward card number');
                return false;
            }
        }
        return true;
    };

    startSessionAloneeBingoChecking = function()
    {
        var bcf = $('#head-bcf > div').children('span').html();
        var terminal = $('#StartSessionFormModel_terminal_id').val();
        var casino = $('#StartSessionFormModel_casino').val();
        var max_deposit = toInt($('#StartSessionFormModel_max_deposit').val());
        var min_deposit = toInt($('#StartSessionFormModel_min_deposit').val());
        var voucher_code = $('#StartSessionFormModel_voucher_code').val();
        var amount = '';
        var site_amount_info = $('#siteamountinfo').val();

        if (site_amount_info == 0)
        {
            showLightbox(function()
            {
                updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                        ''
                        );
                ;
            });
        }
        else
        {
            if ($('#StartSessionFormModel_terminal_id').val() == '')
            {
                alert('Please select terminal');
                return false;
            }

            if ($('#StartSessionFormModel_sel_amount').is(':disabled') && $('#StartSessionFormModel_voucher_code').val(''))
            {
                amount = $('#StartSessionFormModel_amount').val();
            }
            else
            {
                amount = $('#StartSessionFormModel_sel_amount').val();
                $('#StartSessionFormModel_amount').val(amount);
            }

            if (voucher_code == '')
            {
                bcf = toInt(bcf);
                amount = toInt(amount);

                if (amount == '' && voucher_code != '')
                {
                    alert('Amount cannot be empty');
                    return false;
                }
                else if (!$('#StartSessionFormModel_amount').is(':disabled') && amount == '')
                {
                    alert('Please enter other amount');
                    return false;
                }
                else if (amount == '' && voucher_code == '')
                {
                    alert('Please select amount or  enter the voucher code');
                    return false;
                }

                // check if amount is divisible by 100
                if (parseFloat(amount) % parseInt(site_amount_info) != 0)
                {
                    alert('Amount should be divisible by ' + site_amount_info);
                    return false;
                }

                // initial deposit should be greater than or equal to minimum deposit
                if (parseFloat(amount) < parseFloat(min_deposit))
                {
                    alert('Amount should be greater than or equal to PhP ' + $('#StartSessionFormModel_min_deposit').val());
                    return false;
                }

                // initial deposit should be less than or equal to maximum deposit
                if (parseFloat(amount) > parseFloat(max_deposit))
                {
                    alert('Amount should be less than or equal to PhP ' + $('#StartSessionFormModel_max_deposit').val());
                    return false;
                }

                // check initial deposit is greater than bcf
                if (parseFloat(amount) > parseFloat(bcf))
                {
                    alert('Not enough bcf');
                    return false;
                }
            }
        }
        return true;
    }

    startSessioneBingoChecking = function()
    {
        var bcf = $('#head-bcf > div').children('span').html();
        var min_deposit = toInt($('#StartSessionFormModel_min_deposit').val());
        var max_deposit = toInt($('#StartSessionFormModel_max_deposit').val());
        var voucher_code = $('#StartSessionFormModel_voucher_code').val();
        var initial_deposit = $('#StartSessionFormModel_amount').val();
        var selected = $('#StartSessionFormModel_sel_amount').val();
        var trace_number = $('#StartSessionFormModel_trace_number').val();
        var reference_number = $('#StartSessionFormModel_reference_number').val();
        var site_amount_info = $('#siteamountinfo').val();

        if (site_amount_info == 0)
        {
            showLightbox(function()
            {
                updateLightbox('<center><label  style="font-size: 24px; color: red; font-weight: bold; width: 600px;">Error[011]: Site amount is not set.</label>' +
                        '<br /><br /><label style="font-size: 20px;  font-weight: bold;">Please contact Philweb Customer</label>' +
                        '<br /><label style="font-size: 20px;  font-weight: bold;">Service Hotline 338-3388.</label></center>' +
                        '<br /><input type="button" style="float: right; width: 50px; height: 25px;"  value="Ok" class="btnClose" />',
                        ''
                        );
                ;
            });
        }
        else
        {
            if (isEwalletSessionMode == false)
            {
                //check if the selected type of deposit is in terms of voucher or money
                if (selected != 'voucher' && selected != 'bancnet')
                {
                    bcf = toInt(bcf);
                    initial_deposit = toInt(initial_deposit);

                    if (initial_deposit == '')
                    {
                        alert('Amount cannot be empty');
                        return false;
                    }

                    // check if amount is divisible by 100
                    if (parseFloat(initial_deposit) % parseInt(site_amount_info) != 0)
                    {
                        alert('Amount should be divisible by ' + site_amount_info);
                        return false;
                    }

                    // initial deposit should be greater than or equal to minimum deposit
                    if (parseFloat(initial_deposit) < min_deposit)
                    {
                        alert('Amount should be greater than or equal to PhP ' + $('#StartSessionFormModel_min_deposit').val());
                        return false;
                    }

                    // initial deposit should be less than or equal to maximum deposit
                    if (parseFloat(initial_deposit) > max_deposit)
                    {
                        alert('Amount should be less than or equal to PhP ' + $('#StartSessionFormModel_max_deposit').val());
                        return false;
                    }

                    // check initial deposit is greater than bcf
                    if (parseFloat(initial_deposit) > parseFloat(bcf))
                    {
                        alert('Not enough bcf');
                        return false;
                    }
                }
                else if (selected == 'bancnet')
                {
                    bcf = toInt(bcf);
                    initial_deposit = toInt(initial_deposit);

                    if (initial_deposit == '')
                    {
                        alert('Amount cannot be empty');
                        return false;
                    }

                    // check if amount is divisible by 100
                    if (parseFloat(initial_deposit) % parseInt(site_amount_info) != 0)
                    {
                        alert('Amount should be divisible by ' + site_amount_info);
                        return false;
                    }

                    // initial deposit should be greater than or equal to minimum deposit
                    if (parseFloat(initial_deposit) < min_deposit)
                    {
                        alert('Amount should be greater than or equal to PhP ' + $('#StartSessionFormModel_min_deposit').val());
                        return false;
                    }

                    // initial deposit should be less than or equal to maximum deposit
                    if (parseFloat(initial_deposit) > max_deposit)
                    {
                        alert('Amount should be less than or equal to PhP ' + $('#StartSessionFormModel_max_deposit').val());
                        return false;
                    }

                    // check initial deposit is greater than bcf
                    if (parseFloat(initial_deposit) > parseFloat(bcf))
                    {
                        alert('Not enough bcf');
                        return false;
                    }

                    if (trace_number == '')
                    {
                        alert('Trace number cannot be empty.');
                        return false;
                    }

                    if (reference_number == '')
                    {
                        alert('Reference number cannot be empty.');
                        return false;
                    }
                }
                else
                {
                    //check if voucher code is empty
                    if (voucher_code == '')
                    {
                        alert('Please enter the voucher code');
                        return false;
                    }
                }
            }
            return true;
        }
    }


});
