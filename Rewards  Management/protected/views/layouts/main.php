<?php /* @var $this Controller */ 
/**
 * SESSION CHECKING
 * If there are no session, the system will redirect back
 * to login page
 * Added by: mgesguerra
 * Date Added: October 7, 2013
 */
$_AccountSessions = new SessionForm();
$_PartnerSessions = new PartnerSessionModel();

if (isset(Yii::app()->session['SessionID'])) {
    $aid = Yii::app()->session['AID'];
    $sessionid = Yii::app()->session['SessionID'];
}
else 
{
    $sessionid = 0;
    $aid = 0;
}
//Check  if PartnerPID is set
if (isset(Yii::app()->session['PartnerPID']))
{
    $partnerPID = Yii::app()->session['PartnerPID'];
    $sessioncount = $_PartnerSessions->checkIfSessionExist($partnerPID, $sessionid); //Partner Account
}
else
{
    $sessioncount = $_AccountSessions->checkifsessionexist($aid, $sessionid); //Admin Account
}
if ($sessioncount == 0) {
    Yii::app()->user->logout();
    $this->redirect(array(Yii::app()->defaultController));
} 
else 
{
?>
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

            <!-- blueprint CSS framework -->
            <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/screen.css" media="screen, projection" />
            <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/print.css" media="print" />
            <!--[if lt IE 8]>
            <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/ie.css" media="screen, projection" />
            <![endif]-->
            <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.jqGrid-4.3.1/css/ui.jqgrid.css" />
            <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/main.css" />
            <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/form.css" />
                         <link rel="stylesheet" type="text/css" href="<?php echo Yii::app()->request->baseUrl; ?>/css/redmond/jquery-ui-1.9.2.custom.css" />

            <title><?php echo CHtml::encode($this->pageTitle); ?></title>
    </head>

    <body>

    <div class="container" id="page">

            <div id="header">
                    <div id="logo"><?php echo CHtml::encode(Yii::app()->name); ?></div>
            </div><!-- header -->
            <div id="mainmenu">
                <br>
                    <?php 

            if(isset(Yii::app()->session['AccountType']))
            {
                $accounttype = Yii::app()->session['AccountType'];

                //Get menus from database by account type id
                $items =  SiteMenu::getMenusByAccountType($accounttype);

            }else
                $items = array();

            $this->widget('zii.widgets.CMenu',array(
                            'items'=>$items
                    ));
                    ?>
                    <div id="divLogout">
                    <?php 
                    if (isset(Yii::app()->session['AID']))
                    {
                        echo CHtml::link('Logout ('.Yii::app()->session['UserName'].')',array('/login/logout'),
                            array('class' => 'logoutlink')
                        ); 
                    }
                    else if (Yii::app()->session['PartnerPID'])
                    {
                        echo CHtml::link('Logout ('.Yii::app()->session['UserName'].')',array('/login/logoutpartner'),
                            array('class' => 'logoutlink')
                        ); 
                    }
                    ?>
                    </div>
            </div><!-- mainmenu -->
            <?php //if(isset($this->breadcrumbs)):?>
                    <?php //$this->widget('zii.widgets.CBreadcrumbs', array(
                            //'links'=>$this->breadcrumbs,
                    //)); ?><!-- breadcrumbs -->
            <?php //endif?>

            <?php echo $content; ?>

            <div class="clear"></div>

            <div id="footer">
                    Copyright &copy; <?php echo date('Y'); ?> Philweb Corporation<br/>
                    All Rights Reserved.<br/>
            </div><!-- footer -->

    </div><!-- page -->
    <!--<script type="text/javascript" src="<?php //echo Yii::app()->request->baseUrl; ?>/js/jquery.fieldvalidator.js" ></script>-->
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.jqGrid-4.3.1/js/i18n/grid.locale-en.js" ></script>
        <script type="text/javascript" src="<?php echo Yii::app()->request->baseUrl; ?>/js/jquery.jqGrid-4.3.1/js/jquery.jqGrid.min.js" ></script>
        <!--<script type="text/javascript" src="<?php //echo Yii::app()->request->baseUrl; ?>/js/fancybox/jquery.mousewheel-3.0.4.pack.js"></script>-->
        <!--<script type="text/javascript" src="<?php //echo Yii::app()->request->baseUrl; ?>/js/fancybox/jquery.fancybox-1.3.4.pack.js"></script>-->
    <!--    <script type="text/javascript" src="<?php //echo Yii::app()->request->baseUrl; ?>/js/lightbox.js"></script>
        <script type="text/javascript" src="<?php //echo Yii::app()->request->baseUrl; ?>/js/jquery.tipTip.minified.js"></script>-->
    <?php 
        Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/idle.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/idlechecker.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/validations.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/trailingspaces.js');
        Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl . '/js/checkinput.js');
    ?>

    <input id="Timeout" type="hidden" value="<?php echo Yii::app()->params->idletimelogout;;?>" />
    <input id="logout" type="hidden" value="<?php echo Yii::app()->params->autologouturl;;?>" />
    <?php
    /** Start Widget **/
    $this->beginWidget('zii.widgets.jui.CJuiDialog',array(
        'id'=>'mydialog',
        'options'=>array(
            'title'=>'Alert',
            'autoOpen'=>false,
            'closeOnEscape' => false,
            'resizable'=>false,
            'draggable'=>false,
            'modal' => true,
            'open'=>'js:function(event, ui) { $(".ui-dialog-titlebar-close").hide(); }',
            'buttons' => array
            (
                'OK'=>'js:function(){
                    window.location.href = $("#logout").val();
                    $(this).dialog("close");
                }',
            ),
        ),
    ));
    echo "<center>";
    echo 'Session Expired';
    echo "<br/>";
    echo "</center>";

    $this->endWidget('zii.widgets.jui.CJuiDialog');
    /** End Widget ***/
}
?>
</body>
</html>