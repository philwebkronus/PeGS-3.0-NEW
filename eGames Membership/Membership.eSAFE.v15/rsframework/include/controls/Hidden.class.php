<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 03 10, 11
 * Company: Philweb
 *****************************/
class Hidden extends BaseControl
{

    function Hidden($Name = null, $ID = null, $Caption = null)
    {
        $this->Name = $Name;
        $this->ID = $ID;
        $this->Caption = $Caption;
        $this->Init("Hidden");
    }

    function Render()
    {
        $text = $this->Text;
        parent::Render();
        $text != null ? $text = "value='$this->Text' " : '';
        $strtextbox = "<input type='hidden' $this->Attributes $text>";
        return $strtextbox;
    }
}
?>
