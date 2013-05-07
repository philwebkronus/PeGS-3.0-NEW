<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="language" content="en" />

	<!-- blueprint CSS framework -->
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
	<!--[if lt IE 8]>
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
	<![endif]-->

	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
	<link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/menu.css" />
        <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/template.css" />
<!--    <link rel="stylesheet" href="http://code.jquery.com/ui/1.9.2/themes/base/jquery-ui.css" />
        <script src="http://code.jquery.com/jquery-1.8.3.js"></script>
        <script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>-->
	<title><?php echo CHtml::encode($this->pageTitle); ?></title>
</head>

<body>

<div class="container" id="page">

<!--	<div id="header">Voucher Management System</div> header -->
        <div id="header"></div>
        
        <div id="mainmenu">
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
          'id' => 'nav',
          'activeCssClass'=>'selected',
          'items'=>$items
        ));
        
        ?>
            <div class="datetime"><?php echo date('l, F d, Y'); ?></div>   
            
	</div><!-- mainmenu -->
                
	<?php if(isset($this->breadcrumbs)):?>
		<?php $this->widget('zii.widgets.CBreadcrumbs', array(
			'links'=>$this->breadcrumbs,
		)); ?><!-- breadcrumbs -->
	<?php endif?>

	<?php echo $content; ?>

	<div id="footer">
		Copyright &copy; <?php echo date('Y'); ?> by Philweb Corporation.<br/>
	</div><!-- footer -->

</div><!-- page -->

        

</body>
</html>