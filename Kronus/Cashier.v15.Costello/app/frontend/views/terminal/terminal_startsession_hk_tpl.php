<script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<script type="text/javascript">
$(document).ready(function()
{                       
    if ($('#siteclassification').val() == 3)
    {
        $('#loyalty_card_tr').css('display','none');
    } 
    
    $('input.auto').autoNumeric();
    $('#StartSessionFormModel_voucher_code').hide();
    $('.bankContainer').hide();
     // CCT - BEGIN uncomment
    $('.hideControls').hide();
    // CCT - BEGIN added VIP
    //$('.hideControlsVIP').hide();
    //$('#StartSessionFormModel_vip_type').val(0);       
    //$('#StartSessionFormModel_lvip_type').val(0);      
    // CCT - END added VIP    
        
    $('#StartSessionFormModel_loyalty_card').bind('keydown', function(event) 
    {
        if (event.keyCode == 13 || event.charCode == 13 || event.keyCode==9) 
        {
            var cardNumber = $('#StartSessionFormModel_loyalty_card').val();
            if(cardNumber=='')
            {
                alert('Please enter loyalty card number.');
                return false;
            }
            var issuccess = identifyCard();
            if(issuccess == "false")
            {
                $('.btnSubmit').focus();
                $('#StartSessionFormModel_sel_amount').focus();
                // CCT - BEGIN added VIP
                //var terminalCode = $('#StartSessionFormModel_terminal_id > option:selected').html();
                //if (strpos(terminalCode, 'VIP') !== false) 
                //{
                //    if ($('#siteclassification').val() == 2) // Platinum
                //    {
                //        $('.hideControlsVIP').show();
                //        $('#viptypeVIP').attr('checked', 'checked');
                //    }
                //}
                // CCT - END added VIP                 
                return false;
            }
        }
        
        if(event.keyCode!=9)
        {
            $('.hideControls').hide();
            // CCT - BEGIN added VIP
            //$('.hideControlsVIP').hide();
            //$('#StartSessionFormModel_vip_type').val(0);
            //$('#StartSessionFormModel_lvip_type').val(0);                  
            // CCT - END added VIP           
            $('.bankContainer').hide();
            isEwalletSessionMode = false;
            isValidated = false;
            $('#StartSessionFormModel_sel_amount').val(0);
            $('#StartSessionFormModel_amount').val('');
            $('#StartSessionFormModel_voucher_code').val('');
            $('#StartSessionFormModel_trace_number').val('');
            $('#StartSessionFormModel_reference_number').val('');
            $('#StartSessionFormModel_amount').autoNumeric();
            document.getElementById('StartSessionFormModel_sel_amount').selectedIndex = 0;
        }
        });        
      // CCT - END uncomment
      })
