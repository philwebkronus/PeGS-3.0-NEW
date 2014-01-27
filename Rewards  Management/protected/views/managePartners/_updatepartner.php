<!--------------------------------------EDIT PARTNER----------------------------------------->
<?php
/**
 * Dialog Box for Editing the Partner Details
 * This Dialog will prompt when the user clicks the EDIT Link
 * 
 */
$this->beginWidget('zii.widgets.jui.CJuiDialog', array(
    'id' => 'editPartner2ndDialog-compdtls',
    'options' => array(
        'title' => 'EDIT PARTNER\'S DETAILS',
        'autoOpen' => false,
        'closeOnEscape' => false,
        'resizable' => false,
        'draggable' => false,
        'modal' => true,
        'height' => '490',
        'width' => '500',
        'open' => 'js:function(event, ui) {
                        $(this).siblings(".ui-dialog-titlebar-close").show();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).attr("disabled",true);
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).css({background:"#e2e2e2",
                                                                                           color:"#aaa"});
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                        $("#secondGroup").hide();
                        $("#firstGroup").show(); 
                        $("#msgdialog").html("");
                                                                                                                                                      
                   }',
        'buttons' => array
            (
            'CANCEL' => 'js:function(){
                $(this).dialog("close");
            }',
            'BACK' => 'js:function(){
                $("#secondGroup").hide();
                $("#firstGroup").show();
                $("#msgdialog").html("");
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).hide();
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).hide();
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).show();
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).show();
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).attr("disabled",true);
                $(this).siblings(".ui-dialog-buttonpane").find("button").eq(4).css({background:"#e2e2e2",
                                                                                   color:"#aaa"});
                $("#cOthers").removeClass("active");
                $("#cDetails").removeClass("active visited").addClass("active");
                $("#wiz2").attr("src","../../images/wizard1.png");
            }',
            'NEXT' => 'js:function(){
                    var result = validateInputs(2, 1);
                    if (result === true)
                    {
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).removeAttr("style");
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).removeAttr("disabled");
                        $("#firstGroup").hide();
                        $("#secondGroup").show();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(0).hide();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(2).hide();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(1).show();
                        $(this).siblings(".ui-dialog-buttonpane").find("button").eq(3).show();
                        $("#cOthers").addClass("active");
                        $("#cDetails").removeClass("active").addClass("active visited");
                        $("#wiz2").attr("src","../../images/wizard2.png");
                    }
            }',
            'SAVE' => 'js:function(){
                    validateInputs(2, 2);
            }',
        ),
    ),
));
?>   
<div id="editNewPartner2">
    <div style="text-align: left;">
        <img id="wiz2" src="<?php echo Yii::app()->request->baseUrl.'/images/wizard1.png'?>">
    </div><br />
    <div style="text-align:left">
        <span id="msgdialog"></span>
    </div>    
    <?php
    //Edit Form when user clicks EDIT
    $form = $this->beginWidget('CActiveForm', array(
        'id' => 'editpartner-form-editlink',
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
        echo $form->hiddenField($model, 'PartnerID', array('id'=>'PartnerID2'));
        echo $form->hiddenField($model, 'presentStatus', array('id'=>'LastStatus2'));
    ?>
    <table id="firstGroup">
        <tr> 
            <td>
                <?php echo $form->labelEx($model, "eGamesPartner", array('style'=>'font-weight:bold;')); ?>
            </td>
            <td>
                <?php echo $form->textField($model, 'eGamesPartner', array('id'=>'Partner2', 'onkeypress'=>'return alphanumericnew4(event)')) ?>
            </td>    
        </tr>
        <tr>
            <td>
                <?php echo $form->labelEx($model, 'companyAddress', array('style'=>'font-weight:bold;')); ?>
            </td>
            <td>
                <?php echo $form->textField($model, 'companyAddress', array('id'=>'companyAddress2', 'onkeypress'=>'return alphanumericnew4(event)')) ?>
            </td>
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'phoneNumber', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'phoneNumber', array('id'=>'PNumber2', 'onkeypress'=>'return telephonenewkeypress(event)')) ?>
           </td>
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'faxNumber', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'faxNumber', array('id'=>'FNumber2', 'onkeypress'=>'return faxnumberonly(event)')) ?>
           </td>
       </tr>    
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'emailAddress', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'emailAddress', array('id'=>'EmailAddress2', 'onkeypress'=>'return emailkeypress(event)')) ?>
           </td>
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'website', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'website', array('id'=>'Website2', 'onkeypress'=>'return websitekeypress(event)')) ?>
           </td>
       </tr>
    </table>
    <table id="secondGroup">
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'contactPerson', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'contactPerson', array('id'=>'ContactPerson2', 'onkeypress'=>'return AlphaOnlyWithSpace(event)')) ?>
           </td>
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'contactPosition', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'contactPosition', array('id'=>'ContactPosition2', 'onkeypress'=>'return alphanumeric4(event)')) ?>
           </td>    
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'contactEmailAddress', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'contactEmailAddress', array('id'=>'ContactEmailAddress2', 'onkeypress'=>'return emailkeypress(event)')) ?>
           </td>    
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'contactPhoneNumber', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'contactPhoneNumber', array('id'=>'ContactPhoneNumber2', 'onkeypress'=>'return telephonenewkeypress(event)')) ?>
           </td>    
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'contactMobile', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'contactMobile', array('id'=>'ContactMobile2', 'onkeypress'=>'return numberonly(event)')) ?>
           </td>    
       </tr>
       <tr>
           <td>
                <?php echo $form->labelEx($model, 'partnershipStatus', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->dropDownList($model, 'partnershipStatus',array('1'=>'Active','0'=>'Inactive'), 
                                                                   array('id'=>'Status2', 'style'=>'width: 200px;')); ?>
           </td>
       </tr>
       <tr>
           <td>
               <?php echo $form->labelEx($model, 'numberOfRewardOfferings', array('style'=>'font-weight:bold;')); ?>
           </td>
           <td>
               <?php echo $form->textField($model, 'numberOfRewardOfferings', array('id'=>'NumberOfRewardOfferings2', 'onkeypress'=>'return numberonly(event)', 'readonly' => 'readonly')) ?>
           </td>    
       </tr>
    </table>
    <?php $this->endWidget(); ?>
</div>
<?php
$this->endWidget('zii.widgets.jui.CJuiDialog');
?>
