<?php Mirage::loadLibraries('LoyaltyScripts'); ?>
<?php if($startSessionFormModel->error_count): ?>
<?php echo $startSessionFormModel->getErrorMessages(); ?>
<?php endif; ?>  
<div id="tm-reload-form">
    <div>
        <form id="frmredeem">
            <table>
                <tr>
                    <th class="left"><?php echo MI_HTML::label($startSessionFormModel, 'terminal_id', 'TERMINAL:'); ?></th>
                    <td><?php echo MI_HTML::dropDownArray($startSessionFormModel, 'terminal_id', $terminals, 'id', 'code', array(''=>'Select Terminal'),null,array('id'=>'redeem_terminal_id')) ?></td>
                </tr>
                <tr>
                    <th class="left">CURRENT CASINO:</th>
                    <td id="current_casino"></td>
                </tr>
                <tr>
                    <th class="left">SHOW DETAILS</th>
                    <td><input type="checkbox" disabled="disabled" name="showdetails" id="showdetails" /></td>
                </tr>
                <tr>
                    <th class="left">BALANCE:</th>
                    <td><span id="redeem_terminal_balance"></span></td>
                </tr>
                <tr>
                    <th><input id="btnRedeemHk" type="submit" value="Submit" /></th>
                    <td><input class="btnClose" type="button" value="Cancel" /></td>
                </tr>
            </table>
        </form>   
    </div>
    <div class="details">
        <table border="1" style="width: 100%">
            <tr>
                <td colspan="5">LOGIN: <b id="reloadlogin"></b></td>
            </tr>
            <tr>
                <td colspan="5">TIME IN: 
                    <b id="reloadtimein">
        
                    </b>
                </td>
            </tr>
            <tr>
                <td colspan="5">INITIAL DEPOSIT: <b id="reloadinitialdeposit"></b></td>
            </tr>
            <tr>
                <td colspan="5">TOTAL RELOAD: <b id="reloadtotalreload"></b></td>
            </tr>
            <tr>
                <th colspan="5" style="background-color: #62AF35">
                    <i>SESSION DETAILS</i>
                </th>
            </tr>
            <tr>
                <th style="width: 70px;">Type</th><th style="width: 100px;">Amount</th><th>Time</th><th>Terminal Type</th><th>Source</th>
            </tr>
            <tbody id="reloadtbody">
                
            </tbody>
        </table>     
    </div>
</div>

