<?php

/**
 * VerifyRewardsForm class.
 */
class VerifyRewardsForm extends CFormModel
{
    public $rewardsecoupons;
    public $raffleecoupons;
    
    public $egamespartner;
    public $rewarditem;
    public $ecouponserial;
    public $ecouponsecuritycode;
    
    public $rafflepromo;
    public $ecouponserial2;
    public $ecouponsecuritycode2;
    
    
    public $partnername;
    public $itemname;
    public $serialcode;
    public $securitycode;
    public $membername;
    public $cardnumber;
    public $idpresented;
    
    public $partnernamecashier;
    public $branchdetails;
    public $remarks;
    
    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules() {
        return array(
            //all fields are required
            array('egamespartner, rewarditem, ecouponserial, ecouponsecuritycode,
                   rafflepromo, ecouponserial2, ecouponsecuritycode2', 'required'),
            array('ecouponserial', 'length', 'max' => 30),
            array('ecouponsecuritycode', 'length', 'max' => 30),
            array('ecouponserial2', 'length', 'max' => 30),
            array('ecouponsecuritycode2', 'length', 'max' => 30),
            array('partnernamecashier', 'length', 'max'=> 30),
            array('branchdetails', 'length', 'max'=> 60),
            array('remarks', 'length', 'max'=> 100)
        );
    }
    
    public function check_in_range($start_date, $end_date, $date_from_user)
    {
        // Convert to timestamp
        $start_ts = strtotime($start_date);
        $end_ts = strtotime($end_date);
        $user_ts = strtotime($date_from_user);

        // Check that user date is between start & end
        return (($user_ts >= $start_ts) && ($user_ts <= $end_ts));
    }
    
    
    public function mailRecordReward($to, $partner, $rewarditem, $serialcode, $securitycode, $timeavailed, $dateavailed, $membercard, $membername, $cashier, $partnerpid, $CC = ''){
        $autoemaillogs = new AutoEmailLogsModel();
        
        $subject  = "[Rewards Availment Notification] Membership Rewards Program";
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .=  "From: "."rewardsmanagement@philweb.com.ph"."\r\n" . "Cc: "."$CC";
        
                $detail = '
                    <html>
                    <head>
                      <title>Rewards Item Management</title>
                      <style>
                        table
                        {
                            border-collapse: collapse;
                        }
                        table tr th 
                        {
                            border-collapse: collapse; border: 1px solid #000;
                        }
                        table tr td 
                        {
                            border-collapse: collapse; border: 1px solid #000; text-align:center;
                        }
                      </style>
                    </head>
                    <body>
                      <p>
                      Hi,
                      </p>
                      <p>
                      This is to inform you that the '.$rewarditem.' of '.$partner.' was availed and was successfully 
                      recorded as detailed below:
                      </p>
                    <table style="border-collapse: collapse;">  
                        <tr style="border-collapse: collapse; border: 1px solid #000;">
                            <th style="border-collapse: collapse; border: 1px solid #000;">Serial</th>
                            <th style="border-collapse: collapse; border: 1px solid #000;">Security</th>
                            <th style="border-collapse: collapse; border: 1px solid #000;">Reward Item</th>
                            <th style="border-collapse: collapse; border: 1px solid #000;">Name of Person Availing</th>
                            <th style="border-collapse: collapse; border: 1px solid #000;">Membership Card Number</th>
                            <th style="border-collapse: collapse; border: 1px solid #000;">Time of Availment</th>
                            <th style="border-collapse: collapse; border: 1px solid #000;">Date of Availment</th>
                            <th style="border-collapse: collapse; border: 1px solid #000;">Cashier on Duty</th>
                        </tr>    
                        <tr style="border-collapse: collapse; border: 1px solid #000;">
                            <td style="border-collapse: collapse; border: 1px solid #000; text-align:center;">'.$serialcode.'</td>
                            <td style="border-collapse: collapse; border: 1px solid #000; text-align:center;">'.$securitycode.'</td>
                            <td style="border-collapse: collapse; border: 1px solid #000; text-align:center;">'.$rewarditem.'</td>
                            <td style="border-collapse: collapse; border: 1px solid #000; text-align:center;">'.$membername.'</td>
                            <td style="border-collapse: collapse; border: 1px solid #000; text-align:center;">'.$membercard.'</td>
                            <td style="border-collapse: collapse; border: 1px solid #000; text-align:center;">'.$timeavailed.'</td>
                            <td style="border-collapse: collapse; border: 1px solid #000; text-align:center;">'.$dateavailed.'</td>
                            <td style="border-collapse: collapse; border: 1px solid #000; text-align:center;">'.$cashier.'</td>
                        </tr>
                    </table>
                    <p>
                    Should there be any concern, please call our 24-hour Customer Service Hotlines at (02) 338-3388 / Toll-Free 1800-10-7445932. 
                    You can also send an email to our Customer Service Team at customerservice@philweb.com.ph.
                    </p>
                    <p>
                    Thanks. <br />
                    **This is an automated email.
                    </p>
                    </body>
                    </html>
                    ';
        $result = mail($to, $subject, $detail, $headers);
        //Log to AutoEmailLogs
        if ($result)
            $autoemaillogs->InsertAutoEmailLogs(RefAutoEmailTypeModel::REWARDS_AVAILMENT_NOTIFICATION, $partnerpid, "", "", $detail, Yii::app()->session['AID']);
        
        return $result;
    }
}
