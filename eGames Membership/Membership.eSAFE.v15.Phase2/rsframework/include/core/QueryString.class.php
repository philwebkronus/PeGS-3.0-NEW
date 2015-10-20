<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 10 19, 10
 * Company: Philweb
 *****************************/
class QueryString extends BaseObject
{
    function QueryString()
    {

    }

    function GetQueryString($key, $defaultvalue = '')
    {
        if ($defaultvalue == '')
        {
            if (isset($_GET) && count($_GET) > 0 && isset($_GET[$key]))
            {
                return $_GET[$key];
            }
            else
            {
                return false;
            }
        }
        else
        {
            if (isset($_GET) && count($_GET) > 0 && isset($_GET[$key]))
            {
                return $_GET[$key];
            }
            else
            {
                return $defaultvalue;
            }
        }

        
    }
}
?>
