<?php $this->beginContent('//layouts/main'); ?>
<div class="container">
    <?php /*
        <script>
        $(function() {
            $( "#menu" ).menu();
        });
        </script>
        <style>
        .ui-menu { 
            width: 180px; 
            font-size:12px; 
            margin:20px 10px;
            padding:10px 4px;
            z-index: 100000;
        }
        </style>
        <div class="span-5 first">
            <div id="sidemenu">
            <?php 
                if(isset(Yii::app()->session['AccountType']))
                {
                    $accounttype = Yii::app()->session['AccountType'];

                    //Get menus from database by account type id
                    $items =  SiteMenu::getMenusByAccountType($accounttype);

                }else
                    $items = array();


                $logout[] = array(
                   'label'         => 'Logout' . ' ('.Yii::app()->user->name.')',
                   'url'           => array('/site/logout'),
                   'itemOptions'   => array('class'=>'menuItem'),
                   'linkOptions'   => array('class'=>'menuItemItemLink', 'title'=>'Logout'),
                   'submenuOptions'=> array(),
                   'visible'=>!Yii::app()->user->isGuest, 

                 );

                $items = array_merge($items,$logout);

                $this->widget('zii.widgets.CMenu', array(
                  'id' => 'menu',
                  'activeCssClass'=>'selected',
                  'items'=>$items
                ));

                ?>
                <div class="datetime"><?php echo date('l, F d, Y'); ?></div>   

            </div><!-- mainmenu -->
        </div>
     * 
     */ ?>
        <!--<div class="span-18 last">-->
            <div id="content">
                    <?php echo $content; ?>
            </div><!-- content -->
        <!--</div>-->
</div>
<?php $this->endContent(); ?>