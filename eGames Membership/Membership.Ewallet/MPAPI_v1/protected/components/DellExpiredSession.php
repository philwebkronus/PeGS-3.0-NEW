<?php

/*
 * This cron is for deleting of expired(has been idle for 20 minutess) member sessions
 * @date 07-24-2014
 * @author fdlsison
 * 
 * 
 */
class DelExpiredSession {
    
    public static function deleteExpiredSession() {

    $memberSessionsModel = new MemberSessionsModel();
    $setMinutes = 20;
      
    $allMemberSessions = $memberSessionsModel->getAllMemberSessions();
    var_dump($allMemberSessions);
    exit;
    $cntMemberSessions = count($allMemberSessions);
    if($cntMemberSessions > 0) {
        $ctr = 0;
        while($ctr < $cntMemberSessions) {
            $lastTransDate = $allMemberSessions[$ctr]['TransactionDate'];
            $MID = $allMemberSessions[$ctr]['MID'];
            $sessionID = $allMemberSessions[$ctr]['SessionID'];
            
            //compute last transdate in minutes
            $dateNow = date("Y-m-d H:i:s.u");
            $diffMins = (int)strtotime($dateNow) - (int)strtotime($lastTransDate);
            $years = floor($diffMins / (365*60*60*24));
            $months = floor(($diffMins - $years * 365*60*60*24) / (30*60*60*24));
            $days = floor(($diffMins - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24)); //actual day difference
            $noOfMins = round(abs($diffMins)/60,2); //actual minute difference
            
            if($noOfMins >= $setMinutes) {
                $isDeleted = $memberSessionsModel->deleteExpiredMemberSession($MID, $sessionID);
                if($isDeleted == 1) {
                    echo "Expired member session is successfully deleted.";
                    exit;
                }
                else {
                    echo "Failed to delete member session.";
                    exit;
                }
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
