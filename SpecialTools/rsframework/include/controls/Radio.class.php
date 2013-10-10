<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 02 16, 11
 * Company: Philweb
 *****************************/
class Radio extends BaseControl
{
    var $Length;
    var $Checked = false;
    var $RadioGroup;

    function Radio($Name = null, $ID = null, $Caption = null)
    {
        $this->Name = $Name;
        $this->ID = $ID;
        $this->Caption = $Caption;
        $this->Init("Radio");
    }

    function Render()
    {
        $checked = $this->Checked;
        parent::Render();
        $checked != null ? $checked = "checked='$this->Checked' " : '';
        $length = $this->Length != null ? "size='$this->Length' " : "";
        $value = $this->Value != null ? "value='$this->Value' " : "";
        $caption = $this->ShowCaption == true ? "<label for='$this->ID'>" . $this->Caption . "</label>" : "";
        $strtextbox = "<input type='radio' $length $value $this->Attributes $checked> $caption";
        return $strtextbox;
    }

    function SetSelectedValue($text)
    {
        if ($text == $this->Value)
        {
            $this->Checked = true;
//            if (isset($this->RadioGroup))
//            {
//                $radiogroup = $this->RadioGroup;
//                $radiogroup->SelectedValue = $this->Value;
//            }
        }
        else
        {
            $this->Checked = false;
        }

    }


}

?>
