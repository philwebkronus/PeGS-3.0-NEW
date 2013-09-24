<?php
/**
 * This widget simply creates a form on the fly giving full control to users
 * in creating their own template. The goal of this widget is to eliminate
 * extreneous html markuping in views.
 * 
 * @author Marx - Lenin C. Topico
 * @version 1.0
 * @package application.components.widgets.views.autoformwidget
 */
class autoformwidget {
    
    /**
     *This class property is used to hold the form elements.
     * 
     *-- The following available control types:
     * 1. text
     * 2. submit
     * 3. reset
     * 4. password
     * 5. image
     * 6. hidden
     * 7. file
     * 8. button (refers to <input type='button' />)
     * 9. control:button (refers to <button></button>)
     * 10. radio
     * 11. checkbox
     * 12. select
     * 13. textarea
     * 
     * --The passed array format for this as follows:
     * 
     * array(
     *      //For controls except radio buttons, checkboxes, select
     *      array(
     *          "label" => "The field name",
     *          "type" => "Field Type as listed above",
     *          "value" => "your value",
     *          "options" => //This refers to HTML Options
     *                      array(
     *                          array("the_html_option","the_desired_value"),
     *                      ),
     *          "actions" => //This refers to events you wanted to bind to the control
     *                       array(
     *                          array("valid_jquery_event","function the_function(){
     *                              //do something here
     *                          }");
     *                       )
     *      ),
     *      //For controls radio buttons, checkboxes and select
     *      array(
     *          "label" => "The field name",
     *          "type" => "checkbox | radio | select",
     *          "value" => array(
     *                          "the_control_name",
     *                          array( //This is the set of values
     *                              array("The options text","The option value"),
     *                              //If select type and optgroup is set to true
     *                              array("The OptGroup Name", array( //The values
     *                                  array("The options text","The option value"),
     *                              ))
     *                          ),
     *                          "optgroup" => true | false //applicable only for select type
     *                     ),
     *          "options" => //This refers to HTML Options
     *                      array(
     *                          array("the_html_option","the_desired_value"),
     *                      ),
     *          "actions" => //This refers to events you wanted to bind to the control
     *                       array(
     *                          array("valid_jquery_event","function the_function(){
     *                              //do something here
     *                          }");
     *                       )
     *      )
     * )
     * 
     * NOTE: 
     * The successful implementation of this widget is highly dependent on the correct
     * markuping of the valuse passed to this class property.
     * 
     * @var Array 
     */
    private $data;
    
    /**
     * This class property holds the form template.
     * 
     * The markup for templating as follows:
     * 
     * <code>
     * 
     *      <form class='your_class'>
     *          <div>l0</div>
     *          <div mx='0'>c0</div>
     *      </form> 
     * 
     * </code>
     * 
     * NOTE:
     * 1. Templating is not limited to using dividers the most important part is
     *      giving the correct position of your labels and content.
     * 2. Labels for a given control is displayed by placing letter l followed by the
     *      numerical key of the element based from $data. So if you have 3 elements
     *      declared in $data, to display the second element label you need to put
     *      in your template anywhere but inside your isolator, in this case, your_class,
     *      l1.
     * 3. For controls, you need to place the attribute mx to the direct parent to which
     *      you wanted to place the control. The value of mx is the numerical key of the
     *      element. Then just inside that markup place letter c followed by the numerical key.
     * 
     * 
     */
    private $template;
    
    /**
     *This is a valid jquery selector. It is recommended that this selector
     * identifies the parent form. But, this is not limited to forms.
     * 
     * @var String
     */
    private $isolator;
    
    /**
     *This property holds Yii::app()->clientScript that is used for registering
     * JS scripts.
     * 
     * @var Object 
     */
    private $cs;
    
    /**
     *This is a string that holds the JS scripts for element attribute manipulation.
     * 
     * @var type 
     */
    private $attributeScript;
    
    /**
     *This is a string that holds the JS scripts for element attribute manipulation.
     * 
     * @var type 
     */
    private $actionScript;
    
