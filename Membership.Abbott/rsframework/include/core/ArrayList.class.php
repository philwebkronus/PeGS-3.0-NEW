<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 07 22, 10
 * Company: Philweb
 *****************************/
class ArrayList extends ArrayObject
{
    var $arrList;

    function ArrayList($arrObj = '')
    {
        $this->AddArray($arrObj);
        //parent::
        //return $this->arrList;
    }

    function Add($obj)
    {
        $this[] = $obj;
    }

    function AddArray($arrObj)
    {
        if (is_array($arrObj))
        {
            foreach($arrObj as $key => $val)
            {
                $this[] = $val;
            }
        }
    }
    
    function AggreegateArray($arrData, $fieldname, $sumfields)
    {
        $retval = "";
        for ($i = 0; $i < count($arrData); $i++)
        {
            $row = $arrData[$i];
            if (ArrayList::isValidArray($sumfields))
            {
                for ($j = 0; $j < count($sumfields); $j++)
                {
                    
                }
            }
        }
    }
    
    function isValidArray($arrData)
    {
        if (isset($arrData) && is_array($arrData) && count($arrData) > 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
}

?>
