<?php
require_once("../init.inc.php");

App::LoadModuleClass("Loyalty", "Cards");

App::LoadControl("TextBox");
App::LoadControl("Button");

$fp = new FormsProcessor();

$txtCardNumber = new TextBox("txtCardNumber", "txtCardNumber", "Card Number ");
$txtCardNumber->ShowCaption = true;
$fp->AddControl($txtCardNumber);

$txtIsReg = new TextBox("txtIsReg","txtIsReg","IsReg ");
$txtIsReg->ShowCaption = true;
$txtIsReg->Text = '0';
$fp->AddControl($txtIsReg);

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
        
        $cardNumber = $txtCardNumber->SubmittedValue;
        $isreg = $txtIsReg->SubmittedValue;
        
        $data = array('cardnumber'=>$cardNumber,'isreg'=>$isreg);
       
        //$url = 'http://192.168.20.8:8092/API/cardinquiry.php'; //.$cardNumber;
        $url = 'http://localhost/membership/membership.rewards/API/cardinquiry.php';
                
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
        <title>getCardInfo API Invoker</title>
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
            <span><b>Invokers</b> | GetCardInfo | <a href="transferPointsInvoker.php">TransferPoints</a> | <a href="processPointsInvoker.php">ProcessPoints</a></span>
            <h3>getCardInfo API Invoker</h3>
            <hr width="100%" size="1" />
            <div class="divForm">
                <?php echo $txtCardNumber; ?><br />
                <?php echo $txtIsReg; ?> [1 - FR; 0 - NFR]<br />
                <?php echo $btnSubmit; ?>
            </div>
            <div class="result">
                 <?php if($submitted) 
                 {?>
                <h3>JSON Result</h3>
                <code><?php echo $result[1];  ?></code>
                <h3>Array</h3>
                <?php App::Pr(json_decode($result[1])); ?>
                <?php 
                 }?>
            </div>
        </div>
        </form>
    </body>
</html>
