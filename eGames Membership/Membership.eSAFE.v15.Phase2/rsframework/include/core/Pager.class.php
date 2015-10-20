<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 10 19, 10
 * Company: Philweb
 *****************************/
class Pager extends BaseObject
{
    var $CurrentPage;
    var $ItemsPerPage;
    var $ItemCount;
    var $ItemFrom;
    var $ItemTo;
    var $PageCount;
    var $ItemCountModulus;

    /**
     *
     * @param Integer $itemsperpage
     * @param Integer $itemcount
     * @param Integer $currentpage zero index
     */
    function Pager($itemsperpage = 10, $itemcount = 0, $currentpage = 0)
    {
        $this->CurrentPage = $currentpage;
        $this->ItemsPerPage = $itemsperpage;
        $this->ItemCount = $itemcount;
        $this->PageCount = ceil($itemcount / $itemsperpage);
        $this->ItemCountModulus = $itemcount % $itemsperpage;

        $this->MoveToPage($currentpage);
    }

    function MoveToPage($currentpage)
    {
        if ($currentpage >= 1 && $currentpage <= $this->PageCount)
        {
            $this->CurrentPage = $currentpage;
            $this->ItemFrom = (($this->CurrentPage - 1)* $this->ItemsPerPage) + 1;
            $this->ItemTo = (($this->CurrentPage) * $this->ItemsPerPage);

            if ($this->CurrentPage == $this->PageCount && $this->ItemCountModulus != 0)
            {
                $this->ItemTo = $this->ItemFrom + $this->ItemCountModulus - 1;
            }
            return true;
        }
        else
        {
            return false;
        }
    }
    
    function MoveToFirstPage()
    {
        $this->MoveToPage(0);
    }
    
    function MoveToLastPage()
    {
        $this->MoveToPage($this->PageCount);
    }

    function NextPage()
    {
        if ($this->CurrentPage < $this->PageCount)
        {
            return $this->MoveToPage($this->CurrentPage + 1);
        }
        else
        {
            return false;
        }
    }

    function PrevPage()
    {
        if ($this->CurrentPage > 1)
        {
            return $this->MoveToPage($this->CurrentPage - 1);
        }
        else
        {
            return false;
        }
    }
}
?>
