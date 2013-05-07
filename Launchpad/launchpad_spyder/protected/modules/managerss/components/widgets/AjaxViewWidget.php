<?php

/**
 * Description of AjaxViewWidget
 * @package application.components.widgets
 * @author Bryan Salazar
 */
class AjaxViewWidget extends CWidget
{
    public $attributes;
    public $title;
    public $model;
    
    public function run() 
    {
        if($this->title == null)
            throw new CException("Please set title");
        $this->render('ajaxviewwidget',array('model'=>$this->model,'attributes'=>$this->attributes,'title'=>$this->title));
    }
}
