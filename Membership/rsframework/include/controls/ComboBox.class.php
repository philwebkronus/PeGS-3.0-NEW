<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 07 22, 10
 * Company: Philweb
 * *************************** */

class ComboBox extends BaseDataControl
{

    /**
     *
     * @var String the string to be displayed on the control
     */
    var $DataSourceText = 0;
    /**
     *
     * @var type the value posted 
     */
    var $DataSourceValue = 0;
    /**
     *
     * @var ListItem the selected ListItem
     */
    var $SelectedItem = null;
    /**
     *
     * @var String The current selected value of the control
     */
    var $SelectedValue = null;
    /**
     *
     * @var String The current selected text of the control
     */
    var $SelectedText = null;
    /**
     *
     * @var Integer The index number of the currently selected option
     */
    var $SelectedIndex = null;
    /**
     *
     * @var Array Array of ListItems used as data for the control
     */
    var $Items;
    /**
     *
     * @var Boolean Sets if the control can select single or multiple values. Set to true if multiple values are allowed. Set to false if only single value is allowed.
     */
    var $Multiple = false;
    /**
     *
     * @var Integer The number of items to be displayed when selection of multiple values is enabled.
     */
    var $Size;
    
    var $AutoSubmit = false;

    function ComboBox($Name = null, $ID = null, $Caption = null)
    {
        $this->Name = $Name;
        $this->ID = $ID;
        $this->Caption = $Caption;
        $this->Init("ComboBox");
    }
    
    function AddDefaultMenu($menutext = '', $menuvalue = '')
    {
        if ($menutext == '')
        {
            $menutext = "Choose one";
        }
        $litem = new ListItem($menutext, $menuvalue, true);
        $this->AddItem($litem);
    }
    
    function RemoveDefaultMenu()
    {
        array_shift($this->Items);
    }

    /**
     * Add a ListItem on the control
     * @param ListItem $listitem 
     */
    function AddItem($listitem)
    {
        $this->Items[] = $listitem;
    }

    /**
     * Clears all items
     */
    function ClearItems()
    {
        $this->Items = '';
    }

    /**
     * returns a string equivalent of the control
     * @return string 
     */
    function Render()
    {
        $size = $this->Size == null ? " size='$this->Size' " : "";
        $multiple = $this->Multiple ? " multiple='multiple'" : "";
        $autosubmit = $this->AutoSubmit == true ? " onchange='\$(this).closest(\"form\").submit();' " : "";
        parent::Render();
        if ($this->ShowCaption)
        {
            $strcombo = "<label for='$this->Name'>$this->Caption</label><select $this->Attributes $size $multiple $autosubmit>";
        }
        else
        {
            $strcombo = "<select $this->Attributes $size $multiple $autosubmit>";
        }
        $strcombo .= $this->RenderItems();
        $strcombo .= "</select>";
        return $strcombo;
    }

    /**
     * Adds all items from the DataSource to the control
     */
    function DataBind()
    {

        if (isset($this->DataSourceText) && isset($this->DataSourceValue))
        {
            $this->DataColumns = array($this->DataSourceText, $this->DataSourceValue);
            parent::DataBind();

            if (isset($this->Data) && $this->Data != null)
            {
                $data = $this->Data;
                $datasourcetext = $this->DataSourceText;
                $datasourcevalue = $this->DataSourceValue;

                foreach ($data as $key => $val)
                {
                    $dataitem = $val;

                    if (is_array($dataitem))
                    {
                        $text = $dataitem[$datasourcetext];
                        $value = $dataitem[$datasourcevalue];
                    }
                    else
                    {
                        $text = $dataitem;
                        $value = $i;
                    }
                    $li = new ListItem();
                    $li->Text = $text;
                    $li->Value = $value;
                    $this->Items[] = $li;
                }
            }
        }
    }

    /**
     *
     * Returns the string equivalent of all items of the control
     * @return String ListItem
     */
    private function RenderItems()
    {
        $strlistitem = "";

        for ($i = 0; $i < count($this->Items); $i++)
        {
            $listitem = new ListItem();
            $listitem = $this->Items[$i];
            $text = $listitem->Text;
            $value = $listitem->Value;
            $enabled = $listitem->Enabled;
            $selected = $listitem->Selected == true ? "selected" : "";
            $strlistitem .= "<option value='$value' $selected>$text</option>";
        }

        return $strlistitem;
    }

    function SelectedIndexChanged($index)
    {
        $listitem = $this->Items[$index];
        if ($listitem != null)
        {
            $this->SelectedItem = $listitem;
            $this->SelectedValue = $listitem->Value;
            $this->SelectedText = $listitem->Text;
            $this->SelectedIndex = $index;
            $listitem->Selected = true;
        }
    }

    /**
     *
     * @param String $text Sets the current selected value to $text.
     */
    function SetSelectedValue($text)
    {
        $listitem = null;
        $index = null;
        if (is_array($this->Items))
        {
            foreach ($this->Items as $key => $val)
            {
                $li = $val;
                $listitem = $this->Items[$key];
                if ($text == $li->Value)
                {
                    $listitem->Selected = true;
                    $this->SelectedItem = $listitem;
                    $this->SelectedValue = $listitem->Value;
                    $this->Value = $listitem->Value;
                    $this->SelectedText = $listitem->Text;
                    $this->SelectedIndex = $key;
                }
                else
                {
                    $listitem->Selected = false;
                }
            }
        }
    }

    /**
     * Sets the default selected value of the control
     * for use of parent class
     */
    protected function SetDefaultSelectedValue()
    {
        if (is_array($this->Items))
        {
            foreach ($this->Items as $key => $val)
            {
                $li = $val;
                $listitem = $this->Items[$key];
                if ($listitem->Selected == true)
                {
                    $listitem->Selected = true;
                    $this->SelectedItem = $listitem;
                    $this->SelectedValue = $listitem->Value;
                    $this->Value = $listitem->Value;
                    $this->SelectedText = $listitem->Text;
                    $this->SelectedIndex = $key;
                }
                else
                {
                    $listitem->Selected = false;
                }
            }
        }
    }

//    public function  __toString()
//    {
//        return $this->Render();
//    }
}

?>
