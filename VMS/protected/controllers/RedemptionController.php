<?php

/**
 * @author owliber
 * @date Nov 11, 2012
 * @filename RedemptionController.php
 * 
 */
class RedemptionController extends VMSBaseIdentity 
{

    public $success = 0;
    public $invalid_voucher = 0;
    public $status;

    public function actionVerify() {
        $_AccountSessions = new SessionModel();

        if (isset(Yii::app()->session['SessionID'])) {
            $aid = Yii::app()->session['AID'];
            $sessionid = Yii::app()->session['SessionID'];
        } else {
            $sessionid = 0;
            $aid = 0;
        }
        
        $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid);
        
        if ($sessioncount == 0) {
            Yii::app()->user->logout();
            $this->redirect(array(Yii::app()->defaultController));
        }
        else{
        $model = new VoucherRedemption();
        $result = array();

        if (isset($_POST['Verify']) 
                && isset($_POST['SearchCode']) 
                && is_numeric($_POST['SearchCode'])) 
        {
            if ($model->verifyVoucher(Yii::app()->user->getId(),$_POST['SearchCode'])) 
            {
                $vouchercode = trim($_POST['SearchCode']);
                $result = $model->getVoucherInfo($vouchercode);
            } 
            else 
            {
                $voucher = $model->verifyVoucherExist($_POST['SearchCode']);
                
                if(count($voucher) > 0)
                   $this->status = "Ticket is generated from other site."; 
                else
                   $this->status = "Ticket does not exist.";
                
                if($voucher['VoucherTypeID'] == 2)
                   $this->status = "Promotional or marketing coupons are not convertible to cash.";
                
                $this->invalid_voucher = 1;
            }
            
            //Log to audit trail            
            $transDetails = ' # '.$_POST['SearchCode'];
            AuditLog::logTransactions(16, $transDetails);
        }

        if (isset($_POST['VoucherCode'])) {
            $vouchercode = trim($_POST['VoucherCode']);
            
            //Redeem voucher
            if ($model->redeemVoucher($vouchercode) == 1) {
                $this->success = 1;
                
                //Log to audit trail            
                $transDetails = ' # '.$_POST['VoucherCode'];
                AuditLog::logTransactions(17, $transDetails);
            
            }
            //Display result
            $result = $model->getVoucherInfo($vouchercode);
        }

        $this->render('index', array('result' => $result));
    }
    }
}

?>
