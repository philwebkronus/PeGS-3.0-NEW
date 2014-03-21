<?php

/**
 * Active form for ajax request. Read CActiveForm for more info
 * Dependencies: jquery.yiiactiveliveform.js
 * You can pass enableAutoScript to false to disable autogenerate script
 * Added Method
 * 1. getGeneratedValidations
 * 2. getFieldValidations
 * @package application.components.widgets
 * @author Bryan Salazar
 */
class CActiveLiveForm extends CActiveForm
{
    protected $_fieldsValidations=array();
    protected $_generatedValidations='not yet end of form';  
    protected $_firstID;
    
    public $enableAutoScript = true;
    
    /**
     * Override init to add validation
     */
    public function init() {
        if(!Yii::app()->request->isAjaxRequest)
            throw new Exception ("Please use CActiveForm instead of CActiveLiveForm. CAtiveLiveForm should only use in AJAX request");

        if(stripos($this->id, 'yw') !== false) {
            throw new Exception("Please set the id of CActiveLiveForm. Because there will be conflict if you use the auto generated id due to this is ajax request");
        }
        parent::init();
    }
    
    /**
     * You can get the script validation at the end of the widget
     * @return string Script generated for validation
     */
    public function getGeneratedValidations()
    {
        return $this->_generatedValidations;
    }

    /**
     * @return array Array of validation
     */
    public function getFieldValidations()
    {
        return $this->_fieldsValidations;
    }    
    
    /**
     * Override error to get all validation in each field
     * @param CActiveRecord $model
     * @param string $attribute
     * @param array $htmlOptions
     * @param boolean $enableAjaxValidation
     * @param boolean $enableClientValidation
     * @return string 
     */
    public function error($model,$attribute,$htmlOptions=array(),$enableAjaxValidation=true,$enableClientValidation=true)
    {

            if(!$this->enableAjaxValidation)
                    $enableAjaxValidation=false;
            if(!$this->enableClientValidation)
                    $enableClientValidation=false;

            if(!isset($htmlOptions['class']))
                    $htmlOptions['class']=$this->errorMessageCssClass;

            if(!$enableAjaxValidation && !$enableClientValidation)
                    return CHtml::error($model,$attribute,$htmlOptions);

            $id=CHtml::activeId($model,$attribute);
            $inputID=isset($htmlOptions['inputID']) ? $htmlOptions['inputID'] : $id;
            unset($htmlOptions['inputID']);
            if(!isset($htmlOptions['id']))
                    $htmlOptions['id']=$inputID.'_em_';
            
            if($this->_firstID == null)
                $this->_firstID = $inputID;
            
            $option=array(
                    'id'=>$id,
                    'inputID'=>$inputID,
                    'errorID'=>$htmlOptions['id'],
                    'model'=>get_class($model),
                    'name'=>$attribute,
                    'enableAjaxValidation'=>$enableAjaxValidation,
            );

            $optionNames=array(
                    'validationDelay',
                    'validateOnChange',
                    'validateOnType',
                    'hideErrorMessage',
                    'inputContainer',
                    'errorCssClass',
                    'successCssClass',
                    'validatingCssClass',
                    'beforeValidateAttribute',
                    'afterValidateAttribute',
            );
            foreach($optionNames as $name)
            {
                    if(isset($htmlOptions[$name]))
                    {
                            $option[$name]=$htmlOptions[$name];
                            unset($htmlOptions[$name]);
                    }
            }
            if($model instanceof CActiveRecord && !$model->isNewRecord)
                    $option['status']=1;

            if($enableClientValidation)
            {
                    $validators=isset($htmlOptions['clientValidation']) ? array($htmlOptions['clientValidation']) : array();
                    foreach($model->getValidators($attribute) as $validator)
                    {
                            if($enableClientValidation && $validator->enableClientValidation)
                            {
                                    if(($js=$validator->clientValidateAttribute($model,$attribute))!='')
                                            $validators[]=$js;
                            }
                    }
                    if($validators!==array())
                            $option['clientValidation']="js:function(value, messages, attribute) {\n".implode("\n",$validators)."\n}";
            }
            // pass validators
            $this->_fieldsValidations[] = $validators;
            $html=CHtml::error($model,$attribute,$htmlOptions);
            if($html==='')
            {
                    if(isset($htmlOptions['style']))
                            $htmlOptions['style']=rtrim($htmlOptions['style'],';').';display:none';
                    else
                            $htmlOptions['style']='display:none';
                    $html=CHtml::tag('div',$htmlOptions,'');
            }

            $this->attributes[$inputID]=$option;
            return $html;
    }
    
    /**
     * Override run to get and generate script even in ajax request
     * Runs the widget.
     * This registers the necessary javascript code and renders the form close tag.
     */
    public function run()
    {

            if(is_array($this->focus))
                    $this->focus="#".CHtml::activeId($this->focus[0],$this->focus[1]);

            echo CHtml::endForm();

            $options=$this->clientOptions;
            if(isset($this->clientOptions['validationUrl']) && is_array($this->clientOptions['validationUrl']))
                    $options['validationUrl']=CHtml::normalizeUrl($this->clientOptions['validationUrl']);

            $options['attributes']=array_values($this->attributes);

            if($this->summaryID!==null)
                    $options['summaryID']=$this->summaryID;

            if($this->focus!==null)
                    $options['focus']=$this->focus;

            $options=CJavaScript::encode($options);

            $id=$this->id;
            if($this->focus == null)
                $this->focus = '#'.$this->_firstID;
            $this->_generatedValidations = "$('#$id').yiiactiveform($options);";
            
            if($this->enableAutoScript) 
                echo '<script type="text/javascript">/*<![CDATA[*/ '. $this->_generatedValidations .' /*]]>*/</script>';
    }
}
