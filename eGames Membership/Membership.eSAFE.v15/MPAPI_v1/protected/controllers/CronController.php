<?php
/**
 * Description of CronController
 *
 * @author fdlsison
 */
class CronController extends Controller
{
    public function actionDeleteSession() {

        $memberSessionsModel = new MemberSessionsModel();
        $setMinutes = 20;

        $allMemberSessions = $memberSessionsModel->getAllMemberSessions();

        $cntMemberSessions = count($allMemberSessions);
        if($cntMemberSessions > 0) {
            $ctr = 0;
            while($ctr < $cntMemberSessions) {
                $lastTransDate = $allMemberSessions[$ctr]['TransactionDate'];
                $MID = $allMemberSessions[$ctr]['MID'];
                $sessionID = $allMemberSessions[$ctr]['SessionID'];

                date_default_timezone_set('Asia/Manila');
                //compute last transdate in minutes
                $dateNow = date("Y-m-d H:i:s.u");
                $diffMins = (int)strtotime($dateNow) - (int)strtotime($lastTransDate);


                //$years = floor($diffMins / (365*60*60*24));
                //$months = floor(($diffMins - $years * 365*60*60*24) / (30*60*60*24));
                //$days = floor(($diffMins - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24)); //actual day difference
                $noOfMins = round(abs($diffMins)/60,2); //actual minute difference

                if($noOfMins >= $setMinutes) {
                    $isDeleted = $memberSessionsModel->deleteExpiredMemberSession($MID, $sessionID);
                    if($isDeleted == 1) {
                        echo "Expired member session is successfully deleted.";
                    }
                    else {
                        echo "Failed to delete member session.";
                    }
                }
                else {
                    echo "Member session is still active.";
                }
                $ctr++;
            }

            unset($allMemberSessions, $cntMemberSessions, $ctr);

        }
        else {
            echo "There are no existing member sessions.";
            exit;
        }
    }

    //@date 07-23-2015
    //@purpose get newly migrated card(s) in membercards table
    public function actionGetNewlyMigratedCards() {
        $memberCardsModel = new MemberCardsModel();
        $memberInfoModel = new MemberInfoModel();
        $memberInfoTempModel = new MembershipTempModel();

        $path = 'lastrundatetime.txt';
        $fp = fopen($path, "r+") or die("Unable to open file!");
        $dateCron = file_get_contents($path);
        $resultNewlyMigratedCards = $memberCardsModel->getNewlyMigratedCards($dateCron);
        if ($resultNewlyMigratedCards) {
            $instanceURL = Yii::app()->params['instanceURL'];
            $apiVersion = Yii::app()->params['apiVersion'];
            $cKey = Yii::app()->params['cKey'];
            $cSecret = Yii::app()->params['cSecret'];
            $sfLogin = Yii::app()->params['sfLogin'];
            $sfPassword = Yii::app()->params['sfPassword'];
            $secToken = Yii::app()->params['secToken'];

            $countNewlyMigratedCards = count($resultNewlyMigratedCards);
            if ($countNewlyMigratedCards > 0) {//var_dump($countNewlyMigratedCards);exit;
                $sfapi = new SalesforceAPI($instanceURL, $apiVersion, $cKey, $cSecret);
                $sfSuccessful = $sfapi->login($sfLogin, $sfPassword, $secToken);
                $newBaseUrl = $sfSuccessful->instance_url;
                $accessToken = $sfSuccessful->access_token;
                if ($sfSuccessful) {
                    for ($i = 0; $i < $countNewlyMigratedCards; $i++) {
                        $MID = $resultNewlyMigratedCards[$i]['MID'];
                        //get temp code
                        $tempCode = $memberCardsModel->getSFIDFromTempCode($MID);
                        //get temp MID in temp members table
                        $tempMID = $memberInfoTempModel->getSFIDFromTemp($tempCode['CardNumber']);
                        //get tempSFID
                        $SFID = $memberInfoTempModel->getSF($tempMID['MID']);
                        //update SFID in membership.memberinfo table
                        $sfUpdate = $memberInfoModel->updateSF($MID, $SFID['SFID']);
                        $cardNumber = $resultNewlyMigratedCards[$i]['CardNumber'];
                        $isUpdated = $sfapi->update_account($SFID['SFID'], null, null, null, $cardNumber, null, null, $newBaseUrl, $accessToken);var_dump($isUpdated);exit;
                    }
                    //return 1;
                } else {
                    //return 0;
                }
            }
        }
        date_default_timezone_set('Asia/Manila');
        fwrite($fp, date('Y-m-d H:i:s'));
        fclose($fp);
    }

}

?>
