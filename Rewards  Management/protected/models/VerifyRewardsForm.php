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
    
    
    public function mailRecordReward($to, $CC = ''){

        $subject  = "Recording of the Reward Transaction";
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        $headers .=  "From: "."RewardsItemManagement@philweb.com.ph"."\r\n" . "Cc: "."$CC";
        
                $detail = '
                    <html>
                    <head>
                      <title>Rewards Item Management</title>
                    </head>
                    <body>
                      <p>Rewards Item Management - E-Coupon Verification</p>
                      <table>
                        <tr>
                          <th>Sample</th><th>Data</th><th>Data</th><th>Data</th>
                        </tr>
                        <tr>
                          <td>Sample</td><td>Data</td><td>Data</td><td>Data</td>
                        </tr>
                      </table>
                    </body>
                    </html>
                    ';
                  
            mail($to, $subject, $detail, $headers);
    }
    
    
}
