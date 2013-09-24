<?php
//CHtml::activeCheckBox($model, $attribute)
//CHtml::activeCheckBoxList($model, $attribute, $data)
//CHtml::activeDropDownList($model, $attribute, $data)
//CHtml::activeFileField($model, $attribute)
//CHtml::activeHiddenField($model, $attribute)
//CHtml::activeLabel($model, $attribute)
//CHtml::activeLabelEx($model, $attribute)
//CHtml::activeListBox($model, $attribute, $data)
//CHtml::activePasswordField($model, $attribute)
//CHtml::activeRadioButton($model, $attribute)
//CHtml::activeRadioButtonList($model, $attribute, $data)  
//CHtml::activeTextArea($model, $attribute)
//CHtml::activeTextField($model, $attribute)

// checkBox
// checkBoxList
// dropDownList
// fileField
// hiddenField
// label
// labelEx
// listBox
// passwordField
// radioButton
// radioButtonList
// textArea
// textField

/**
 * 
 * @package application.components.widgets
 * @author Bryan Salazar
 * @example
 * <code>
 * $type = 'new' or 'update'
 * $model has parent class of CModel
 * $url for action of form
 * 
 * $this->widget('application.components.widgets.AjaxFormWidget',array('type'=>$type,'url'=>$url,'model'=>$model,'attributes'=>array(
 *     'IconPath'=>array('type'=>'raw','value'=>'<img id="itemIcon" src="'.Yii::app()->baseUrl .'/images/no_pic.jpg" />'),
 *     'Name',
 *     'ItemCount',
 *     'Description'=>array('type'=>'textArea'),
 * )));
 * </code>
 * CURRENT AVAILABLE type
 * 1. textField
 * 2. textArea
 * 3. raw
 * 4. hiddenField
 */
class AjaxFormWidget extends CWidget
{
    public $attributes=array();
    public $model;
    public $url;
    public $type='';
    
    public function run() 
    {   
        if($this->model == null)
            throw new CException("Please set model");
        if($this->url == null)
            throw new CException("Please set url");
        
        $this->render('ajaxformwidget',array('type'=>$this->type,'attributes'=>$this->attributes,'model'=>$this->model,'url'=>$this->url));
    }
}
