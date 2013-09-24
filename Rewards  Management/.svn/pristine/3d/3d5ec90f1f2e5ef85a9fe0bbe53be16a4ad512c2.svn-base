<?php
/**
 * This file contains the controller for the side menu widget
 * 
 * @package application.components.widgets
 * @author Sheryl S. Basbas
 * @date-created April 12, 2012
 * @last-modified April 12, 2012
 * 
 * @example
 * <code>
 * $this->widget('application.components.widgets.ResponseWidget',
 *                           array('type'=>1,'title'=>Transaction Successful,'message'=>'Message body here'));
 * </code> 
 * 
 */

class ResponseWidget extends CWidget
{
    
    public $message;
    public $title;
    public $type;
    public $closeToRefresh = 0;
    public $isCashier = 0;
    public $id = 0;
    public $link = "";
    
    public function run() 
    {          
        
        $this->render('responsewidget',array('type'=>$this->type,'message'=>$this->message, 'title'=>$this->title, 
            'refresh' => $this->closeToRefresh, "isCashier" => $this->isCashier, "id" => $this->id, "link" => $this->link));
    }
}
?>
