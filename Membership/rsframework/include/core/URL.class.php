<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 12 16, 10
 * Company: Philweb
 *****************************/
class URL extends BaseObject
{
    function URL()
    {

    }

    function Redirect($url)
    {
        header("Location: $url");
    }

    function CurrentPage()
    {
        return substr($_SERVER["SCRIPT_NAME"],strrpos($_SERVER["SCRIPT_NAME"],"/")+1);
    }
}
?>
