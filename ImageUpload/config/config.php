<?php

/**
 * @Description: Configurations for Image Upload Testing Environment
 * @DateCreated: 2014-03-18
 * @Author: aqdepliyan
 */

global $_Config;

$_Config = array(
	'tmp_path' => '/var/www/rewardsmanagement/images/tmp',             //PROD LOCATION
	'img_path' => '/var/www/rewardsmanagement/images/rewarditems',     //PROD LOCATION
	'tmp_url' => 'https://membership.egamescasino.ph/rewardsmanagement/images/tmp',             //PROD LOCATION
	'img_url' => 'https://membership.egamescasino.ph/rewardsmanagement/images/rewarditems',     //PROD LOCATION
	// 'tmp_path' => '/var/www/rewards.management/images/tmp/',						//DEV LOCATION
	// 'img_path' => '/var/www/rewards.management/images/rewarditems/',				//DEV LOCATION
	// 'tmp_url' => 'http://localhost/rewards.management/images/tmp/',				//DEV LOCATION
	// 'img_url' => 'http://localhost/rewards.management/images/rewarditems/',		//DEV LOCATION
	'image_allowed_ext' => array("jpg", "jpeg", "png", "gif")
);

?>
