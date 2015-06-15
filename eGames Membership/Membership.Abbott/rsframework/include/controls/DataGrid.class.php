<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 07 28, 10
 * Company: Philweb
 * *************************** */

class DataGrid extends BaseDataControl
{

    public $Rows;
    public $DataItems;
    public $DataGridHeader;
    public $CssClass = 'reporttable';
    public $Style = '';
    public $HeaderCssClass = 'bg_tableheader2';
    public $DataGridColumns;
    public $AlternatingClass = 'bg_alternate';
    public $ShowFooter = false;
    public $FooterValues;
    public $FooterCSSClass = "bold";
    private $hasRendered = false;
    public $Currency = '';
    private $SortAscending = false;
    public $EventListener = '';
    public $IdentityField = '';
    public $SelectedID = '';
    public $SelectedIDCssClass = 'selectedrow';
    public $SelectColumn = '';
    public $UseJQuery = false;
    public $RowStyle = '';
    
    function DataGrid($ControlID = '')
    {
        if ($ControlID != '')
        {
            $this->ID = $ControlID;
        }
    }

    function AddColumnHeader($text, $datasourcecolumn, $class = '', $style = '')
    {
        if ($this->DataGridHeader == '')
        {
            $this->DataGridHeader = new DataGridHeader();
        }
        $dgheader = $this->DataGridHeader;
        $dgheader->ColumnHeaders[] = new DataGridColumnHeader($text, $datasourcecolumn, $class, $style);
        return $dgheader->ColumnHeaders[count($dgheader->ColumnHeaders) - 1];
    }

    /**
     *
     * @param type $columnheader
     * @param type $datasourcecolumn
     * @param type $datagridcolumntype
     * @param type $alignment
     * @param type $text
     * @param type $footertext
     * @param type $footercalculation
     * @param type $width
     * @param type $class
     * @param type $style
     * @return type 
     */
    function AddColumn($columnheader, $datasourcecolumn, $datagridcolumntype = DataGridColumnType::Text, $alignment = DataGridColumnAlignment::None, $text = '', $footertext = '', $footercalculation = '', $width = '', $class = '', $style = '')
    {
        $ch = $this->AddColumnHeader($columnheader, $datasourcecolumn);
        $ch->Width = $width;
        $this->DataGridColumns[] = new DataGridColumn($datasourcecolumn, $datagridcolumntype, $text, $class, $style);
        $gridcolumn = $this->DataGridColumns[count($this->DataGridColumns) - 1];
        $gridcolumn->Width = $width;
        $gridcolumn->Alignment = $alignment;
        $gridcolumn->FooterText = $footertext;
        $gridcolumn->FooterCalculation = $footercalculation;
        return $gridcolumn;
    }

