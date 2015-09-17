<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 07 23, 10
 * Company: Philweb
 * *************************** */

class TextBox extends BaseControl
{

    /**
     *
     * @var String sets the maximum length of characters allowed for the control 
     */
    var $Length;
    /**
     *
     * @var Boolean set to true if field is for password, false if plain text
     */
    var $Password = false;
    /**
     *
     * @var Boolean set to true to show TextArea
     */
    var $Multiline = false;
    /**
     *
     * @var Integer The number of columns of the TextBox if MultiLine is set to true 
     * @
     */
    var $Columns = 20;
    /**
     *
     * @var Integer The number of rows of the TextBox in MultiLine Mode
     */
    var $Rows = 4;
    /**
     *
     * @var Boolean Allows/Restricts autocomplete on a TextBox
     */
    var $AutoComplete = true;
    /**
     *
     * @var Integer The number of visible characters on the text box
     */
    var $Size = '';
    private $EnableMagicQuotes = false;

    function TextBox($Name = null, $ID = null, $Caption = null, $Length=null)
    {
        $this->Name = $Name;
        $this->ID = $ID;
        $this->Caption = $Caption;
        $this->Length = $Length;
        $this->Init("TextBox");
        $this->EnableMagicQuotes = App::getParam("EnableMagicQuotes");
    }

    function Render()
    {
        $text = $this->Text;
        parent::Render();
        if ($this->Multiline)
        {
            $text = $this->Text;
            $rows = 'rows="'.$this->Rows.'"';
            $columns = 'cols="'.$this->Columns.'"';
            $caption = $this->ShowCaption == true ? '<label for="'.$this->Name.'">' . $this->Caption . '</label>' : '';
//            $strtextbox = $caption<textarea $rows $columns $this->Attributes >$text</textarea>";
            $strtextbox = ''.$caption.'<textarea '.$rows.' '.$columns.' '.$this->Attributes.' >'.$text.'</textarea>';
        }
        else
        {
            $text != null ? $text = "value=\"" . $this->EscapeHTML($this->Text) . "\" " : '';
            $autocomplete = $this->AutoComplete != true ? 'autocomplete="off" ' : '';
            $length = $this->Length != null ? 'maxlength="'.$this->Length.'" ' : '';
            $size = $this->Size != null ? 'size="'.$this->Size.'" ' : '';
            $password = $this->Password == true ? "password" : "text";
            $caption = $this->ShowCaption == true ? '<label for="'.$this->Name.'">' . $this->Caption . '</label>' : '';
//            $strtextbox = "$caption<input type='$password' $length $size $this->Attributes $text $autocomplete>";
            $strtextbox = ''.$caption.'<input type="'.$password.'" '.$length.' '.$size.' '.$this->Attributes.' '.$text.' '.$autocomplete.'>';
        }
        return $strtextbox;
    }

    function EscapeHTML($str)
    {

        $str = str_replace("\"", "&quot;", $str);
        return $str;
    }

    function UnescapeHTML($str)
    {
        if (get_magic_quotes_gpc() && $this->EnableMagicQuotes == false)
        {
            $str = stripslashes($str);
        }
        $str = str_replace("&quot;", "\"", $str);
        return $str;
    }

}

?>
