<?php
require_once("../init.inc.php");

App::LoadModuleClass("Loyalty", "Cards");

App::LoadControl("TextBox");
App::LoadControl("Button");

$fp = new FormsProcessor();

$txtOldCardNumber = new TextBox("txtOldCardNumber", "txtOldCardNumber", "Old Card Number ");
$txtOldCardNumber->ShowCaption = true;
$fp->AddControl($txtOldCardNumber);

$txtNewCardNumber = new TextBox("txtNewCardNumber", "txtNewCardNumber", "New Card Number ");
$txtNewCardNumber->ShowCaption = true;
$fp->AddControl($txtNewCardNumber);

$txtAID = new TextBox("txtAID","txtAID","AID ");
$txtAID->ShowCaption = true;
$fp->AddControl($txtAID);

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
        
        $OldCardNumber = $txtOldCardNumber->SubmittedValue;
        $NewCardNumber = $txtNewCardNumber->SubmittedValue;
        $AID = $txtAID->SubmittedValue;
        
        $data = array('oldnumber'=>$OldCardNumber,'newnumber'=>$NewCardNumber,'aid'=>$AID);
       
        $url = 'http://192.168.20.8:8092/API/transferpoints.php'; //.$cardNumber;
        //$url = 'http://localhost/philweb/membership/API/transferpoints.php';
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
        <title>transferPoints API Invoker</title>
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
            <span><b>Invokers</b> | <a href="getCardInvoker.php">GetCardInfo</a> | TransferPoints | <a href="processPointsInvoker.php">ProcessPoints</a></span>
            <h3>transferPoints API Invoker</h3>
            <hr width="100%" size="1" />
            <div class="divForm">
                <?php echo $txtOldCardNumber; ?><br />
                <?php echo $txtNewCardNumber; ?><br />
                <?php echo $txtAID; ?><br />
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
