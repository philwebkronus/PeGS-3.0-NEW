<?php

/**
 * @author Noel Antonio
 * @dateCreated 03-17-2014
 */

include 'config.php';
include 'cron.class.php';

try 
{
    $cron = new cron($connString[0]);
    $cron->open();
    $pending_cron = $cron->selectPendingCron(1);
    
    if ($pending_cron["PendingCron"] == 0)
    {
        $result = $cron->createCronSession(1);
        if ($result)
        {
            $cron->cURL( $expiryURI );
        }
        else
        {
            print "Cron 1: Creation of cron session failed.";
        }
    }
    else
    {
        print "Cron 1: There is still pending cron on processed.";
    }
   
    $cron->close();
} 
catch (PDOException $e) 
{
    print "Error!: " . $e->getMessage() . "<br/>";
}
?>