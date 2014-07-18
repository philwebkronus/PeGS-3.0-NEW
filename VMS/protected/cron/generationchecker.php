<?php

/**
 * @author Noel Antonio
 * @dateCreated 03-13-2014
 */

include './config.php';
include './cron.class.php';

try 
{
    $cron = new cron($connString[0]);
    $cron->open();
    $pending_cron = $cron->selectPendingCron(2);
    
    if ($pending_cron["PendingCron"] == 0)
    {
        $result = $cron->createCronSession(2);
        if ($result)
        {
            $cron->cURL( $generateURI );
        }
        else
        {
            print "Cron 2: Creation of cron session failed.";
        }
    }
    else
    {
        print "Cron 2: There is still pending cron on processed.";
    }
   
    $cron->close();
} 
catch (PDOException $e) 
{
    print "Error!: " . $e->getMessage() . "<br/>";
}
?>
