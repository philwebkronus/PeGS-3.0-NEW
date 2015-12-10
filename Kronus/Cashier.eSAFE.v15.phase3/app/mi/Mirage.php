<?php
defined('MIRAGE_DEBUG') or define('MIRAGE_DEBUG',true);

require_once 'base/MI_SystemHandler.php';
require_once 'web/MI_Controller.php';
MI_SystemHandler::init();

class Mirage {
    private static $_app = null;
    private static $_app_path;
    private static $_config_path;
    private $_controller_name;
    private $_action_name;
    private $_config;
    private $_routes;
    private static $_config_file;
    private static $_route_file;
    public $param;
    protected $_module_name = null;
    
    protected $_core_class = array(
        'MI_Database'=>'base/MI_Database.php',
        'MI_Model'=>'web/MI_Model.php',
        'CVarDumper'=>'util/CVarDumper.php',
        'MI_UserIdentity'=>'web/MI_UserIdentiry.php',
        'MI_Validator'=>'web/MI_Validator.php',
        'MI_HTML'=>'web/MI_HTML.php',
        'MI_Logger'=>'util/MI_Logger.php',
        'MI_Widget'=>'web/MI_Widget.php',
    );
    
    private function __construct() {}
    
    public static function createWebApp($app_path,$config_path) {
        self::$_app_path = $app_path;
        if(is_array($config_path)) {
            if(!isset($config_path['config_file']))
                throw new Exception('config_file not set');
            if(!isset($config_path['route_file']))
                throw new Exception('route_file not set');
            self::$_config_file = $config_path['config_file'];
            self::$_route_file = $config_path['route_file'];
        } else {
            self::$_config_path = $config_path;
        }
        
        if(self::$_app == null)
            self::$_app = new Mirage();
        
        return self::$_app;
    }
    
    /**
     *
     * @return Mirage
     */
    public static function app() {
        return self::$_app;
    }
    
    public function getAppPath() {
        return self::$_app_path;
    }
    
    private function _coreAutoload($class) {
        if(isset($this->_core_class[$class]))  
            include_once $this->_core_class[$class];
    }
    
    public function getDomain() {
        return $this->_config['domain'];
    }

    public function getConfig() {
        return $this->_config;
    }

    public function run() {
        spl_autoload_register(array($this,'_coreAutoload'));
        
        if(self::$_config_file)
            $this->_config = include_once self::$_config_file;
        else
            $this->_config = include_once self::$_config_path . DIRECTORY_SEPARATOR . 'config.php';
        
        $this->param = $this->_config['params'];
        
        if(self::$_route_file)
            $routes = include_once self::$_route_file;
        else
            $routes = include_once self::$_config_path . DIRECTORY_SEPARATOR . 'routes.php';

        $this->_routes = $routes;
        if(!isset($_GET['r']) || $_GET['r'] == '') {
            if(!isset($routes['default_page']))
                $this->defaultPage();
            $obj = explode('/', $routes['default_page']);
        } else {
            if(!isset($routes[$_GET['r']])) {
                if(!isset($routes['error_404']))
                    $this->error404();
                else    
                    $obj = explode('/', $routes['error_404']);
            } else 
                $obj = explode('/', $routes[$_GET['r']]);
        }
        if(count($obj) == 3) {
            $this->_module_name = $obj[0];
            $this->_controller_name = $obj[1];
            $this->_action_name = $obj[2];
            $clsController = $obj[1] . 'Controller';
            $action = $obj[2] . 'Action';
            include_once self::$_app_path . DIRECTORY_SEPARATOR . 'sub_modules' . DIRECTORY_SEPARATOR . $this->_module_name .
                    DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $clsController . '.php';
            $objController = new $clsController;
            $objController->$action();            
        } else {
            $this->_controller_name = $obj[0];
            $this->_action_name = $obj[1];
            $clsController = $obj[0] . 'Controller';
            $action = $obj[1] . 'Action';
            include_once self::$_app_path . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $clsController . '.php';
            $objController = new $clsController;
            $objController->$action();
        }
        if(isset($this->_config['shutdown'])) {
            foreach($this->_config['shutdown'] as $shutdown) {
                if(isset($shutdown['class']) && isset($shutdown['method']) && isset($shutdown['file']) && isset($shutdown['runnable']) && $shutdown['runnable'] === true) {
                    include_once $shutdown['file'];
                    $class = $shutdown['class'];
                    $method = $shutdown['method'];
                    $obj = new $class();
                    $obj->$method();
                }
            }
        }
        $this->end();
    }
    
    public function getModuleName() {
        return $this->_module_name;
    }
    
    public function getControllerName() {
        return $this->_controller_name;
    }
    
    public function getActionName() {
        return $this->_action_name;
    }
    
    protected function defaultPage() {
        die('Please set default page in routes');
    }

