<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 07 23, 10
 * Company: Philweb
 * *************************** */
/**
 * @author Roger Sanchez
 */
class BaseControl extends BaseObject
{

    /**
     *
     * @var String The name of the control
     * @example <input NAME="$Name"
     */
    var $Name;
    /**
     *
     * @var String The ID of the control
     * @example <input ID="$ID"
     */
    var $ID;
    /**
     *
     * @var String The style attribute of the control
     * @example <input STYLE="$Style"
     */
    var $Style;
    /**
     *
     * @var String additional attributes of the control
     * @example <input [ONCLICK=Javascript: doSomething()]
     */
    var $Args;
    /**
     *
     * @var String The string value of a control
     * @example TextBox, TextArea
     */
    var $Text;
    /**
     *
     * @var string The caption beside a control. All controls have labels by default.
     */
    var $Caption;
    /**
     *
     * @var String (READ ONLY) The value of the form when submitted
     */
    var $SubmittedValue;
    /**
     *
     * @var Boolean The state of the control. Set to true if enabled. Set to false if disabled.
     */
    var $Enabled = true;
    /**
     *
     * @var Boolean The visibility of the control. Set to true if visible. Set to false if invisible.
     */
    var $Visible = true;
    /**
     *
     * @var Boolean The writeable property of the control. Set to true if changing of values is not allowed. Set to false if changing of values is allowed.
     */
    var $ReadOnly = false;
    /**
     *
     * @var Boolean Will display/hide the label of the control. Set to true if label will be displayed. Set to false if label will not be displayed.
     */
    var $ShowCaption = false;
    var $Value;
    /**
     *
     * @var CSS-Class the CSS class to be used by the control
     */
    var $CssClass;
    /**
     *
     * @var String contains the attributes of the control
     */
    protected $Attributes;
    /**
     *
     * @var Integer The id of the control as passed to FormsProcessor()
     */
    private $ControlNumber;
    /**
     *
     * @var ControlType The type of control
     * @example TextBox, ComboBox etc
     */
    public $ControlType;
    
    public $TabIndex;

/*
    //protected function BaseControl()
  */  
    
    /**
     * Constructor
     * @param string $Name The control name eg <input name='[namehere]'>
     * @param string $ID The ID attribute of the HTML control <input id='[idhere]'>
     * @param string $Caption The caption of the control <label>[texthere]</label><input name='[namehere]'>
     */
    function __construct($Name = null, $ID = null, $Caption = null)
    {
        $this->Name = $Name;
        $this->ID = $ID;
        $this->Text = $Caption;
    }

    protected function Init($controltype)
    {
        $this->ControlType = $controltype;
        global $_ControlTypes;

        if ($this->Name == null)
        {
            if (!isset($_ControlTypes[$controltype]))
            {
                $_ControlTypes[$controltype][] = $controltype . "1";
            }
            else
            {
                $_ControlTypes[$controltype][] = $controltype . (count($_ControlTypes[$controltype]) + 1);
            }

            $name = $controltype . (count($_ControlTypes[$controltype]));
            $this->Name = $name;
            $this->ID = $name;
            $this->Caption = $name;
        }
    }

    private function AssignControlName()
    {
        global $_ControlNames;
        global $_Controls;

        $nameexists = false;
        if (is_array($_ControlNames))
        {
            foreach ($_ControlNames as $key => $val)
            {
                if ($key != $this->ControlNumber)
                {
                    if ($val == $this->Name)
                    {
                        $nameexists = true;
                    }
                }
            }
        }

        if ($nameexists == false || $this->ControlType == "Radio" || $this->ControlType == "CheckBox")
        {
            $this->ControlNumber = count($_ControlNames);
            $_ControlNames[$this->ControlNumber] = $this->Name;
            $_Controls[$this->ControlNumber] = $this;
        }
        else
        {
            die("Control Name $this->Name Already Exists");
        }
    }

    function ProcessForms()
    {
        $this->AssignControlName();
        if (isset($_POST[$this->Name]))
        {
            $this->SubmittedValue = $_POST[$this->Name];

            switch ($this->ControlType)
            {
                case "ComboBox": $this->SetSelectedValue($this->SubmittedValue);
                    break;
                case "TextBox": $this->Text = $this->UnescapeHTML($this->SubmittedValue);
                    break;
                case "DataList": $this->SelectedIndexChanged($this->SubmittedValue);
                    break;
                case "Radio": $this->SetSelectedValue($this->SubmittedValue);
                    break;
                case "RadioGroup": $this->SetSelectedValue($this->SubmittedValue);
                    break;
                case "CheckBox": $this->SetSelectedValue($this->SubmittedValue);
                    break;
                case "CheckBoxList": $this->SetSelectedValue($this->SubmittedValue);
                    break;
                case "Hidden": $this->Text = $this->SubmittedValue;
                    break;
                case "DatePicker": $this->SelectedDate = $this->UnescapeHTML($this->SubmittedValue);
                    break;
            }
        }
        else
        {
            switch ($this->ControlType)
            {
                case "ComboBox": $this->SetDefaultSelectedValue();
                    break;
                case "CheckBoxList": $this->SetDefaultSelectedValue();
                    break;
                case "RadioGroup": $this->SetDefaultSelectedValue();
                    break;
                case "TextBox": $this->Text = $this->Text;
                    break;
                case "DatePicker": $this->SelectedDate = $this->SelectedDate;
                    break;
                case "Hidden": $this->Text = $this->Text;
                    break;
            }
        }
    }

    function UnProcessForms()
    {
        $this->AssignControlName();
        if (isset($_POST[$this->Name]))
        {
            $this->SubmittedValue = $_POST[$this->Name];

            switch ($this->ControlType)
            {
                case "ComboBox": $this->SetDefaultSelectedValue();
                    break;
                case "TextBox": $this->Text = "";
                    break;
                case "Hidden": $this->Text = "";
                    break;
                case "DataList": $this->SelectedIndexChanged($this->SubmittedValue);
                    break;
                case "Radio": $this->Checked = false;
                    break;
            }
        }
    }

    protected function Render()
    {
        //$this->AssignControlName();
        $name = $this->Name;
        $id = $this->ID;
        $caption = $this->Caption;
        $style = $this->Style;
        $args = $this->Args;
        $enabled = $this->Enabled;
        $readonly = $this->ReadOnly;
        $tabindex = $this->TabIndex;
        $attributes = "";

        $name = $this->Name != null ? 'name="'.$this->Name.'" ' : '';
        $id = $this->ID != null ? 'id="'.$this->ID.'" ' : '';
        $style = $style != null ? 'style="'.$this->Style.'" ' : '';
        $args = $this->Args != null ? $args = $this->Args : '';
        $enabled = $this->Enabled == true ? '' : 'disabled="disabled" ';
        $visible = $this->Visible == true ? '' : 'style="display:none;" ';
        $readonly = $this->ReadOnly == false ? '' : 'readonly ';
        $tabindex = $this->TabIndex == null ? '' : 'tabindex="'.$this->TabIndex.'" ';
        $cssclass = $this->CssClass != null ? 'class="'.$this->CssClass.'" ' : '';
        $attributes = $name . $id . $cssclass . $style . $args . $tabindex . $enabled . $visible . $readonly;

        $this->Attributes = $attributes;

        return $attributes;
    }

    public function __toString()
    {
        return $this->Render();
    }

}

?>
