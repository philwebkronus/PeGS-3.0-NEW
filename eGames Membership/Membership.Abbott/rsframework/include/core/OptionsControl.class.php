<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 07 13, 10
 * Company: Philweb
 *****************************/
class OptionsControl
{
    public $name;
    public $value;
    public $NameValue;
    public $Selected;
    public $SelectedName;
    var $OptionString;

    function OptionsControl()
    {
        $this->NameValue = "";
    }

    function AddOption($name, $value)
    {
        $this->NameValue[$name] = $value;
    }

    function SetOptions($arrOptions)
    {
        $this->NameValue = $arrOptions;
    }

    function GetNameValueFromArray($arrMixed, $namefield, $valuefield)
    {
        for ($i = 0; $i < count($arrMixed); $i++)
        {
            $item = $arrMixed[$i];
            $this->AddOption($item[$namefield], $item[$valuefield]);
        }
    }

    function GetNameValueFromObject($arrMixed, $propertyname, $propertyvalue)
    {
        for ($i = 0; $i < count($arrMixed); $i++)
        {
            $item = $arrMixed[$i];
            $this->AddOption($item->{$propertyname}, $item->{$propertyvalue});
        }
    }

    function Generate()
    {
        $retval = "";
        $namevalue = $this->NameValue;
        foreach ($namevalue as $name => $value)
        {
            $this->name = $name;
            $this->value = $value;
            $selected = "";
            if (isset($this->SelectedName) && $this->SelectedName == $name)
            {
                $this->Selected = "selected";
                $selected = "selected";
            }
            else
            {
                $this->Selected = "";

            }
            $optionstring = "<option value=\"$value\" $selected>$name</option>";
            $retval .= $optionstring;
        }
        return $retval;
    }
}
?>
