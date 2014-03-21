<?php

return array(
    'accoounttypeid'=>'13',
    'homeUrl'=>array('/managerss/auth/login'),
    'db'=>array(
        'connectionString' => 'mysql:host=172.16.102.35;dbname=npos',
        'emulatePrepare' => true,
        'username' => 'nposconn',
        'password' => 'npos',
        'charset' => 'utf8',
    ),
    
    // mail
    'from'=>'poskronusadmin@philweb.com.ph',
    'from_name'=>'poskronusadmin',
    
    // max login attempt
    'max_login_attempts'=>3,
    
    // user status
    'account_status'=>'(1,6)',
    
    // minimum password length
    'min_password_length'=>8,
    
    'theme'=>'redmond',//ui-darkness, start, redmond
    'initialLimit'=>10,
    'pageGridLimit'=>array(10,20,30),
    
    'max_length_content'=>330,
    'max_height'=>'125px',
    
    // xml config
    'rssTitle'=>'Lanchpad',
    'rssUrl'=>Yii::app()->createUrl('managerss/rss/feed'),
    'rssLanguage'=>'en-us',
    'rssCategory'=>'Announcement',
);