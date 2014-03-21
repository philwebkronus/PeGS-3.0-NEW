<?php
Yii::import('application.modules.launchpad.components.*');
Yii::import('application.modules.launchpad.models.*');
/**
 * Bootstrap for launchpad modules
 * @package application.modules.launchpad
 */
class LaunchpadModule extends CWebModule
{
    /**
     * @var string base directory publish assets of module launchpad
     */	
    public $baseAssets;
    public $defaultController = 'Lobby';
        
    /**
     * 
     */	
    public function init()
    {
        // add method on end request
        Yii::app()->onEndRequest=array('LaunchpadModule', 'endRequest');
        
//        Yii::import('application.modules.launchpad.components.debug-toolbar.ShowDebugEvent');

        // import the module-level models and components
        $this->setImport(array(
            'launchpad.models.*',
            'launchpad.components.*',
            'launchpad.components.casinoapi.*',
        ));


        // set error page of launchpad module
        Yii::app()->setComponents(array(
                'errorHandler'=>array(
                    'errorAction'=>'launchpad/lobby/error',
                ),
                'session'=>array(
                    'sessionName'=>'LPsession'
                ), 
		'urlManager'=>array(
			'urlFormat'=>'path',
			'rules'=>array(
				'<controller:\w+>/<id:\d+>'=>'<controller>/view',
				'<controller:\w+>/<action:\w+>/<id:\d+>'=>'<controller>/<action>',
				'<controller:\w+>/<action:\w+>'=>'<controller>/<action>',
			),
		),            
            )
        );
        
        // set config file for launchpad module for modular and standalone purpose
        $file = Yii::getPathOfAlias('application.modules.launchpad.config') . '/main.php';
        LPConfig::app()->setConfigFile($file);
        
        
        // set database connetion
        LPDB::app(LPConfig::app()->params['db']['connectionString'],
                LPConfig::app()->params['db']['username'], 
                LPConfig::app()->params['db']['password']);
            LPDB::app()->enableParamLogging = true;
        if(YII_DEBUG) {    
            LPDB::app()->enableProfiling = true;
        }
    }