    function Render()
    {
        $style = $this->Style != '' ? " style='$this->Style' " : '';
        $class = $this->CssClass != '' ? " class='$this->CssClass' " : '';
        $tbodyclass = (isset($this->TBodyCssclass) && $this->TBodyCssclass != '') ? " class='$this->TBodyCssclass' " : '';

        if (is_array($this->DataItems) && $this->ID != '' && $this->EventListener != '')
        {
            $evt = $this->EventListener;

            if ($evt->EventSender == $this->ID && $evt->EventValue != '')
            {
                $sortfield = $evt->EventValue;
                $newdataitems = '';
                $dataitems = $this->DataItems;
                //App::Pr($dataitems);
                for ($i = 0; $i < count($dataitems); $i++)
                {
                    $newdataitem = $dataitems[$i];
                    $newkey = str_replace(" ", "", $newdataitem[$sortfield]);
                    $newdataitems[$newkey][] = $newdataitem;
                    //App::Pr($newkey);
                }

                if ($evt->EventAction == 'sortascending')
                {
                    ksort($newdataitems);
                    $this->SortAscending = true;
                }

                if ($evt->EventAction == 'sortdescending')
                {
                    krsort($newdataitems);
                    $this->SortAscending = false;
                    //App::Pr($newdataitems);
                }

                if ($evt->EventAction == 'selectedindexchange')
                {
                    $this->SelectedID = $evt->EventValue;
                }

                foreach ($newdataitems as $sortkey => $dataitem)
                {
                    for ($i = 0; $i < count($dataitem); $i++)
                    {
                        $finaldataitems[] = $dataitem[$i];
                    }
                }
                $this->DataItems = $finaldataitems;
                unset($newdataitems);
                unset($finaldataitems);
            }
        }
        $dgheader = $this->DataGridHeader;
        $dgheader->EventListener = $this->EventListener;
        $dgheader->SortAscending = $this->SortAscending;
        $dgheader->DataGridID = $this->ID;
        $alternate = false;
        $tablestring = "";
        if ($this->HeaderCssClass != '')
        {
            $dgheader->CssClass = $this->HeaderCssClass;
        }
        if ($this->UseJQuery == true)
        {
            $tablestring .= $this->RenderJQueryScript();
        }
        $tablestring .= "<table " . $style . $class . " id=\"" . $this->ID . "\" >";
        $tablestring .= $dgheader->Render();
        $tablestring .= "<tbody $tbodyclass>";
        if (is_array($this->DataItems))
        {

            foreach ($this->DataItems as $key => $item)
            {
                $class = $alternate ? $this->AlternatingClass : "";
                if ($this->IdentityField != '')
                {
                    if ($this->SelectedID != '' && $this->SelectedID == $item[$this->IdentityField] && $this->SelectedIDCssClass != '')
                    {
                        $class = $class . " $this->SelectedIDCssClass";
                    }
                }
                $row = new DataGridRow($item, $this->DataGridColumns);
                $row->CssClass .= " $class ";
                $row->Currency = $this->Currency;
                $row->IdentityField = $this->IdentityField;
                $row->SelectColumn = $this->SelectColumn;
                $row->DataGridID = $this->ID;
                $row->Style = $this->RowStyle;
                $tablestring .= $row->Render();
                $alternate = !$alternate;
            }
        }
        if ($this->ShowFooter == true)
        {
            $dgfooter = new DataGridFooter($this->DataGridColumns, $this->FooterCSSClass);
            $dgfooter->Currency = $this->Currency;
            $tablestring .= $dgfooter->Render();
            $this->FooterValues = $dgfooter->ColumnValues;
        }
        $tablestring .= "</tbody></table>";
        $this->hasRendered = true;
        //App::Pr($tablestring);
        return $tablestring;
    }
    
    

