<div class="form wide" style="margin-left: 20px;">
    <br />
    <h1>Member Registration</h1>
    <br />
    <?php echo CHtml::beginForm(); ?>
        <div class="row">
            <?php echo CHtml::label('FirstName','FirstName'); ?>
            <?php echo CHtml::textField('FirstName'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('MiddleName','MiddleName'); ?>
            <?php echo CHtml::textField('MiddleName'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('LastName','LastName'); ?>
            <?php echo CHtml::textField('LastName'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('NickName','NickName'); ?>
            <?php echo CHtml::textField('NickName'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Password','Password'); ?>
            <?php echo CHtml::textField('Password'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('PermanentAdd.','PermanentAdd.'); ?>
            <?php echo CHtml::textField('PermanentAddress'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('MobileNo','MobileNo'); ?>
            <?php echo CHtml::textField('MobileNumber'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('AlternateMobileNo','AlternateMobileNo'); ?>
            <?php echo CHtml::textField('AlternateMobileNumber'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('EmailAddress','EmailAddress'); ?>
            <?php echo CHtml::textField('EmailAddress'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('AlternateEmail','AlternateEmail'); ?>
            <?php echo CHtml::textField('AlternateEmail'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Gender','Gender'); ?>
            <?php echo CHtml::textField('Gender'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('IDPresented','IDPresented'); ?>
            <?php echo CHtml::textField('IDPresented'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('IDNumber','IDNumber'); ?>
            <?php echo CHtml::textField('IDNumber'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Nationality','Nationality'); ?>
            <?php echo CHtml::textField('Nationality'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Birthdate','Birthdate'); ?>
            <?php echo CHtml::textField('Birthdate'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('Occupation','Occupation'); ?>
            <?php echo CHtml::textField('Occupation'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('IsSmoker','IsSmoker'); ?>
            <?php echo CHtml::textField('IsSmoker'); ?>
        </div>
        <div class="row">
            <?php echo CHtml::label('ReferralCode','ReferralCode'); ?>
            <?php echo CHtml::textField('ReferralCode'); ?>
        </div>
<!--        <div class="row">
            <?php //echo CHtml::label('Age','Age'); ?>
            <?php //echo CHtml::textField('Age'); ?>
        </div>
        <div class="row">
            <?php //echo CHtml::label('Please send me offers, bonuses and casino announcements by Email. ','Please send me offers, bonuses and casino announcements by Email.'); ?>
            <?php //echo CHtml::checkbox('EmailSubscription'); ?>
        </div>
        <div class="row">
            <?php //echo CHtml::label('Please send me offers, bonuses and casino announcements by SMS. ','Please send me offers, bonuses and casino announcements by SMS.'); ?>
            <?php //echo CHtml::checkbox('SMSSubscription'); ?>
        </div>
        <div class="row">
            <?php //echo CHtml::label('I hereby confirm that I am at least 21 years old and have read and accepted the Terms And Conditions. ','I hereby confirm that I am at least 21 years old and have read and accepted the Terms And Conditions.'); ?>
            <?php //echo CHtml::checkbox('ConfirmAge'); ?>
        </div>-->
        <div class="row" style="margin-left: 225px;">
            <?php echo CHtml::submitButton('Invoke'); ?>
        </div>
    
    <?php echo CHtml::endForm(); ?>
</div>

<div class="result" style="margin-left: 20px;word-wrap: break-word;">
    <?php if (!is_null($result)) : ?>
        <p><strong>JSON Result :</strong></p>
        <?php echo $result; ?>
    <?php endif; ?>
</div>
