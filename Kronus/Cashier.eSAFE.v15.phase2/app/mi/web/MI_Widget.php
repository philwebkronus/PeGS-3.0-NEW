<?php

/**
 * Date Created 12 12, 11 2:34:46 PM <pre />
 * Description of MI_Widget
 * @author Bryan Salazar
 */
class MI_Widget {
    public function run($param=array()) {
        
    }
    
    public function render($_viewFile_,$_data_=null,$_return_=false) {
        $reflector = new ReflectionClass(get_called_class());
        $_viewFile_ = dirname($reflector->getFileName()) . '/view/' . $_viewFile_ . '_tpl.php';
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

