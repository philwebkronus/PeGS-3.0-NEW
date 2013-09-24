<script type='text/javascript'>
function editDialog(){
        $('#editPartnerDialog').dialog("open");
        }
</script>
<h1>Manage Partners</h1>
<table id="grid1"></table>
<div id="pager1"></div>
<center>
    <br>
    <div id="linkButton">
        <?php echo CHtml::link('ADD PARTNER', '#', array('onclick' => '$("#addPartnerDialog").dialog("open"); return false;',)); ?>
    </div>
</center>
<?php
/** Start Widget * */
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'addPartnerDialog',
    'options' => array(
        'title' => 'ADD PARTNER',
        'autoOpen' => false,
        'closeOnEscape' => false,
        'resizable' => false,
        'draggable' => false,
        'modal' => true,
        'open' => 'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
            (
            'CANCEL' => 'js:function(){
                $(this).dialog("close");
            }',
            'NEXT' => 'js:function(){
                $(addPartner2Dialog).dialog("open");
            }',
        ),
    ),
));
?>
<div id="addNewPartner">
    <ul id="wzd-menu">
        <li><b><span>Company Details</span></b></li>
        <li><span>Other Details</span></li>
    </ul>
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'addpartner-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('addPartner')
    ));
    ?>
    <?php echo $form->errorSummary($model); ?><br>
    <div class="row">
        <?php echo CHtml::label("e-Games Partner", "eGamesPartner"); ?><br>
        <?php echo $form->textField($model, 'eGamesPartner', array('id'=>'eGamesPartner')) ?>
        <?php echo $form->error($model, 'eGamesPartner'); ?>
    </div>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'companyName'); ?><br>
        <?php echo $form->textField($model, 'companyName', array('id'=>'companyName')) ?>
        <?php echo $form->error($model, 'companyName'); ?>
    </div>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'phoneNumber'); ?><br>
        <?php echo $form->textField($model, 'phoneNumber', array('id'=>'phoneNumber')) ?>
        <?php echo $form->error($model, 'phoneNumber'); ?>
    </div>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'faxNumber'); ?><br>
        <?php echo $form->textField($model, 'faxNumber', array('id'=>'faxNumber')) ?>
        <?php echo $form->error($model, 'faxNumber'); ?>
    </div>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'emailAddress'); ?><br>
        <?php echo $form->textField($model, 'emailAddress', array('id'=>'emailAddress')) ?>
        <?php echo $form->error($model, 'emailAddress'); ?>
    </div>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'website'); ?><br>
        <?php echo $form->textField($model, 'website', array('id'=>'website')) ?>
        <?php echo $form->error($model, 'website'); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>

<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');

/** Start Widget * */
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'addPartner2Dialog',
    'options' => array(
        'title' => 'ADD PARTNER',
        'autoOpen' => false,
        'closeOnEscape' => false,
        'resizable' => false,
        'draggable' => false,
        'modal' => true,
        'open' => 'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
            (
            'BACK' => 'js:function(){
                $(this).dialog("close");
            }',
            'SAVE' => 'js:function(){
                $("#addpartner-form").submit();
            }',
        ),
    ),
));
?>
<div id="addNewPartner">
    <ul id="wzd-menu">
        <li><span>Company Details</span></li>
        <li><b><span>Other Details</span></b></li>
    </ul>
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'addpartner-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('addPartner')
    ));
    ?>
    <?php echo $form->errorSummary($model); ?>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'contactPerson'); ?><br>
        <?php echo $form->textField($model, 'contactPerson', array('id'=>'contactPerson')) ?>
        <?php echo $form->error($model, 'contactPerson'); ?>
    </div>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'contactPosition'); ?><br>
        <?php echo $form->textField($model, 'contactPosition', array('id'=>'contactPosition')) ?>
        <?php echo $form->error($model, 'contactPosition'); ?>
    </div>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'contactPhoneNumber'); ?><br>
        <?php echo $form->textField($model, 'contactPhoneNumber', array('id'=>'contactPhoneNumber')) ?>
        <?php echo $form->error($model, 'contactPhoneNumber'); ?>
    </div>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'contactMobile'); ?><br>
        <?php echo $form->textField($model, 'contactMobile', array('id'=>'contactMobile')) ?>
        <?php echo $form->error($model, 'contactMobile'); ?>
    </div>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'contactEmailAddress'); ?><br>
        <?php echo $form->textField($model, 'contactEmailAddress', array('id'=>'contactEmailAddress')) ?>
        <?php echo $form->error($model, 'contactEmailAddress'); ?>
    </div>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'partnershipStatus'); ?><br>
        <?php echo $form->dropDownList($model, 'partnershipStatus', array('1'=>'Active', '0'=>'Inactive'), array('prompt' => '--- Please select ---'), array('id'=>'partnershipStatus')); ?>
        <?php echo $form->error($model, 'partnershipStatus'); ?>
    </div>
    <br>
    <div class="row">
        <?php echo $form->labelEx($model, 'numberOfRewardOfferings'); ?><br>
        <?php echo $form->textField($model, 'numberOfRewardOfferings', array('id'=>'numberOfRewardOfferings')) ?>
        <?php echo $form->error($model, 'numberOfRewardOfferings'); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>

<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>

<script>
var partnerid = $('#editlinkid').val();
    $.ajax(
                    {
                        url: "edit",
                        type: 'post',
                        datatype: 'json',
                        data: {
                            id: function() {
                                return partnerid;
                            }
                        },
                        success: function()
                        {
                            $('#editPartnerDialog').dialog("open");
                        },
                        error: function(error)
                        {
                            alert("Error fetching data:"+error);
                        }
                    });
