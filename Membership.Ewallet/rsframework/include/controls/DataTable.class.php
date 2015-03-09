<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 11 2, 10
 * Company: Philweb
 * *************************** */

class DataTable extends BaseObject
{

    public $Style;
    public $Class;
    public $DataItems;
    public $Cols;
    public $DataTableHeaders;
    public $DataItemColumns;
    public $DataTableFooters;
    public $AlternatingClass;
    public $SelectIdentity;
    public $SelectedIndex;
    public $DeleteIdentity;
    public $EventIdentity;
    public $DataRowClasses;
    public $URLEncodeSelectLink = false;
    public $TBodyCssclass;
    private $hasRendered = false;

    function DataTable()
    {
        
    }

    function PreRender()
    {
        $style = $this->Style != '' ? " style='$this->Style' " : '';
        $class = $this->Class != '' ? " class='$this->Class' " : '';
        $tbodyclass = $this->TBodyCssclass != '' ? " class='$this->TBodyCssclass' " : '';
        $tablestring = "<table " . $style . $class . ">";
        if (count($this->DataTableHeaders) > 0)
        {
            foreach ($this->DataTableHeaders as $key => $val)
            {
                $tablestring .= $val->Render();
            }
        }
        $tablestring .= "<tbody $tbodyclass>";

        $alternate = false;
        if ($this->DataItems != null && count($this->DataItems) > 0)
        {
            foreach ($this->DataItems as $key => $val)
            {
                $class = $alternate ? $this->AlternatingClass : "";
                if (isset($this->DataRowClasses[$key]))
                {
                    $datarowclass = $this->DataRowClasses[$key];
                    $class .= $datarowclass->CssClass;
                }
                $dr = new DataRow($key, $val, $this->DataItemColumns, $class, '');
                $dr->SelectIdentity = $this->SelectIdentity;
                $dr->DeleteIdentity = $this->DeleteIdentity;
                $dr->URLEncodeSelectLink = $this->URLEncodeSelectLink;
                $tablestring .= $dr->Render();
                $alternate = !$alternate;
            }
        }

        if (count($this->DataTableFooters) > 0)
        {
            foreach ($this->DataTableFooters as $key => $val)
            {
                if ($this->DataItems != null && count($this->DataItems) > 0)
                {
                    $val->DataItemCount = count($this->DataItems);
                }
                $tablestring .= $val->Render();
            }
        }

        $tablestring .= "</tbody></table>";
        $this->hasRendered = true;
        return $tablestring;
    }

    function Render()
    {
        return $this->PreRender();
    }

    function DownloadCSV($filename = 'reportcsv.csv')
    {
        if ($this->hasRendered)
        {
            $arrfieldtitles = null;
            $arrfieldnames = null;
            for ($i = 0; $i < count($this->DataItemColumns); $i++)
            {
                $datacol = $this->DataItemColumns[$i];
                $arrfieldnames[] = $datacol->DataColumn;
            }

            for ($i = 0; $i < count($this->DataTableHeaders); $i++)
            {
                $dataheader = $this->DataTableHeaders[$i];
                $colhead = $dataheader->ColumnHeaders;
                $arrfieldtitles = null;
                for ($j = 0; $j < count($colhead); $j++)
                {
                    $coltitle = $colhead[$j];
                    $arrfieldtitles[] = $coltitle->Title;
                }
            }

            App::LoadCore("CSV.class.php");
            $csv = new CSV();
            $csvdata = $csv->CreateCSVFromResultSet($this->DataItems, $arrfieldtitles, $arrfieldnames);
            header("Content-type: text/csv");
            header("Content-Disposition: attachment; filename=$filename");
            header("Pragma: no-cache");
            header("Expires: 0");
            echo $csvdata;
            exit();
        }
        else
        {
            $this->setError("Control has not yet rendered.");
        }
    }

}

class DataTableHeader
{

    public $Style;
    public $ColumnHeaders;
    public $Class;

    function DataTableHeader($columnheaders='', $class = '', $style = '')
    {
        $this->ColumnHeaders = $columnheaders;
        $this->Class = $class;
        $this->Style = $style;
    }

    function Render()
    {
        $tablestring = "";
        $style = $this->Style != '' ? " style='$this->Style' " : '';
        $class = $this->Class != '' ? " class='$this->Class' " : '';
        //$title = $this->Title != '' ? " class='$this->Title' ": '&nbsp;';
        $tablestring .= "<tr " . $style . $class . ">";
        foreach ($this->ColumnHeaders as $key => $val)
        {
            $tablestring .= $val->Render();
        }
        $tablestring .= "</tr>";
        return $tablestring;
    }

}

class ColumnHeader
{

    public $Style;
    public $Title;
    public $ColSpan;
    public $Class;
    public $DataColumn;

    function ColumnHeader($title = '', $colspan = '', $datacolumn = '', $class = '', $style = '')
    {
        $this->Title = $title;
        $this->ColSpan = $colspan;
        $this->DataColumn = $datacolumn;
        $this->Class = $class;
        $this->Style = $style;
    }

    function Render()
    {
        $tablestring = "";
        $style = $this->Style != '' ? " style='$this->Style' " : '';
        $class = $this->Class != '' ? " class='$this->Class' " : '';
        $title = $this->Title != '' ? $this->Title : '&nbsp;';
        $colspan = $this->ColSpan != '' ? " colspan='$this->ColSpan' " : '';
        $tablestring .= "<td " . $style . $class . $colspan . ">" . $this->Title . "</td>";
        return $tablestring;
    }

}

class DataRow
{

