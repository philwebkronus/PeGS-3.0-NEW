<?php Mirage::loadLibraries(array('CardScripts','LoyaltyScripts')); ?>
<script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<script type="text/javascript" src="jscripts/validation.js"></script>
<script type="text/javascript">
    $('document').ready(function(){
        $('#ForceTFormModel_amount').autoNumeric();
    });
</script>
<form id="frmreloadsa">
    <?php if($FTModel->error_count): ?>
    <?php echo $FTModel->getErrorMessages(); ?>
    <?php endif; ?>       
    <?php echo MI_HTML::inputHidden($FTModel, 'max_deposit') ?>
    <?php echo MI_HTML::inputHidden($FTModel, 'min_deposit') ?>    
    <table class="standalonetbl">
        <tr>
            <th><?php echo MI_HTML::label($FTModel, 'loyalty_card', 'Membership Card') ?></th>
            <td><?php echo MI_HTML::inputPassword($FTModel, 'loyalty_card') ?></td>
            <td><a href="javascript:void(0);" id="get_info_card3">Get Card Info</a><a style="display: none;" href="javascript:void(0);" id="register">Register</a></td>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($FTModel, 'sel_amount', 'Amount'); ?></th>
            <td><?php echo MI_HTML::dropDown($FTModel, 'sel_amount', array(''=>'Select Amount')) ?></td>
            <th>
                <input type="checkbox" id="chkotheramount" disabled="disabled" name="chkotheramount"/>
                <?php echo MI_HTML::label($FTModel, 'amount', 'Other Amount',array('id'=>'lblotheramount'))  ?>
            </th>
            <td><?php echo MI_HTML::inputText($FTModel, 'amount', array('disabled'=>'disabled','class'=>'auto','maxlength'=>9)) ?></td>
        </tr>
        <tr>
            <th>
                <input type="checkbox" id="chkbancnet" disabled="disabled" name="chkbancnet"/>
                <?php echo MI_HTML::label($FTModel, 'lblbancnet', 'Bancnet',array('id'=>'lblbancnet'))  ?>
            </th>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($FTModel, 'lbltracenumber', 'Trace Number'); ?></th>
            <td><?php echo MI_HTML::inputText($FTModel, 'tracenumber', array('disabled'=>'disabled','class'=>'auto','maxlength'=>20)) ?> <td>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($FTModel, 'lblreferencenumber', 'Reference Number'); ?></th>
            <td><?php echo MI_HTML::inputText($FTModel, 'referencenumber', array('disabled'=>'disabled','class'=>'auto','maxlength'=>20)) ?></td>
        </tr>    
        <tr>
            <th><?php echo MI_HTML::label($FTModel, 'voucher_code', 'Voucher Code') ?></th>
            <td><?php echo MI_HTML::inputText($FTModel, 'voucher_code', array('maxlength'=>20)) ?></td>
        </tr>
        <tr>
            <td><input type="button" value="Load" id="btnLoad"/></td>
        </tr>
    </table>
</form>

