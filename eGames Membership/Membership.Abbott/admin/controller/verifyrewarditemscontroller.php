<?php

App::LoadModuleClass("Rewards", "ItemRedemptionLogs");

App::LoadCore('Validation.class.php');
App::LoadControl("TextBox");
App::LoadControl("Button");

$fproc = new FormsProcessor();
$_ItemRedemptionLogs = new ItemRedemptionLogs();

$txtSerialCode = new TextBox("txtSerialCode", "txtSerialCode", "Serial Code");
$txtSerialCode->ReadOnly = false;
$txtSerialCode->ShowCaption = false;
$txtSerialCode->Length = 30;
$txtSerialCode->Size = 30;
$txtSerialCode->CssClass = "validate[required]";
$txtSerialCode->Args = 'placeholder="Enter Serial Code"onkeypress="javascript: return alphanumeric4(event)"';
$fproc->AddControl($txtSerialCode);

$txtSecurityCode = new TextBox("txtSecurityCode", "txtSecurityCode", "Security Code");
$txtSecurityCode->ReadOnly = false;
$txtSecurityCode->ShowCaption = false;
$txtSecurityCode->Length = 30;
$txtSecurityCode->Size = 30;
$txtSecurityCode->CssClass = "validate[required]";
$txtSecurityCode->Args = 'placeholder="Enter Security Code"onkeypress="javascript: return alphanumeric4(event)"';
$fproc->AddControl($txtSecurityCode);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Submit");
$btnSubmit->ShowCaption = true;
$btnSubmit->IsSubmit = true;
$btnSubmit->Enabled = true;
$btnSubmit->Style = "margin-left: 20px;padding-left: 12px; padding-right: 12px; padding-top: 3px; padding-bottom: 3px;";
$fproc->AddControl($btnSubmit);

$fproc->ProcessForms();

$isOpen = 'false';

if ($fproc->IsPostBack) {

    if ($btnSubmit->SubmittedValue == 'Submit') {
        $SerialCode = $txtSerialCode->SubmittedValue;
        $SecurityCode = $txtSecurityCode->SubmittedValue;
        $itemCode = $_ItemRedemptionLogs->getItemCode($SerialCode, $SecurityCode);
        $countItemCode = count($itemCode);
        
        $itemSerialCode2 = $_ItemRedemptionLogs->getSerialCode($SerialCode);
        foreach ($itemSerialCode2 as $value) {
            $itemSerial1 = $value['SerialCode'];
        }
        $countItemSerialCode2 = count($itemSerialCode2);
        $itemSecurityCode2 = $_ItemRedemptionLogs->getSecurityCode($SecurityCode);
        foreach ($itemSecurityCode2 as $value) {
            $itemSecurity2 = $value['SecurityCode'];
        }
        $countItemSecurityCode2 = count($itemSecurityCode2);
        if (($countItemSerialCode2 == 0)&&($countItemSecurityCode2 == 0)) {
            $resultmsg = '<font color="red"><b>Invalid Serial Code and Security Code</b></font>';
        }
        else if (($countItemSerialCode2 == 0)&&($countItemSecurityCode2 > 0)) {
            $resultmsg = '<font color="red"><b>Invalid Serial Code</b></font>';
        }
        else if (($countItemSerialCode2 > 0)&&($countItemSecurityCode2 == 0)) {
            $resultmsg = '<font color="red"><b>Invalid Security Code</b></font>';
        } 
        else if (($countItemSerialCode2 > 0)&&($countItemSecurityCode2 > 0)) {
                if($countItemCode == 0){
                    $resultmsg = '<font color="red"><b>Invalid Reward Item</b></font>';
                } else {
                    $resultmsg = '<font color="green"><b>Valid Reward Item</b></font>';
                }
        }
        else {
            $resultmsg = '<font color="red"><b>Cannot verify reward item!</b></font>';
        }
        $isOpen = true;
    }
}
?>
