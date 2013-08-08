<?php

/*
 * @author : owliber
 * @date : 2013-04-17
 */

class Helper extends BaseEntity
{
    function Helper()
    {
        $this->ConnString = 'membership';
    }
        
    public function getParameterValue( $paramName )
    {
        $this->TableName = "ref_parameters";
        $where = " WHERE ParamName = '$paramName' ";
        $result = parent::SelectByWhere($where);
        
        return $result[0]['ParamValue'];
    }
    
    public function sendEmailVerification( $email, $name, $tempcode)
    {
        App::LoadCore("PHPMailer.class.php");
        $mailer = new PHPMailer();
                        
        $mailer->AddAddress($email, $name);
        $mailer->IsHTML(true);
        
        $mailer->Body = "<html><body>Dear <label style='font-style: italic;'>$name</label>,<br />";
        $mailer->Body .= "<p style='text-align: justify; text-justify: inter-word;'><br />Thank you for signing up! This is to inform you that your User Account has been successfully created on this date ".date('m/d/Y',time())." and time (".date('H:i:s',time())."). Your Temporary Account Code is $tempcode. Present this code and one (1) government-issued ID at any e-Games cafe near you. ";
        $mailer->Body .= "<br /><br />To verify your account, please click this link <b><a href='http://".$_SERVER['HTTP_HOST']."/verify.php?email=$email&tempcode=$tempcode'>http://".$_SERVER['HTTP_HOST']."/verify.php?email=$email&tempcode=$tempcode</a></b>";
        $mailer->Body .= "<br />To read the Terms & Conditions, please click this link <b><a href='http://www.egamescasino.ph/terms-and-conditions/'>http://www.egamescasino.ph/terms-and-conditions/</a></b>.";
        $mailer->Body .= "<br />To locate the e-Games cafes near you, please click this link <b><a href='http://www.egamescasino.ph/location/'>http://www.egamescasino.ph/location/</a></b>.";
        $mailer->Body .= "<br /><br />Please be advised that your Temporary Account Code will be activated only after 24 hours.";
        $mailer->Body .= "<br />For inquiries, please call our 24-hour Customer Service Hotlines at (02) 338-3388 / Toll Free 1800-10-7445932. You can also send an email to our Customer Service Team at customerservice@philweb.com.ph.";
        $mailer->Body .= "<br /><br />Regards,";
        $mailer->Body .= "<br />e-Games</p></body></html>";
        
        $mailer->From = "membership@egamescasino.ph";
        $mailer->FromName = "E-Games Membership";
        $mailer->Host = "localhost";
        $mailer->Subject = "E-Games Membership";
        $mailer->Send();
    }
    
    public function sendEmailForgotPassword($email, $name, $hashedubcards){
        App::LoadCore("PHPMailer.class.php");
        $mailer = new PHPMailer();
                        
        $mailer->AddAddress($email, $name);
        $mailer->IsHTML(true);
        
        $mailer->Body = "Hi <label style='font-style: italic;'>$name</label>,<br />";
                
        $mailer->Body .= "<p style='text-align: justify; text-justify: inter-word;'><br />Your password has been reset on ".date('m-d-Y',time())." ".date('H:i:s',time()).".";
        $mailer->Body .= "<br /><br />It is advisable that you change your password upon log-in. ";
        $mailer->Body .= "<br /><br />Please click through the link provided below to log-in to your account. ";
        $mailer->Body .= "<br /><br /><b><a href='http://".$_SERVER['HTTP_HOST']."/changepassword.php?CardNumber=$hashedubcards'>Forgot Password</a></b> ";
        $mailer->Body .= "<br /><br />For inquiries, please call our 24-hour Customer Service Hotlines at (02) 338-3388 / Toll Free 1800-10-7445932. You can also send an email to our Customer Service Team at <b>customerservice@philweb.com.ph</b>.";
        $mailer->Body .= "<br /><br />Thank you and good day! ";
        $mailer->Body .= "<br /><br />Best Regards, ";
        $mailer->Body .= "<br />PhilWeb Customer Service Team";
        $mailer->Body .= "<br /><br />This email and any attachments are confidential and may also be privileged. If you are not the addressee, do not disclose, copy, circulate or in any other way use or rely on the information contained in this";
        $mailer->Body .= "email or any attachments. If received in error, notify the sender immediately and delete this email and any attachments from your system. Any opinions expressed in this message do not necessarily ";
        $mailer->Body .= "represent the official positions of PhilWeb Corporation. Emails cannot be guaranteed to be secure or error free as the message and any attachments could be intercepted, corrupted, lost, delayed, incomplete";
        $mailer->Body .= "or amended. PhilWeb Corporation and its subsidiaries do not accept liability for damage caused by this email or any attachments and may monitor email traffic.</p>";
        
        $mailer->From = "membership@egamescasino.ph";
        $mailer->FromName = "Philweb Membership";
        $mailer->Host = "localhost";
        $mailer->Subject = "E-Games Membership";
        $mailer->Send();
    }
    
