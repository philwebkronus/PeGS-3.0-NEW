<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Deposit</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        
        <div class="row">
            <?php echo CHtml::label('CardNumber','lblcardnumber'); ?>
            <?php echo CHtml::textField('CardNumber'); ?>
        </div>
        
        <div class="row">
            <?php echo CHtml::label('AccessDate','AccessDate'); ?>
            <?php echo CHtml::textField('AccessDate'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Token','Token'); ?>
            <?php echo CHtml::textField('Token'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('ServiceID','lblServiceID'); ?>
            <?php echo CHtml::textField('ServiceID'); ?>
        </div>
        
        <div class="row">
            <?php echo CHtml::label('PaymentType','lblPaymentType'); ?>
            <?php echo CHtml::textField('PaymentType'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('Amount','lblAmount'); ?>
            <?php echo CHtml::textField('Amount'); ?>
        </div>    
    
        <div class="row">
            <?php echo CHtml::label('SiteID','lblSiteID'); ?>
            <?php echo CHtml::textField('SiteID'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('AID','lblAID'); ?>
            <?php echo CHtml::textField('AID'); ?>
        </div>
        
        <div class="row">
            <?php echo CHtml::label('TraceNumber','lblTraceNumber'); ?>
            <?php echo CHtml::textField('TraceNumber'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('ReferenceNumber','lblReferenceNumber'); ?>
            <?php echo CHtml::textField('ReferenceNumber'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('CouponCode','CouponCode'); ?>
            <?php echo CHtml::textField('CouponCode'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('PaymentTrackingID','PaymentTrackingID'); ?>
            <?php echo CHtml::textField('PaymentTrackingID'); ?>
        </div>
    
        <div class="row">
            <?php echo CHtml::label('System Username','lblSystemUsername'); ?>
            <?php echo CHtml::textField('SystemUsername'); ?>
        </div>
    
        <div class="row" style="margin-left: 225px;">
            <?php echo CHtml::submitButton('Invoke'); ?>
        </div>
    
    <?php echo CHtml::endForm(); ?>
</div>


<div class="result" style="margin-left: 20px; word-wrap: break-word;">
    <?php if (!is_null($result)) : ?>
        <p>JSON Result :</p>
        <?php echo $result; ?>
    <?php endif; ?>
</div>
