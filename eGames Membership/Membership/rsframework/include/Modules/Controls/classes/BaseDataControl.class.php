<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 07 22, 10
 * Company: Philweb
 *****************************/
class BaseDataControl extends BaseControl
{
    var $DataSource;
    var $Data;
    var $DataColumns = null;


    function BaseDataControl()
    {
        
    }

    public function DataBind()
    {
        //$this->DataSource = new DataSource();
        $ds = new DataSource($this->DataSource);
        $ds->DataList = $this->DataSource;
        $ds->arrDataColumns = $this->DataColumns;
        $this->Data = $ds->GetData($this->DataColumns);
        
    }

    function Render()
    {
        parent::Render();
    }
}
?>
