<?php
/**
 * @author owliber
 * @date Nov 11, 2012
 * @filename _voucherinfo.php
 * 
 */
?>

<?php if (is_array($result) && count($result) > 0): ?>
<!--class="results prepend-top append-bottom span-21"-->
    <div id="voucher-result" class="view" >
         <?php
        echo CHtml::beginForm(array(
            '/redemption/verify'), 'POST', array(
            'id' => 'RedeemForm',
            'name' => 'RedeemForm'));
        ?>
        <table class="">
            <tr>
                <td class="colborder right">Voucher Type</td>
                <td><b><?php echo CHtml::encode($result['VoucherType']); ?></b></td>
            </tr>
            <tr>
                <td class="colborder right">Voucher Code</td>
                <td><b><?php echo CHtml::encode($result['VoucherCode']); ?></b></td>
            </tr>
            <tr>
                <td class="colborder right">Amount</td>
                <td><b><?php echo CHtml::encode(number_format($result['Amount'],2)); ?></b></td>
            </tr>
            <tr>
                <td class="colborder right">Date Created</td>
                <td><b><?php echo CHtml::encode(date('F d, Y H:i', strtotime($result['DateCreated']))); ?></b></td>
            </tr>
            <?php if ($result['Status'] == 'Claimed'): ?>
                <tr>
                    <td class="colborder right">Date Claimed</td>
                    <td><b><?php echo CHtml::encode(date('F d, Y H:i', strtotime($result['DateClaimed']))); ?></b></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td class="colborder right">Expiry Date</td>
                <td><b><?php echo CHtml::encode(date('F d, Y', strtotime($result['DateExpiry']))); ?></b></td>
            </tr>
            <tr>
                <td class="colborder right">Terminal Code</td>
                <td><b><?php echo CHtml::encode($result['TerminalCode']); ?></b></td>
            </tr>
            <tr>
                <td class="colborder right">Site Name</td>
                <td><b><?php echo CHtml::encode($result['SiteName']); ?></b></td>
            </tr>
            <tr>
                <td class="colborder right">Status</td>
                <?php $statcolor = $result['Status'] == 'Claimed' ? '#FF0000' : '#0C7A3A' ?>
                <td style="color:<?php echo $statcolor; ?>"><b><?php echo CHtml::encode($result['Status']); ?></b></td>
            </tr>
        </table> 

        <div class="row button middle">
            <?php if ($result['StatusCode'] == 1 || $result['StatusCode'] == 2): ?>
                <?php echo CHtml::hiddenField("VoucherCode", $result['VoucherCode']); ?>
                <?php
                echo CHtml::button("Redeem", array(
                    'name' => 'Redeem',
                    'value' => 'Redeem Ticket',
                    'class' => 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                    'onclick' => '$("#confirm-dialog").dialog("open")'
                ));
                ?>
                <?php
                echo CHtml::button("Cancel", array(
                    'class' => 'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only',
                    'onclick' => 'document.location="verify"'));
                ?>
    <?php endif; ?>    
        </div>
        <?php echo CHtml::endForm(); ?>

    </div>

    <!-- Confirm redemption dialog -->
    <?php
    $this->beginWidget('zii.widgets.jui.CJuiDialog', array(
        'id' => 'confirm-dialog',
        'options' => array(
            'title' => 'Confirm Voucher Redemption',
            'modal' => true,
            'width' => '400',
            'height' => '250',
            'resizable' => false,
            'autoOpen' => false,
            'buttons' => array(
                'Redeem' => 'js:function(){
                        $("#RedeemForm").submit();
                        $("#confirm-dialog").dialog("close")
                }',
                'Cancel' => 'js:function(){
                    $("#confirm-dialog").dialog("close")
                }',
            ),
        ),
    ));
    ?>

    <p>Voucher Code : <b><?php echo $result['VoucherCode']; ?></b><br />
        Voucher : <b>Payout Ticket</b><br />
        Amount : <b><?php echo number_format($result['Amount'],2); ?></b><br />
        Status : <b><?php echo $result['Status']; ?></b></b><br /><br />
    Are you sure you want to continue?</p>

    <?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>

<?php endif; ?>

<!-- Redemption message -->
<?php
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'message-dialog',
    'options' => array(
        'title' => 'Redemption Status',
        'modal' => true,
        'width' => '400',
        'height' => '155',
        'resizable' => false,
        'autoOpen' => $this->success == 1 ? true : false,
        'buttons' => array(
            'Close' => 'js:function(){
                    $("#message-dialog").dialog("close");
                    document.location = "verify";
                }',
        ),
    ),
));
?>

<p><?php echo $this->success == 1 ? 'Ticket redemption is successful' : 'Ticket redemption failed'; ?></p>

<?php $this->endWidget('zii.widgets.jui.CJuiDialog'); ?>



