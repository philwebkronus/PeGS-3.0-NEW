<br />
    <?php if($UTFormModel->error_count): ?>
    <?php echo $UTFormModel->getErrorMessages(); ?>
    <?php endif; ?>
<form id="frmUnlocksa">
    <input type="hidden" name="acc_id" id="acc_id" value="<?php echo $_SESSION['accID'] ?>" />
    <input type="hidden" name="sitecode" id="sitecode" value="<?php echo $_SESSION['site_code'] ?>" />
    <?php echo MI_HTML::inputHidden($UTFormModel, 'max_deposit') ?>
    <?php echo MI_HTML::inputHidden($UTFormModel, 'min_deposit') ?>
    <table class="standalonetbl">
        <tr>
            <th><?php echo MI_HTML::label($UTFormModel, 'terminal_id', 'Gaming Terminal'); ?></th>
            <td><?php echo MI_HTML::dropDownArray($UTFormModel, 'terminal_id', $terminals, 'id', 'code', array(''=>'--Select Terminal--')) ?></td>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($UTFormModel, 'casino', 'Casino'); ?></th>
            <td><?php echo MI_HTML::dropDown($UTFormModel, 'casino', array(''=>'Select Casino'))  ?></td>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($UTFormModel, 'sel_amount', 'Initial Deposit'); ?></th>
            <td><?php echo MI_HTML::dropDown($UTFormModel, 'sel_amount', array(''=>'Select Amount', '0.00'=>'0.00')) ?></td>
            <th>
                <input type="checkbox" id="chkotheramount" disabled="disabled" name="chkotheramount"/>
                <?php echo MI_HTML::label($UTFormModel, 'amount', 'Other Amount',array('id'=>'lblotheramount'))  ?>
            </th>
            <td><?php echo MI_HTML::inputText($UTFormModel, 'amount', array('disabled'=>'disabled','class'=>'auto','maxlength'=>8)) ?></td>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($UTFormModel, 'voucher_code', 'Voucher Code') ?></th>
            <td><?php echo MI_HTML::inputText($UTFormModel, 'voucher_code', array('maxlength'=>20)) ?></td>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($UTFormModel, 'loyalty_card', 'Membership Card') ?></th>
            <td><?php echo MI_HTML::inputPassword($UTFormModel, 'loyalty_card') ?></td>
            <td><a href="javascript:void(0);" id="get_info_card">Get Card Info</a><a style="display: none;" href="javascript:void(0);" id="register">Register</a></td>
        </tr>
        <tr>
            <td><input type="button" value="Unlock" id="btnUnlockTerminalsa"/></td>
        </tr>
    </table>
