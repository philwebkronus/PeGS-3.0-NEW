<?php

require_once("../init.inc.php");

/**
 * Test Project for PlayTech API Integration
 * @author elperez
 * dateCreated 12/10/12
 */

$MID = $_GET['MID'];

App::LoadModuleClass("Membership", "MemberInfo");
App::LoadModuleClass("Membership", "MemberServices");

//Configuration requirements
$URI = 'https://cashier-dev.egamescasino-ss.ph';
$casino = 'egamesqa';
$playerSecretKey = 'playtech';
$depositSecretKey = 'PhilWeb123';
$withdrawSecretkey = 'PhilWeb123';

/*
 * Instantiate Modles
 */
$_MemberInfo = new MemberInfo();
$_MemberServices = new MemberServices();

$memberservices = $_MemberServices->getUserBasedMemberServices( $MID );
$memberinfo = $_MemberInfo->getMemberInfo( $MID );

//App::Pr($memberinfo); exit;
/*
 * Member account info
 */


$userName = $memberservices[0]['ServiceUsername'];
$password = $memberservices[0]['ServicePassword'];
$email = str_replace(' ','_',$memberinfo[0]['Email']);
$firstName = str_replace(' ','',$memberinfo[0]['FirstName']);
$lastName = str_replace(' ','_',$memberinfo[0]['LastName']);
$birthDate = date('Y-m-d',strtotime($memberinfo[0]['Birthdate']));
(!empty($address)) ? $address = str_replace(' ','_',$memberinfo[0]['Address1']) : $address = 'NA';
(!empty($city)) ? $city = $memberinfo[0]['Address2'] : $city = "NA";
$countryCode = 'PH';
(!empty($phone)) ? $phone = $memberinfo[0]['MobileNumber'] : $phone = '9999';
$zip = 'NA';

//VIP level : mimie
$vipLevel = 1; //1-reg ; 2-vip

//App::Pr($casinoservice); 
//App::Pr($email);
//exit;

echo $userName . '<br />';
echo $password . '<br />';
echo $email . '<br />';
echo $firstName . '<br />';
echo $lastName . '<br />';
echo $birthDate . '<br />';
echo $address . '<br />';
echo $city . '<br />';
echo $countryCode . '<br />';
echo $phone . '<br />';
echo $zip . '<br />';
echo $vipLevel . '<br />';

//Instantiate, PT Classs Wrapper
//include 'PlayTechAPI.class.php';
App::LoadModuleClass("CasinoProvider", "PlayTechAPI");
$playtechAPI = new PlayTechAPI($URI, $casino, $playerSecretKey);
          
//Create Account
$apiResult = $playtechAPI->NewPlayer($userName, $password, $email, $firstName, 
                $lastName, $birthDate, $address, $city, $countryCode, $phone, 
                $zip, $vipLevel);

App::Pr($apiResult);
?>
