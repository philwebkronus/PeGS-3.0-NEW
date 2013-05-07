<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 07 23, 10
 * Company: Philweb
 *****************************/
class ListItem
{
    var $Selected = false;
    var $Text;
    var $Value;
    var $Enabled = true;

    function ListItem($text = '', $value = '', $selected = '', $enabled = '')
    {
        if ($text != '')
        {
            $this->Text = $text;
        }

        if ($value != '')
        {
            $this->Value = $value;
        }

        if ($selected != '')
        {
            $this->Selected = $selected;
        }

        if ($enabled != '')
        {
            $this->Enabled = $enabled;
        }
    }
}
?>
