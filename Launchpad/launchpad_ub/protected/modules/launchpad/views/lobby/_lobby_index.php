<br />

<div id="egames-logo-container">
    <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/e_games_logo.png">
</div>

<div id="casinogames-wrapper">
    <?php if(Yii::app()->user->isGuest): // if guest ?>
        <div id="magic-macau" class="link-container">
            <a href="#">
                <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/magic_macau_deactivated.jpg">
            </a>
        </div>

        <div id="slots-world"  class="link-container">
            <a href="#">
                <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/slots_world_deactivated.jpg">
            </a>
        </div>

        <div id="vibrant-vegas"  class="link-container">
            <a href="#">
                <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/vibrant_vegas_deactivated.jpg">
            </a>
        </div>
    <?php else: ?>
        <?php foreach($cas as $key => $casino): // loop in each casino position ?>
            <?php if($casino == 'N/A'): ?>
                <div id="<?php echo Lib::getCasinoDivID($key) ?>">
                    <a>
                        <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/<?php echo Lib::getDisableImage($key) ?>">
                    </a>
                </div>
            <?php else: ?>
                <div id="<?php echo Lib::getCasinoDivID($key) ?>">
<!--                    <a class="casino" href="<?php //echo $this->createUrl('casinoClick',array('serviceid'=>$casino['ServiceID'])); ?>"></a>-->
                <?php 
                $type = $casino['type'];
                switch($type) {
                    case 'mg':
                        $client = 'mg';
                        break;
                    case 'rtg':
                        $client = 'rtg';
                        break;
                    case 'pt':
                        $client = 'pt';
                        break;
                }                    
                $botPath = LPConfig::app()->params['bot_path'];
                $casinoPath = LPConfig::app()->params['game_client'][$casino['ServiceID']-1];
//                $formTitle = LPConfig::app()->params['game_path'][$client]['title'];
//                formtitle=" echo $formTitle "
                ?>
                    <a botpath="<?php echo $botPath ?>" casinopath="<?php echo $casinoPath ?>"  serviceType="<?php echo $casino['type']; ?>" serviceid="<?php echo $casino['ServiceID']; ?>" class="casino" href="<?php echo $this->createUrl('transfer'); ?>"></a>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; // end of if guest ?>
    <div class="clearer">&nbsp;</div>
</div><!-- #casinogames-wrapper -->
<div class="clearer"></div>
<div id="form-details" style="">
    <div class="span-5 prepend-1">
        <div class="span-1" style="margin-left: -5px;">Casino</div>
        <div class="span-3 push-1 bolder" style="margin: 0 -40px 1.5em 10px"><?php echo isset($currentCasino)?$currentCasino:''; ?></div>
    </div>
    <div class="span-4">
        <div class="span-1 " style="margin-left: -25px; padding-right: 7px;">Balance</div>
        <div class="span-3  bolder" style="margin: 0 -40px 1.5em 10px" id="currentBalance"><?php echo isset($currentBalance)?$currentBalance:''; ?></div>
    </div>
    <div class="span-5 last" ><input style="margin-top: -5px;" type="image" src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/refresh.png" id="refresh" /></div>
</div>
<div class="clearer">&nbsp;</div>
<div id="footer-wrapper">

    <div id="copyright">
        <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/under_21.png">
        <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets ?>/images/philweb_logo.png">
        <br>
        &copy; 2012 e-Games &bull; All Rights Reserved.
    </div>

    <div class="clearer">&nbsp;</div>

</div><!-- #footer-wrapper -->

<script type="text/javascript">
$(document).ready(function(){
    var xhrbalance = null;
    
    // document hover
   $(document).live('hover',function(){
      if(xhrbalance == null) {
         url = '<?php echo $this->createUrl('getBalance',array('serviceID'=>Yii::app()->user->getState('currServiceID'),'format'=>'yes')); ?>';
         jQuery('#currentBalance').html('Loading ...');
         xhrbalance = jQuery.ajax({
            url : url,
            type : 'post',
            dataType : 'json',
            success : function(data){
               if(data.balance == undefined)
                   jQuery('#currentBalance').html('<span syle="color:red">N/A</span>');
               else
                   jQuery('#currentBalance').html(data.balance);
               xhrbalance = null;
            },
            error : function(e){
               jQuery('#currentBalance').html('<span syle="color:red">N/A</span>');
               xhrbalance = null;
            }
         });
      } 
   });
   
    $('#refresh').click(function(){
        location.reload();
        showLightbox();
    });
})
</script>