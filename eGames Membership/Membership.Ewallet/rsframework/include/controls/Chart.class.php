<?php

/*
 * Author: Roger Sanchez
 * Date Created: 2011-09-08
 * Company: Philweb
 */

class Chart extends BaseControl
{
    const MSLine = "MSLine";
    const MSColumn2D = "MSColumn2D";
    const MSColumn3D = "MSColumn3D";
    const Line = "Line";
    const Column2D = "Column2D";
    const Column3D = "Column3D";
    const Bar2D = "Bar2D";
    const Bar3D = "Bar3D";
    const Pie3D = "Pie3D";


    private $LabelFields;
    private $FieldColors;
    var $ChartType;
    var $Length;
    var $Width;
    var $Transparent = true;
    var $ChartPath;
    var $DataSource;
    var $CheckListItems;
    var $ShowValues = false;
    var $CategoryField;
    var $CheckListItemValues;
    var $SingleDimension = false;
    var $SingleDimensionIndex;
    var $xAxisName = '';
    var $yAxisName = '';
    var $DecimalPrecision = 0;

    function Chart($charttype, $chartpath, $length, $width, $transparent = true)
    {
        $this->ChartType = $charttype;
        $this->Length = $length;
        $this->Width = $width;
        $this->Transparent = $transparent;
        $this->ChartPath = $chartpath;
        $this->CheckListItems = null;
    }

    function AddField($fieldname, $label, $display = true, $color = '')
    {
        $this->LabelFields[$fieldname] = $label;
        $chkItem["Caption"] = $label;
        $chkItem["Value"] = $fieldname;
        $chkItem["Checked"] = $display;
        $this->CheckListItems[] = $chkItem;
        $this->CheckListItemValues[$fieldname] = $display;
        if ($color != '')
        {
            $this->FieldColors[$fieldname] = $color;
        }
    }

    function ClearFields()
    {
        $this->LabelFields = null;
        $this->CheckListItems = null;
        $this->CheckListItemValues = null;
    }

    function Render()
    {

        $itemvalues = $this->CheckListItemValues;
        $showvalues = $this->ShowValues == true ? "showValues='1'" : "showValues='0'";
//        if (is_array($this->CheckListItems) && count($this->CheckListItems) > 1)
//        {
//            $dimensioncount = 0;
//            $dimensionindex = "";
//            for ($i = 0; $i < count($this->CheckListItems); $i++)
//            {
//                $chkbox = $this->CheckListItems[$i];
//                if ($chkbox["Checked"] == true)
//                {
//                    $dimensioncount++;
//                    $dimensionindex = $chkbox["Value"];
//                }
//            }
//            if ($dimensioncount > 1)
//            {
//                $this->SingleDimension = false;
//            }
//            elseif ($dimensioncount == 1)
//            {
//                $this->SingleDimension = true;
//                $this->SingleDimensionIndex = $dimensionindex;
//            }
//        }
        if ($this->DataSource != '' && is_array($this->DataSource))
        {
            App::LoadLibrary("FusionCharts/FusionCharts_Gen.php");
            $chart2 = new FusionCharts($this->ChartType, $this->Length, $this->Width, '', $this->Transparent);

            $rtgwinningsdata = $this->DataSource;
            $labels = $this->LabelFields;
            $colors = $this->FieldColors;
            $minimumvalue = 0;
            $maximumvalue = 100;

            $arrCat = "";
            for ($i = 0; $i < count($rtgwinningsdata); $i++)
            {
                $winning = $rtgwinningsdata[$i];
                $arrCat[$i] = $winning[$this->CategoryField];
                

                if ($this->SingleDimension && $this->SingleDimensionIndex != null)
                {
                    $this->RenderSingleDimension($chart2, $winning);
                }
                else
                {
                    foreach ($itemvalues as $key => $val)
                    {
                        if ($val == true)
                        {
                            if ($i == 0)
                            {
                                if (isset($colors[$key]))
                                {
                                    $color = $colors[$key];
                                }
                                $zaxis = "";
                                $args = "";
                                switch ($key)
                                {
                                    case "TotalNetWin": $color = "8BBA00";
                                        $args = "numberPrefix=Php ;";
                                        break;
                                    case "TotalBet": $color = "F6BD0F";
                                        $args = "numberPrefix=Php ;";
                                        break;
                                    case "Payout": $color = "AFD8F8";
                                        $args = "numberPrefix=Php ;";
                                        break;
                                    case "BetCount": $color = "FF8E46";
                                        $zaxis = "parentYaxis=S; RenderAs=Area;";
                                        break;
                                }
                                $options = $showvalues;
                                if (isset($color))
                                {
                                    $options .= "; color=$color; thickness=4; $zaxis $args;";
                                }

                                $arrData[$key][0] = $labels[$key];
                                $arrData[$key][1] = $options;
                            }

                            $arrData[$key][$i + 2] = $winning[$key];
                            if ($minimumvalue >= $winning[$key])
                            {
                                $minimumvalue = $winning[$key];
                            }
                            if ($maximumvalue <= $winning[$key])
                            {
                                $maximumvalue = $winning[$key];
                            }
                        }
                    }
                }
            }

            $chart2->setSWFPath($this->ChartPath);
            if ($this->Caption != '' && $this->ShowCaption == true)
            {
                $chart2->setChartParam("caption", $this->Caption);
            }
            $chart2->setChartParam("rotateNames", "1");
            $chart2->setChartParam("decimalPrecision", $this->DecimalPrecision);
            $chart2->setChartParam("formatNumberScale", "0");
            $chart2->setChartParam("xAxisName", $this->xAxisName);
            $chart2->setChartParam("yAxisName", $this->yAxisName);
            $chart2->setChartParam("yAxisMinValue", $minimumvalue * 1.1);
            $chart2->setChartParam("yAxisMaxValue", $maximumvalue * 1.1);
            $chart2->setChartParam("xAxisMinValue", $minimumvalue * 1.1);
            $chart2->setChartParam("xAxisMaxValue", $maximumvalue * 1.1);            
            $chart2->setChartParam("exportEnabled", "1");
            $chart2->setChartParam("exportShowMenuItem", "1");
            $chart2->setChartParam("exportAtClient", "0");

            if ($this->SingleDimension == false)
            {
                $chart2->addChartDataFromArray($arrData, $arrCat);
            }

            return $chart2->renderChart(false, false);
        }
        else
        {
            App::LoadLibrary("FusionCharts/FusionCharts_Gen.php");
            $chart2 = new FusionCharts($this->ChartType, $this->Length, $this->Width, '', $this->Transparent);
            $chart2->setSWFPath($this->ChartPath);
            if ($this->Caption != '' && $this->ShowCaption == true)
            {
                $chart2->setChartParam("caption", $this->Caption);
            }
            $chart2->setChartParam("rotateNames", "1");
            
            return $chart2->renderChart(false, false);
        }
    }

