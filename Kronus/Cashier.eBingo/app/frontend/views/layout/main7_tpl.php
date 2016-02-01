<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title><?php echo $this->title; ?></title>
        <link rel="stylesheet" type="text/css" media="screen" href="css/default.css" />
        <!--[if IE]>
            <link rel="stylesheet" href="css/ie.css" type="text/css" media="screen, projection">
        <![endif]-->
        <link rel="stylesheet" type="text/css" media="screen" href="jscripts/fancybox/jquery.fancybox-1.3.4.css" />
        
        <script type="text/javascript" src="jscripts/jquery.min.js"></script>
        <script type="text/javascript" src="jscripts/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
        <script type="text/javascript" src="jscripts/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
        <script type="text/javascript" src="jscripts/jquery.lightbox.js"></script>
        <script type="text/javascript" src="jscripts/accounting.min.js"></script>
        <script type="text/javascript" src="jscripts/jquery.helpers.js"></script>
        <script type="text/javascript" src="jscripts/autoNumeric-1.6.2.js"></script>
<!--        <script type="text/javascript" src="https://getfirebug.com/firebug-lite.js"></script>-->
    </head>
    <body lang="en">
        <div id="container">
            <div id="header">
                <div id="top-header">
                    <div id="head-version"><?php echo Mirage::app()->param['sysversionname'] ?></div>
                    <div id="head-bcf" url="<?php echo $this->createUrl('terminal/sitebalance'); ?>"><div>BCF: PhP <span><?php echo $this->getSiteBalance(); ?></span></div></div>
                    <div id="head-date-time">
                        <div id="head-time"></div>
                        <div><?php echo date('l, F d, Y') ?></div>
                    </div>
                    <div id="logout"><input id="btnLogout" type="button" value="Logout" /></div>
                    <div class="clear"></div>
                    <div id="head-spacer"><div>e-Games Station Manager</div></div>
                    <div id="main-menu">
                    <?php
                        echo Menu::display(array(
                            'Start Session'=>array('link'=>$this->createUrl('startsession'),'act'=>'overview','con'=>'StartSession','attr'=>'desc="Start a new player session"'),
                            'Reload Session'=>array('link'=>$this->createUrl('reload'),'act'=>'overview','con'=>'ReloadSession','attr'=>'desc="Increase the player\'s playing money"'),
                            'Redemption'=>array('link'=>$this->createUrl('redeem'),'act'=>'overview','con'=>'Redeem','attr'=>'desc="End a player\'s session and process cash redemption"'),
                            'Reports'=>array('link'=>$this->createUrl('reports'),'act'=>'overview','con'=>'Reports','attr'=>'desc="List of all player session transactions"'),
                            'View Transaction History'=>array('link'=>$this->createUrl('viewtrans/history'),'act'=>'history','con'=>'ViewTransaction','attr'=>'desc="View Transactions History"'),
                            'Refresh'=>array('link'=>'','attr'=>'desc="Refresh the page" id="refresh_getbal"','visible'=>$this->show_refresh)
                        )); 
                    ?>
                        <div id="user-details">
                            <?php echo $_SESSION['account_name'] . ' - ' . $_SESSION['site_code'] .' / '. $_SESSION['pos_account']; ?>    
                        </div>
                    
                    <?php //debug($_SESSION); ?>    
                    </div> 
                    <div id="menu-description">
                        
                    </div>
                </div>
            </div>
            <div class="clear"></div>
            <?php echo $content; ?>
        </div>
        <div class="clear"></div>
        <?php if($this->show_refresh): ?>
        <div>
            <b>HOT KEYS <i>D</i></b> - Start Session <b><i>R</i></b> - Reload <b><i>W</i></b> - Redeem <b><i>Esc</i></b> - Close form
        </div>
        <?php endif; ?>
        <div style="text-align: center">e-Games Station Manager. Copyright &copy; 2011. PhilWeb Corporation. All rights reserved.</div>
        <script type="text/javascript">
            <?php echo clock('head-time'); ?>
        </script>
        <script>
        $(document).ready(function(){                    
            $('#btnLogout').click(function(){
                if (confirm('Are you sure you want to logout?')) {
                    document.location = '<?php echo Mirage::app()->param['logout_page'] ?>';
                }
            })
            var hearbeatAjax = null;
            setInterval(function(){
                if(hearbeatAjax == null) {
                    hearbeatAjax = $.ajax({
                        url:'<?php echo Mirage::app()->createUrl('terminal/ping') ?>',
                        success:function(){
                            hearbeatAjax = null
                        },
                        error:function(){
                            hearbeatAjax = null;
                        }
                    });
                }
            }, <?php echo Mirage::app()->param['heartbeat_rate'] ?>);
            
            <?php if($_SESSION['spyder_enabled'] == 0): ?>
            try {
                var axo = new ActiveXObject("PEGS.StationManager.ActiveX.Controller");
            } catch(e) {
                $.fancybox("<?php echo Mirage::app()->param['pegsstationerrormsg'] ?>",{modal:false});
            }
            <?php endif; ?>
        });
        </script>
        <script type="text/javascript" src="jscripts/refresh-sitebalance.js"></script>
    </body>
</html>
