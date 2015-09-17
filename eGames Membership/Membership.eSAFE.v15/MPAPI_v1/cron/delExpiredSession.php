<?php

/*
 * This cron is for deleting of expired(has been idle for 20 minutess) member sessions
 * @date 07-07-2014
 * @author fdlsison
 * 
 * 
 */

    $memberSessionModel = new MemberSessionsModel();
    $setMinutes = 20;
    $setDays = 3;
    
    $memberSessionModel->delExpiredMemberSession();
    
?>