    public static function loadWidget($class_name,$param=array()) {
//        if(file_exists(self::$_app_path . '/widgets/'.$class_name.'/'.$class_name . '.php')) {
//            die('ok');
//        }
//        die('not ok');
//        var_dump(self::$_app_path . '/widget/'.$class_name.'/'.$class_name . '.php'); exit;
        include_once self::$_app_path . '/widget/'.$class_name.'/'.$class_name . '.php';
        $c = new $class_name();
        $c->run($param);
    }
    
    public static function loadComponents($class_name) {
        self::loader('components', $class_name);
    }
    
    public static function loadModels($class_name) {
        self::loader('models', $class_name);
    }
    
    public static function loadLibraries($file_name) {
        self::loader('libraries', $file_name);
    }
    
    public static function loadModuleComponents($module_name,$file_name) {
        self::moduleLoader('components',$module_name,$file_name);
    }
    
    public static function loadModuleModels($module_name,$file_name) {
        self::moduleLoader('models',$module_name,$file_name);
    }
    
    public static function loadModuleLibraries($module_name,$file_name) {
        self::moduleLoader('libraries',$module_name,$file_name);
    }

    public static function moduleLoader($dir,$module_name,$class_name) {
        $module_path = self::$_app_path . DIRECTORY_SEPARATOR . 'sub_modules' . DIRECTORY_SEPARATOR . $module_name . DIRECTORY_SEPARATOR;
        if(is_array($class_name)) {
            foreach($class_name as $class) {
                $filename = $module_path . $dir . DIRECTORY_SEPARATOR . $class . '.php';
                if(!file_exists($filename)) {
                    $type = ucfirst($dir);
                    throw new Exception( $type . ' error: '.$filename . ' not found');
                }
                include_once $filename;
            }
        } else {
            $filename = $module_path . $dir . DIRECTORY_SEPARATOR . $class_name . '.php';
            if(!file_exists($filename)) {
                $type = ucfirst($dir);
                throw new Exception( $type . ' error: '.$filename . ' not found');
            }
            include_once $filename;
        }
    }
    
    public static function loader($dir,$class_name) {
        if(is_array($class_name)) {
            foreach($class_name as $class) {
                $filename = self::$_app_path . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $class . '.php';
                if(!file_exists($filename)) {
                    $type = ucfirst($dir);
                    throw new Exception( $type . ' error: '.$filename . ' not found');
                }
                include_once $filename;
            }
        } else {
            $filename = self::$_app_path . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $class_name . '.php';
            if(!file_exists($filename)) {
                $type = ucfirst($dir);
                throw new Exception( $type . ' error: '.$filename . ' not found');
            }
            include_once $filename;
        }
            
    }
    
    /********************************************************
    * Original Author: Bryan Salazar
    * Date Creation: June 9, 2011
    * Return: boolean
    * Description: Return true if request is ajax else false
    ********************************************************/
    public function isAjaxRequest(){
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH']==='XMLHttpRequest')
            return true;
        return false;
    }

    /*****************************************
    * Original Author: Bryan Salazar
    * Date Creation: June 9, 2011
    * Return: boolean
    * Description: Return true if post request
    ******************************************/
    public function isPostRequest() {
        if(isset($_SERVER['REQUEST_METHOD']) && !strcasecmp($_SERVER['REQUEST_METHOD'],'POST'))
            return true;
        return false;
    }
    
    /**
     * default implementation for page not found
     */
    public function error404() {
        MI_Database::close();
        $routes = $this->_routes;
        header('HTTP/1.0 404 Not Found');
        if(!isset($routes['error_404'])) {
            die('Page Not Found');
        }  
        $obj = explode('/', $routes['error_404']);
        
        $this->callErrorPage($obj);
    }
    
    protected function callErrorPage($obj) {
        $this->_controller_name = $obj[0];
        $this->_action_name = $obj[1];
        $clsController = $obj[0] . 'Controller';
        $action = $obj[1] . 'Action';
        include_once self::$_app_path . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $clsController . '.php';
        $objController = new $clsController;
        $objController->$action();
        exit;
    }
    
    /**
     * default implementation for unauthorized
     */
    public function error401() {
        MI_Database::close();
        $routes = $this->_routes;
        header('HTTP/1.1 401 Unauthorized');
        if(!isset($routes['error_401'])) {
            die('You are not authorized for this action');
        }          
        $obj = explode('/', $routes['error_401']);
        $this->callErrorPage($obj);
    }
    
    /**
     * default implementation for internal server error
     */
    public function error500() {
        MI_Database::close();
        $routes = $this->_routes;
        header('HTTP/1.1 500 Internal server error');
        if(!isset($routes['error_500'])) {
            die('Internal server error');
        }         
        $obj = explode('/', $routes['error_500']);
        $this->callErrorPage($obj);
    }

    /**
     * TODO: translate
     */
    public static function t($message,$param=null) {
        if(is_array($param)) {
        
        }
    }
    
    public function createUrl($route,$params=array()) {
        $url = '';
        $p = '';
        foreach($params as $key => $param) {
            $p.= '&' . $key . '=' . $param;
        }
        return Mirage::app()->getDomain() . '?r=' . $route . $p;
    }
    
    public function end() {
        MI_Database::close();
        exit;
    }
}
