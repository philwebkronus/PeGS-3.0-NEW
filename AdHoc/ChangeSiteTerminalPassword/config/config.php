<?php
//Credentials
$credentials = array(
    'testuser' => 'testpassword',
    'sampleuser' => 'samplepassword',
    'test' => 'test',
);


//IP that can access only
$IPrestriction = array('192.168.41.37','192.168.41.15', '192.168.41.188');

//Database
$svrname = '172.16.116.17';
$dbType = 'mysql';
$dbPort = '3307';
$dbName = 'npos';
$dbUsername = 'pegsconn';
$dbPassword = 'pegsconnpass';

//RTG
$urlRTG = 'https://topaz2001.egamescasino.ph/PHPTOPZDGXXS2DQWTWEZ/RTG.Services/Player.svc?wsdl';
$certFilePath = '/var/www/ChangeSiteTerminalPassword/process/RTGClientCert/22/cert.pem';
$keyFilePath = '/var/www/ChangeSiteTerminalPassword/process/RTGClientCert/22/key.pem';
$combiFilePath = '/var/www/ChangeSiteTerminalPassword/process/RTGClientCert/22/cert-key.pem';
$caching = '';

//Habanero
$urlHabanero = 'https://ws-pw.insvr.com/jsonapi';
$BrandID = 'e13b5650-16de-e711-a950-288023b998c7';
$APIkey = '5BC838D5-E208-40CD-B378-EDA14B32E9A1';
$Currency = 'PHP';


//Maximum terminal to process
$max_terminal_process = '10';

//Available Casino to Change Password
$avilable_casino = array('22', '25');

