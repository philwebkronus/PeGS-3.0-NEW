<?php
/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2013-03-06
 * Company: Philweb
 * ***************** */
require_once("../init.inc.php");
//include('sessionmanager.php');

App::LoadControl("TextBox");
$fproc = new FormsProcessor();


if ($fproc->IsPostBack && $_POST["SubmitButton"] == "Check")
{
    $checkstring = $_POST["checkstring"];
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
        }
    }

    $refcheckstring = $couponseries . $quantity . $cardno . $playername . $birthdate . $email . $contactno;
    $checksum = crc32($refcheckstring);
    if ($checksum == $refchecksum)
    {
        App::SetSuccessMessage("Checksum is Valid");
    }
    else
    {
        App::SetErrorMessage("Invalid Checksum");
    }
}
else
{
    App::SetSuccessMessage("Please enter message for checking");
}
?>
<?php include('header.php'); ?>
<?php //include('menu.php'); ?>
<style>
    .couponmessagebody table 
    {
        font-family: arial;
        font-size: 9pt;
    }

    .couponmessagebody td 
    {
        vertical-align: top;
    }

    .couponmessagebody .serialcode
    {
        font-size: 11pt;
    }

</style>

<div id="Menu">
    <table width="900" cellpadding="5" cellspacing="0" class="mainmenu">
        <tr>

            <td class="<?php echo $page == "Coupon Checker" ? "selectedmenuitem" : "menuitem"; ?>">
                <a href="checksumchecker.php">Coupon Checker</a>
            </td>
            <td class="<?php echo $page == "Serial Number Checker" ? "selectedmenuitem" : "menuitem"; ?>">
                <a href="serialnumberchecker.php">Serial Number Checker</a>
            </td>
        </tr>
    </table>
</div>
<?php //include("menu.php"); ?>
<?php //include("message.php"); ?>

<div class="maincontent">
    <b>Message:</b><br/>
    <textarea name="checkstring" cols="100" rows="10"></textarea><br/>
    <input type="submit" name="SubmitButton" value="Check" class="btnDisable"/><br/><br/>
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
            </pre></div>
    </center>
</div>
</form>
</body>
</html>