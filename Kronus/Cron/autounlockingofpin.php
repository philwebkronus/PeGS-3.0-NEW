<?php
/**
 * Auto-unlocking of PIN
 * @author Mark Kenneth Esguerra
 * @date Febraury 12, 2015
 */
include_once "init.php";
include "Membership.class.php";

ini_set('display_errors',true);
ini_set('log_errors',true);

$membership = new Membership($_DBConnectionString[1]);
$datetime_now = date('Y-m-d H:i:s');
$max_attempts = 15;
$cooling_time = 1;
$connected = $membership->open();

if ($connected)
{
    //get all members that had reached maximum pin login attempts
     
    $lockedpins = $membership->checkLockedPINs($max_attempts);
    if (count($lockedpins) > 0)
    {
        foreach ($lockedpins as $row)
        {
            $time_diff = round(abs(strtotime($datetime_now) - strtotime($row['DatePINLocked'])) / 60, 2);  
            if ($time_diff >= $cooling_time)
            {
                //reset PINLoginAttempts
                $membership->resetPINLoginAttempts($row['MID']);
            }
        }
    }
}

?>