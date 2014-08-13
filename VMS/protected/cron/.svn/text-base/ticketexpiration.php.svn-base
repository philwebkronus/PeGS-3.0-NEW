<?php

/**
* @author Noel Antonio
* @dateCreated 03-11-2014
* This function is used to automatically tag the tickets as expired.
* Here are the following steps:
* STEP 1:  Compare the ticket's VALID_TO_DATE to the current server date.
*          If VALID_TO_DATE is greater than the current date then proceed
*          to the next step else, end of the cron.
* STEP 2:  Update ticket status to 7 (EXPIRED) with maximum limit of 5,000
*          to avoid too much load on each execution.
* 
* This cron runs starting at 12:00AM then every 30 minutes.
*/

include 'config.php';
include 'cron.class.php';

try 
{
    $cron = new cron($connString[0]);
    $cron->open();
    
    $current_date = date('Y-m-d');
    $result = $cron->selectActiveTicketCount($current_date);
    $active_tickets = $result["ActiveTickets"];
    
    if ($active_tickets >= $update_limit)
    {
        $loop_ctr = $active_tickets / $update_limit;
        if (is_float($loop_ctr))
        {
            $loop_ctr = floor($loop_ctr) + 1;
        }
    }
    else
    {
        $loop_ctr = 1;
    }
    
    do
    {
        $cron->updateTicketsToExpiration($current_date, $update_limit);
        $loop_ctr--;
    }
    while ($loop_ctr != 0);     
    
    // close the connection
    $cron->close();
} 
catch (PDOException $e) 
{
    print "Error!: " . $e->getMessage() . "<br/>";
}
?>
