<!---------------------------------------ADD PARTNER---------------------------------------->
<?php
/**
 * Dialog Box for ADD PARTNER
 * Prompts when the Add Partner Button is clicked
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'addPartnerDialog',
    'options' => array(
        'title' => 'ADD PARTNER',
        'autoOpen' => false,
        'closeOnEscape' => false,
        'resizable' => false,
        'draggable' => false,
        'width' => '500',
        'height' => '500',
        'modal' => true,
        'open' => 'js:function(event, ui) {
                        $(this).siblings(".ui-dialog-titlebar-close").show();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).attr("disabled",true);
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).css({background:"#e2e2e2",
                                                                                           color:"#aaa"});
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                        $("#secondGroupAdd").hide();
                        $("#firstGroupAdd").show(); 
                        $("#msgdialogAdd").html("");
                                                                                                                                                      
                   }',
        'buttons' => array
            (
            'CANCEL' => 'js:function(){
                $(this).dialog("close");
            }',
            'BACK' => 'js:function(){
                $("#secondGroupAdd").hide();
                $("#firstGroupAdd").show();
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).attr("disabled",true);
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).css({background:"#e2e2e2",
                                                                                   color:"#aaa"});
                $("#msgdialog").html("");
                $("#wiz").attr("src","../../images/wizard1.png");
            }',
            'NEXT' => 'js:function(){
                    var result = validateInputs(0, 1);
                    if (result === true)
                    {
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).removeAttr("style");
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).removeAttr("disabled");
                        $("#firstGroupAdd").hide();
                        $("#secondGroupAdd").show();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).show();
                        $("#wiz").attr("src","../../images/wizard2.png");
                    }
                    
            }',
            'SAVE' => 'js:function(){
                    validateInputs(0, 2);
            }',
        ),
    ),
));
?>
<div id="addNewPartner">
    <div style="text-align: left;">
        <img id="wiz" src="<?php echo Yii::app()->request->baseUrl.'/images/wizard1.png'?>">
    </div>
    <br />
    <div style="text-align:left">
        <span id="msgdialogAdd"></span>
    </div>
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
   <table id="firstGroupAdd">
        <tr>
            <td>
                <?php echo $form->labelEx($model, "eGamesPartner", array('style'=>'font-weight:bold;')); ?>
            </td>
            <td>
                <?php echo $form->textField($model, 'eGamesPartner', array('id'=>'PartnerAdd', 'onkeypress'=>'return alphanumericnew4(event)')) ?>
            </td>    
        </tr>
        <tr>
            <td>
                <?php echo $form->labelEx($model, 'companyAddress', array('style'=>'font-weight:bold;')); ?>
            </td>
            <td>
                <?php echo $form->textField($model, 'companyAddress', array('id'=>'companyAddressAdd', 'onkeypress'=>'return alphanumericnew4(event)')) ?>
            </td>
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'phoneNumber', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'phoneNumber', array('id'=>'PNumberAdd', 'onkeypress'=>'return telephonenewkeypress(event)')) ?>
           </td>
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'faxNumber', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'faxNumber', array('id'=>'FNumberAdd', 'onkeypress'=>'return faxnumberonly(event)')) ?>
           </td>
       </tr>    
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'emailAddress', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'emailAddress', array('id'=>'EmailAddressAdd', 'onkeypress'=>'return emailkeypress(event)')) ?>
           </td>
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'website', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'website', array('id'=>'WebsiteAdd', 'onkeypress'=>'return websitekeypress(event)')) ?>
           </td>
       </tr>
    </table>
    <table id="secondGroupAdd">
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'contactPerson', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'contactPerson', array('id'=>'ContactPersonAdd', 'onkeypress'=>'return AlphaOnlyWithSpace(event)')) ?>
           </td>
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'username', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'username', array('id'=>'UsernameAdd', 'onkeypress'=>'return usernamekeypress(event)')) ?>
           </td>
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'contactPosition', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'contactPosition', array('id'=>'ContactPositionAdd', 'onkeypress'=>'return alphanumeric4(event)')) ?>
           </td>    
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'contactEmailAddress', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'contactEmailAddress', array('id'=>'ContactEmailAddressAdd', 'onkeypress'=>'return emailkeypress(event)')) ?>
           </td>    
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'contactPhoneNumber', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'contactPhoneNumber', array('id'=>'ContactPhoneNumberAdd', 'onkeypress'=>'return telephonenewkeypress(event)')) ?>
           </td>    
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'contactMobile', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'contactMobile', array('id'=>'ContactMobileAdd', 'onkeypress'=>'return numberonly(event)')) ?>
           </td>    
       </tr>
       <tr>
           <td>
                <?php echo $form->labelEx($model, 'partnershipStatus', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->dropDownList($model, 'partnershipStatus',array('-1'=>'Select Status','1'=>'Active','0'=>'Inactive'), 
                                                                   array('id'=>'StatusAdd', 'style'=>'width: 200px;')); ?>
           </td>
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'numberOfRewardOfferings', array('style'=>'font-weight:bold;', 'style' => 'display:none')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'numberOfRewardOfferings', array('id'=>'NumberOfRewardOfferingsAdd', 'onkeypress'=>'return numberonly(event)', 'style' => 'display:none')) ?>
           </td>    
       </tr>
    </table>
    <?php $this->endWidget(); ?>
</div>
<!--------------------------------------------------END OF ADD PARTNER-------------------------------------------------------->
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
