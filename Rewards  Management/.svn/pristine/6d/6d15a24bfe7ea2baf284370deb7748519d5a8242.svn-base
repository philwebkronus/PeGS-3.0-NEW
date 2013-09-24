<?php
/**
 * This class generates form based on given list of elements and template.
 * 
 * @author Marx - Lenin C. Topico
 * @version 1.0
 * @package application.components.widgets.AutoForm
 */
class AutoForm extends CWidget {
    
    /**
     *For documentation, check @see autoformwidget::$data
     * 
     * @var Array 
     */
    public $data;
    
    /**
     *For documentation, check @see autoformwidget::$template
     * 
     * @var Array 
     */
    public $template;
    
    /**
     *For documentation, check @see autoformwidget::$isolator
     * 
     * @var Array 
     */
    public $isolator;
    
    /**
     * This class property is not yet being used as of this version.
     * 
     * @todo
     * @var Object 
     * @ignore
     */
    public $model;
    
    /**
     *For documentation, check @see autoformwidget::$formAction
     * 
     * @var Array 
     */
    public $formAction;
    
    /**
     * This is the default Yii method called to run the widget.
     * 
     */
    public function run() {
    
        $this->render("autoformwidget", array(
                "data" => $this->data,
                "template" => $this->template,
                "isolator" => $this->isolator,
                "model" => $this->model,
                "formAction" => $this->formAction
                ));
    
    }
    
}

?>
