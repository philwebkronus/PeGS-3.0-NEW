<br />
    <?php if($startSessionFormModel->error_count): ?>
    <?php echo $startSessionFormModel->getErrorMessages(); ?>
    <?php endif; ?>
<form id="frmstartsessionsa">
    <input type="hidden" name="acc_id" id="acc_id" value="<?php echo $_SESSION['accID'] ?>" />
    <input type="hidden" name="sitecode" id="sitecode" value="<?php echo $_SESSION['site_code'] ?>" />
    <?php echo MI_HTML::inputHidden($startSessionFormModel, 'max_deposit') ?>
    <?php echo MI_HTML::inputHidden($startSessionFormModel, 'min_deposit') ?>
    <table class="standalonetbl">
        <tr>
            <th><?php echo MI_HTML::label($startSessionFormModel, 'terminal_id', 'Gaming Terminal'); ?></th>
            <td><?php echo MI_HTML::dropDownArray($startSessionFormModel, 'terminal_id', $terminals, 'id', 'code', array(''=>'--Select Terminal--')) ?></td>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($startSessionFormModel, 'casino', 'Casino'); ?></th>
            <td><?php echo MI_HTML::dropDown($startSessionFormModel, 'casino', array(''=>'Select Casino'))  ?></td>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($startSessionFormModel, 'sel_amount', 'Initial Deposit'); ?></th>
            <td><?php echo MI_HTML::dropDown($startSessionFormModel, 'sel_amount', array(''=>'Select Amount')) ?></td>
            <th>
                <input type="checkbox" id="chkotheramount" disabled="disabled" name="chkotheramount"/>
                <?php echo MI_HTML::label($startSessionFormModel, 'amount', 'Other Amount',array('id'=>'lblotheramount'))  ?>
            </th>
            <td><?php echo MI_HTML::inputText($startSessionFormModel, 'amount', array('disabled'=>'disabled','class'=>'auto','maxlength'=>8)) ?></td>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($startSessionFormModel, 'voucher_code', 'Voucher Code') ?></th>
            <td><?php echo MI_HTML::inputText($startSessionFormModel, 'voucher_code', array('maxlength'=>20)) ?></td>
        </tr>
        <tr>
            <th><?php echo MI_HTML::label($startSessionFormModel, 'loyalty_card', 'Membership Card') ?></th>
            <td><?php echo MI_HTML::inputPassword($startSessionFormModel, 'loyalty_card') ?></td>
            <td><a href="javascript:void(0);" id="get_info_card">Get Card Info</a><a style="display: none;" href="javascript:void(0);" id="register">Register</a></td>
        </tr>
        <tr>
            <td><input type="button" value="Start Session" id="btnstartsessionsa" /></td>
        </tr>
    </table>