    public function sendEmailCouponRedemption($playername,$address,$sitecode,$cardNumber,$birthdate,$email,$contactno,$cityname,
                                                                            $regionname,$newheader,$newfooter,$coupon,$CouponSeries,$Quantity,$CheckSum,
                                                                            $SerialNumber,$redemptiondate,$PromoCode,$PromoName,$promoperiod,$drawdate)
    {
        
        App::LoadCore("PHPMailer.class.php");
        $mailer = new PHPMailer();
                        
        $mailer->AddAddress($email, $playername);
        $mailer->IsHTML(true);
        
        $mailer->Body = '<div id="couponmessagebody" class="couponmessagebody" style="background-color: #FFFFFF;" align="center">
    <table cellpadding="0" cellspacing="0" width="612" style="text-align: left;font-family: arial; font-size: 9pt;background-color: #FFFFFF;">
        <tr>
            <td style="vertical-align:top;" align="center" colspan ="2">
                <img src="'.$newheader.'" width="612" height="80" />
            </td>
        </tr>
        <tr>
            <td style="vertical-align:top;" align="center" colspan ="1" width="50%">
                <br/>
                <table width="100%" cellpadding="2" style="text-align: left;">
                    <tr>
                        <td style="vertical-align:top;" nowrap><strong>e-Coupon Series: </strong></td>
                        <td><strong>'.$CouponSeries.'</strong></td>
                    </tr>
                    <tr>
                        <td>No. of Coupons: </td>
                        <td>'.$Quantity.'</td>
                    </tr>
                    <tr>
                        <td>Issuing Cafe:</td>
                        <td>'.$sitecode.'</td>
                    </tr>
                    <tr>
                        <td>Date Redeemed:</td>
                        <td>'.$redemptiondate.'</td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top;" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Promo Code:</td>
                        <td>'.$PromoCode.'</td>
                    </tr>
                    <tr>
                        <td>Promo Title:</td>
                        <td>'.$PromoName.'</td>
                    </tr>
                    <tr>
                        <td>Promo Period:</td>
                        <td>'.$promoperiod.'</td>
                    </tr>
                    <tr>
                        <td>Draw Date:</td>
                        <td>'.$drawdate.'</td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top;" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Card Number:</td>
                        <td>'.$cardNumber.'</td>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td>'.$playername.'</td>
                    </tr>
                    <tr>
                        <td>Address:</td>
                        <td>'.$address.' <br/> '.$cityname.' <br/> '.$regionname.'</td>
                    </tr>

                    <tr>
                        <td style="vertical-align:top;" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Birthday:</td>
                        <td>'.$birthdate.'</td>
                    </tr>
                    <tr>
                        <td>E-mail Address:</td>
                        <td>'.$email.'</td>
                    </tr>
                    <tr>
                        <td>Contact Number:</td>
                        <td>'.$contactno.'</td>
                    </tr>
                    <tr>
                        <td>Control Number:</td>
                        <td>'.$CheckSum.'<br/><br/></td>
                    </tr>

                    <tr>
                        <td>'.$SerialNumber.'</td>
                        <td>&nbsp;</td>
                    </tr>

                </table>
            </td>
            <td style="vertical-align:top;" align="center" colspan ="1" style="font-size: 14pt;">
                <br/>
                <img src="'.$coupon.'" style="height:auto; width:auto; max-width:300px; max-height:350px;"/>
            </td>
        </tr>
        <tr>
            <td style="vertical-align:top;" colspan ="2" align="justify" style=" font-size: 9pt;">
                <br/>
                <hr/>
                <strong>Raffle Mechanics</strong> <br/>
                1. Every 50 points earned in the Membership Card or VIP Rewards card entitles its holder to one (1) raffle e-coupon.<br/>
                2. There will be no physical raffle coupons. All players with Membership Card may choose to redeem via the cashier or through the online Membership Portal found in the e-Games website. The cashier and the online notification that will be sent to the registered email reiterate that winners will have to provide one (1) valid ID to claim their prize. Player information on the e-coupon should match with that of the identification card presented otherwise, he/she will be disqualified.<br/>
                3. A copy of the accomplished e-coupon, containing player information and redemption details, will be sent to the player’s registered email address. Transaction reference code will also be sent to the registered mobile number.<br/>
                4. Raffle draw will be done at PhilWeb Corporate Head Office, 19th Floor Alphaland Southgate Tower 2258 Roces Avenue corner Edsa, Makati City under the supervision of a PAGCOR Representative and PhilWeb Audit Team.<br/>
                5. Winning e-Coupons will be announced during the raffle draw which will be videotaped and streamed live at http://www.ustream.tv/channel/egamescasino-membershipraffle-2013. This link is still subject to change. Winners will also be informed through their registered contact details.<br/><br/>
                <strong>Claiming of Prizes  </strong><br/>
                1. Winners must bring the following when claiming their prize:
                 <ol style="list-style-type:lower-alpha; list-style-position:inside; text-indent: 5px;">
                    <li>Printed copy of the winning e-Coupon or Redemption receipt SMS from e-Games. Winners will be asked to provide the 13-digit alphanumeric serial number indicated in the winning e-Coupon or SMS<br/></li>
                    <li>Membership Card or VIP Rewards Card<br/></li>
                    <li>One (1) valid government-issued identification card (ex. Passport, Driver\'s license, Voter\'s ID, SSS ID, GSIS ID, TIN ID, PAG-IBIG ID, etc.). Winner\'s registered details should match with that of the identification card presented otherwise he/she will be disqualified.<br/></li>
                </ol>
                2. Prizes must be claimed by the actual winners. Authorization letters will not be honored.<br/>
                3. Major and Minor prizes can be redeemed at PhilWeb Corporate Head Office, 19th Floor Alphaland Southgate Tower 2258 Chino Roces Avenue corner EDSA, Makati City. Consolation Prizes can be claimed at the e-Games Cafe indicated in the winning raffle coupon.<br/>
                4. Prizes can be claimed at least three (3) weeks from the draw date, depending on the availability of the item. If any prize is not claimed within eight (8) weeks after the draw date, a replacement winner will be drawn in a draw date and time to be determined by PhilWeb from the remaining qualifying participants in the draw, in accordance with the same process and procedures as applicable to the original draw.<br/><br/>
                <strong>Terms and Conditions</strong><br/>
                1. The raffle promo entitles the player to win only once.<br/>
                2. PhilWeb reserves the right to invalidate an entry or prize winner if the proper data or authorization was not provided. If an entrant does not truthfully provide all requested personal information, PhilWeb may solely determine that such entrant shall not be eligible to take part in any way in the raffle promo or win any prize.<br/>
                3. Prizes with monetary value of PhP10,000 and above are subject to 20% withholding tax, which shall be for the account of the winners. As withholding agent, PhilWeb will process, withhold, and remit to BIR the 20% withholding tax in behalf of the winners. Upon receipt of tax payment, PhilWeb will issue winners an official receipt and a copy of BIR Form 2306 Certificate at Final Tax Withheld at Source. <br/>
                4. Prizes are non-transferable and non-convertible to cash. <br/>
                5. Prizes, other than the Toyota 86 grand prize, are convertible to slots load. The Toyota 86 has already been pre-ordered due to the limited supply of this car model in the country. Monetary value of the prize (less withholding tax) may be converted to casino bet vouchers. Electronic vouchers will be issued by e-Games Marketing and PhilWeb Top-up. Vouchers are good as cash and can be redeemed at the specified e-Games café/s within 30 days from date of issuance. Vouchers are transferable but not convertible to cash. <br/>
                <ol style="list-style-type:lower-alpha; list-style-position:inside; text-indent: 5px;">
                    <li>Winner may use up to PhP30, 000 bet voucher credits per day.<br/></li>
                    <li>Winner must play for at least one (1) hour to redeem his winnings.<br/></li>
                    <li>Winner cannot collect winnings from the cashier if bet voucher credits were used to play any non-slot games.<br/></li>
                    <li>Cash reloads cannot be done when voucher is used as initial deposit.<br/></li>
                    <li>Voucher cannot be used to reload current game.<br/></li>
                </ol>
                <br/>                   

            </td>
        </tr>
        <tr>
            <td style="vertical-align:top;" align="center" colspan ="2">
                <img src="'.$newfooter.'" width="612" height="40" />
            </td>
        </tr>
    </table>
</div>';
                        
        $mailer->From = "membership@egamescasino.ph";
        $mailer->FromName = "Philweb Membership";
        $mailer->Host = "localhost";
        $mailer->Subject = "E-Games Membership";
        $mailer->Send();
    }
    
    public function sendEmailItemRedemption($playername,$email,$sitecode,$redemptiondate,$cardNumber,$newheader,$newfooter,$item,
                                                                                                $startdate,$enddate,$ProductName,$PartnerName,$rewarditemcode,$checksum)
    {
        App::LoadCore("PHPMailer.class.php");
        $mailer = new PHPMailer();
                        
        $mailer->AddAddress($email, $playername);
        $mailer->IsHTML(true);
        
        $mailer->Body = '<div id="itemmessagebody" class="itemmessagebody" style="background-color: #FFFFFF;" align="center">
    <table cellpadding="0" cellspacing="0" width="612" style="text-align: left;font-family: arial; font-size: 9pt;background-color: #FFFFFF;">
        <tr>
            <td style="vertical-align:top;" align="center" colspan ="2">
                <img src="'.$newheader.'" width="612" height="80"/>
            </td>
        </tr>
        <tr>
            <td style="vertical-align:top;" align="center" colspan ="1" width="50%">
                <br/>
                <img src="'.$item.'" style="height:auto; width:320px; max-width:320px; max-height:350px;"/>
            </td>
            <td style="vertical-align:top;" align="center" colspan ="1" style="font-size: 14pt;">
                <br/>
                <table width="100%" cellpadding="2" style="text-align: left;">
                    <tr>
                        <td>e-Coupon Code:</td>
                        <td>'.$rewarditemcode.'</td>
                    </tr>
                    <tr>
                        <td>Mode of Redemption:</td>
                        <td>'.$sitecode.'</td>
                    </tr>
                    <tr>
                        <td>Date Redeemed:</td>
                        <td>'.$redemptiondate.'</td>
                    </tr>
                    <tr>
                        <td style="vertical-align:top;" colspan="2">&nbsp;</td>
                    </tr>
                    <tr>
                        <td>Membership Card #:</td>
                        <td>'.$cardNumber.'</td>
                    </tr>
                    <tr>
                        <td>Name:</td>
                        <td>'.$playername.'</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <br/>
            <td colspan="2">
            <table width="100%" cellpadding="2" style="text-align: left;">
                <tr>
                    <td colspan="2">Item Name: &nbsp;&nbsp;'.$ProductName.'</td>
                </tr>
                <tr>
                    <td colspan="2">Partner Name: &nbsp;&nbsp;'.$PartnerName.'</td>
                </tr>
                <tr>
                    <td style="vertical-align:top;" colspan="2">&nbsp;</td>
                </tr>
                <tr>
                    <td colspan="2">Redemption Period:</td>
                </tr>
                <tr>
                    <td colspan="2">From:  '.$startdate.' &nbsp;&nbsp;&nbsp;Until: '.$enddate.'</td>
                </tr>
                <tr>
                    <td colspan="2">Control Number: '.$checksum.'</td>
                </tr>
            </table>
            </td>
        </tr>
        <tr>
            <td style="vertical-align:top;" colspan ="2" align="justify" style=" font-size: 9pt;">
                <br/>
                <hr/>
                <strong>About this Reward</strong> <br/>
                1. Lorem ipsum dolor sit amet, eu maecenas cursus, congue curabitur. Id mi sit.<br/>
                2. Nam arcu, tincidunt eleifend faucibus, mollis metus urna. Eget turpis metus.<br/>
                3. Eleifend elit hendrerit, proin voluptatem turpis, vulputate pharetra pede. Montes sollicitudin molestie.<br/>
                4. Potenti libero, est libero tincidunt, neque sit. Urna vestibulum condimentum, velit ante, mauris sit in.<br/>
                5. Elementum neque, et feugiat vivamus. Ut vestibulum.<br/>
                6. Aliquam tempor dui, bibendum in, mauris vehicula. Convallis vivamus nulla, orci tempor.<br/>
                7. Sit in. Turpis justo.<br/>
                8. In in, quis quam. Pellentesque metus.<br/>
                9. Ut nec sapien. Et integer sed, lectus sapien, elit lectus.<br/>
                10. Maecenas ut. Praesent mauris.<br/><br/>
                <strong>Term of the Reward</strong><br/>
                <p style="font-size: 11px;">
                1. The raffle promo entitles the player to win only once (i.e. if a player wins more than once, the prize with the higher monetary value will be given).<br/>
                2. PhilWeb reserves the right to invalidate an entry or prize winner if the proper data or authorization was not provided. If an entrant does not truthfully provide all requested personal information, PhilWeb may solely determine that such entrant shall not be eligible to take part in any way in the raffle promo or win any prize.<br/>
                3. If the raffle draw is postponed to another date for any reason whatsoever, the new draw date will be within one (1) week from the original draw date.<br/>
                4. Prizes are non-transferable and non-convertible to cash. <br/>
                5. Prizes are subject to withholding tax, which shall be for the account of the winners. The winners shall furnish PhilWeb a copy of the BIR Return evidencing correct payment of the said withholding tax.<br/>
                6. The decision of e-Games Management shall be final in the event of any dispute.</p><br/>
                <br/>                   

            </td>
        </tr>
        <tr>
            <td style="vertical-align:top;" align="center" colspan ="2">
                <img src="'.$newfooter.'" width="612" height="40" />
            </td>
        </tr>
    </table>
</div>';
                        
        $mailer->From = "membership@egamescasino.ph";
        $mailer->FromName = "Philweb Membership";
        $mailer->Host = "localhost";
        $mailer->Subject = "E-Games Membership";
        $mailer->Send();
    }

    public static function removeDash($str){
        return str_replace("-", "", $str);
    }
    
    /**
     * Reference method for card types
     * @author Kenneth
     * @date 07-04-13
     * @param int $cardtype
     * @return string
     */
    public static function DetermineCardTypes($cardtype)
    {
        switch ($cardtype)
        {
            case 1: return "Gold Member Card";
                    break;
            case 2: return "Green Member Card";
                    break;
            case 3: return "Temporary Card";
                    break;
            default: return "Invalid Card Type";
                    break;
        }
    }
    
    public static function CheckCardTypeStatus($cardTypeStatus)
    {
        switch($cardTypeStatus)
        {
            case 0: return "Inactive";
                    break;
            case 1: return "Active";
                    break;
            case 2: return "Deactivated";
                    break;
        }
    }
    public static function DetermineCardStatus($cardstatus)
    {
        switch ($cardstatus)
        {
            case 0: return "Inactive";
                    break;
            case 1: return "Active";
                    break;
            case 2: return "Deactived";
                    break;
            case 5: return "Active Temporary";
                    break;
            case 7: return "New Migrated";
                    break;
            case 8: return "Temporary Migrated";
                    break;
            case 9: return "Banned Card";
        }
    }
    
    /**
     * @author Kenneth
     * @param int $cardstatus card status
     * @return string
     */
    public static function SetErrorMsgForCardStatus($cardstatus)
    {
        switch ($cardstatus)
        {
            case 0: return App::setErrorMessage("Membership Card is Inactive");
                    break;
            case 2: return App::setErrorMessage("Membership Card is Deactived ");
                    break;
            case 7: return App::setErrorMessage("Membership Card is New Migrated ");
                    break;
            case 8: return App::setErrorMessage("Membership Card is Temporary Migrated");
                    break;
            case 9: return App::setErrorMessage("Membership Card is Banned");
                    break;
            default: return App::setErrorMessage("Membership Card is Invalid");
                    break;
        }
    }
}
?>