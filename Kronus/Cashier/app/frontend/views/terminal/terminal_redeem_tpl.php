<?php Mirage::loadLibraries('LoyaltyScripts'); ?>
<div id="tm-reload-form">
    <input type="hidden" id="hidterminalid" value="<?php echo $terminal_id; ?>" />
    <div>
        <form id="frmredeem">
            <input id="StartSessionFormModel_terminal_id" name="StartSessionFormModel[terminal_id]" type="hidden" value="<?php echo $terminal_id; ?>" />
            <table>
                <tr>
                    <th>Show details</th>
                    <td><input type="checkbox" name="showdetails" id="showdetails_click" /></td>
                </tr>
                <tr>
                    <th>BALANCE:</th>
                    <td><span id="redeem_terminal_balance">PhP <?php echo $data['amount']; ?></span></td>
                </tr>
                <tr>
                    <th><input id="btnRedeemHk" type="submit" value="Submit" /></th>
                    <td><input class="btnClose" type="button" value="Cancel" /></td>
                </tr>
            </table>
        </form>   
    </div>
    <div class="details">
        <?php
            $initial_deposit = 0;
            $total_reload = 0;
            foreach($data['total_detail'] as $val) {
                if($val['TransactionType'] == 'D') {
                    $initial_deposit = $val['total_amount'];
                    $time_in = $val['DateCreated'];
                } else if($val['TransactionType'] == 'R') {
                    $total_reload = $val['total_amount'];
                }
            }
        ?>
        
        <table border="1" style="width: 100%">
            <tr>
                <td colspan="5">LOGIN: <b id="reloadlogin"></b></td>
            </tr>
            <tr>
                <td colspan="5">TIME IN: 
                    <b id="reloadtimein">
                        <?php echo $time_in; ?>
                    </b>
                </td>
            </tr>
            <tr>
                <td colspan="5">INITIAL DEPOSIT: <b id="reloadinitialdeposit"><?php echo 'PhP '.toMoney($initial_deposit); ?></b></td>
            </tr>
            <tr>
                <td colspan="5">TOTAL RELOAD: <b id="reloadtotalreload"><?php echo 'PhP '.toMoney($total_reload); ?></b></td>
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