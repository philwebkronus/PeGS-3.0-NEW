<?php

/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2012-05-09
 * Company: Philweb
 * ***************** */
class Hashing
{
    const MD5 = "md5";
    const SHA1 = "sha1";

    function Hashing()
    {

    }

    function HashString($string, $hashing = Hashing::MD5)
    {
        $retval = "";
        if ($hashing == Hashing::MD5)
        {
            $retval = md5($string);
        }
        
        if ($hashing == Hashing::SHA1)
        {
            $retval = sha1($string);
        }
        
        return $retval;
    }
    
}
?>
