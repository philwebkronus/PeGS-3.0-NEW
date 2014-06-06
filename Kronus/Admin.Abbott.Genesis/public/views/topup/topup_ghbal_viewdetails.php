<?php 
if(isset($param['SiteID']) && $param['SiteID'] != ''){
    $pagetitle = "GH Balance Per Cut-off >> View Details"; 
    include "header.php";
    $vaccesspages = array('5');
        $vctr = 0;
        if(isset($_SESSION['acctype']))
        {
            foreach ($vaccesspages as $val)
            {
                if($_SESSION['acctype'] == $val)
                {
                    break;
                }
                else
                {
                    $vctr = $vctr + 1;
                }
            }

            if(count($vaccesspages) == $vctr)
            {
                echo "<script type='text/javascript'>document.getElementById('blockl').style.display='block';
                             document.getElementById('blockf').style.display='block';</script>";
            }
            else
            {
    ?>
    <div id="workarea">
        <form method="post" id="frmexportviewdetails">
            <input type="hidden" id="hdnvSiteID" name="hdnvSiteID" value="<?php echo $param['SiteID']; ?>">
            <input type="hidden" id="hdnvStartDate" name="hdnvStartDate" value="<?php echo $param['StartDate']; ?>">
            <input type="hidden" id="hdnvEndDate" name="hdnvEndDate" value="<?php echo $param['EndDate']; ?>">
            <div id="pagetitle"><?php echo $pagetitle; ?></div>
            <br />
            <table id="ghbalviewdetails" style="width: 700px; border: 1px solid white; font-style: Arial;">
                <tr id="tbldarkgray">
                    <td style="width: 150px;"><b>Site/ PEGS Name</b></td><td id="pegsname"style="width: 150px;" ><?php echo $param['SiteName']; ?></td><td style="width: 150px;"></td><td style="width: 150px;"></td>
                </tr>
                <tr id="tbllightgray">
                    <td><b>Site/ PEGS Code</b></td><td id="pegscode"><?php echo $param['SiteCode']; ?></td><td></td><td></td>
                </tr>
                <tr id="tbldarkgray">
                    <td><b>POS Account</b></td><td id="posaccount"><?php echo $param['POSAccountNo']; ?></td><td></td><td></td>
                </tr>
                <tr id="tbllightgray">
                    <td><b>Cut Off Date</b></td><td id="cutoff"><?php echo $param['CutOff']; ?></td><td></td><td></td>
                </tr>
                <tr id="tbldarkgray" style="height: 25px;">
                    <td></td><td></td><td></td><td></td>
                </tr>
                <tr id="tbllightgray">
                    <td><b>Beginning Balance</b></td><td></td><td></td><td id="begbal" style="text-align: right;"><?php echo $param['BegBal']; ?></td>
                </tr>
                <tr id="tbldarkgray">
                    <td><b>Deposit</b></td><td></td><td></td><td id="deposit" style="text-align: right;"><?php echo $param['TotalDeposit']; ?></td>
                </tr>
                <tr id="tbllightgray">
                    <td></td><td><b>Cash</b></td><td id="depositcash" style="text-align: right;"><?php echo $param['DepositCash']; ?></td><td></td>
                </tr>
                <tr id="tbldarkgray">
                    <td></td><td><b>Ticket</b></td><td id="depositticket" style="text-align: right;"><?php echo $param['DepositTicket']; ?></td><td></td>
                </tr>
                <tr id="tbllightgray">
                    <td></td><td><b>Coupon</b></td><td id="depositcoupon" style="text-align: right;"><?php echo $param['DepositCoupon']; ?></td><td></td>
                </tr>
                <tr id="tbldarkgray">
                    <td><b>Reloads</b></td><td></td><td></td><td id="reload" style="text-align: right;"><?php echo $param['TotalReload']; ?></td>
                </tr>
                <tr id="tbllightgray">
                    <td></td><td><b>Cash</b></td><td id="reloadcash"style="text-align: right;" ><?php echo $param['ReloadCash']; ?></td><td></td>
                </tr>
                <tr id="tbldarkgray">
                    <td></td><td><b>Ticket</b></td><td id="reloadticket"style="text-align: right;" ><?php echo $param['ReloadTicket']; ?></td><td></td>
                </tr>
                <tr id="tbllightgray">
                    <td></td><td><b>Coupon</b></td><td id="reloadcoupon" style="text-align: right;" ><?php echo $param['ReloadCoupon']; ?></td><td></td>
                </tr>
                <tr id="tbldarkgray">
                    <td><b>Redemption</b></td><td></td><td></td><td id="redemption" style="text-align: right;" ><?php echo $param['TotalRedemption']; ?></td>
                </tr>
                <tr id="tbllightgray">
                    <td></td><td><b>Cashier</b></td><td id="redemptioncashier" style="text-align: right;" ><?php echo $param['RedemptionCashier']; ?></td><td></td>
                </tr>
                <tr id="tbldarkgray">
                    <td></td><td><b>Genesis</b></td><td id="redemptiongenesis" style="text-align: right;" ><?php echo $param['RedemptionGenesis']; ?></td><td></td>
                </tr>
                <tr id="tbllightgray">
                    <td><b>Manual Redemption</b></td><td></td><td></td><td id="manualredemption" style="text-align: right;" ><?php echo $param['ManualRedemption']; ?></td>
                </tr>
                <tr id="tbldarkgray">
                    <td><b>Encashed Tickets</b></td><td></td><td></td><td id="encashedtickets" style="text-align: right;"><?php echo $param['EncashedTickets']; ?></td>
                </tr>
                <tr id="tbllightgray" style="height: 25px;">
                    <td></td><td></td><td></td><td></td>
                </tr>
                <tr id="tbldarkgray">
                    <td><b>Cash on Hand</b></td><td></td><td></td><td id="cashonhand" style="text-align: right;"><?php echo $param['CashOnHand']; ?></td>
                </tr>
                <tr id="tbllightgray">
                    <td><b>Gross Hold</b></td><td></td><td></td><td id="grosshold" style="text-align: right;"><?php echo $param['GrossHold']; ?></td>
                </tr>
                <tr id="tbldarkgray">
                    <td><b>Replenishment</b></td><td></td><td></td><td id="replenishment" style="text-align: right;"><?php echo $param['Replenishment']; ?></td>
                </tr>
                <tr id="tbllightgray">
                    <td><b>Collection</b></td><td></td><td></td><td id="collection" style="text-align: right;"><?php echo $param['Collection']; ?></td>
                </tr>
                <tr id="tbldarkgray">
                    <td><b>End Balance</b></td><td></td><td></td><td id="endbal" style="text-align: right;"><?php echo $param['EndBal']; ?></td>
                </tr>
                <tr id="tbllightgray">
                    <td colspan="4" ><input type="button" id="btnexportpdf" value="Export to PDF" style="float: right; display: block; text-decoration: none;" /></td>
                </tr>
            </table>
        </form>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function(){

            jQuery('#btnexportpdf').click(function(){
                jQuery('#frmexportviewdetails').attr('action','process/ProcessTopUpGenerateReports.php?action=grossholdbalanceviewdetailspdf');
                jQuery('#frmexportviewdetails').submit();            
            });

        });
    </script>
    <?php  
        }
    }
    include "footer.php";
} else {
    header("Location: GrossHoldBalance.php");
} ?>