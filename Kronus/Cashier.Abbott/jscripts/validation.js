$(document).ready(function(){
    startSessionStandAloneChecking = function(){
        var bcf = $('#head-bcf > div').children('span').html();
        var terminal = $('#StartSessionFormModel_terminal_id').val();
        var casino = $('#StartSessionFormModel_casino').val();
        var max_deposit = toInt($('#StartSessionFormModel_max_deposit').val());
        var min_deposit = toInt($('#StartSessionFormModel_min_deposit').val());
        var voucher_code = $('#StartSessionFormModel_voucher_code').val();
        var loyalty_card = $('#StartSessionFormModel_loyalty_card').val();
        var amount = '';
        
        if($('#StartSessionFormModel_terminal_id').val() == '') {
            alert('Please select terminal');
            return false;
        }        
        
        if($('#StartSessionFormModel_sel_amount').is(':disabled') && $('#StartSessionFormModel_voucher_code').val('')) {
            amount = $('#StartSessionFormModel_amount').val();
        } else {
            amount = $('#StartSessionFormModel_sel_amount').val();
            $('#StartSessionFormModel_amount').val(amount);
        }  
        
       if(voucher_code == '')
        {
            bcf = toInt(bcf);
            amount = toInt(amount);

            if(amount == '' && voucher_code != '') {
                alert('Amount cannot be empty');
                return false;
            } else if(!$('#StartSessionFormModel_amount').is(':disabled') && amount == ''){
                alert('Please enter other amount');
                return false;
            } else if(amount == '' && voucher_code == '') {
                alert('Please select amount or  enter the voucher code');
                return false;
            }
        
            // check if amount is divisible by 100
            if(parseFloat(amount) % 100 != 0) {
                alert('Amount should be divisible by 100');
                return false;
            }

            // initial deposit should be greater than or equal to minimum deposit
            if(parseFloat(amount) < parseFloat(min_deposit)) {
                alert('Amount should be greater than or equal to PhP ' + $('#StartSessionFormModel_min_deposit').val());
                return false;
            }
            // initial deposit should be less than or equal to maximum deposit
            if(parseFloat(amount) > parseFloat(max_deposit)) {
                alert('Amount should be less than or equal to PhP ' + $('#StartSessionFormModel_max_deposit').val());
                return false;
            }
            
            // check initial deposit is greater than bcf
            if(parseFloat(amount) > parseFloat(bcf)) {
                alert('Not enough bcf');
                return false;
            }
        }
        
        //check loyalty card if empty
        if(loyalty_card == ''){
            alert('Please enter VIP reward card number');
            return false;
        }
        
        return true;
    }
    
    startSessionChecking = function() {
        var bcf = $('#head-bcf > div').children('span').html();
        var min_deposit = toInt($('#StartSessionFormModel_min_deposit').val());
        var max_deposit = toInt($('#StartSessionFormModel_max_deposit').val());
        var voucher_code = $('#StartSessionFormModel_voucher_code').val();   
         var initial_deposit = $('#StartSessionFormModel_amount').val();
         var selected = $('#StartSessionFormModel_sel_amount').val();
         var loyalty_card = $('#StartSessionFormModel_loyalty_card').val();

         //check if the selected type of deposit is in terms of voucher or money
        if(selected != 'voucher'){
                bcf = toInt(bcf);
                initial_deposit = toInt(initial_deposit);
                
                if(initial_deposit == '') {
                    alert('Amount cannot be empty');
                    return false;
                }
                
                // check if amount is divisible by 100
                if(parseFloat(initial_deposit) % 100 != 0) {
                    alert('Amount should be divisible by 100');
                    return false;
                }
                // initial deposit should be greater than or equal to minimum deposit
                if(parseFloat(initial_deposit) < min_deposit) {
                    alert('Amount should be greater than or equal to PhP ' + $('#StartSessionFormModel_min_deposit').val());
                    return false;
                }
                // initial deposit should be less than or equal to maximum deposit
                if(parseFloat(initial_deposit) > max_deposit) {
                    alert('Amount should be less than or equal to PhP ' + $('#StartSessionFormModel_max_deposit').val());
                    return false;
                }

                // check initial deposit is greater than bcf
                if(parseFloat(initial_deposit) > parseFloat(bcf)) {
                    alert('Not enough bcf');
                    return false;
                }
        } else {
            //check if voucher code is empty
            if(voucher_code == ''){
                alert('Please enter the voucher code');
                return false;
            }
        }
        
        //check loyalty card if empty
        if(loyalty_card == ''){
            alert('Please enter loyalty card number');
            return false;
        }
        
        return true;
    }
});