</form>
<?php Mirage::loadLibraries(array('CardScripts','LoyaltyScripts')); ?>
<script type="text/javascript" src="jscripts/validation.js"></script> 
<script type="text/javascript" src="jscripts/check_partner.js"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $('#StartSessionFormModel_terminal_id').focus();
        
        
        $('#btnstartsessionsa').click(function(){
            if(!startSessionStandAloneChecking()) {
                return false;
            } else {
                var issuccess = identifyCard();
            }
            
            if(issuccess == "false")
            {
                    if($("#StartSessionFormModel_voucher_code").val() == ''){
                        //alert(toMoney($('#StartSessionFormModel_amount').val()));return false;
                        if(!confirm('Are you sure you want to start a new session with the initial playing balance of  ' + toMoney($('#StartSessionFormModel_amount').val())+'?')) {
                            return false;
                        }
                    } else {
                        if(!confirm('Are you sure you want to start a new session using a voucher?')) {
                            return false;
                        }
                    }

                    //get terminal code for blocking
                    var preffixCode = '<?php echo $_SESSION['last_code']; ?>';
                    var terminalCode = $('#StartSessionFormModel_terminal_id > option:selected').html();


                    terminalCode = terminalCode.replace(/vip/i,'');
                    terminalCode = preffixCode+terminalCode;

                    showLightbox(function(){
                        url = '<?php echo Mirage::app()->createUrl('startsession') ?>';
                        data = $('#frmstartsessionsa').serialize();
                        $.ajax({
                            type : 'post',
                            data : data,
                            url : url,
                            success : function(data){
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
                                    alert(json.message);
                                } catch(e) {
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
        
        // change denomination
        $('#StartSessionFormModel_sel_amount').live('change',function(){
            if($(this).val() != '--') {
                $('#StartSessionFormModel_amount').val($(this).children('option:selected').html());
                if($(this).val() == '')
                    $('#StartSessionFormModel_amount').val('');
            }   
        });
        
        
        $('#StartSessionFormModel_terminal_id').change(function(){
            $('#StartSessionFormModel_casino').html('<option value="">Select Casino</option>');
            $('#StartSessionFormModel_sel_amount').html('<option value="">Select Amount</option>');
            $('#StartSessionFormModel_amount').val('');
            $('#StartSessionFormModel_max_deposit').val('');
            $('#StartSessionFormModel_min_deposit').val('');
            $('#StartSessionFormModel_amount').attr('disabled','disabled');
            $('#chkotheramount').attr('disabled','disabled');
            $('#chkotheramount').removeAttr('checked');
            $('#StartSessionFormModel_sel_amount').removeAttr('disabled');        
            if($(this).val() == '') {
                return false;
            }
            
            if(!checkPartner($(this).children('option:selected').html())) {
                return false;
            }
            
            showLightbox(function(){
                url = '<?php echo $this->createUrl('terminal/denomination'); ?>';
                data = {terminal_id:$('#StartSessionFormModel_terminal_id').val()};
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
                            $.each(json.denomination,function(k,v){
                                opt+='<option value="'+k+'" >'+v+'</option>';
                            });
                            $('#StartSessionFormModel_sel_amount').append(opt);
                            $.each(json.casino,function(k,v){
                                casopt+='<option value="'+k+'" >'+v+'</option>';
                            });
                            $('#StartSessionFormModel_casino').html(casopt);
//                            console.log(json.max_denomination);
                            $('#StartSessionFormModel_max_deposit').val(json.max_denomination);
                            $('#StartSessionFormModel_min_deposit').val(json.min_denomination);
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
            if($('#StartSessionFormModel_terminal_id').val() == '') {
                return false;
            }
            $('#StartSessionFormModel_amount').val('');
            $('#StartSessionFormModel_sel_amount').val('');
            if($('#chkotheramount').is(':checked')) {
                $('#chkotheramount').removeAttr('checked');
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').attr('disabled','disabled');
            } else {
                $('#chkotheramount').attr('checked','checked');
                $('#StartSessionFormModel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').val(0.00);
                $('#StartSessionFormModel_sel_amount').attr('disabled','disabled');
            }
        });
        
        $('#chkotheramount').click(function(){
            if($(this).is(':checked')) {
                $('#StartSessionFormModel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_amount').val(0.00);
                $('#StartSessionFormModel_sel_amount').attr('disabled','disabled');
                $('#StartSessionFormModel_voucher_code').attr('disabled','disabled');
                $('#StartSessionFormModel_sel_amount').val('');
                $('#StartSessionFormModel_voucher_code').val('');
            } else {
                $('#StartSessionFormModel_amount').val('');
                $('#StartSessionFormModel_amount').attr('disabled','disabled');
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
            }
        });
        
        //for mouseout event for initial deposit and voucher fields
        $('#StartSessionFormModel_sel_amount').mouseout(function() {       
            if($('#chkotheramount').is(':checked')){
                //voucher code textbox  will stay disabled..
            } else {
                $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
            }
        });
        $('#StartSessionFormModel_voucher_code').mouseout(function() {
            if($('#chkotheramount').is(':checked')){
                //sel_amount drop down  will stay disabled..
            } else {
                $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
                if($('#StartSessionFormModel_voucher_code').val() != ''){
                    $('#StartSessionFormModel_amount').val('');
                }
            }
        });
        //-------------------------end code for mouseout event------------------------------//
        

        //onfocus event for initial deposit, other amount and voucher fields
        $('#StartSessionFormModel_voucher_code').focus(function() {
            $('#StartSessionFormModel_voucher_code').removeAttr('disabled');
            $('#StartSessionFormModel_sel_amount').attr('disabled', 'disabled');    
                        //for keypress event, allow alphanumeric values in voucher code
                        $("#StartSessionFormModel_voucher_code").bind("keypress", function (event) {
                                if (event.charCode!=0) {
                                    var regex = new RegExp("^[a-zA-Z0-9]+$");
                                    var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                                    if (!regex.test(key)) {
                                        event.preventDefault();
                                        return false;
                                    }
                                }
                                
                                //clear amount textbox when keypress event start in voucher code textbox
                                if($('#StartSessionFormModel_voucher_code').val() != '' ){
                                        $('#StartSessionFormModel_sel_amount').val('');
                                        $('#StartSessionFormModel_amount').val('');
                                        $('#chkotheramount').removeAttr('checked');
                                        $('#StartSessionFormModel_amount').removeAttr('disabled');
                                        $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
                                }
                        });
                        
                        //clear amount textbox when voucher code textbox has value
                        if($('#StartSessionFormModel_voucher_code').val() != '' ){
                                    $('#StartSessionFormModel_sel_amount').val('');
                                    $('#StartSessionFormModel_amount').val('');
                                    $('#chkotheramount').removeAttr('checked');
                                    $('#StartSessionFormModel_amount').removeAttr('disabled');
                                    $('#StartSessionFormModel_amount').attr('disabled', 'disabled');
                            }
                        //-------------------end code for keypress event---------------------//
        });
        $('#StartSessionFormModel_sel_amount').focus(function() {
            $('#StartSessionFormModel_sel_amount').removeAttr('disabled');
            $('#StartSessionFormModel_voucher_code').attr('disabled', 'disabled');
            $('#StartSessionFormModel_sel_amount').change(function() {
                if($('#StartSessionFormModel_sel_amount').val() != '' ){
                        $('#StartSessionFormModel_voucher_code').val('');
                }
            });
        });
        $('#StartSessionFormModel_amount').focus(function() {
            $('#StartSessionFormModel_voucher_code').attr('disabled', 'disabled');
            $('#StartSessionFormModel_amount').change(function() {
                if($('#StartSessionFormModel_amount').val() != '' ){
                        $('#StartSessionFormModel_voucher_code').val('');
                }
            });
        });
        //-------------------------end code for onfocus event------------------------------//
        
       
    })
</script>