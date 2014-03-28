<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/css/lp.css" media="screen, projection" />	
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
        <link rel="shortcut icon" href="http://pj.pagcoregames.com/favicon.ico" type="image/x-icon" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>
    <div class="container" id="page">

            <?php echo $content; ?>

            <div class="clear"></div>

            <div id="footer">

            </div><!-- footer -->

    </div><!-- page -->
</body>
</html>