</form>
<?php Mirage::loadLibraries(array('CardScripts','LoyaltyScripts')); ?>
<script type="text/javascript" src="jscripts/validation.js"></script> 
<script type="text/javascript" src="jscripts/check_partner.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $('#UnlockTerminalFormModel_terminal_id').focus();
        
        
        // change denomination
        $('#UnlockTerminalFormModel_sel_amount').live('change',function(){
            if($(this).val() != '--') {
                $('#UnlockTerminalFormModel_amount').val($(this).val());
                if($(this).val() == '')
                    $('#UnlockTerminalFormModel_amount').val('');
            }   
        });
        
        $('#btnUnlockTerminalsa').live('click',function(){
        if(!unlockStandAloneChecking()) {
            return false;
        } else {
            var issuccess = identifyCard2();
        }

        if(issuccess == "false")
        {
                if($("#UnlockTerminalFormModel_sel_amount").val() != 'voucher'){
                    if(!confirm('Are you sure you want to unlock this terminal with the initial playing balance of  ' + toMoney($('#UnlockTerminalFormModel_amount').val())+'?')) {
                        return false;
                    }
                } else {
                    if(!confirm('Are you sure you want to unlock this terminal using a voucher?')) {
                        return false;
                    }
                }
                
                //get terminal code for blocking
                    var preffixCode = '<?php echo $_SESSION['last_code']; ?>';
                    var terminalCode = $('#UnlockTerminalFormModel_terminal_id > option:selected').html();
                    terminalCode = terminalCode.replace(/vip/i,'');
                    terminalCode = preffixCode+terminalCode;
                
                var data = $('#frmUnlocksa').serialize();
                showLightbox(function(){
                    $.ajax({
                        url : '<?php echo Mirage::app()->createUrl('unlock'); ?>',
                        type :'post',
                        data : data,
                        success : function(data) {
                            try {
                                var json = $.parseJSON(data);
                                <?php if($_SESSION['spyder_enabled'] == 0): ?>
                                try {
                                    var oaxPSMAC = new ActiveXObject("PEGS.StationManager.ActiveX.Controller");
                                    if(oaxPSMAC.UnlockScreen(terminalCode,<?php echo Mirage::app()->param['port'] ?>) != 1) {
                                        alert('<?php echo Mirage::app()->param['failed_unlock'] ?> ' + terminalCode);
                                    }
                                } catch(e) {
                                    alert('<?php echo Mirage::app()->param['pegsstationerrormsg'] ?>');
                                }
                                <?php endif; ?>
                            alert('Transaction Successful \n New player session started. The player initial playing balance is PhP ' + json.initial_deposit);
                            location.reload(true);
                            } catch(e) {
                                updateLightbox(data,'START SESSION');
                            }
                        },
                        error : function(e) {
                            displayError(e);
                        }
                    });
                });
        }

        return false;
    });
        $('#UnlockTerminalFormModel_terminal_id').change(function(){
            $('#UnlockTerminalFormModel_casino').html('<option value="">Select Casino</option>');
            $('#UnlockTerminalFormModel_sel_amount').html('<option value="">Select Amount</option>');
            $('#UnlockTerminalFormModel_amount').val('');
            $('#UnlockTerminalFormModel_max_deposit').val('');
            $('#UnlockTerminalFormModel_min_deposit').val('');
            $('#UnlockTerminalFormModel_amount').attr('disabled','disabled');
            $('#chkotheramount').attr('disabled','disabled');
            $('#chkotheramount').removeAttr('checked');
            $('#UnlockTerminalFormModel_sel_amount').removeAttr('disabled');        
            if($(this).val() == '') {
                return false;
            }
            
            if(!checkPartner2($(this).children('option:selected').html())) {
                return false;
            }
            
            showLightbox(function(){
                url = '<?php echo $this->createUrl('terminal/denomination'); ?>';
                data = {terminal_id:$('#UnlockTerminalFormModel_terminal_id').val()};
                $.ajax({
                    type: 'post',
                    url:url,
                    data:data,
                    success : function(data){
                        $('#chkotheramount').removeAttr('disabled');
                        try {
                            var json = $.parseJSON(data);
                            var casopt = '';
                            var opt = '';
                            opt+='<option value="0.00" >Unlock Session</option>'
                            $.each(json.denomination,function(k,v){
                                opt+='<option value="'+k+'" >'+v+'</option>';
                            });
                            $('#UnlockTerminalFormModel_sel_amount').append(opt);
                            $.each(json.casino,function(k,v){
                                casopt+='<option value="'+k+'" >'+v+'</option>';
                            });
                            $('#UnlockTerminalFormModel_casino').html(casopt);
//                            console.log(json.max_denomination);
                            $('#UnlockTerminalFormModel_max_deposit').val(json.max_denomination);
                            $('#UnlockTerminalFormModel_min_deposit').val(json.min_denomination);
                            hideLightbox();
                        } catch(e) {
                            alert('Oops! Something went wrong');
                            location.reload(true);
                        }
                    },
                    error : function(e){
                        displayError(e);
                    }
                }); 
            });
        });
        
        $('#lblotheramount').click(function(){
            if($('#UnlockTerminalFormModel_terminal_id').val() == '') {
                return false;
            }
            $('#UnlockTerminalFormModel_amount').val('');
            $('#UnlockTerminalFormModel_sel_amount').val('');
            if($('#chkotheramount').is(':checked')) {
                $('#chkotheramount').removeAttr('checked');
                $('#UnlockTerminalFormModel_sel_amount').removeAttr('disabled');
                $('#UnlockTerminalFormModel_amount').attr('disabled','disabled');
            } else {
                $('#chkotheramount').attr('checked','checked');
                $('#UnlockTerminalFormModel_amount').removeAttr('disabled');
                $('#UnlockTerminalFormModel_amount').val(0.00);
                $('#UnlockTerminalFormModel_sel_amount').attr('disabled','disabled');
            }
        });
        
        $('#chkotheramount').click(function(){
            if($(this).is(':checked')) {
                $('#UnlockTerminalFormModel_amount').removeAttr('disabled');
                $('#UnlockTerminalFormModel_amount').val(0.00);
                $('#UnlockTerminalFormModel_sel_amount').attr('disabled','disabled');
                $('#UnlockTerminalFormModel_voucher_code').attr('disabled','disabled');
                $('#UnlockTerminalFormModel_sel_amount').val('');
                $('#UnlockTerminalFormModel_voucher_code').val('');
            } else {
                $('#UnlockTerminalFormModel_amount').val('');
                $('#UnlockTerminalFormModel_amount').attr('disabled','disabled');
                $('#UnlockTerminalFormModel_sel_amount').removeAttr('disabled');
                $('#UnlockTerminalFormModel_voucher_code').removeAttr('disabled');
            }
        });
        
        //for mouseout event for initial deposit and voucher fields
        $('#UnlockTerminalFormModel_sel_amount').mouseout(function() {       
            if($('#chkotheramount').is(':checked')){
                //voucher code textbox  will stay disabled..
            } else {
                $('#UnlockTerminalFormModel_voucher_code').removeAttr('disabled');
            }
        });
        $('#UnlockTerminalFormModel_voucher_code').mouseout(function() {
            if($('#chkotheramount').is(':checked')){
                //sel_amount drop down  will stay disabled..
            } else {
                $('#UnlockTerminalFormModel_sel_amount').removeAttr('disabled');
                if($('#UnlockTerminalFormModel_voucher_code').val() != ''){
                    $('#UnlockTerminalFormModel_amount').val('');
                }
            }
        });
        //-------------------------end code for mouseout event------------------------------//
        

        //onfocus event for initial deposit, other amount and voucher fields
        $('#UnlockTerminalFormModel_voucher_code').focus(function() {
            $('#UnlockTerminalFormModel_voucher_code').removeAttr('disabled');
            $('#UnlockTerminalFormModel_sel_amount').attr('disabled', 'disabled');    
                        //for keypress event, allow alphanumeric values in voucher code
                        $("#UnlockTerminalFormModel_voucher_code").bind("keypress", function (event) {
                                if (event.charCode!=0) {
                                    var regex = new RegExp("^[a-zA-Z0-9]+$");
                                    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                                    if (!regex.test(key)) {
                                        event.preventDefault();
                                        return false;
                                    }
                                }
                                
                                //clear amount textbox when keypress event start in voucher code textbox
                                if($('#UnlockTerminalFormModel_voucher_code').val() != '' ){
                                        $('#UnlockTerminalFormModel_sel_amount').val('');
                                        $('#UnlockTerminalFormModel_amount').val('');
                                        $('#chkotheramount').removeAttr('checked');
                                        $('#UnlockTerminalFormModel_amount').removeAttr('disabled');
                                        $('#UnlockTerminalFormModel_amount').attr('disabled', 'disabled');
                                }
                        });
                        
                        //clear amount textbox when voucher code textbox has value
                        if($('#UnlockTerminalFormModel_voucher_code').val() != '' ){
                                    $('#UnlockTerminalFormModel_sel_amount').val('');
                                    $('#UnlockTerminalFormModel_amount').val('');
                                    $('#chkotheramount').removeAttr('checked');
                                    $('#UnlockTerminalFormModel_amount').removeAttr('disabled');
                                    $('#UnlockTerminalFormModel_amount').attr('disabled', 'disabled');
                            }
                        //-------------------end code for keypress event---------------------//
        });
        $('#UnlockTerminalFormModel_sel_amount').focus(function() {
            $('#UnlockTerminalFormModel_sel_amount').removeAttr('disabled');
            $('#UnlockTerminalFormModel_voucher_code').attr('disabled', 'disabled');
            $('#UnlockTerminalFormModel_sel_amount').change(function() {
                if($('#UnlockTerminalFormModel_sel_amount').val() != '' ){
                        $('#UnlockTerminalFormModel_voucher_code').val('');
                }
            });
        });
        $('#UnlockTerminalFormModel_amount').focus(function() {
            $('#UnlockTerminalFormModel_voucher_code').attr('disabled', 'disabled');
            $('#UnlockTerminalFormModel_amount').change(function() {
                if($('#UnlockTerminalFormModel_amount').val() != '' ){
                        $('#UnlockTerminalFormModel_voucher_code').val('');
                }
            });
        });
        //-------------------------end code for onfocus event------------------------------//
        
       
    })
</script>