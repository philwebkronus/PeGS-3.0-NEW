<?php
/*
 * Author: Roger Sanchez
 * Date Created: 2011-09-07
 * Company: Philweb
 */
/**
 * Groups CheckBox controls into one.
 */
class CheckBoxList extends BaseControl
{
    var $Items;
    var $CheckBoxItems;
    var $CheckBoxes;
    var $SelectedValues;
    var $ItemValues;

    function CheckBoxList($Name = null, $ID = null, $Caption = null)
    {
        $this->Name = $Name;
        $this->ID = $ID;
        $this->Caption = $Caption;
        $this->Init("CheckBoxList");
        App::LoadControl("CheckBox");
    }

    function Initialize()
    {
        if (is_array($this->CheckBoxItems))
        {
            for ($i = 0; $i < count($this->CheckBoxItems); $i++)
            {
                $chkDetails = $this->CheckBoxItems[$i];
                $chkItems[$i] = new CheckBox($this->Name . "[]", $this->ID . $i, $chkDetails["Caption"]);
                if (isset($chkDetails["Checked"]))
                {
                    $chkItems[$i]->Checked = $chkDetails["Checked"];
                }
                $chkItems[$i]->Value = $chkDetails["Value"];
                $this->CheckBoxes[] = $chkItems[$i];
                $chkItems[$i]->Args = $this->Args;
                $chkItems[$i]->CssClass = $this->CssClass;
                $chkItems[$i]->Style = $this->Style;
                $chkItems[$i]->ShowCaption = $this->ShowCaption;
            }
        }
    }
    
    function AddCheckBox($value, $label, $checked = true)
    {
        $chkItem["Caption"] = $label;
        $chkItem["Value"] = $value;
        $chkItem["Checked"] = $checked;
        $this->CheckBoxItems[] = $chkItem;
    }

    function SetSelectedValue($values)
    {
        $items = $this->CheckBoxes;
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
                        }
                    }
                }
                else
                {
                    if ($item->Value == $values)
                    {
                        $item->Checked = true;
                    }
                }
                $itemvalues[$item->Value] = $item->Checked;
            }
        }
        $this->ItemValues = $itemvalues;
    }
    
    function SetDefaultSelectedValue()
    {
        $items = $this->CheckBoxes;
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
