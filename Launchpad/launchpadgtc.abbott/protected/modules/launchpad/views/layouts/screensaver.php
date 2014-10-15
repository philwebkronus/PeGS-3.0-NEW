<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/fancybox/jquery.fancybox-1.3.4.css" media="screen" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/css/style.css" media="screen, projection" />	
        <link rel="shortcut icon" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/images/favicon.ico" type="image/x-icon" />
        <!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>
    <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/loading_big.gif" style="display: none">
        <div id="page-wrapper">
            
            <div class="overlay-spacer" style="height:145px;"></div>
            
            <div id="casino-games-wrapper">
                
                <div id="casino-games-container">
                    
                    <?php echo $content; ?>
                     
                    <div class="clearer"></div>
                </div><!-- #casino-games-container -->
            </div><!-- #casino-games-wrapper-->
        </div><!-- #page-wrapper-->
        
        <div id="wood-background">
            
            <div id="bars-container" class="disabled">
                
                <div id="progressive-jackpot-container">
                    <div id="prog-text">PROGRESSIVE JACKPOT</div>
                    <div id="prog-text2">FIELD OF GREEN</div>
                    <div id="prog-amount">4,733.85</div>
                    <div class="clearer"></div>
                </div><!-- #progressive-jackpot-container -->
                
                <div id="main-casino-game-container">

                    <div id="casino-game-container">

                        <div id="casino-text">CASINO</div>
                        <div id="casino-text2">VIBRANT VEGAS</div>
                        <div id="casino-balance-text">BALANCE</div>                    
                        <div id="casino-balance">4,733.85</div>                    
                        <div class="clearer"></div>
                    </div><!-- #casino-game-container -->

                    <div id="refresh-button">
                        <a href="#" id="refresh_screensaver"><img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/refresh_button.png"></a>
                    </div><!-- #refresh-button -->

                    <div class="clearer"></div>                    

                </div><!-- #main-casino-game-container -->                
                
            </div><!-- #bars-container -->
            
        </div><!-- #wood-background -->
        
        <div id="overlay">
            <div class="overlay-child">
<!--                <div id="egames-logo-container"><img src="<?php //echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/e_games_logo.jpg"></div>-->
                <div id="virtual-ecity-logo-container"><img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/virtual_entertainment_city_logo.png"></div>
                
                 <div style="height: 333px;" id="overlay-spacer-button"></div>
                
                <div style="margin:25px auto; width:142px;">
                    
                    <div id="link-overlay-refresh" class="link-container">
                        <a href="">
                            <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/refresh_large.png">
                        </a>
                    </div><!-- #link-vibrant-vegas -->
                    
                </div>
                
                <div id="footer-wrapper">                
                    <div id="copyright-overlay">
                        <div style="margin-top: 40px; width: 100%; float: left;">
                            <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/under_21_logo.png" />
                            <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/pagcor_logo.png" />
                            <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/philweb_logo.png" />
                            <br>
                            &copy; 2012 e-Games &bull; All Rights Reserved.
                        </div>
                    </div>

                    <div class="clearer">&nbsp;</div>


                </div>
            </div>
        </div> 

<style>
#feedcontent{list-style: none}    
body{background-color: #010000}
</style>
<script type="text/javascript">
        try {
            window.external.ScreenBlocker(TRUE); 
        } catch(e) {
            //do nothing
        }
</script>
<script type="text/javascript">
    $(document).ready(function(){
//        $.ajax({
//            url:'<?php //echo LPConfig::app()->params['rssFeedUrl'] ?>',
//            dataType:'xml',
//            cache: false,
//            success:function(xml){
//                if(!$(xml).find('item').length) {
//                    $('#rotating-content').html('');
//                    return false;
//                }
//                    
//                var ul = '<ul id="feedcontent">'
//                $(xml).find('item').each(function(){
//                    ul+='<li><input type="hidden" value="'+$(this).find('title').text()+'" />'+$(this).find('description').text()+'</li>';
//                });
//                ul+='</ul>';
//                $('#rotating-content').html(ul);
//                $('#rotating-content').vTicker({
//                    speed: 500,
//                    pause: 3000,
//                    showItems: 1,
//                    animation: 'fade',
//                    mousePause: false,
//                    height: 0,
//                    direction: 'up',
//                    onLoad:function(obj){
//                       var title = obj.children('li:first').children('input').val();
//                       $('#messages-and-announcement-container').html('<h2 class="messages">'+title+'</h2>');
//                    },
//                    onChange:function(obj){
//                       if(obj != undefined) {
//                            var title = obj.children('li:first').children('input').val();
//                            $('#messages-and-announcement-container').html('<h2 class="messages">'+title+'</h2>');
//                       }
//                    }
//                });
//            }
//        });
    });

</script>
</body>
</html>
