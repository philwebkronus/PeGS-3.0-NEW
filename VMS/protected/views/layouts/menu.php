<?php

/*
 * @Date Dec 7, 2012
 * @Author owliber
 */
?>
<div id="sidemenu">
    <?php 

    if(isset(Yii::app()->session['AccountType']))
    {
        $accounttype = Yii::app()->session['AccountType'];

        //Get menus from database by account type id
        $items =  SiteMenu::getMenusByAccountType($accounttype);

    }else
        $items = array();


    //Set Login/Logout as default menus
    //        $login[] = array(
    //           'label'         => 'Login',
    //           'url'           => array('/site/login'),
    //           'itemOptions'   => array('class'=>'menuItem'),
    //           'linkOptions'   => array('class'=>'menuItemLink', 'title'=>'Login'),
    //           'submenuOptions'=> array(),
    //           'visible'=>Yii::app()->user->isGuest,
    //         );

    $logout[] = array(
       'label'         => 'Logout' . ' ('.Yii::app()->user->name.')',
       'url'           => array('/site/logout'),
       'itemOptions'   => array('class'=>'menuItem'),
       'linkOptions'   => array('class'=>'menuItemItemLink', 'title'=>'Logout'),
       'submenuOptions'=> array(),
       'visible'=>!Yii::app()->user->isGuest, 
    //           'items' => array(
    //               array(
    //                    'label'         => 'Change Password',
    //                    'url'           => array('/user/changepassword'),
    //                    'itemOptions'   => array('class'=>'listItem'),
    //                    'linkOptions'   => array('class'=>'listItemLink', 'title'=>'Change Password'),
    //                    'submenuOptions'=> array(),
    //                   ),
    //           ),
     );

    //$defaultmenus = array_merge($login,$logout);

    $items = array_merge($items,$logout);

    $this->widget('zii.widgets.CMenu', array(
      'id' => 'nav',
      'activeCssClass'=>'selected',
    //          'linkLabelWrapper'=>null, 
      //'htmlOptions'=>array('class'=>'leftNav'),
      'items'=>$items
    ));

    ?>
    <div class="datetime"><?php echo date('l, F d, Y'); ?></div>   

</div><!-- mainmenu -->
