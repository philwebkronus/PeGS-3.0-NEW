<?php
require_once("../init.inc.php");

App::LoadModuleClass("Loyalty", "ProcessPointsAPI");

App::LoadControl("TextBox");
App::LoadControl("Button");

$fp = new FormsProcessor();

$date = new DateSelector();

$txtCardNumber = new TextBox("txtCardNumber", "txtCardNumber", "Card Number ");
$txtCardNumber->ShowCaption = true;
$fp->AddControl($txtCardNumber);

$txtTransDate = new TextBox("txtTransDate","txtTransDate", "Transaction Date ");
$txtTransDate->ShowCaption = true;
$txtTransDate->Text = $date->GetNowUSec();
$fp->AddControl($txtTransDate);

$txtPaymentType = new TextBox("txtPaymentType", "txtPaymentType", "Payment Type ");
$txtPaymentType->ShowCaption = true;
$txtPaymentType->Text = 1;
$fp->AddControl($txtPaymentType);

$txtTransType = new TextBox("txtTransType", "txtTransType", "Transaction Type ");
$txtTransType->ShowCaption = true;
$txtTransType->Text = 'D';
$fp->AddControl($txtTransType);

$txtAmount = new TextBox("txtAmount", "txtAmount", "Amount ");
$txtAmount->ShowCaption = true;
$fp->AddControl(($txtAmount));

$txtSiteID = new TextBox("txtSiteID", "txtSiteID", "Site ID ");
$txtSiteID->ShowCaption = true;
$fp->AddControl($txtSiteID);

$txtServiceID = new TextBox("txtServiceID", "txtServiceID", "ServiceID ");
$txtServiceID->ShowCaption = true;
$fp->AddControl($txtServiceID);

$txtTransID = new TextBox("txtTransID", "txtTransID", "Transaction ID ");
$txtTransID->ShowCaption = true;
$fp->AddControl($txtTransID);

$txtTerminalLogin = new TextBox("txtTerminalLogin", "txtTerminalLogin", "Terminal Login ");
$txtTerminalLogin->ShowCaption = true;
$fp->AddControl($txtTerminalLogin);

$txtVoucherCode = new TextBox("txtVoucherCode", "txtVoucherCode", "Voucher Code ");
$txtVoucherCode->ShowCaption = true;
$fp->AddControl($txtVoucherCode);

$txtIsCreditable = new TextBox("txtIsCreditable","txtIsCreditable","Is Creditable ");
$txtIsCreditable->ShowCaption = true;
$txtIsCreditable->Text = '0';
$fp->AddControl($txtIsCreditable);

$btnSubmit = new Button("btnSubmit", "btnSubmit", "Submit");
$btnSubmit->IsSubmit = true;
$btnSubmit->ShowCaption = true;
$fp->AddControl($btnSubmit);

$fp->ProcessForms();

if($fp->IsPostBack)
{
    $submitted = true;
    
    if($btnSubmit->SubmittedValue == 'Submit')
    {        
        include('Invoker.class.php');
        
        $curl = new Invoker();
        
        $cardnumber = $txtCardNumber->SubmittedValue;
        $transdate = $txtTransDate->SubmittedValue;
        $paymenttype = $txtPaymentType->SubmittedValue;   
        $transactiontype = $txtTransType->SubmittedValue;
        $amount = $txtAmount->SubmittedValue;
        $siteid= $txtSiteID->SubmittedValue;
        $serviceid = $txtServiceID->SubmittedValue;
        $transactionid = $txtTransID->SubmittedValue;
        $terminallogin = $txtTerminalLogin->SubmittedValue;
        $vouchercode = $txtVoucherCode->SubmittedValue;
        $iscreditable = $txtIsCreditable->SubmittedValue;
        
//        /echo $transdate; exit;
        
        $data = array('cardnumber'=>$cardnumber,
                        'transdate'=> $transdate,
                        'paymenttype'=>$paymenttype,
                        'transtype'=>$transactiontype,
                        'amount'=>$amount,
                        'siteid'=>$siteid,
                        'serviceid'=>$serviceid,
                        'transactionid'=>$transactionid,
                        'terminallogin'=>$terminallogin,
                        'iscreditable'=>$iscreditable,
                        'vouchercode'=>$vouchercode);
       
        $url = 'http://192.168.20.8:8092/API/addpoints.php'; 
        //$url = 'http://localhost/philweb/membership/api/addpoints.php';
                
        $result = $curl->SendRequest( $url, http_build_query( $data ) );
       
    }
}
else
    $submitted = false;
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>ProcessPoints API Invoker</title>
        <style>
            body{
                margin: 4% 0 0 10%;
            }
            #wrapper{
                border: 1px solid #CCC;
                width: 80%;
                padding: 2% 4%;
            }
            
            #wrapper .result{
                width: 100%;
                font-size:12px;
                word-wrap: break-word;
            }
        </style>
    </head>
    <body>
        <form name="cardInfo" method="post" action="">
        <div id="wrapper">
            <span><b>Invokers</b> | <a href="getCardInvoker.php">GetCardInfo</a> | <a href="transferPointsInvoker.php">TransferPoints</a> | ProcessPoints</span>
            <h3>ProcessPoints API Invoker</h3>
            <hr width="100%" size="1" />
            <div class="divForm">

                <?php echo $txtCardNumber; ?><br />
                <?php echo $txtTransDate; ?><br />
                <?php echo $txtPaymentType; ?>[1 - Cash; 2 - Voucher]<br />
                <?php echo $txtTransType; ?>[D,R,W,RD]<br />
                <?php echo $txtAmount; ?><br />
                <?php echo $txtSiteID; ?><br />
                <?php echo $txtServiceID; ?><br />
                <?php echo $txtTransID; ?><br />
                <?php echo $txtTerminalLogin; ?><br />
                <?php echo $txtVoucherCode; ?><br />
                <?php echo $txtIsCreditable; ?> [1 - Yes; 2 - No;]<br />
                <?php echo $btnSubmit; ?>
                
            </div>
            <div class="result">
                 <?php if($submitted) 
                 {?>
                <h3>JSON Result</h3>
                <code><?php echo $result[1]; ?></code>
                <h3>Array</h3>
                <?php App::Pr(json_decode($result[1])); ?>
                <?php 
                 }?>
            </div>
        </div>
        </form>
    </body>
</html>
