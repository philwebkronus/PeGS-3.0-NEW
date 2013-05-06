<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
    <head>
        <title><?php echo $this->title ?></title>
        <link rel="stylesheet" type="text/css" media="screen" href="css/default.css" />
        <!--[if IE]>
            <link rel="stylesheet" href="css/ie.css" type="text/css" media="screen, projection">
        <![endif]-->
        <link rel="stylesheet" type="text/css" media="screen" href="jscripts/fancybox/jquery.fancybox-1.3.4.css" />     
        <script type="text/javascript" src="jscripts/jquery.min.js"></script>
        <script type="text/javascript" src="jscripts/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>
        <script type="text/javascript" src="jscripts/fancybox/jquery.fancybox-1.3.4.pack.js"></script>
        <script type="text/javascript" src="jscripts/jquery.lightbox.js"></script>        
    </head>
    <body>
        <div id="container">
            <div id="header">
                <div id="top-header">
                    <div id="head-bcf" url="<?php echo $this->createUrl('terminal/sitebalance'); ?>"><div>BCF: PhP <span><?php echo $this->getSiteBalance(); ?></span></div></div>
                    <div id="head-date-time">
                        <div id="head-time"></div>
                        <div><?php echo date('l, F d, Y') ?></div>
                    </div>
                    <div id="logout">
                        <?php if($_SESSION['acctype'] != 2): ?>
                        <input id="btnLogout" type="button" value="Logout" />
                        <?php endif; ?>
                    </div>
                    <div class="clear"></div>
                    <div id="head-spacer"><div>PAGCOR e-Games Station Manager</div></div>
                    <div id="main-menu">
                    <?php
                        echo Menu::display(array(
                            'Terminal Monitoring'=>array('link'=>$this->createUrl('monitoring/overview'),'act'=>'overview','con'=>'TerminalMonitoring','mod'=>'monitoring','attr'=>'desc="Terminal activity monitoring"'),
                            'Refresh'=>array('link'=>'','attr'=>'desc="Refresh the page" id="refresh_getbal"','attr'=>'style="display:none" id="refresh_getbal"')
                        )); 
                    ?>
                        <div id="user-details">
                            <?php echo $_SESSION['account_name'] . ' - ' . $_SESSION['site_code'] .' / '. $_SESSION['pos_account']; ?>    
                        </div>
                    </div> 
                    <div id="menu-description">
                        
                    </div>
                </div>
            </div>
            <div class="clear"></div>
            <?php echo $content; ?>
        </div>
        <script>
        $(document).ready(function(){
            $('#btnLogout').click(function(){
                if (confirm('Are you sure you want to logout?')) {
                    document.location = '<?php echo Mirage::app()->param['logout_page'] ?>';
                }
            })
        });
        </script>
    </body>
</html>
