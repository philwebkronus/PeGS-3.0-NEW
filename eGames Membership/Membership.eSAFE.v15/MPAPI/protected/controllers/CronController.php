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
}

?>