    /**
     *The word padded before class or ID names of each form components.
     * 
     * @var String 
     */
    private static $formPrefix = "_autoform";
    
    /**
     *This is holds the function executed before the form submits. This is
     * optional and is only needed if you wanted to do input verification before
     * for submission.
     * 
     * @var String 
     */
    private $formAction;
    
    /**
     *This is the method that connects the other class methods.
     * 
     * The method flow as follows:
     * 
     * 1. It starts by assigning class property $data with the form elements
     *      from $data.
     * 2. It then assigns class properties $template, $isolator and $formAction to the values
     *      of $template, $isolator and $formAction respectively.
     * 3. Next, it initializes class property $cs. This property will be used to register
     *      the scripts generated by this widget.
     * 4. Lastly, it makes a call to $output which implements the cascaded methods to come up
     *      with a visible form.
     * 
     * @param Array $data Refers to the set of form elements declared in the calling view. For structure, check @see $data
     * @param String $template Refers to the form interface or look. For structure, check @see $template
     * @param String $isolator Refers to a valid jquery selector. For more, check @see $isolator
     * @param String $formAction Refers to the pre-submission method of a form. For more, check @see $formAction
     */
    public function __construct($data, $template, $isolator, $formAction) {
        
        $this->data = $data;
        
        $this->template = $template;
        
        $this->isolator = $isolator;
        
        $this->formAction = $formAction;
        
        $this->cs = Yii::app()->clientScript;
        
        $this->output();
        
    }
    
    /**
     *This method simply replaces the template markups with the equivalent
     * HTML markup.
     * 
     * The method flow as follows:
     * 
     * 1. It walks thru the class property $data.
     * 2. It then enters Display Control section which 
     *      will only display elements with explicit type declared Inside this code block,
     *      it will check if an explicit control value has been declared. The only difference
     *      between explicit declaration of value and ommission of it is the parameter passed
     *      to htmlInputType() method. Once a value has been explicitly declared, it will be
     *      automatically bound to the control and otherwise. For each cycle of this section,
     *      markup replacements were made. Undeclared type equates to a textbox.
     * 3. It then enters the Display Label which simply replaces l0,l1,... ln markups to the
     *      element label.
     * 4. For every completion of cycle, the original template created by user is replaced with
     *      HTML controls and is returned.
     * 
     * @return String 
     */
    private final function template () {
        
        foreach( $this->data as $k => $d ) {
            
            /**
             * @section Display Control
             */
            if(array_key_exists("type", $d)) {
                
                if(array_key_exists("value", $d)) {
                    
                    $this->template = str_replace("c{$k}", 
                                                    $this->htmlInputType($d['type'], 
                                                            $d["value"]), $this->template);
                
                    
                }
                else {
                    
                    $this->template = str_replace("c{$k}", 
                                                    $this->htmlInputType($d['type'], NULL), 
                                                            $this->template);
                 
                }
                
            }
            
            /**
             * @section Display Label
             */
            if(array_key_exists("label", $d)) {
                
                $this->template = str_replace("l{$k}", $d['label'], $this->template);
                
            }
            
        }
        
        return $this->template;
        
    }
    
