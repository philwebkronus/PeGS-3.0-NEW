<?php

/*
 * @author : owliber
 * @date : 2013-05-16
 */

class VMSCheckExpiredVouchersJobController extends Controller {

    //const RUN_START = 1;
    //const RUN_END = 2;

    public function actionRun() {
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
        } else {
            $model = new Vouchers();

            $status = Utilities::getParameters('JOB_SCHEDULER');

            /*
             * Check if Job scheduler is ON
             */
            if ($status == 1) {
                /*
                 * Check if job is running by checking the PID file.
                 */

                if (!Yii::app()->file->set(Yii::app()->basePath . '\runtime\VMSCheckVoucherExpiryJob.pid')->exists) {

                    $PIDFile = Yii::app()->file->set(Yii::app()->basePath . '\runtime\VMSCheckVoucherExpiryJob.pid');
                    $PIDFile->create();
                    $PIDFile->setContents('1', true);

                    //$model->logJob(self::RUN_START);

                    $results = $model->checkExpiredVouchers();

                    if (count($results) > 0) {
                        //CVarDumper::dump($results);

                        foreach ($results as $val) {
                            $voucherCode = $val['VoucherCode'];

                            $model->updateExpiredVouchers($voucherCode, 6);
                        }
                    }


                    /*
                     * Remove PID file
                     */
                    $PIDFile->delete();
                    //$model->logJob(self::RUN_END);

                    echo 'Job Status: Done';
                }
            } else {
                echo 'Cron job not enabled';
            }
        }
    }

}

?>
