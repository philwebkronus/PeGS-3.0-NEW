<?php

/**
* @author Noel Antonio
* @dateCreated 03-11-2014
* This function is used to auto-generate the tickets when it reaches the
* threshold value set on the database. Here are the following steps:
* STEP 1:  Check TICKET_AUTOGEN_JOB on ref_parameters if ON, 
*          then run the cron script.
* STEP 2:  Retrieve # of Queued Tickets and TICKET_THRESHOLD on
*          ref_parameters. If Queued tickets is less than the threshold
*          then proceed to Step 3 else end of cron.
* STEP 3:  Generate tickets according to the set value of TICKET_COUNT
*          on ref_parameters table. Insert into ticketbatch, tickets,
*          ticketautogenlogs and audittrail.
* 
* This cron always run every hour.
*/

include './config.php';
include './cron.class.php';

try 
{
    $cron = new cron($connString[0]);
    $cron->open();
    $autogen_resultset = $cron->selectParamValueById(10);
    $autogen_value = $autogen_resultset["ParamValue"];

    if ($autogen_value == 1) // 1 - ON, 2 - OFF
    {
        $queued_tickets_resultset = $cron->selectQueuedTickets();
        $queued_tickets_count = $queued_tickets_resultset["QueuedTickets"];
        $threshold_resultset = $cron->selectParamValueById(13);
        $ticket_threshold = $threshold_resultset["ParamValue"];

        if ($queued_tickets_count <= $ticket_threshold)
        {
            $ticket_count = $cron->selectParamValueById(14);
            $count = $ticket_count["ParamValue"];
            $cron->generateTickets($count);
        }
        else
        {
            print "Queued tickets is still not on threshold level.";
        }
    }
    else
    {
        print "Job scheduler for auto-generation of tickets is OFF.";
    }
    
    // close the connection
    $cron->close();
} 
catch (PDOException $e) 
{
    print "Error!: " . $e->getMessage() . "<br/>";
}

?>
