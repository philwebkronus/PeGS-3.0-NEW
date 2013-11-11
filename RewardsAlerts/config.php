<?php

/**
 * @Description: Config for Manage Rewards Cron Job
 * @Author: aqdepliyan
 * @DateCreated: 2013-10-30
 */

return array(
    
    // Rewards Management Database
    'db'=>array(
        'connectionString' => 'mysql:host=<database host>;dbname=<database name>',
         'username' => '<username>',
         'password' => '<password>',
    ),
    
    'params'=>array(
        'EmailRecipient' =>array('<marketing email address>'), //marketing email address
        'EmailSender' =>'rewardsmanagement@philweb.com.ph',
        'CriticalLvl' => '0.25',
    ),
);
?>