    /**
     * This method is called before any module controller action is performed
     * @param string $controller
     * @param string $action
     */
    public function beforeControllerAction($controller, $action)
    {
	    $controller->layout = 'main';
            $controller->pageTitle = 'Lobby';

            // publish assets
            $baseAssets=Yii::app()->getModule('launchpad')->baseAssets = Yii::app()->getAssetManager()
                            ->publish(Yii::getPathOfAlias('application.modules.launchpad.assets'));
		
            $cs = Yii::app()->clientScript;
            // change core script jquery.js to jquery-1.7.min.js
            $cs->scriptMap=array(
                    'jquery.js'=>Yii::app()->getModule('launchpad')->baseAssets . '/js/jquery-1.7.1.min.js',
            );
            $cs->registerCoreScript('jquery');
            $cs->registerScript('lp_assets',"var lpAssetsPath = '$baseAssets'",CClientScript::POS_HEAD);
            $cs->registerScriptFile($baseAssets . '/fancybox/jquery.mousewheel-3.0.4.pack.js');
            $cs->registerScriptFile($baseAssets . '/fancybox/jquery.fancybox-1.3.4.pack.js');
            $cs->registerScriptFile($baseAssets . '/js/lighbox.js?path='.Yii::app()->getModule('launchpad')->baseAssets);
            $cs->registerScriptFile($baseAssets . '/js/jquery.vticker.js');
            $cs->registerScriptFile($baseAssets . '/js/disable_selection.js');
//            $cs->registerScriptFile(Yii::app()->getModule('launchpad')->baseAssets . '/js/launchpad.js');

            if(Lib::isIEBrowser()) { // add firebug lite if browser is ie and YII_DEBUG is true
                if(YII_DEBUG) {
                    $cs->registerScriptFile('https://getfirebug.com/firebug-lite.js#enableTrace,overrideConsole=false,startInNewWindow=true');
                }
            }
            
            if(!Lib::isIEBrowser()) {
                $cs->registerScript('ieonly',"
                    $.fancybox({content:'Please use internet explorer',scrolling:'no',modal:true});
                ");
            } else {
                 //commented on 02/26/13 - unused code for sso integration
//                //new registry path 
//                $regPath2 = LPConfig::app()->params['registry_path2'];
//                $terminalCode1 = $regPath2['terminalCode'];
//                
//                //if new registry path fails, read the old registry path
//                $regpath = LPConfig::app()->params['registry_path'];
//                $terminalCode2 = $regpath['terminalCode'];
                $regPath = LPConfig::app()->params['registry_path'];
                $terminalCode = $regPath['terminalCode'];
                
                $interval = LPConfig::app()->params['interval_is_login'];
                $checkLoginUrl = Yii::app()->createUrl('launchpad/lobby/checkLogin');
                $lobbyUrl = Yii::app()->createUrl('launchpad/lobby/index');
                $entranceUrl = Yii::app()->createUrl('launchpad/lobby/entrance');
                $getBalanceUrl = Yii::app()->createUrl('launchpad/lobby/getBalance');
                $saveTerminalCodeUrl = Yii::app()->createUrl('launchpad/lobby/saveTerminalCode');
                $userMode = Yii::app()->user->getState('casinoMode');
                if($userMode == 0){
                    $login = Yii::app()->user->getState('terminalCode');
                    $terminalPassword =  Yii::app()->user->getState('encryptPwd');
                }
                if($userMode == 1){
                    $login = Yii::app()->user->getState('UBUsername');
                    $terminalPassword =  Yii::app()->user->getState('UBHashedPwd');
                }
                $currentPage = $action->id;
                
                $redirect = 0;
                if(Yii::app()->user->isGuest) {
                    $redirect = 1;
                } elseif(!Yii::app()->user->isGuest && $action->id == 'screensaver') {
                    $redirect = 1;
                }
                
                $currentServiceID = Yii::app()->user->getState('currServiceID');

                $enableBlocker = 0;
                if(LPConfig::app()->params['enable_blocker']) {
                    $enableBlocker = 1;
                }
                
                $cs->registerScript('registry',"
                    var currPage = '$currentPage';
                    $(this).bind('contextmenu', function(e) {
                        e.preventDefault();
                    });
                    $(':not(input,select,textarea)').disableSelection();    
                        
                        
                    try {
//                        if($redirect) { // blocker
//                            displayRefresh();
//                        }
                        
                        var Shell = new ActiveXObject('WScript.Shell');
                        
                        var terminalCode;
                        var login = '$login';
                        var terminalPass = '$terminalPassword';
                        
                        try {
                            terminalCode = Shell.RegRead('$terminalCode');
                        } catch(e) {
                            displayMessageLightbox('Please setup the registry',function(){
                                
                            });
                            return false;
                        }
                        
                        
                        $('.btnClose').live('click',function(){
                            jQuery.fancybox.close();
                        });
                        
                        $('.casino').live('click',function(){
                            var pickServiceID = $(this).attr('serviceid');
                            var currServiceID = '$currentServiceID';
                            var bot = $(this).attr('botpath');
                            var casinopath = $(this).attr('casinopath');
                            
//                            var formTitle = $(this).attr('formtitle');
//                            var serviceID = $(this).attr('serviceid');
                            var data = {'serviceID':pickServiceID};
                            
                            if(currServiceID != pickServiceID) { // transfer
                                var url = $(this).attr('href');
                                var serviceType = $(this).attr('serviceType');
                                showLightbox(function(){
                                    $.ajax({
                                        url:url,
                                        dataType:'json',
                                        data:{'serviceID':pickServiceID,'serviceType':serviceType},
                                        success:function(data){
                                            var pickservicepass = data.password;
                                            try {
                                                if(data.html == 'not ok') {
                                                    displayMessageLightbox('<b style=\"width:125px\">Session Ended</b>',function(){
                                                        location.reload();
                                                        return false;
                                                    })
                                                }
                                                
                                                displayMessageLightbox('<b style=\"width:125px\">'+data.html+'</b>',function(){
                                                    try {
                                                        setTimeout(function(){
                                                            if($enableBlocker) {
                                                                var oaxPSMAC = new ActiveXObject('PEGS.Terminal.ActiveX.Controller');
                                                                oaxPSMAC.OpenGameClient(pickServiceID, login, pickservicepass); 
                                                            } else {
                                                                Shell.Run(casinopath);
                                                            }
                                                        },1000);
                                                        setTimeout(\"redirectPage('$lobbyUrl')\",3000);
                                                    } catch(e) {
                                                        displayMessageLightbox('<b style=\"width:125px\">Game client not found</b>',function(){
                                                            setTimeout(\"redirectPage('$lobbyUrl')\",3000);
                                                        });
                                                    }
                                                });
                                            } catch(e) {
                                                displayMessageLightbox('<b style=\"width:125px\">Parse error</b>',function(){
                                                    setTimeout(\"redirectPage('$lobbyUrl')\",3000);
                                                });
                                            }
                                        },
                                        error:function(e) {
                                            displayMessageLightbox('<b style=\"width:125px\">'+e.responseText+'</b>',function(){
                                                setTimeout(\"redirectPage('$lobbyUrl')\",3000);
                                            });
                                        }
                                    });
                                });
                            } else {
                                showLightbox(function(){
                                    try {
                                        if($enableBlocker) {
                                            var oaxPSMAC = new ActiveXObject('PEGS.Terminal.ActiveX.Controller');  
                                            oaxPSMAC.OpenGameClient(pickServiceID, login, terminalPass); 
                                        } else {
                                            Shell.Run(casinopath);
                                        }
                                        jQuery.fancybox.close();
                                    } catch(e) {
                                        displayMessageLightbox('<b style=\"width:125px\">Game client not found</b>',function(){
                                            setTimeout(\"redirectPage('$lobbyUrl')\",3000);
                                        });
                                    }
                                });
                            }
                            return false;
                        });
                    }catch(e) {
                        alert('There is a problem in activex');
                    }
                        
                    var param = {'terminalCode':terminalCode};    
                    $.ajax({
                        url:'$saveTerminalCodeUrl',
                        data: param,
                        success:function() {
                            (function()
                                {
                                  if( window.localStorage && currPage == 'screensaver')
                                  {
                                    if( !localStorage.getItem( 'firstLoad' ) )
                                    {
                                      localStorage[ 'firstLoad' ] = true;
                                      window.location.reload();
                                    }  
                                    else
                                      localStorage.removeItem( 'firstLoad' );
                                  }
                                })();
                        }
                    });
                        
                ");
            }
        
        if(parent::beforeControllerAction($controller, $action))
        {
            return true;
        }
        else
            return false;
    }
    
    /**
     * Close database on end request
     */
    public static function endRequest() {
        LPDB::app()->setActive(false);
    }
}
