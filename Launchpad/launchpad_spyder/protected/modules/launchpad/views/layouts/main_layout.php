<!DOCTYPE html>
<html>
    
    <head>
        
        <title>PEGS Casino Lobby</title>
        <link rel="stylesheet" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/css/reset.css" type="text/css">
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
        <link rel="stylesheet" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/css/style.css" type="text/css">
         <link rel="shortcut icon" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/images/favicon.ico" type="image/x-icon" />
    </head>
    
    <body>
        
        <div id="page-wrapper">
            <?php echo $content; ?>
        </div><!-- #page-wrapper-->        
        
        <div id="footer-wrapper">

            <div id="copyright" style="float: left;">
                <div style="margin-top: 43px; width: 100%; float: left;">
                    <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/images/under_21.png" />
                    <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/images/pagcor_logo.png" />
                    <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/images/philweb_logo.png" />
                    <br>
                    &copy; 2012 e-Games &bull; All Rights Reserved.
                </div>
            </div>

            <div class="clearer">&nbsp;</div>

        </div><!-- #footer-wrapper -->

     
    </body>
</html>