    /**
     *This method creates the correct html control based on type, and if $value
     * has been set, will biond such value to the control.
     * 
     * The method flow as follows:
     * 1. It first initializes $_html to an empty string.
     * 2. It then enters the switch block to select the correct control.
     *      For checkbox, radio and select controls read specific case documentation.
     * 3. It then return $_html.
     * 
     * @param String $type This is any of the valid types defined in this widget. For list read @see $data
     * @param Mixed $value Refers to the element value
     * @return String 
     */
    private static final function htmlInputType ($type, $value) {
        
        $_html = '';
        
        switch(strtolower($type)) {
            
            case "text" : $_html = "<input type='text' value='{$value}'/>"; break;
        
            case "submit" : $_html = "<input type='submit' value='{$value}'/>"; break;
            
            case "reset" : $_html = "<input type='reset' value='{$value}'/>"; break;
            
            case "password" : $_html = "<input type='password' value='{$value}'/>"; break;
            
            case "image" : $_html = "<input type='image' value='{$value}'/>"; break;
            
            case "hidden" : $_html = "<input type='hidden' value='{$value}'/>"; break;
            
            case "file" : $_html = "<input type='file' />"; break;
            
            case "button" : $_html = "<input type='button' value='{$value}'/>"; break;
            
            case "control:button" : $_html = "<button>{$value}</button>"; break;
            
            /**
             * The radio case flow as follows:
             * 
             * 1. It first initializes $ctr to 0. This is used to create ID per radio instance.
             * 2. $_html is then concatenated with an HTML markup.
             * 3. Then we walk thru the list of radio instances -- bind values and labels.
             *      Each radios and label can be modified via CSS.
             * 4. It then concatenates $_html again with a closing HTML markup.
             * 5. It then unsets $ctr;
             * 
             */
            case "radio" : 
            
                $ctr = 0;

                $_html .= "<div class='".self::$formPrefix."_radioContainer'>";
                
                foreach($value[1] as $k => $v) {

                    $_html .= "<span class='".self::$formPrefix."_radio'>
                                <input type='radio' value='{$v[1]}' id='".self::$formPrefix."_radio_{$ctr}' name='{$value[0]}'/></span>
                              <span class='".self::$formPrefix."_radioLabel'>
                                <label for='".self::$formPrefix."_radio_{$ctr}'>{$v[0]}</label></span>";

                    $ctr++;

                }
                
                $_html .= "</div>";
                
                unset($ctr);
                
            break;
            
            /**
             * The checkbox case flow as follows:
             * 
             * 1. It first initializes $ctr to 0. This is used to create ID per checkbox instance.
             * 2. $_html is then concatenated with an HTML markup.
             * 3. Then we walk thru the list of checkbox instances -- bind values and labels.
             *      Each checkbox and label can be modified via CSS.
             * 4. It then concatenates $_html again with a closing HTML markup.
             * 5. It then unsets $ctr;
             * 
             */
            case "checkbox" : 
            
                $ctr = 0;

                $_html .= "<div class='".self::$formPrefix."_checkboxContainer'>";
                
                foreach($value[1] as $k => $v) {

                    $_html .= "<span class='".self::$formPrefix."_checkbox'>
                                <input type='checkbox' value='{$v[1]}' id='".self::$formPrefix."_checkbox_{$ctr}' name='{$value[0]}'/></span>
                              <span class='".self::$formPrefix."_checkboxLabel'>
                                <label for='".self::$formPrefix."_checkbox_{$ctr}'>{$v[0]}</label></span>";

                    $ctr++;

                }
                
                $_html .= "</div>";
                
                unset($ctr);
                
            break;
        
            /**
             * The select case flow as follows:
             * 
             * 1. Special to select control is the grouping parameter. And so, the block
             *      first checks if optgroup is true or false. If it is true, then it goes to the
             *      else block.
             * 2. The ELSE Block as follows:
             *      2.1. $_html is concatenated with SELECT markup.
             *      2.2. It then walks thru the optgroups values. It first bind the optgroup markup
             *              followed by the list of options.
             *      2.3 This block is then ended by concatenating $_html with the SELECT closing 
             *              markup.
             * 3. The IF block is similar to the ELSE block, the only difference is that in the IF block
             *      there is no optgroup markup.
             * 
             */
            case "select" : 

                if(!$value["optgroup"]) {
                    
                    $_html .= "<select class='".self::$formPrefix."_select' name='{$value[0]}'>";

                    foreach($value[1] as $k => $v) {

                        $_html .= "<option class='".self::$formPrefix."_options' value='{$v[1]}'>{$v[0]}</option>";

                    }

                    $_html .= "</select>";
                
                }
                else {
                    
                    $_html .= "<select class='".self::$formPrefix."_select' name='{$value[0]}'>";

                    foreach($value[1] as $k => $v) {

                        $_html .= "<optgroup label='{$v[0]}' class='".self::$formPrefix."_optgroup'>";
                        
                        foreach($v[1] as $kx => $vx) {
                        
                            $_html .= "<option class='".self::$formPrefix."_options' value='{$vx[1]}'>{$vx[0]}</option>";
                        
                        }
                        
                        $_html .= "</optgroup>";

                    }

                    $_html .= "</select>";
                    
                }
                
            break;            
            
            case "textarea" : $_html = "<textarea>{$value}</textarea>"; break;
            
            
            default: $_html = "<input type='text' value='{$value}' />";
            
        }
        
        return $_html;
        
    }
    
