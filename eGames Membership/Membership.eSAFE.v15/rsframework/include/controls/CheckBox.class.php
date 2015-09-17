<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 02 16, 11
 * Company: Philweb
 *****************************/
class CheckBox extends BaseControl
{
    var $Length;
    var $Checked = false;

    function CheckBox($Name = null, $ID = null, $Caption = null)
    {
        $this->Name = $Name;
        $this->ID = $ID;
        $this->Caption = $Caption;
        $this->Init("CheckBox");
    }

    function Render()
    {
        $checked = $this->Checked;
        parent::Render();
        $checked != null ? $checked = "checked='$this->Checked' " : '';
        $length = $this->Length != null ? "size='$this->Length' " : "";
        $value = $this->Value != null ? "value='$this->Value' " : "";
        $caption = $this->ShowCaption == true ? "<label for='$this->ID'>" . $this->Caption . "</label>" : "";
        $strtextbox = "<input type='checkbox' $length $value $this->Attributes $checked> $caption";
        return $strtextbox;
    }

    function SetSelectedValue($text)
    {
        //App::Pr($this->ID . " - " . $text . " - " . $this->Value);
        if ($text == $this->Value)
        {
            $this->Checked = true;
        }
        else
        {
            $this->Checked = false;
        }

    }


}

?>