    function RenderSingleDimension($chart2, $winning)
    {
        $itemvalues = $this->CheckListItemValues;
        $color = null;
        if (isset($winning["day"]))
        {
            switch ($winning["day"])
            {
                case "Mon": $color = "AFD8F8";
                    break;
                case "Tue": $color = "F6BD0F";
                    break;
                case "Wed": $color = "8BBA00";
                    break;
                case "Thu": $color = "FF8E46";
                    break;
                case "Fri": $color = "008E8E";
                    break;
                case "Sat": $color = "D64646";
                    break;
                case "Sun": $color = "8E468E";
                    break;
            }
        }
        if (isset($winning["graphmonth"]))
        {
            switch ($winning["graphmonth"])
            {
                case "Jan": $color = "AFD8F8";
                    break;
                case "Feb": $color = "F6BD0F";
                    break;
                case "Mar": $color = "8BBA00";
                    break;
                case "Apr": $color = "FF8E46";
                    break;
                case "May": $color = "008E8E";
                    break;
                case "Jun": $color = "D64646";
                    break;
                case "Jul": $color = "8E468E";
                    break;
                case "Aug": $color = "588526";
                    break;
                case "Sep": $color = "B3AA00";
                    break;
                case "Oct": $color = "008ED6";
                    break;
                case "Nov": $color = "9D080D";
                    break;
                case "Dec": $color = "A186BE";
                    break;
            }
        }
        if (isset($winning["color"]))
        {
            $color = $winning["color"];
        }

        $name = "name=" . $winning[$this->CategoryField];
        if ($color != null)
        {
            $name .= ";color=$color";
        }

        if (isset($winning["graphlink"]))
        {
            $name .= ";link=" . $winning["graphlink"];
        }

        foreach ($itemvalues as $key => $val)
        {
            if ($val == true && (isset($winning["day"]) && $winning["day"] == $key))
            {
                $chart2->addChartData($winning[$this->SingleDimensionIndex], $name);
            }

            if ($val == true && (isset($winning["graphmonth"]) && $winning["graphmonth"] == $key))
            {

                $chart2->addChartData($winning[$this->SingleDimensionIndex], $name);
            }

            if ($val == true && !isset($winning["day"]) && !isset($winning["graphmonth"]))
            {
                $chart2->addChartData($winning[$this->SingleDimensionIndex], $name);
            }
        }
        
        if ($this->ShowValues)
        {
            $chart2->setChartParam("showValues", 1);
        }
        else
        {
            $chart2->setChartParam("showValues", 0);
        }
    }

}

?>