    /**
     *This method creates the scripts for actions and control appearance bound
     * directly to each controls.
     * 
     * The method flow as follows:
     * 
     * 1. It walks thru the given list of elements.
     * 2. It looks for array keys [options | actions]. If OPTIONS exists then it converts
     *      the array options to JSON and binds to the control. This json is then parsed later
     *      in the client side via the accentuate() method. The accentuate method waks thru the
     *      parsed JSON string and assigns each option via jquery attr().
     * 3. If ACTIONS exists then it calls for the applyActions() method that requires the list of actions
     *           and the current control key. This method returns a string that is later executed as
     *           JS script.
     * 
     * @return String This is the JS script for both looks and actions of each controls.
     */
    private final function applyAttributes() {
        
        foreach( $this->data as $k => $d ) {
        
            if(array_key_exists("options", $d)) {
            
                $options = json_encode($d["options"]);
                
                $this->attributeScript .= "$('{$this->isolator} *[mx = {$k}]').accentuate({$options});";
            
            }
            
            if(array_key_exists("actions", $d)) {
                
                $actions = $d["actions"];
                
                $this->actionScript .= $this->applyActions($actions, $k);
                
            }
            
            
        }
        
        return $this->attributeScript.$this->actionScript;
        
    }
    
    /**
     *This method simply create JS scripts bound to each form elements.
     * 
     * The methods flow as follows:
     * 
     * 1. Declares $localAction and set it as empty string to which the actions
     *      for a given control is appended.
     * 2. After which, it walks through the given actions of a given control and creates
     *      a string of JS script. 
     * 3. It then returns the action string.
     * 
     * @param Array $actions This refer to the list of events bound to a given control. For more, read @see $data
     * @param Int $k This the numeircal key of a given element from @see $data
     * @return String This is a long text of all bound actions per controls.
     */
    private final function applyActions ($actions, $k) {

        $localAction = '';
        
        foreach($actions as $i) {

            $localAction .= "$('{$this->isolator} *[mx = {$k}]').bind('{$i[0]}', {$i[1]});";

        }
        
        return $localAction;
        
    }
    
    /**
     *This method simple returns the form pre-submission method and the accentuate
     * script for adding attributes to each form controls.
     * 
     * @return String 
     */
    private final function baseJS () {
        
        /**
         * @section This section binds to the given form the formAction.
         */
        if($this->formAction != "") {
            
            $this->formAction .= "$('{$this->isolator}').bind('submit', {$this->formAction});";
            
        }
        
        /**
         * @section Returns the accentuate method and the form pre-submission action.
         */
        return "
               $.fn.accentuate = function(x) {
                    
                    for(j = 0; j < x.length; j++) {

                        $(this).children().attr(x[j][0], x[j][1]);

                    }

                }
                
                {$this->formAction}

        ";
        
    }
    
    /**
     * This method simple calls all the methods need to display the user form. This
     * is a cascading method.
     * 
     */
    private final function output() {
        
        echo $this->template();
        
        $this->cs->registerScript("baseJS-Autoform", $this->baseJS(), 2);
        
        $this->cs->registerScript("baseJS-Autoform-attr-actions", $this->applyAttributes(), 2);
        
    }
    
}

/**
 * Instantiates the autoformwidget class based on passed values.
 */
$x = new autoformwidget($data, $template, $isolator, $formAction);

?>
