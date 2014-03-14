<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 07 24, 10
 * Company: Philweb
 *****************************/
class Label extends BaseControl
{
    function Label($Name = null, $ID = null)
    {
        $this->Name = $Name;
        $this->ID = $ID;
        $this->Init("Label");
    }

    function Render()
    {
        $text = $this->Text;
        parent::Render();
        //$text != null ? $text = "value='$this->Text'" : '';
        $strtextbox = "<label $this->Attributes>$text</label>";
        return $strtextbox;
    }

    function  __toString()
    {
        return $this->Render();
    }
}

?>