</script>
<form id="frmhotkey">
    <?php if($startSessionFormModel->error_count): ?>
    <?php echo $startSessionFormModel->getErrorMessages(); ?>
    <?php endif; ?>       
    <?php echo MI_HTML::inputHidden($startSessionFormModel, 'max_deposit'); ?>
    <?php echo MI_HTML::inputHidden($startSessionFormModel, 'min_deposit'); ?>
    <!-- CCT BEGIN added VIP -->
    <!-- <?php //echo MI_HTML::inputHidden($startSessionFormModel, 'lvip_type') ?> -->
    <!-- CCT END added VIP-->
    <input type="hidden" name="acc_id" id="acc_id" value="<?php echo $_SESSION['accID'] ?>" />
    <input type="hidden" name="sitecode" id="sitecode" value="<?php echo $_SESSION['site_code'] ?>" />
    <input type="hidden" name="siteclassification" id="siteclassification" value="<?php echo $siteClassification; ?>" />
    <input type="hidden" name="siteamountinfo" id="siteamountinfo" value="<?php echo $siteAmountInfo; ?>" />
    <table id="tblstartsession">
        <tbody>
            <tr>
                <th><?php echo MI_HTML::label($startSessionFormModel, 'terminal_id', 'TERMINAL') ?></th>
                <td><?php echo MI_HTML::dropDownArray($startSessionFormModel, 'terminal_id', $terminals, 'id', 'code', array(''=>'Select Terminal'), array(),array('class'=>'width204')) ?></td>
            </tr>
            <!--
            // CCT - BEGIN uncomment
            -->
            <tr id="loyalty_card_tr">
                <th><?php echo MI_HTML::label($startSessionFormModel, 'loyalty_card', 'Membership Card') ?></th><td><?php echo MI_HTML::inputPassword($startSessionFormModel, 'loyalty_card', array('class'=>'width200')) ?></td>
            </tr>
            <!-- 
            // CCT - END uncomment
            -->
            <tr>
                <!--<th colspan ="2"><center><a href="javascript:void(0);" id="get_info_card">Get Card Info</a></center></th>-->
                <!--<td><b><a href="javascript:void(0);" id="register">Register</a></b></td>-->
            </tr>
            <!-- CCT BEGIN ADDED VIP -->
            <!--
            <tr class='hideControlsVIP'>
                <th><?php // echo MI_HTML::label($startSessionFormModel, 'vip_type', 'VIP TYPE') ?></th>
                <td>
                    <div>
                        <input type="radio" id="viptypeVIP" name="vip_type" value="1"/> VIP <input type="radio" id="viptypeSVIP" name="vip_type" value="2"/> SVIP 
                    </div>
                </td>
            </tr>
            -->
            <!-- CCT END ADDED VIP -->            
            <tr class='hideControls'>
                <th><?php echo MI_HTML::label($startSessionFormModel, 'sel_amount', 'AMOUNT') ?></th>
                <td>
                    <div>
                        <?php 
                        if($startSessionFormModel->terminal_id != '') 
                        {
                            echo MI_HTML::dropDown($startSessionFormModel, 'sel_amount', $denomination,array(''=>'Select Amount'),array('--'=>'Other denomination','voucher'=>'Voucher','bancnet'=>'Bancnet'), array('class'=>'width204'));
                        } 
                        else 
                        {
                            echo MI_HTML::dropDown($startSessionFormModel, 'sel_amount', array(),array(''=>'Select Amount'),array(),array('class'=>'width204'));
                        }
                        ?>
                    </div>
                    <?php echo MI_HTML::inputText($startSessionFormModel, 'amount',array('readonly'=>'readonly','class'=>'auto width200','maxlength'=>8)); ?>
                    <?php echo MI_HTML::inputText($startSessionFormModel, 'voucher_code',array('maxlength'=>20,'class'=>'width200')); ?>
                </td>
            </tr>
            <tr class="hideControls">
                <th class='bankContainer'><?php echo MI_HTML::label($startSessionFormModel, 'lbl_traceNumber', 'TRACE NUMBER') ?></th>
                <td class='bankContainer'><?php echo MI_HTML::inputText($startSessionFormModel, 'trace_number',array('class'=>'width200','maxlength'=>20)); ?> <td>
            </tr>
            <tr class="hideControls">
                <th class='bankContainer'><?php echo MI_HTML::label($startSessionFormModel, 'lbl_refNumber', 'REFERENCE NUMBER') ?></th>
                <td class='bankContainer'><?php echo MI_HTML::inputText($startSessionFormModel, 'reference_number',array('class'=>'width200','maxlength'=>20)); ?><td>
            </tr>
            <tr class='hideControls'>
                <th><?php echo MI_HTML::label($startSessionFormModel, 'casino', 'CASINO'); ?></th>
                <td>
                    <?php 
                    if($startSessionFormModel->terminal_id != '') 
                    {
                        echo MI_HTML::dropDown($startSessionFormModel, 'casino', $casinos);
                    } 
                    else 
                    {
                         echo MI_HTML::dropDown($startSessionFormModel, 'casino', array(''=>'Select Casino'), array(),array(),array('class'=>'width204'));
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <th colspan="2" class="childtableCell center" style="padding-left:100px;">
                    <div><input id="btnInitailDepositHk" mode="" type="button" value="Submit" class="btnSubmit"/></div>
                    <div><input class="btnClose" type="button" value="Cancel" /></div>
                </th>
            </tr>
        </tbody>
    </table>
</form>
<?php Mirage::loadLibraries('LoyaltyScripts'); ?>