<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 07 22, 10
 * Company: Philweb
 *****************************/
class DataList extends BaseDataControl
{
    var $DataSourceText = 0;
    var $DataSourceValue = 0;
    var $SelectedItem = null;
    var $SelectedValue = null;
    var $SelectedText = null;
    var $SelectedIndex = null;
    var $Items;

    function DataList($Name = null, $ID = null, $Caption = null)
    {
        $this->Name = $Name;
        $this->ID = $ID;
        $this->Caption = $Caption;
        $this->Init("DataList");
    }

    function AddItem($listitem)
    {
        $this->Items[] = $listitem;
    }

    function ClearItems()
    {
        $this->Items = null;
    }

    function DataBind()
    {
        $this->ClearItems();
        if ($this->DataSourceText != null && $this->DataSourceValue != null)
        {
            $this->DataColumns = array($this->DataSourceText, $this->DataSourceValue);
        }
        
        parent::DataBind();
        
        if (isset($this->Data))
        {
            $data = $this->Data;
            $datasourcetext = $this->DataSourceText;
            $datasourcevalue = $this->DataSourceValue;

            for($i = 0; $i < count($data); $i++)
            {
                $dataitem = $data[$i];
                $value = '';
                if (is_array($dataitem))
                {
                    $text = $dataitem[$datasourcetext];
                    for($j = 0; $j < count($this->DataColumns); $j++)
                    {
                        if ($this->DataColumns[$j] != $datasourcetext)
                        {
                            $value[$this->DataColumns[$j]] = $dataitem[$this->DataColumns[$j]];
                        }
                    }
                }
                else
                {
                    $text = $dataitem;
                    $value = $i;
                }
                $li = new ListItem();
                $li->Text = $text;
                $li->Value = $value;
                
                $this->Items[] = $li;

            }
        }
        
    }

    function OldRender()
    {
        parent::Render();
        $strcombo = "$this->Caption<br><ul $this->Attributes>";
        $strcombo .= $this->RenderItems();
        $strcombo .= "</ul>";
        return $strcombo;
    }

    function Render()
    {
        parent::Render();
        $strcombo = "$this->Caption<br><table $this->Attributes cellpadding='3' cellspacing='0' border='1'>";
        $strcombo .= $this->RenderItems();
        $strcombo .= "</table>";
        return $strcombo;
    }

    private function RenderItems()
    {
        $strlistitem = "";
        $strlistitem .= "<tr>";
        for($j = 0; $j < count($this->DataColumns); $j++)
        {
            $strlistitem .= "<td align='center'>";
            $strlistitem .= $this->DataColumns[$j];
            $strlistitem .= "</td>";
        }
        $strlistitem .= "</tr>";
        for($i = 0; $i < count($this->Items); $i++)
        {
            $listitem = new ListItem();
            $listitem = $this->Items[$i];
            $text = $listitem->Text;
            $value = $listitem->Value;
            $enabled = $listitem->Enabled;
            $selected = $listitem->Selected == true ? "font-weight:bold;" : "" ;
            $strlistitem .= "<tr>";

            for($j = 0; $j < count($this->DataColumns); $j++)
            {
                if ($this->DataColumns[$j] != $this->DataSourceText)
                {
                    $strlistitem .= "<td>";
                    $strlistitem .= $value[$this->DataColumns[$j]];
                    $strlistitem .= "</td>";
                }
                else
                {
                    $strlistitem .= "<td>";
                    $strlistitem .= "<a onclick=\"javascript:dopostback('select', '$i');\" style='cursor:pointer;text-decoration:underline;$selected'>$text</a>";
                    $strlistitem .= "</td>";
                }
            }

            $strlistitem .= "</tr>";
        }

        return $strlistitem;
    }

    private function OldRenderItems()
    {
        $strlistitem = "";

        for($i = 0; $i < count($this->Items); $i++)
        {
            $listitem = new ListItem();
            $listitem = $this->Items[$i];
            $text = $listitem->Text;
            $value = $i;
            $enabled = $listitem->Enabled;
            $selected = $listitem->Selected == true ? "font-weight:bold;" : "" ;
            $strlistitem .= "<li>
            <a onclick=\"javascript:dopostback('select', '$value');\" style='cursor:pointer;text-decoration:underline;$selected'>$text</a></li>";
        }

        return $strlistitem;
    }

    function SelectedIndexChanged($index)
    {
        $listitem = $this->Items[$index];
        if ($listitem != null)
        {
            $this->SelectedItem = $listitem;
            $this->SelectedValue = $listitem->Value;
            $this->SelectedText = $listitem->Text;
            $this->SelectedIndex = $index;
            $listitem->Selected = true;
        }
    }

    public function  __toString()
    {
        return $this->Render();
    }

}

?>
