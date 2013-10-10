<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 11 3, 10
 * Company: Philweb
 *****************************/
class TabControl extends BaseObject
{
    public $TabItems;
    public $Style;
    public $Class;
    public $SelectedIndex;
    public $SelectedClass;
    public $UnselectedClass;
    public $BaseURL;
    public $QueryStringArray;
    public $QueryStringName;
    public $Title;

    function TabControl()
    {

    }

    function Render()
    {
        $style = $this->Style != '' ? " style='$this->Style' ": '';
        $class = $this->Class != '' ? " class='$this->Class' ": '';
        $tabstring = "";
        if ($this->Title != '')
        {
            $tabstring .= "<div style='float:left;'><strong>$this->Title</strong></div>";
        }
        $tabstring .= "<table " . $style . $class . " cellpadding='0' cellspacing='0'>";
        $maxindex = count($this->TabItems);
        
        foreach($this->TabItems as $key => $val)
        {
            $url = "";
            $querystring = null;
            if ($this->QueryStringArray != null && $this->BaseURL != null)
            {
                $url = $this->BaseURL . "?";
                foreach($this->QueryStringArray as $qskey => $qsval)
                {
                    if ($qskey == $this->QueryStringName)
                    {
                        $qsval = $val->Index;
                    }
                    $querystring[] = "$qskey=$qsval";
                }
                $querystring = implode("&", $querystring);
                $url .= $querystring;
                $val->URL = $url;
            }
            
            
            if ($this->SelectedIndex == $val->Index)
            {
                $val->Selected = true;
                $val->Class = $this->SelectedClass;
            }
            else
            {
                if ($val->Index == ($this->SelectedIndex -1))
                {
                    $val->BeforeSelected = true;
                }
                if ($val->Index == ($this->SelectedIndex + 1))
                {
                    $val->AfterSelected = true;
                }
                $val->Selected = false;
                $val->Class = $this->UnselectedClass;
            }
            $val->MaxIndex = $maxindex;
            $tabstring .= $val->Render();
        }
        $tabstring .= "</table>";
        return $tabstring;
    }
}

class TabItem
{
    public $Title;
    public $URL;
    public $Selected = false;
    public $Index;
    public $MaxIndex;
    public $Style;
    public $Class;
    public $BeforeSelected = false;
    public $AfterSelected = false;

    function TabItem($index, $title = '', $url = '', $selected = false, $class = '', $style = '')
    {
        $this->Title = $title;
        $this->URL = $url;
        $this->Selected = $selected;
        $this->Index = $index;
        $this->Class = $class;
        $this->Style = $style;
    }

    function Render()
    {
        if ($this->URL != '')
        {
            $url = "<a href='$this->URL'>$this->Title</a>";
        }
        else
        {
            $url = "$this->Title";
        }
        
        $style = $this->Style != '' ? " style='$this->Style' ": '';
        $class = $this->Class != '' ? " class='$this->Class' ": '';
        $tabstring = "";
        $tabstatus = $this->Selected ? "on" : "off";

        if ($this->Index == 1)
        {
            $imgleft = "tabcontroltableft_$tabstatus.jpg";
            $imgmid = "tabcontroltabbg_$tabstatus.jpg";
            $imgright = "tabcontroltabrightmid_$tabstatus.jpg";
            if (!$this->Selected)
            {
                $imgright = "tabcontroltableftmid_off.jpg";
            }
            if ($this->BeforeSelected)
            {
                $imgright = "tabcontroltableftmid_on.jpg";
            }
            $tabstring .= "<td><img src='images/TabControl/$imgleft'></td>";
            $tabstring .= "<td $style $class background='images/TabControl/$imgmid'>$url</td>";
            $tabstring .= "<td><img src='images/TabControl/$imgright'></td>";
        }
        if (($this->Index > 1) && ($this->Index != $this->MaxIndex))
        {
            //$imgleft = "tabcontroltableftmid_$tabstatus.jpg";
            $imgmid = "tabcontroltabbg_$tabstatus.jpg";
            $imgright = "tabcontroltabrightmid_$tabstatus.jpg";
            if (!$this->Selected)
            {
                $imgright = "tabcontroltableftmid_off.jpg";
            }
            if ($this->BeforeSelected)
            {
                $imgright = "tabcontroltableftmid_on.jpg";
            }
            $tabstring .= "<td $style $class background='images/TabControl/$imgmid'>$url</td>";
            $tabstring .= "<td><img src='images/TabControl/$imgright'></td>";
        }
        if ($this->Index == $this->MaxIndex)
        {
            $imgmid = "tabcontroltabbg_$tabstatus.jpg";
            $imgright = "tabcontroltabright_$tabstatus.jpg";
            $tabstring .= "<td $style $class background='images/TabControl/$imgmid'>$url</td>";
            $tabstring .= "<td><img src='images/TabControl/$imgright'></td>";
        }
    
        return $tabstring;
    }
    
}
?>
