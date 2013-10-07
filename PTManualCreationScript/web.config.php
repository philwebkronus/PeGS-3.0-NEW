<?php
global $_DBConnectionString, $URI, $casino, $secretKey, $reportUri, $rptCasinoName,
        $admin, $password, $reportCode;

//membership database
$_DBConnectionString[0] = "mysql:host=<hostname>;dbname=<db name>,<username>,<password>";

//configuration requirements of PT
$URI = 'https://cashier-dev.egamescasino-ss.ph';
$casino = 'egamesqa';
$secretKey = 'playtech';

//configuration requirements of PT report
$reportUri = 'https://devadmin.egamescasino-ss.ph';
$rptCasinoName = '19501';
$admin = 'ftgapuz';
$password = 'e3uBmNJdCi';
$reportCode = 49451;

?>
