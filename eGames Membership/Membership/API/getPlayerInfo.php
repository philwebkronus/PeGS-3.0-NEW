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
$URI = 'https://extdev-devhead-cashier.extdev.eu';
$casino = 'playtech800041';
$playerSecretKey = 'PhilWeb123';
$depositSecretKey = 'PhilWeb123';
$withdrawSecretkey = 'PhilWeb123';
        
/*
 * Instantiate Modles
 */
$_MemberServices = new MemberServices();

$casinoservice = $_MemberServices->getUserBasedMemberServices( $MID );
//App::Pr($memberinfo); exit;
/*
 * Member account info
 */
$userName = $casinoservice[0]['ServiceUsername'];
$password = $casinoservice[0]['ServicePassword'];

//Instantiate, PT Classs Wrapper
//include 'PlayTechAPI.class.php';
App::LoadModuleClass("CasinoProvider", "PlayTechAPI");
$playtechAPI = new PlayTechAPI($URI, $casino, $playerSecretKey);

//Create Account
$apiResult = $playtechAPI->GetPlayerInfo($userName, $password);

App::Pr($apiResult);

?>
