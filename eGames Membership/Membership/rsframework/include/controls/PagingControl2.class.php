<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 11 7, 10
 * Company: Philweb
 * *************************** */

class PagingControl2 extends BaseControl
{

    private $Pager;
    public $SelectedPageClass;
    public $SelectedPage;
    public $SelectedItemFrom;
    public $SelectedItemTo;
    public $URL;
    public $ShowMoveToFirstPage = false;
    public $ShowMoveToLastPage = false;
    public $ShowNextLink = true;
    public $ShowPrevLink = true;
    public $PageGroup = 3;
    public $GroupPageNumbers = true;
    public $FirstLinkText = "&lt;&lt;";
    public $LastLinkText = "&gt;&gt;";
    public $PrevLinkText = "&lt;";
    public $NextLinkText = "&gt;";

    function PagingControl2($itemsperpage, $itemcount)
    {
        $this->SelectedPage = 1;
        if (App::GetFormValues("pgSelectedPage"))
        {
            $this->SelectedPage = App::GetFormValues("pgSelectedPage");
        }
        $this->Initialize($itemsperpage, $itemcount);
    }
    
    function Initialize($itemsperpage, $itemcount)
    {
        $selectedpage = $this->SelectedPage;
        $currentselectedpage = $selectedpage - 1;

        App::LoadCore("Pager.class.php");
        $pg = new Pager($itemsperpage, $itemcount, 0);
        $this->Pager = $pg;
        $this->SelectedItemFrom = ($currentselectedpage * $itemsperpage) + 1;
        $this->SelectedItemTo = ($currentselectedpage + 1) * $itemsperpage;
    }

    function PreRender()
    {
        $pg = $this->Pager;
        //$pg = new Pager();

        $pages = null;
        $pagestring = "<input type='hidden' name='pgSelectedPage' id='pgSelectedPage' value='$this->SelectedPage'>";
        while ($pg->NextPage())
        {
            $url = " href='$this->URL' ";
            $class = "";

            $nexturl = str_replace("%currentpage", $this->SelectedPage + 1, $url);
            $prevurl = str_replace("%currentpage", $this->SelectedPage - 1, $url);
            $url = str_replace("%currentpage", $pg->CurrentPage, $url);
            if ($this->ShowMoveToFirstPage && $pg->CurrentPage == 1)
            {
                $pages[] = "<a $url $class >$this->FirstLinkText</a> ";
            }
            if ($this->ShowPrevLink && $pg->CurrentPage == 1)
            {
                if ($this->SelectedPage > 1)
                {
                    $pages[] = "<a $prevurl $class >$this->PrevLinkText</a> ";
                }
                else
                {
                    $pages[] = "$this->PrevLinkText ";
                }
            }

            if ($this->GroupPageNumbers)
            {
                $selectedgroup = ceil($this->SelectedPage / $this->PageGroup);
                $currentgroup = ceil($pg->CurrentPage / $this->PageGroup);

                if ($currentgroup == $selectedgroup)
                {
                    if ($pg->CurrentPage == $this->SelectedPage)
                    {
                        $pages[] = $pg->CurrentPage;
                    }
                    else
                    {
                        $pages[] = "<a $url $class >$pg->CurrentPage</a> ";
                    }
                }
            }
            else
            {

                if ($pg->CurrentPage == $this->SelectedPage)
                {
                    $pages[] = $pg->CurrentPage;
                }
                else
                {
                    $pages[] = "<a $url $class >$pg->CurrentPage</a> ";
                }
            }

            if ($this->ShowNextLink && $pg->CurrentPage == $pg->PageCount)
            {
                if ($this->SelectedPage < $pg->PageCount)
                {
                    $pages[] = "<a $nexturl $class >$this->NextLinkText</a> ";
                }
                else
                {
                    $pages[] = "$this->NextLinkText ";
                }
            }
            if ($this->ShowMoveToLastPage && $pg->CurrentPage == $pg->PageCount)
            {
                $pages[] = "<a $url $class >$this->LastLinkText</a> ";
            }
        }
        if (count($pages) > 0)
        {
            $pagestring .= implode(" &nbsp; ", $pages);
        }
        $pg->MoveToFirstPage();
        return $pagestring;
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
