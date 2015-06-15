<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 11 7, 10
 * Company: Philweb
 * *************************** */

class PagingControl extends BaseObject
{

    public $Pager;
    public $SelectedPageClass;
    public $SelectedPage;
    public $URL;

    function PagingControl()
    {
        
    }

    function Render()
    {
        $pg = $this->Pager;
        $pg->MoveToPage(0);
        $pages = null;
        $pagestring = "";
        while ($pg->NextPage())
        {
            $url = " href='$this->URL' ";
            $class = "";

            $url = str_replace("%currentpage", $pg->CurrentPage, $url);

            if ($pg->CurrentPage == 1)
            {
                $pages[] = "<a $url $class >&lt;&lt;</a> ";
            }

            if (isset($this->SelectedPage) && $this->SelectedPage == $pg->CurrentPage)
            {
                $class = " class='$this->SelectedPageClass' ";


                $url = "";
                $pagestring .= "Displaying page $pg->CurrentPage of $pg->PageCount &nbsp;";
            }


            $pages[] = "<a $url $class >$pg->CurrentPage</a> ";
            if ($pg->CurrentPage == $pg->PageCount)
            {
                $pages[] = "<a $url $class >&gt;&gt;</a> ";
            }
        }
        if (count($pages) > 0)
        {
            $pagestring .= implode(" | ", $pages);
        }
        return $pagestring;
    }

    function Render2()
    {
        $pg = $this->Pager;
        $pages = null;
        $pagestring = "";
        while ($pg->NextPage())
        {
            $url = " href='$this->URL' ";
            $class = "";

            $url = str_replace("%currentpage", $pg->CurrentPage, $url);

            if (isset($this->SelectedPage) && $this->SelectedPage == $pg->CurrentPage)
            {
                $class = " class='$this->SelectedPageClass' ";
                if ($pg->CurrentPage == 1)
                {
                    $pages[] = "<a $url $class >&lt;&lt;</a> ";
                }
                $url = "";
                $pagestring .= "Displaying page $pg->CurrentPage of $pg->PageCount &nbsp;";
            }


            $pages[] = "<a $url $class >$pg->CurrentPage</a> ";
            if ($pg->CurrentPage == $pg->PageCount)
            {
                $pages[] = "<a $url $class >&gt;&gt;</a> ";
            }
        }
        if (count($pages) > 0)
        {
            $pagestring .= implode(" | ", $pages);
        }
        return $pagestring;
    }

}

?>