    function DownloadCSV($filename = 'reportcsv.csv', $delimiter = ',')
    {
        if ($this->hasRendered)
        {
            $arrfieldtitles = null;
            $arrfieldnames = null;
            $dgheader = null;
            if ($this->DataGridHeader != '')
            {
                $dgheader = $this->DataGridHeader;
            }
            for ($i = 0; $i < count($dgheader->ColumnHeaders); $i++)
            {
                $datacol = $dgheader->ColumnHeaders[$i];
                $arrfieldtitles[] = $datacol->Text;
            }

            for ($i = 0; $i < count($this->DataGridColumns); $i++)
            {
                $dc = $this->DataGridColumns[$i];
                $arrfieldnames[] = $dc->DataSourceColumn;
            }

            //App::Pr($dgheader);
            //App::Pr($arrfieldnames);

            App::LoadCore("CSV.class.php");
            $csv = new CSV();
            $csv->Delimiter = $delimiter;
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

    function RenderJQueryScript()
    {
        $jqueryscript = "
            <script language=\"javascript\" type=\"text/javascript\">
    $(document).ready(
    function() 
    {
        
        $(\".linkbutton a\").click(function()
        {
            datagridid = $(this).attr('datagridid');
            selectedid = $(this).attr('selectedid');
            $(\"#_EventValue\").val(selectedid);
            $(\"#_EventSender\").val(datagridid);
            $(\"#_EventAction\").val('selectedindexchange');
            $(this).closest(\"form\").submit();
        });
        $(\".linkbutton\").mousedown(function(event)
        {
            $(this).closest(\"form\").validationEngine('detach');
        });
        $(\".columnheader\").mousedown(function(event)
        {
            $(this).closest(\"form\").validationEngine('detach');
        });
    });
    
    
</script>
            ";
        return $jqueryscript;
    }

}

class DataGridBaseControl
{

    public $Text = '';
    public $CssClass = '';
    public $Style = '';

    function DataGridBaseControl()
    {
        
    }

    protected function RenderBaseControl()
    {
        $cssclass = $this->CssClass;
        $style = $this->Style;
        $retval = " class='$cssclass' style='$style' ";
        return $retval;
    }

}

class DataGridRow extends DataGridBaseControl
{

    var $ItemRow;
    var $DataGridColumns;
    public $Currency;
    public $SelectColumn = '';
    public $IdentityField = '';
    public $DataGridID;

    function DataGridRow($itemrow, $datagridcolumns, $class = '', $style = '')
    {
        $this->ItemRow = $itemrow;
        $this->DataGridColumns = $datagridcolumns;
        $this->CssClass = $class;
        $this->Style = $style;
    }

    function Render()
    {
        $tablestring = "";
        $style = $this->Style != '' ? " style='$this->Style' " : '';
        $class = $this->CssClass != '' ? " class='$this->CssClass' " : '';
        $tablestring .= "<tr " . $style . $class . ">";
        foreach ($this->DataGridColumns as $key => $val)
        {
            $buttonclass = $val->ColumnType == DataGridColumnType::LinkButton ? " linkbutton" : "";
            if (key_exists($val->DataSourceColumn, $this->ItemRow))
            {
                if (is_array(($this->ItemRow[$val->DataSourceColumn])))
                {
                    $cellvalue = $this->ItemRow[$val->DataSourceColumn];
                    if (isset($cellvalue["CellCssClass"]))
                    {
                        $val->CssClass .= " " . $cellvalue["CellCssClass"];
                    }
                    if (isset($cellvalue["Value"]))
                    {
                        $this->ItemRow[$val->DataSourceColumn] = $cellvalue["Value"];
                    }
                }
                $item = $val->Text == '' ? $this->ItemRow[$val->DataSourceColumn] : $val->Text;
                $itemvalue = $val->Text == '' ? $this->ItemRow[$val->DataSourceColumn] : $val->Text;
                $item = DataGridFormatValues::FormatByType($item, $val->ColumnType, $this->Currency);
                
                //if ($this->IdentityField != '' && $this->SelectColumn != '' && $val->DataSourceColumn == $this->SelectColumn)
                if ($this->IdentityField != '' && (($this->SelectColumn != '' && $val->DataSourceColumn == $this->SelectColumn) || $val->ColumnType == DataGridColumnType::LinkButton))
                {
                    $item = "<a class='linkbutton' selectedid='" . $this->ItemRow[$this->IdentityField] . "' datagridid='" . $this->DataGridID . "'"  . " ' action='" . $item . "'>$item</a>";
                }
                
                if ($val->Visible)
                {
                    $colwidth = $val->Width != '' ? " style='width: $val->Width;' " : '';
                    $colstyle = $val->Style != '' ? " style='$val->Style' " : '';
                    $colclass = $val->CssClass != '' ? " $val->CssClass $buttonclass " : " $buttonclass ";
                    $colclass = "class='" . $colclass . "'";
                    $colalignment = $val->Alignment != '' ? " align='$val->Alignment' " : '';
                    //$tablestring .= "<td " . $colstyle . $colclass . $colwidth . $colalignment . ">" . DataGridFormatValues::FormatByType($item, $val->ColumnType, $this->Currency) . "</td>";
                    $tablestring .= "<td " . $colstyle . $colclass . $colwidth . $colalignment . " datagridid='" . $this->DataGridID . "'>" . $item . "</td>";
                    $val->Calculate($itemvalue);
                }
            }
            else
            {
                if (isset($item))
                {
                    
                }
                $item = DataGridFormatValues::FormatByType($item, $val->ColumnType, $this->Currency);
                if ($this->IdentityField != '' && $this->SelectColumn != '' && ($val->DataSourceColumn == $this->SelectColumn || $val->ColumnType == DataGridColumnType::LinkButton))
                {
                    $item = "<a class='selecteditem linkbutton' selectedid='" . $this->ItemRow[$this->IdentityField] . "' >$item</a>";
                }
                
                if ($val->Visible && $val->Text != '')
                {
                    $item = $val->Text;
                    $itemvalue = $val->Text;
                    $colwidth = $val->Width != '' ? " style='width: $val->Width;' " : '';
                    $colstyle = $val->Style != '' ? " style='$val->Style' " : '';
                    $colclass = $val->CssClass != '' ? " $val->CssClass $buttonclass " : " $buttonclass ";
                    $colclass = "class='" . $colclass . "'";
                    $colalignment = $val->Alignment != '' ? " align='$val->Alignment' " : '';
                    //$tablestring .= "<td " . $colstyle . $colclass . $colwidth . $colalignment . ">" . DataGridFormatValues::FormatByType($item, $val->ColumnType, $this->Currency) . "</td>";
                    $tablestring .= "<td " . $colstyle . $colclass . $colwidth . $colalignment . " datagridid='" . $this->DataGridID . "'>" . $item . "</td>";
                    $val->Calculate($itemvalue);
                }
            }
        }
        $tablestring .= "</tr>";
        return $tablestring;
    }

}

class DataGridHeader extends DataGridBaseControl
{

    public $EventListener;
    public $SortAscending;
    public $ColumnHeaders;
    public $DataGridID;

    function DataGridHeader($columnheaders='', $class = '', $style = '')
    {
        $this->ColumnHeaders = $columnheaders;
        $this->CssClass = $class;
        $this->Style = $style;
    }

    function Render()
    {
        $tablestring = "";
        $style = $this->Style != '' ? " style='$this->Style' " : '';
        $class = $this->CssClass != '' ? " class='$this->CssClass' " : '';
        //$title = $this->Title != '' ? " class='$this->Title' ": '&nbsp;';
        $tablestring .= "<tr " . $style . $class . ">";
        foreach ($this->ColumnHeaders as $key => $val)
        {
            $val->EventListener = $this->EventListener;
            $val->SortAscending = $this->SortAscending;
            $val->DataGridID = $this->DataGridID;
            $tablestring .= $val->Render();
        }
        $tablestring .= "</tr>";
        return $tablestring;
    }

}

class DataGridColumnHeader extends DataGridBaseControl
{

    public $EventListener = '';
    public $SortAscending;
    public $DataGridID;
    public $Width = '';
    public $DataSourceColumn;

    function DataGridColumnHeader($text = '', $datasourcecolumn = '', $class = '', $style = '')
    {
        $this->Text = $text;
        $this->CssClass = $class;
        $this->Style = $style;
        $this->DataSourceColumn = $datasourcecolumn;
    }

    function Render()
    {
        $tablestring = "";
        $style = $this->Style != '' ? " style='$this->Style' " : '';
        $class = $this->CssClass != '' ? " class='$this->CssClass' " : '';
        $title = $this->Text != '' ? $this->Text : '&nbsp;';
        //$width = $this->Width != '' ? " style='width: $this->Width;' " : '';
        $width = $this->Width != '' ? " style='width: $this->Width;' " : '';
        $tablestring .= "<th " . $style . $class . $width . ">";
        if ($this->EventListener != '')
        {
            $evt = $this->EventListener;
            $eventaction = $this->SortAscending == true ? "sortdescending" : "sortascending";
            $sorticon = $this->SortAscending != true ? "icondescending" : "iconascending";
            $sorttitle = $this->SortAscending == true ? "Sort Descending" : "Sort Ascending";
            $eventsender = $this->DataGridID;
            $eventvalue = $this->DataSourceColumn;
            $tablestring .= "<a title=\"$sorttitle\" onclick=\"" . $evt->ReturnEventHandler($eventvalue, $eventsender, $eventaction) . "\" class='columnheader'>";
            $tablestring .= $this->Text; // . "-" . $eventaction;
            if ($evt->EventValue == $this->DataSourceColumn && $evt->EventSender == $this->DataGridID)
            {
                $tablestring .= "<div class=\"columnheadericon $sorticon\"></div>";
            }
            $tablestring .= "</a>";
            if ($evt->EventValue == $this->DataSourceColumn && $evt->EventSender == $this->DataGridID)
            {
                $tablestring .= "<a title=\"Clear Sorting\" onclick=\"" . $evt->ReturnEventHandler('', $eventsender, 'clearsorting') . "\" class='columnheader' ><div class=\"columnheadericon iconclear\"></div></a>";
            }
        }
        else
        {
            $tablestring .= $this->Text;
        }
        $tablestring .= "</th>";
        return $tablestring;
    }

}

class DataGridColumn extends DataGridBaseControl
{

    public $ColumnType = DataGridColumnType::Text;
    public $DataSourceColumn = '';
    public $Text = ''; // The default text that will be displayed on the column
    public $Visible = true;
    public $Sum;
    public $Average;
    public $RowCount;
    public $Width;
    public $Alignment = DatagridColumnAlignment::None;
    public $FooterCalculation = DataGridFooterCalculation::None;
    public $FooterText;
    public $LinkButtonValue;
    public $LinkButtonAction;

    function DataGridColumn($datasourcecolumn = '', $columntype = DataGridColumnType::Text, $text = '', $cssclass = '', $style = '')
    {
        $this->DataSourceColumn = $datasourcecolumn;
        $this->Text = $text;
        $this->ColumnType = $columntype;
        $this->CssClass = $cssclass;
        $this->Style = $style;
    }

    function Calculate($value)
    {
        if ($this->ColumnType == DataGridColumnType::CommaStyle || $this->ColumnType == DataGridColumnType::Money || $this->ColumnType == DataGridColumnType::Number)
        {
            $this->Sum += $value;
            $this->RowCount++;
            $this->Average = $this->Sum / $this->RowCount;
        }
    }

    function setLinkButtonParams($eventvalue, $eventaction)
    {
        $this->LinkButtonValue = $eventvalue;
        $this->LinkButtonAction = $eventaction;
    }

    function GetSum()
    {
        
    }

    function GetAverage()
    {
        
    }

}

class DataGridFooter extends DataGridBaseControl
{

    var $ColumnValues;
    var $DataGridColumns;
    public $Currency;

    function DataGridFooter($datagridcolumns, $class = '', $style = '')
    {
        $this->DataGridColumns = $datagridcolumns;
        $this->CssClass = $class;
        $this->Style = $style;
    }

    function Render()
    {
        $tablestring = "";
        $style = $this->Style != '' ? " style='$this->Style' " : '';
        $class = $this->CssClass != '' ? " class='$this->CssClass' " : '';
        $tablestring .= "<tr " . $style . $class . ">";
        foreach ($this->DataGridColumns as $key => $val)
        {

            if ($val->Visible)
            {
                if ($val->FooterText != '')
                {
                    $item = $val->FooterText;
                    $colwidth = $val->Width != '' ? " style='width: $val->Width;' " : '';
                    $colstyle = $val->Style != '' ? " style='$val->Style' " : '';
                    $colclass = $val->CssClass != '' ? " class='$val->CssClass' " : '';
                    $colalignment = $val->Alignment != '' ? " align='$val->Alignment' " : '';
                    $tablestring .= "<td " . $colstyle . $colclass . $colwidth . $colalignment . ">" . DataGridFormatValues::FormatByType($item, $val->ColumnType) . "</td>";
                }
                else
                {

                    switch ($val->FooterCalculation)
                    {
                        case DataGridFooterCalculation::None: $item = "&nbsp;";
                            break;
                        case DataGridFooterCalculation::Sum: $item = $val->Sum;
                            break;
                        case DataGridFooterCalculation::Average: $item = $val->Average;
                            break;
                        default: $item = "&nbsp;";
                            break;
                    }
                    $colwidth = $val->Width != '' ? " style='width: $val->Width;' " : '';
                    $colstyle = $val->Style != '' ? " style='$val->Style' " : '';
                    $colclass = $val->CssClass != '' ? " class='$val->CssClass' " : '';
                    $colalignment = $val->Alignment != '' ? " align='$val->Alignment' " : '';
                    $formatteditem = DataGridFormatValues::FormatByType($item, $val->ColumnType, $this->Currency);
                    $tablestring .= "<td " . $colstyle . $colclass . $colwidth . $colalignment . ">" . $formatteditem . "</td>";
                    //App::Pr($val);
                }
                $this->ColumnValues[] = $item;
            }
        }
        $tablestring .= "</tr>";
        return $tablestring;
    }

}

class DataGridColumnType
{
    const Text = "text";
    const Number = "number";
    const LinkButton = "linkbutton";
    const CheckBox = "checkbox";
    const Image = "image";
    const Money = "money";
    const CommaStyle = "commastyle";
}

class DataGridColumnAlignment
{
    const None = "";
    const Left = "left";
    const Center = "center";
    const Right = "right";
}

class DataGridFooterCalculation
{
    const None = "";
    const Sum = "sum";
    const Average = "average";
}

class DataGridFormatValues
{

    function FormatByType($value, $type, $currency = '')
    {

        $retval = $value;
        switch ($type)
        {
            case DataGridColumnType::Money : $retval = $currency . DataGridFormatValues::FormatMoney($value);
                break;
            case DataGridColumnType::CommaStyle : $retval = DataGridFormatValues::FormatCommaStyle($value);
                break;
        }
        return $retval;
    }

    function FormatMoney($value)
    {
        return number_format($value, 2);
    }

    function FormatCommaStyle($value)
    {
        return number_format($value, 0);
    }

}

?>
