<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 07 26, 10
 * Company: Philweb
 * *************************** */

class FormsProcessor
{

    var $IsPostBack = false;
    var $HasGetVars = false;
    var $arrControls;
    var $ShowCaptions = false;
    var $IsFormProcessed = false;

    function FormsProcessor()
    {
        $this->CheckPostBack();
        $this->CheckGetVars();
    }

    private function CheckPostBack()
    {
        if (isset($_POST) && count($_POST) > 0)
        {
            $this->IsPostBack = true;
        }
    }

    private function CheckGetVars()
    {
        if (isset($_GET) && count($_GET) > 0)
        {
            $this->HasGetVars = true;
        }
    }

    function AddControl($control)
    {

        if (isset($control->ControlType) && $control->ControlType == "CheckBoxList")
        {
            if (is_array($control->CheckBoxes) && count($control->CheckBoxes) > 0)
            {
                for ($i = 0; $i < count($control->CheckBoxes); $i++)
                {
                    $chkcontrol = $control->CheckBoxes[$i];
                    $this->arrControls[] = $chkcontrol;
                }
            }
            $this->arrControls[] = $control;
        }
        else
        {
            $this->arrControls[] = $control;
        }
    }

    function ProcessForms()
    {
        if (is_array($this->arrControls))
        {
            foreach ($this->arrControls as $key => $val)
            {
                if ($val != null)
                {
                    $val->ProcessForms();
                    if ($this->ShowCaptions)
                    {
                        $val->ShowCaption = true;
                    }
                }
            }
            $this->IsFormProcessed = true;
        }
        //App::Pr(strpos($_SERVER["HTTP_REFERER"], $_SERVER["REQUEST_URI"]));
        if (isset($_SERVER["HTTP_REFERER"]) && strpos($_SERVER["HTTP_REFERER"], $_SERVER["REQUEST_URI"])=== false || !$this->IsPostBack)
        {
            $this->ClearFormMode();
        }
        
    }

    function UnProcessForms()
    {
        if (is_array($this->arrControls))
        {
            foreach ($this->arrControls as $key => $val)
            {
                $val->UnProcessForms();
            }
        }
    }

    function GetQueryStringParams($arrFields)
    {
        $arrQueryString = null;
        if ($this->HasGetVars)
        {
            foreach ($arrFields as $key => $val)
            {
                if (key_exists($val, $_GET))
                {
                    $arrQueryString[$val] = $_GET[$val];
                }
            }
        }

        return $arrQueryString;
    }

    function GetPostVar($formname)
    {
        if ($this->IsPostBack)
        {
            if (key_exists($formname, $_POST))
            {
                return $_POST[$formname];
            }
            else
            {
                return false;
            }
        }
    }
    
    function GetFilesVar($formname)
    {
        if ($this->IsPostBack)
        {
            if (key_exists($formname, $_FILES))
            {
                return $_FILES[$formname];
            }
            else
            {
                return false;
            }
        }
    }
    
    function SetFormMode($formmode)
    {
        $_SESSION["FormMode"] = $formmode;
    }
    
    function GetFormMode()
    {
        if (isset($_SESSION["FormMode"]) )
        {
            return $_SESSION["FormMode"];
        }
        else
        {
            return false;
        }
    }
    
    function ClearFormMode()
    {
        if (isset($_SESSION["FormMode"]) )
        {
            session_unregister("FormMode");
            unset($_SESSION["FormMode"]);
        }
    }

}

?>