    public $Style;
    public $ColSpan;
    public $Class;
    public $DataItem;
    public $DataItemColumns;
    public $DataItemIndex;
    public $SelectIdentity;
    public $DeleteIdentity;
    public $EventIdentity;
    public $URLEncodeSelectLink = false;

    function DataRow($index = '', $dataitem = '', $dataitemcolumns = '', $class = '', $style = '')
    {
        $this->DataItem = $dataitem;
        $this->Class = $class;
        $this->Style = $style;
        $this->DataItemColumns = $dataitemcolumns;
        $this->DataItemIndex = $index;
    }

    function Render()
    {
        $tablestring = "";
        $style = $this->Style != '' ? " style='$this->Style' " : '';
        $class = $this->Class != '' ? " class='$this->Class' " : '';
        $tablestring .= "<tr " . $style . $class . ">";
        foreach ($this->DataItemColumns as $key => $val)
        {
            if (key_exists($val->DataColumn, $this->DataItem))
            {
                $item = $this->DataItem[$val->DataColumn];
                if ($val->WillTotal)
                {
                    $val->Total += $item;
                }
                if ($val->DataColumnType == 'Money')
                {
                    $item = number_format($item, 2);
                }
                if ($val->DataColumnType == 'AverageMoney')
                {
                    $item = number_format($item, 2);
                }
                if ($val->DataColumnType == 'CSV')
                {
                    $item = number_format($item, 0);
                }
                if ($val->SelectColumn == true)
                {
                    $selectidentity = $this->DataItem[$this->SelectIdentity];
                    if ($this->URLEncodeSelectLink)
                    {
                        $selectidentity = urlencode($selectidentity);
                    }
                    $item = "<a href='javascript:SelectedIndexChange(\"$selectidentity\");'>$item</a>";
                }

                if ($val->DeleteColumn == true)
                {
                    $deleteidentity = $this->DataItem[$this->DeleteIdentity];
                    $item = "<a href='javascript:DeleteIndexChange($deleteidentity);'>$item</a>";
                }

                if ($val->EventColumn == true)
                {
                    $deleteidentity = $this->DataItem[$this->EventIdentity];
                    $item = "<a href='javascript:EventRaised($deleteidentity);'>$item</a>";
                }

                if ($val->Visible)
                {
                    $tablestring .= "<td " . $val->Style . $val->Class . ">" . $item . "</td>";
                }
            }
        }
        $tablestring .= "</tr>";
        return $tablestring;
    }

}

class DataItemColumn
{

    public $Style;
    public $Text;
    public $ColSpan;
    public $Class;
    public $DataColumn;
    public $DataColumnType;
    public $WillTotal;
    public $Total;
    public $SelectColumn = false;
    public $DeleteColumn = false;
    public $EventColumn = false;
    public $EventName = false;
    public $Visible = true;

    function DataItemColumn($text = '', $colspan = '', $datacolumn = '', $class = '', $style = '', $datacolumntype = '', $willtotal = false, $selectcolumn = false, $deletecolumn = false, $eventcolumn = false, $eventname = '')
    {
        $this->Text = $text;
        $this->ColSpan = $colspan;
        $this->DataColumn = $datacolumn;
        $this->Class = $class;
        $this->Style = $style;
        $this->DataColumnType = $datacolumntype;
        $this->WillTotal = $willtotal;
        $this->SelectColumn = $selectcolumn;
        $this->DeleteColumn = $deletecolumn;

        $this->Style = $this->Style != '' ? " style='$this->Style' " : '';
        $this->Class = $this->Class != '' ? " class='$this->Class' " : '';
    }

}

class LinkButtonColumn
{

    public $Style;
    public $Text;
    public $ColSpan;
    public $Class;
    public $DataColumn;
    public $DataColumnType;
    public $WillTotal;
    public $Total;
    public $SelectColumn = false;
    public $DeleteColumn = false;
    public $Visible = true;

    function LinkButtonColumn($text = '', $datacolumn = '', $class = '', $style = '')
    {
        
    }

}

class DataRowClass
{

    public $CssClass;

    function DataRowClass($cssclass)
    {
        $this->CssClass = $cssclass;
        $this->CssClass = $this->CssClass != '' ? " $this->CssClass " : '';
    }

}

class DataTableFooter
{

    public $Style;
    public $Class;
    public $DataItemColumns;
    public $DataItemCount;

    function DataTableFooter($dataitemcolumns = '', $class = '', $style = '')
    {
        $this->Class = $class;
        $this->Style = $style;
        $this->DataItemColumns = $dataitemcolumns;
    }

    function Render()
    {
        $tablestring = "";
        $style = $this->Style != '' ? " style='$this->Style' " : '';
        $class = $this->Class != '' ? " class='$this->Class' " : '';
        $tablestring .= "<tr " . $style . $class . ">";
        foreach ($this->DataItemColumns as $key => $val)
        {
            $item = "&nbsp;";
            if ($val->WillTotal)
            {
                $item = $val->Total;
            }

            if ($val->DataColumnType == 'Money')
            {
                $item = number_format($item, 2);
            }
            if ($val->DataColumnType == 'AverageMoney')
            {
                if (isset($this->DataItemCount) && $this->DataItemCount > 0)
                {
                    $item = $item / $this->DataItemCount;
                }
                $item = number_format($item, 2);
            }
            if ($val->DataColumnType == 'CSV')
            {
                $item = number_format($item, 0);
            }
            if ($val->Visible)
            {
                $tablestring .= "<td " . $val->Style . $val->Class . ">" . $item . "</td>";
            }
        }
        $tablestring .= "</td></tr>";
        return $tablestring;
    }

}

?>