</script>

<?php
/** Start Widget * */
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'editPartnerDialog',
    'options' => array(
        'title' => 'EDIT PARTNER',
        'autoOpen' => false,
        'closeOnEscape' => false,
        'resizable' => false,
        'draggable' => false,
        'modal' => true,
        'open' => 'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
        'buttons' => array
            (
            'CANCEL' => 'js:function(){
                $(this).dialog("close");
            }',
            'EDIT' => 'js:function(){
                $(editPartner2Dialog).dialog("open");
            }',
        ),
    ),
));
?>
<div id="addNewPartner">
    <?php
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'editpartner-form',
        'enableClientValidation' => true,
        'enableAjaxValidation' => true,
        'clientOptions' => array(
            'validateOnSubmit' => true,
        ),
        'action' => $this->createUrl('index')
    ));
    ?>
    <?php echo $form->errorSummary($model); ?><br>
    <div class="row">
        <?php echo CHtml::label("e-Games Partner", "eGamesPartner"); ?><br>
        <?php echo $form->textField($model, 'eGamesPartner', array('id'=>'eGamesPartner', 'value' => "$model->zeGamesPartner", 'disabled' => 'true')) ?>
        <?php echo $form->error($model, 'eGamesPartner'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'companyAddress'); ?><br>
        <?php echo $form->textField($model, 'companyAddress', array('id'=>'companyAddress', 'value' => "$model->zcompanyAddress", 'disabled' => 'true')) ?>
        <?php echo $form->error($model, 'companyAddress'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'phoneNumber'); ?><br>
        <?php echo $form->textField($model, 'phoneNumber', array('id'=>'phoneNumber', 'value' => "$model->zphoneNumber", 'disabled' => 'true')) ?>
        <?php echo $form->error($model, 'phoneNumber'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'faxNumber'); ?><br>
        <?php echo $form->textField($model, 'faxNumber', array('id'=>'faxNumber', 'value' => "$model->zfaxNumber", 'disabled' => 'true')) ?>
        <?php echo $form->error($model, 'faxNumber'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'emailAddress'); ?><br>
        <?php echo $form->textField($model, 'emailAddress', array('id'=>'emailAddress', 'value' => "$model->zemailAddress", 'disabled' => 'true')) ?>
        <?php echo $form->error($model, 'emailAddress'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'website'); ?><br>
        <?php echo $form->textField($model, 'website', array('id'=>'website', 'value' => "$model->zwebsite", 'disabled' => 'true')) ?>
        <?php echo $form->error($model, 'website'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'contactPerson'); ?><br>
        <?php echo $form->textField($model, 'contactPerson', array('id'=>'contactPerson', 'value' => "$model->zcontactPerson", 'disabled' => 'true')) ?>
        <?php echo $form->error($model, 'contactPerson'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'contactPosition'); ?><br>
        <?php echo $form->textField($model, 'contactPosition', array('id'=>'contactPosition', 'value' => "$model->zcontactPosition", 'disabled' => 'true')) ?>
        <?php echo $form->error($model, 'contactPosition'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'contactEmailAddress'); ?><br>
        <?php echo $form->textField($model, 'contactEmailAddress', array('id'=>'contactEmailAddress', 'value' => "$model->zcontactEmailAddress", 'disabled' => 'true')) ?>
        <?php echo $form->error($model, 'contactEmailAddress'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'contactPhoneNumber'); ?><br>
        <?php echo $form->textField($model, 'contactPhoneNumber', array('id'=>'contactPhoneNumber', 'value' => "$model->zcontactPhoneNumber", 'disabled' => 'true')) ?>
        <?php echo $form->error($model, 'contactPhoneNumber'); ?>
    </div>
    <div class="row">
        <?php echo $form->labelEx($model, 'contactPhoneNumber'); ?><br>
        <?php echo $form->textField($model, 'contactPhoneNumber', array('id'=>'contactPhoneNumber', 'value' => "$model->zpartnershipStatus", 'disabled' => 'true')) ?>
        <?php echo $form->error($model, 'contactPhoneNumber'); ?>
    </div>
    <?php $this->endWidget(); ?>
</div>

<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');

$this->widget('application.components.widgets.JqGridWidget', array('tableID' => 'grid1', 'pagerID' => 'pager1',
    'jqGridParam' => array(
        'url' => $this->createUrl('index'),
        'loadonce' => true,
        'caption' => 'Manage Partner',
        'height' => '50',
        'colNames' => array('Partner Name', 'Status', 'Number of Reward Offers', 'Contact Person', "Contact Person's Email", ''),
        'colModel' => array(
            array('name' => 'Name', 'sortable' => false, 'width' => '35%', 'resizable' => true, 'align' => 'center'),
            array('name' => 'Status', 'sortable' => false, 'width' => '15%', 'resizable' => true, 'align' => 'center'),
            array('name' => 'NumberOfRewardOffers', 'sortable' => false, 'width' => '35%', 'resizable' => true, 'align' => 'center'),
            array('name' => 'ContactPerson', 'sortable' => false, 'width' => '35%', 'resizable' => true, 'align' => 'center'),
            array('name' => 'ContactPersonEmail', 'sortable' => false, 'width' => '35%', 'resizable' => true, 'align' => 'center'),
            array('name' => 'EditLink', 'sortable' => false, 'width' => '10%', 'resizable' => false, 'align' => 'center'),
        ),
)));
?>