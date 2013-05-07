<!--<div id="egames-logo-container"><img src="<?php //echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/e_games_logo.png"></div>-->
<div id="virtual-ecity-logo-container">
    <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/virtual_entertainment_city_logo.png">
</div>

<div id="casino-games-wrapper" align="center">

    <div align="center" id="casino-games-container">

        <?php foreach($cas as $key => $casino): // loop in each casino position ?>
        <div id="<?php echo Lib::getCasinoName($key) ?>-container" <?php echo ($casino == 'N/A')?'class="disabled"':'' ?>>
            <div id="link-<?php echo Lib::getCasinoName($key) ?>" class="link-container">
                <?php if($casino == 'N/A'): ?>
                <a>
                <?php else: ?>
                <?php
                $botPath = LPConfig::app()->params['bot_path'];
                $casinoPath = LPConfig::app()->params['game_client'][$casino['ServiceID']-1];
                ?>
                <a botpath="<?php echo $botPath ?>" casinopath="<?php echo $casinoPath ?>"  serviceType="<?php echo $casino['type']; ?>" serviceid="<?php echo $casino['ServiceID']; ?>" class="casino" href="<?php echo $this->createUrl('transfer'); ?>">
                <?php endif; ?>
                    <?php if($key == 'SW'){?>
                        <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/images/<?php echo Lib::getImage($key) ?>.png">
                    <?php } else { ?>
                        <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/images/<?php echo Lib::getImage($key) ?>.jpg">
                    <?php }?>
                </a>
            </div><!-- #link-magic-macau -->
        </div>
        <?php endforeach; ?>

        <div class="clearer"></div>

    </div><!-- #casino-games-container -->

</div><!-- #casino-games-wrapper -->

<!--<div id="copies-container">-->
<div id="wood-background">
    <div id="bars-container">
        <?php if($currentCasino == 'Magic Macau'): ?>
        <div id="progressive-jackpot-container">

            <div id="prog-text">PROGRESSIVE JACKPOT</div>
            <div id="prog-text2"><?php echo ProgressiveJackpot::getJockpot($currentCasino,$currentServiceID); ?></div>
             <div id="prog-amount"></div>
            <div class="clearer"></div>
            
        </div>
        <?php endif; ?>

        <div id="main-casino-game-container">
            <div id="casino-game-container">
                <div id="casino-text">CASINO</div>
                <div id="casino-text2"><?php echo isset($currentCasino)?$currentCasino:''; ?></div>
                <div id="casino-balance-text">BALANCE</div>                    
                <div id="casino-balance"><?php echo isset($currentBalance)?$currentBalance:''; ?></div>                    
                <div class="clearer"></div>
            </div>
            <div id="refresh-button">
                <a href="" id="refresh"><img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/images/refresh_button.png"></a>
            </div><!-- #refresh-button -->    

            <div class="clearer"></div>
        </div>
    </div>
</div>
<!--</div>-->

<script type="text/javascript">
$(document).ready(function(){
    var xhrbalance = null;
    
    $('#prog-text2').vTicker({
        speed: 500,
        pause: 3000,
        showItems: 1,
        animation: 'fade',
        mousePause: false,
        height: 0,
        direction: 'up'
//        onLoad:function(obj){
//           var title = obj.children('li:first').children('input').val();
//           $('#messages-and-announcement-container').html('<h2 class="messages">'+title+'</h2>');
//        },
//        onChange:function(obj){
//           if(obj != undefined) {
//                var title = obj.children('li:first').children('input').val();
//                $('#messages-and-announcement-container').html('<h2 class="messages">'+title+'</h2>');
//           }
//        }
    });
    
    
    
    // document hover
   $(document).live('hover',function(){
      if(xhrbalance == null) {
         //url = '<?php //echo $this->createUrl('getBalance',array('serviceID'=>Yii::app()->user->getState('currServiceID'),'format'=>'yes')); ?>';
         url = '<?php echo $this->createUrl('getcasinoandabalance',array('format'=>'yes')); ?>';
         jQuery('#casino-balance').html('Loading ...');
         jQuery('#casino-text2').html('Loading ...');
         xhrbalance = jQuery.ajax({
            url : url,
            type : 'post',
            dataType : 'json',
            success : function(data){
               if(data.balance == undefined) {
                   jQuery('#casino-balance').html('<span syle="color:red">N/A</span>');
                   jQuery('#casino-text2').html('<span syle="color:red">N/A</span>');
               } else {
                   jQuery('#casino-balance').html(data.balance);
                   jQuery('#casino-text2').html(data.casino);
               }
               xhrbalance = null;
            },
            error : function(e){
               jQuery('#casino-balance').html('<span syle="color:red">N/A</span>');
               xhrbalance = null;
            }
         });
      }
   });
   
    $('#refresh').click(function(){
        location.reload();
        showLightbox();
    });
    
    var heartbeat = null;
    setInterval(function(){
        if(heartbeat == null) {
            heartbeat = $.ajax({
                url:'<?php echo Yii::app()->createUrl('launchpad/lobby/ping'); ?>',
                success:function(){
                    heartbeat = null;
                },
                error:function(){
                    heartbeat = null;
                }
            })
        }
        },'<?php echo LPConfig::app()->params['heart_beat'] ?>')
})
</script>