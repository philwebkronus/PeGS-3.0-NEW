<!-----------------------------------VIEW/EDIT PARTNER------------------------------------------>
<?php
/**
 * Dialog Box for Viewing/Editing the Partner Details
 * This Dialog will prompt when the user clicks the PARTNERNAME
 * together with the details of the partner
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'editPartnerDialog',
    'options' => array(
        'title' => 'PARTNER\'S DETAILS',
        'autoOpen' => false,
        'closeOnEscape' => false,
        'resizable' => false,
        'draggable' => false,
        'modal' => true,
        'height' => '500',
        'width' => '300',
        'open' => 'js:function(event, ui) { 
                        $(this).siblings(".ui-dialog-titlebar-close").hide();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                        $("#msgdialog2").html("");
                        $(this).scrollTop(0);
                   }',
        'buttons' => array
            (
            'CANCEL' => 'js:function(){
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                $(this).dialog("close");
            }',
            'EDIT' => 'js:function(){
                    $(this).dialog("close");
                    $("#editPartner2ndDialog-compdtls").dialog("open");
            }',
            'SAVE' => 'js:function(){
                    validateInputs(1);
                    $(this).animate({ scrollTop: "0" });
            }'
        ),
    ),
));
?>
<div id="editNewPartner" style="text-align: left;">
    <span id="msgdialog2" style="text-align:left;"></span>
    <?php
    //Edit Form when user clicks PARTNERNAME
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'editpartner-form-pname',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('updatedetails')
    ));
    ?>
    <?php //echo $form->errorSummary($model); ?><br>
    <?php 
        echo $form->hiddenField($model, 'PartnerID', array('id'=>'PartnerID'));
        echo $form->hiddenField($model, 'presentStatus', array('id' => 'LastStatus'))
    ?>
    <div class="row">
        <?php echo $form->labelEx($model, "eGamesPartner"); ?><br>
        <?php echo $form->textField($model, 'eGamesPartner', array('id'=>'Partner', 'onkeypress'=>'return alphanumericnew4(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'eGamesPartner'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'companyAddress'); ?><br>
        <?php echo $form->textField($model, 'companyAddress', array('id'=>'companyAddress', 'onkeypress'=>'return addresskeypress(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'companyAddress'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'phoneNumber'); ?><br>
        <?php echo $form->textField($model, 'phoneNumber', array('id'=>'PNumber', 'onkeypress'=>'return telephonenewkeypress(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'phoneNumber'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'faxNumber'); ?><br>
        <?php echo $form->textField($model, 'faxNumber', array('id'=>'FNumber', 'onkeypress'=>'return faxnumberonly(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'faxNumber'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'emailAddress'); ?><br>
        <?php echo $form->textField($model, 'emailAddress', array('id'=>'EmailAddress', 'onkeypress'=>'return emailkeypress(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'emailAddress'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'website'); ?><br>
        <?php echo $form->textField($model, 'website', array('id'=>'Website', 'onkeypress'=>'return websitekeypress(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'website'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'contactPerson'); ?><br>
        <?php echo $form->textField($model, 'contactPerson', array('id'=>'ContactPerson', 'onkeypress'=>'return AlphaOnlyWithSpace(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'contactPerson'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'contactPosition'); ?><br>
        <?php echo $form->textField($model, 'contactPosition', array('id'=>'ContactPosition', 'onkeypress'=>'return alphanumeric4(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'contactPosition'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'contactEmailAddress'); ?><br>
        <?php echo $form->textField($model, 'contactEmailAddress', array('id'=>'ContactEmailAddress', 'onkeypress'=>'return emailkeypress(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'contactEmailAddress'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'contactPhoneNumber'); ?><br>
        <?php echo $form->textField($model, 'contactPhoneNumber', array('id'=>'ContactPhoneNumber', 'onkeypress'=>'return telephonekeypress(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'contactPhoneNumber'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'contactMobile'); ?><br>
        <?php echo $form->textField($model, 'contactMobile', array('id'=>'ContactMobile', 'onkeypress'=>'return numberonly(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'contactPhoneNumber'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'partnershipStatus'); ?><br>
        <?php echo $form->dropDownList($model, 'partnershipStatus',array('1'=>'Active','0'=>'Inactive'), 
                                                                   array('id'=>'Status', 'style'=>'width: 200px;')); ?>
        <?php //echo $form->error($model, 'partnershipStatus'); ?>
    </div>
    <br />
    <div class="row">
        <?php echo $form->labelEx($model, 'numberOfRewardOfferings'); ?><br>
        <?php echo $form->textField($model, 'numberOfRewardOfferings', array('id'=>'NumberOfRewardOfferings', 'onkeypress'=>'return numberonly(event)', 'disabled' => 'true')) ?>
        <?php //echo $form->error($model, 'contactPhoneNumber'); ?>
    </div>
    <br />
    <?php $this->endWidget(); ?>
<?php $this->endWidget(); ?>
</div>