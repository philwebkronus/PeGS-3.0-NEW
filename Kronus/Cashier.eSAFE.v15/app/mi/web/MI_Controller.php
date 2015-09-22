<?php

class MI_Controller {
    protected $layout='';

    /**
     *
     * @param string $route
     * @param array $params
     * @return string 
     */
    public function createUrl($route,$params=array()) {
        return Mirage::app()->createUrl($route,$params);
    }
    
    /**
     * Description: redirect
     * @param string $url (url created by createUrl)
     */
    public function redirect($url) {
        header('LOCATION: ' . $url);
    }
    
    /**
     * Description: return true if request type is ajax else false
     * @return boolean 
     */
    public function isAjaxRequest() {
        return Mirage::app()->isAjaxRequest();
    }
    
    /**
     * Description: return true if request type is post else false
     * @return boolean 
     */
    public function isPostRequest() {
        return Mirage::app()->isPostRequest();
    }
    
    /**
     * Description: Display the view with layout
     * @param string $view
     * @param array|string|int $param
     * @param boolean $same 
     */
    public function render($view,$param=null,$same=true) {
        $con_view_dir = '';
        
        if($same) {
            if(Mirage::app()->getModuleName()) {
                $con_view_dir = strtolower(Mirage::app()->getModuleName() . '/views' . DIRECTORY_SEPARATOR . Mirage::app()->getControllerName()) . DIRECTORY_SEPARATOR;
                $view_file = Mirage::app()->getAppPath(). DIRECTORY_SEPARATOR . 'sub_modules' . DIRECTORY_SEPARATOR . $con_view_dir . $view . '_tpl.php';
            } else {
                $con_view_dir = strtolower(Mirage::app()->getControllerName()) . DIRECTORY_SEPARATOR;
                $view_file = Mirage::app()->getAppPath() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $con_view_dir . $view . '_tpl.php';
                
                
            }
        }
        $content = $this->renderInternal($view_file, $param,true);
        $layout = Mirage::app()->getAppPath() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $this->layout . '_tpl.php';

        include $layout;
    }
    
    /**
     * Description: Display the view without the layout
     * @param string $view
     * @param array|string|int $param
     * @param boolean $same 
     */
    public function renderPartial($view,$param=null,$same=true) {
        $con_view_dir = '';
        if($same) {
            if(Mirage::app()->getModuleName()) {
                $con_view_dir = strtolower(Mirage::app()->getModuleName() . '/views' . DIRECTORY_SEPARATOR . Mirage::app()->getControllerName()) . DIRECTORY_SEPARATOR;
                $view_file = Mirage::app()->getAppPath(). DIRECTORY_SEPARATOR . 'sub_modules' . DIRECTORY_SEPARATOR . $con_view_dir . $view . '_tpl.php';
            } else {
                $con_view_dir = strtolower(Mirage::app()->getControllerName()) . DIRECTORY_SEPARATOR;
                $view_file = Mirage::app()->getAppPath() . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . $con_view_dir . $view . '_tpl.php';
            }
        }
        $this->renderInternal($view_file, $param);
    }
    
    /**
     * Description: To buffer or include the view
     * @param string $_viewFile_
     * @param array|string|int $_data_
     * @param boolean $_return_ (if false it will include the view else it will buffer the view)
     * @return string buffered view 
     */
	protected function renderInternal($_viewFile_,$_data_=null,$_return_=false) {
		// we use special variable names here to avoid conflict when extracting data
		if(is_array($_data_))
			extract($_data_,EXTR_PREFIX_SAME,'data');
		else
			$data=$_data_;
		if($_return_) {
			ob_start();
			ob_implicit_flush(false);
			require($_viewFile_);
			return ob_get_clean();
		}
		else
			require($_viewFile_);
	}
}