<script>
    $('document').ready(function(){
        $('#chkotheramount').removeAttr('disabled');
        $('#chkbancnet').removeAttr('disabled');
        $('#ForceTFormModel_tracenumber').attr('disabled','disabled');
        $('#ForceTFormModel_referencenumber').attr('disabled','disabled');
        
        var url = '<?php echo Mirage::app()->createUrl('terminal/reloadamount') ?>';
                var data = 'isreload=1';
                $.ajax({
                    type: 'post',
                    url : url,
                    data : data,
                    success : function(data){
                        try {
                            var json = $.parseJSON(data);
                            var opt = '';
                            $('#cur_playing_bal').html(json.terminal_balance);
                            $('#ForceTFormModel_max_deposit').val(json.max_denomination);
                            $('#ForceTFormModel_min_deposit').val(json.min_denomination);
                            $.each(json.denomination,function(k,v){
                                opt+='<option value="'+k+'" >'+v+'</option>';
                            });
                            $('#ForceTFormModel_sel_amount').append(opt);
                        }catch(e) {
                            alert('Oops! Something went wrong');
                            location.reload(true);
                        }
                    },
                    error : function(e) {
                        displayError(e);
                }
            });
            
            $('#ForceTFormModel_loyalty_card').bind("enterKey",function(e){
                identifyCard3();
            });
            $('#ForceTFormModel_loyalty_card').keyup(function(e){
                if(e.keyCode == 13)
            {
                $(this).trigger("enterKey");
            }
            });
            
        $('#lblotheramount').click(function(){
            if($('#ForceTFormModell_terminal_id').val() == '') {
                return false;
            }
            $('#ForceTFormModel_amount').val('');
            $('#ForceTFormModel_sel_amount').val('');
            if($('#chkotheramount').is(':checked')) {
                $('#chkotheramount').removeAttr('checked');
                $('#ForceTFormModel_sel_amount').removeAttr('disabled');
                $('#ForceTFormModel_amount').attr('disabled','disabled');
            } 
            else if($('#chkbancnet').is(':checked')){
                $('#chkotheramount').removeAttr('checked');
                $('#ForceTFormModel_sel_amount').removeAttr('disabled');
                $('#ForceTFormModel_amount').attr('disabled','disabled');
            }
            else {
                $('#chkotheramount').attr('checked','checked');
                $('#ForceTFormModel_amount').removeAttr('disabled');
                $('#ForceTFormModel_amount').val(0.00);
                $('#ForceTFormModel_sel_amount').attr('disabled','disabled');
            }
        });
        
        $('#chkotheramount').click(function(){
            if($(this).is(':checked')) {
                $('#ForceTFormModel_amount').removeAttr('disabled');
                $('#ForceTFormModel_amount').val(0.00);
                $('#chkbancnet').attr('disabled','disabled');
                $('#ForceTFormModel_tracenumber').val('');
                $('#ForceTFormModel_sel_amount').attr('disabled','disabled');
                $('#ForceTFormModel_voucher_code').attr('disabled','disabled');
                $('#ForceTFormModel_sel_amount').val('');
                $('#ForceTFormModel_voucher_code').val('');
            } else {
                $('#ForceTFormModel_amount').val('');
                $('#ForceTFormModel_tracenumber').val('');
                $('#ForceTFormModel_referencenumber').val('');
                $('#chkbancnet').removeAttr('disabled');
                $('#ForceTFormModel_amount').attr('disabled','disabled');
                $('#ForceTFormModel_sel_amount').removeAttr('disabled');
                $('#ForceTFormModel_voucher_code').removeAttr('disabled');
            }
        });
        
        $('#chkbancnet').click(function(){
            if($(this).is(':checked')) {
                $('#ForceTFormModel_referencenumber').removeAttr('disabled');
                $('#ForceTFormModel_tracenumber').removeAttr('disabled');
                $('#ForceTFormModel_amount').removeAttr('disabled');
                $('#ForceTFormModel_amount').val(0.00);
                $('#ForceTFormModel_sel_amount').attr('disabled','disabled');
                $('#chkotheramount').attr('disabled','disabled');
                $('#ForceTFormModel_voucher_code').attr('disabled','disabled');
                $('#ForceTFormModel_sel_amount').val('');
                $('#ForceTFormModel_voucher_code').val('');
            } else {
                $('#ForceTFormModel_amount').val('');
                $('#ForceTFormModel_tracenumber').val('');
                $('#ForceTFormModel_amount').attr('disabled','disabled');
                $('#ForceTFormModel_tracenumber').attr('disabled','disabled');
                $('#ForceTFormModel_amount').attr('disabled','disabled');
                $('#chkotheramount').removeAttr('disabled');
                $('#ForceTFormModel_referencenumber').val('');
                $('#ForceTFormModel_referencenumber').attr('disabled','disabled');
                $('#ForceTFormModel_sel_amount').removeAttr('disabled');
                $('#ForceTFormModel_voucher_code').removeAttr('disabled');
            }
        });
        
        //for mouseout event for initial deposit and voucher fields
        $('#ForceTFormModel_sel_amount').mouseout(function() {       
            if($('#chkotheramount').is(':checked')){
                //voucher code textbox  will stay disabled..
            } else {
                $('#ForceTFormModel_voucher_code').removeAttr('disabled');
            }
        });
        $('#ForceTFormModel_voucher_code').mouseout(function() {
            if($('#chkotheramount').is(':checked')){
                //sel_amount drop down  will stay disabled..
            } else {
                $('#ForceTFormModel_sel_amount').removeAttr('disabled');
                if($('#ForceTFormModel_voucher_code').val() != ''){
                    $('#ForceTFormModel_amount').val('');
                }
            }
        });    
            
        $('#ForceTFormModel_voucher_code').focus(function() {
        $('#ForceTFormModel_voucher_code').removeAttr('disabled');
        $('#ForceTFormModel_sel_amount').attr('disabled', 'disabled');    
                    //for keypress event, allow alphanumeric values in voucher code
                    $("#ForceTFormModel_voucher_code").bind("keypress", function (event) {
                            if (event.charCode!=0) {
                                var regex = new RegExp("^[a-zA-Z0-9]+$");
                                var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                                if (!regex.test(key)) {
                                    event.preventDefault();
                                    return false;
                                }
                            }

                            //clear amount textbox when keypress event start in voucher code textbox
                            if($('#ForceTFormModel_voucher_code').val() != '' ){
                                    $('#ForceTFormModel_sel_amount').val('');
                                    $('#ForceTFormModel_amount').val('');
                                    $('#chkotheramount').removeAttr('checked');
                                    $('#ForceTFormModel_amount').removeAttr('disabled');
                                    $('#ForceTFormModel_amount').attr('disabled', 'disabled');
                            }
                    });

                    //clear amount textbox when voucher code textbox has value
                    if($('#ForceTFormModel_voucher_code').val() != '' ){
                                $('#ForceTFormModel_sel_amount').val('');
                                $('#ForceTFormModel_amount').val('');
                                $('#chkotheramount').removeAttr('checked');
                                $('#ForceTFormModel_amount').removeAttr('disabled');
                                $('#ForceTFormModel_amount').attr('disabled', 'disabled');
                        }
                    //-------------------end code for keypress event---------------------//
    });
    
    $('#ForceTFormModel_sel_amount').focus(function() {
            $('#ForceTFormModel_sel_amount').removeAttr('disabled');
            $('#ForceTFormModel_voucher_code').attr('disabled', 'disabled');
            $('#ForceTFormModel_sel_amount').change(function() {
                if($('#ForceTFormModel_sel_amount').val() != '' ){
                        $('#ForceTFormModel_voucher_code').val('');
                }
            });
        });
        $('#ForceTFormModel_amount').focus(function() {
            $('#ForceTFormModel_voucher_code').attr('disabled', 'disabled');
            $('#ForceTFormModel_amount').change(function() {
                if($('#ForceTFormModel_amount').val() != '' ){
                        $('#ForceTFormModel_voucher_code').val('');
                }
            });
        });
    
    });
    
    $('#chkotheramount').click(function(){
        if($(this).is(':checked')) {
            $('#ForceTFormModel_amount').removeAttr('disabled');
            $('#ForceTFormModel_amount').val(0.00);
            $('#ForceTFormModel_tracenumber').val('');
            $('#ForceTFormModel_sel_amount').attr('disabled','disabled');
            $('#ForceTFormModel_voucher_code').attr('disabled','disabled');
            $('#ForceTFormModel_sel_amount').val('');
            $('#ForceTFormModel_voucher_code').val('');
        } else {
            $('#ForceTFormModel_amount').val('');
            $('#ForceTFormModel_tracenumber').val('');
            $('#ForceTFormModel_amount').attr('disabled','disabled');
            $('#ForceTFormModel_sel_amount').removeAttr('disabled');
            $('#ForceTFormModel_voucher_code').removeAttr('disabled');
        }
    });
    
    $('#lblotheramount').click(function(){
        if($('#ForceTFormModel_terminal_id').val() == '') {
            return false;
        }
        $('#ForceTFormModel_amount').val('');
        $('#ForceTFormModel_sel_amount').val('');
        if($('#chkotheramount').is(':checked')) {
            $('#chkotheramount').removeAttr('checked');
            $('#ForceTFormModel_sel_amount').removeAttr('disabled');
            $('#ForceTFormModel_amount').attr('disabled','disabled');
        } else {
            $('#chkotheramount').attr('checked','checked');
            $('#ForceTFormModel_amount').removeAttr('disabled');
            $('#ForceTFormModel_amount').val(0.00);
            $('#ForceTFormModel_sel_amount').attr('disabled','disabled');
        }
    });
    
    
    $('#btnLoad').click(function(){
            var voucher = $("#ForceTFormModel_voucher_code").val();
            var cardnumber = $("#ForceTFormModel_loyalty_card").val();
            var bcf = $('#head-bcf > div').children('span').html();
            var min_load = toInt($('#ForceTFormModel_min_deposit').val());
            var max_load = toInt($('#ForceTFormModel_max_deposit').val());
            
            //check loyalty card if empty
            if(cardnumber == ''){
                alert('Please enter card number');
                return false;
            }
            
            var issuccess = identifyCard3();
            
            if(issuccess == "false"){
            
            if($('#ForceTFormModel_sel_amount').is(':disabled')) {
                var amount = $('#ForceTFormModel_amount').val();
            } else {
                var amount = $('#ForceTFormModel_sel_amount').val();
            }
            
            bcf = toInt(bcf);
            amount = toInt(amount);
            
        
            if(voucher == ''){
                // check if amount is divisible by 100
                if(amount == '') {
                    alert('Please select amount');
                    return false;
                }
                
                // check if amount is divisible by 100
                if(parseFloat(amount) % 100 != 0) {
                    alert('Amount should be divisible by 100');
                    return false;
                }

                if(parseFloat(amount) < parseInt(min_load)) {
                    alert('Amount should be greater than or equal to PhP '.concat(min_load));
                    return false;
                }
                
                if(parseFloat(amount) > parseFloat(max_load)) {
                    alert('Amount should be less than or equal to PhP '.concat(max_load));
                    return false;
                }

                // check initial deposit is greater than bcf
                if(parseFloat(amount) > parseFloat(bcf)) {
                    alert('Not enough bcf');
                    return false;
                }
            }
                if($('#chkbancnet').is(':checked')){
                    if($('#ForceTFormModel_tracenumber').val() == ''){
                        alert('Please enter trace number.');
                        return false;
                    }
                    if($('#ForceTFormModel_referencenumber').val() == ''){
                        alert('Please enter reference number');
                        return false;
                    }
                 }
                
            //check voucher length
            if(voucher.length == 0){
                if(!confirm('Are you sure you want to load this account with the amount of ' + toMoney(amount)+ ' ?')) {
                    return false;
                }
            } else {
                $("#ForceTFormModel_voucher_code").val(voucher);
                if(!confirm('Are you sure you want to load this account using a voucher?')) {
                    return false;
                }
            }
            
                    showLightbox(function(){
                    var url = '<?php echo Mirage::app()->createUrl('reload/ubaccount') ?>';
                    var data = $('#frmreloadsa').serialize();
                    $.ajax({
                        type : 'post',
                        url : url,
                        data :data,
                        success : function(data) {
                            try {
                                var json = $.parseJSON(data);
                                alert(json.message);
                            }catch(e) {
                                alert('Oops! Something went wrong');
                            }
                            location.reload(true);
                        },
                        error : function(e) {
                            displayError(e);
                        }
                    });
                });
            }
            return false;
        });
</script>