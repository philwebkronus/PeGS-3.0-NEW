        <?php foreach ($CasinoCode as $casinoCode): // loop in each casino position  ?>
            <div id="<?php echo Lib::getCasinoName($casinoCode); ?>-container">
                <div id="link-<?php echo Lib::getCasinoName($casinoCode); ?>" class="link-container">
                    <?php if($casinoCode == 'SW'){?>
                        <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/images/<?php echo Lib::getImage($casinoCode) ?>.png">
                    <?php } else { ?>
                        <img src="<?php echo Yii::app()->getModule('launchpad')->baseAssets; ?>/images/<?php echo Lib::getImage($casinoCode) ?>.jpg">
                    <?php }?>
                </div><!-- #link-magic-macau -->
            </div>
        <?php endforeach; ?>