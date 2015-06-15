<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 03 9, 11
 * Company: Philweb
 *****************************/
class RadioGroup extends BaseControl
{
    
    
    /**
     *
     * @var String The selected value of the Radio group
     */
    var $SelectedValue;
    /**
     * @var array The array of Radio Controls under the group 
     */
    var $RadioItems;
    var $Radios;
    var $SelectedValues;
    var $ItemValues;

    function RadioGroup($Name = null, $ID = null, $Caption = null)
    {
        $this->Name = $Name;
        $this->ID = $ID;
        $this->Caption = $Caption;
        $this->Init("RadioGroup");
        App::LoadControl("Radio");
    }
    
    function Initialize()
    {
        if (is_array($this->RadioItems))
        {
            for ($i = 0; $i < count($this->RadioItems); $i++)
            {
                $chkDetails = $this->RadioItems[$i];
                $chkItems[$i] = new Radio($this->Name . "[]", $this->ID . $i, $chkDetails["Caption"]);
                if (isset($chkDetails["Checked"]))
                {
                    $chkItems[$i]->Checked = $chkDetails["Checked"];
                }
                $chkItems[$i]->Value = $chkDetails["Value"];
                $this->Radios[] = $chkItems[$i];
                $chkItems[$i]->Args = $this->Args;
                $chkItems[$i]->CssClass = $this->CssClass;
                $chkItems[$i]->Style = $this->Style;
                $chkItems[$i]->ShowCaption = $this->ShowCaption;
            }
        }
    }
    
    function AddRadio($value, $label, $checked = false)
    {
        $chkItem["Caption"] = $label;
        $chkItem["Value"] = $value;
        $chkItem["Checked"] = $checked;
        $this->RadioItems[] = $chkItem;
    }

    function SetSelectedValue($values)
    {
        $items = $this->Radios;
        $this->SelectedValues = $values;

        $itemvalues = "";
        if (is_array($items) && count($items) > 0)
        {
            for ($i = 0; $i < count($items); $i++)
            {
                $item = $items[$i];
                $item->Checked = false;
                if (is_array($values) && count($values) > 0)
                {
                    for ($j = 0; $j < count($values); $j++)
                    {
                        $value = $values[$j];

                        if ($item->Value == $value)
                        {
                            $item->Checked = true;
                            $itemvalues = $value;
                            $this->SubmittedValue = $value;
                        }
                    }
                }
                else
                {
                    if ($item->Value == $values)
                    {
                        $item->Checked = true;
                        $itemvalues = $values;
                        $this->SubmittedValue = $values;
                    }
                }
                //$itemvalues[$item->Value] = $item->Checked;
            }
        }
        $this->ItemValues = $itemvalues;
    }
    
    function SetDefaultSelectedValue()
    {
        $items = $this->Radios;
        //$this->SelectedValues = $values;

        $itemvalues = "";
        if (is_array($items) && count($items) > 0)
        {
            for ($i = 0; $i < count($items); $i++)
            {
                $item = $items[$i];
                $itemvalues[$item->Value] = $item->Checked;
            }
        }
        $this->ItemValues = $itemvalues;
    }

}
?>
