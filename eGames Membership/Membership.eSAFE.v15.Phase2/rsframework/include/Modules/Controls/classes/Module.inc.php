<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 07 16, 10
 * Company: Philweb
 *****************************/

class ControlsModuleClass extends BaseModule
{
    function ControlsModuleClass()
    {
        $this->arrClasses = array(
        "BaseControl" => "KeywordProcessor",
        "BaseDataControl" => "LottoCombination",
        "ListItem" => "LottoType",
        "DataSource" => "DataSource",
        "FormsProcessor" => "FormsProcessor"
        );
    }
}
?>
