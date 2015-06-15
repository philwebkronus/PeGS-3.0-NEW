<?php
/* * ***************** 
 * Author: Roger Sanchez
 * Date Created: 2012-05-30
 * Company: Philweb
 * ***************** */

class Randomizer extends BaseObject
{

    var $HasAlpha = true;

    function Randomizer()
    {
        
    }

    function GenerateAlphaNumeric($stringlength)
    {
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $string = '';
        for ($i = 0; $i < $stringlength; $i++)
        {
            $string .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $string;
    }

}
?>
