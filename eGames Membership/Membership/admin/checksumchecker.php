<?php
/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-03-06
 * Modified by: Mark Kenneth Esguerra
 * Date Modified: 2013-07-05
 * Additional validation, put under marketing access rights
 * Company: Philweb
 * ***************** */
require_once("../init.inc.php");
include('sessionmanager.php');

$pagetitle = "Checksum Validator";
$currentpage = "Administration";

App::LoadCore('ErrorLogger.php');

App::LoadControl("TextBox");
$fproc = new FormsProcessor();

$logger = new ErrorLogger();
$logdate = $logger->logdate;
$logtype = "Error ";

if ($fproc->IsPostBack && $_POST["SubmitButton"] == "Check")
{
    $checkstring = $_POST["checkstring"];
    //check if the message is empty. If empty display Error Message
    if(strlen($checkstring) == 0)
    {
        App::SetErrorMessage("Please enter message for checking");        
    } 
    else
    {
        $checkstring = str_replace("\r", "", $checkstring);
        $arrinfo = explode("\n", $checkstring);
        $pairs = "";
        for ($i = 0; $i < count($arrinfo); $i++)
        {
            $line = $arrinfo[$i];
            $pair = explode(":", $line);
            $pair[0] = trim($pair[0]);
            if (isset($pair[1]))
            {
                $pair[1] = trim($pair[1]);
            }
            else
            {
                $pair[1] = "";
            }
            $pairs[] = $pair;
            switch ($pair[0])
            {
                case "e-Coupon Series": $couponseries = $pair[1];
                    break;
                case "No. of Coupons": $quantity = $pair[1];
                    break;
                case "Card Number": $cardno = $pair[1];
                    break;
                case "Name": $playername = $pair[1];
                    break;
                case "Birthday": $birthdate = $pair[1];
                    break;
                case "E-mail Address": $email = $pair[1];
                    break;
                case "Contact Number": $contactno = $pair[1];
                    break;
                case "Control Number": $refchecksum = $pair[1];
                    break;
                default :
                     App::SetErrorMessage("Invalid Checksum");
                    break;
            }
        }
        //If following details are not set due to invalid message, an Error message is display
        if((!isset($couponseries)) || (!isset($quantity)) || (!isset($cardno)) || 
                (!isset($playername)) || (!isset($birthdate)) || (!isset($email))
                || (!(isset($contactno)))) 
        {
            App::SetErrorMessage("Invalid Checksum");
            $error = "Invalid Checksum";
            $logger->logger($logdate, $logtype, $error);
        } 
        else 
        {
            $refcheckstring = $couponseries . $quantity . $cardno . $playername . $birthdate . $email . $contactno;
            $checksum = crc32($refcheckstring);
            if ($checksum == $refchecksum)
            {
                App::SetSuccessMessage("Checksum is Valid");
            }
            else
            {
                App::SetErrorMessage("Invalid Checksum");
                $error = "Invalid Checksum";
                $logger->logger($logdate, $logtype, $error);
            }
        } 
    }
}
?>
<?php include('header.php'); ?>

<div align="center">
    <div class="maincontainer">
    <?php include('menu.php'); ?>
            <br />
            <div class="title">Checksum Validator</div>
            <br />
            <b>Message:</b><br/>
            <div align="center">
                <textarea name="checkstring" cols="100" rows="10"></textarea><br/>
                <input type="submit" name="SubmitButton" value="Check" class="btnDisable"/><br/><br/>
            </div>
            <center>
                <div align="left" style="width:600px;">
                    <b>Sample Message:</b>
                    <pre>
                        e-Coupon Series:	0000804
                        No. of Coupons :	1
                        Issuing Cafe:	TST
                        Date Redeemed:	March 22, 2013, 7:49 am

                        Promo Code:	13001
                        Promo Title:	Summer Raffle
                        Promo Period:	March 30 to April 30, 2013
                        Draw Date:	May 10, 2013 4PM

                        Card Number:	000000005NA5
                        Name:	Bennette Manrique
                        Address:	asdfasdf 
                        Quezon City 
                        NCR (National Capital Region)

                        Birthday:	March 15, 1905
                        E-mail Address:	juandelacruz@gmail.com
                        Contact Number:	632121231
                        Control Number:	-1664495788
                    </pre>
                </div>
            </center>
        </form>
    </div>
</div>


<?php include("footer.php"); ?>
