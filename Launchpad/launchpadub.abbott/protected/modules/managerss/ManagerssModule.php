<?php

/**
 * Bootstrap for module Managerss
 * @package application.modules.managerss
 * @author Bryan Salazar
 */
class ManagerssModule extends CWebModule
{
    public $defaultController = 'Auth';
    public $baseAssets;
    
    public function init()
    {
        // add method on end request
        Yii::app()->onEndRequest=array('ManagerssModule', 'endRequest');

        $this->setImport(array(
            'managerss.models.*',
            'managerss.components.*'
        ));
        
        Yii::app()->setComponents(array(
                'errorHandler'=>array(
                    'errorAction'=>'managerss/error/error',
                ),
                'session'=>array(
                    'sessionName'=>'RSSsession'
                ), 
            )
        );
        $file = Yii::getPathOfAlias('application.modules.managerss.config') . '/main.php';
        RssConfig::app()->setConfigFile($file);
        
        // set database connection
        $db = RssConfig::app()->params['db'];
        RssDB::app($db['connectionString'],$db['username'],$db['password']);
        RssDB::app()->enableParamLogging = true;
        if(YII_DEBUG) {
            RssDB::app()->enableProfiling = true;
        }
    }
    
    /**
     * Close database on end request
     */
    public function endRequest() {
        RssDB::app()->setActive(false);
    }
    
    public function beforeControllerAction($controller, $action) {
        
        $this->baseAssets = Yii::app()->getAssetManager()->publish(Yii::getPathOfAlias('application.modules.managerss.assets'));
        
        $cs = Yii::app()->clientScript;
        
        $cs->scriptMap=array(
            'jquery.js'=>Yii::app()->getModule('managerss')->baseAssets . '/js/jquery-1.7.1.min.js',
            'jquery-ui.min.js'=>Yii::app()->getModule('managerss')->baseAssets . '/js/jquery-ui-1.8.18.custom.min.js',
            'jquery.yiiactiveform.js'=>Yii::app()->getModule('managerss')->baseAssets . '/js/jquery.yiiactiveliveform.js',
        );
        
        $cs->registerCoreScript('jquery');
        $cs->registerScript("imagepath","
            var IMAGE_URL = '$this->baseAssets/images/';
        ",  CClientScript::POS_HEAD);
        $cs->registerCoreScript('yiiactiveform');
        Yii::app()->clientScript->registerCoreScript('jquery.ui');

        $cs->registerScriptFile($this->baseAssets . '/fancybox/jquery.mousewheel-3.0.4.pack.js');
        $cs->registerScriptFile($this->baseAssets . '/fancybox/jquery.fancybox-1.3.4.pack.js');
        $cs->registerScriptFile($this->baseAssets . '/js/lightbox.js',  CClientScript::POS_END);
        
        if(parent::beforeControllerAction($controller, $action))
        {
            return true;
        }
        else
            return false;
    }
    
    
}
