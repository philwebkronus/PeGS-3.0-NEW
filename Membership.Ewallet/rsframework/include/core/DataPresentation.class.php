<?php


function presentData($arr)
{
	$strResult = "";
	foreach($arr as $key=>$val)
	{
		$strResult .= "<ul>";
		if (is_array($val))
		{
			$strResult .= presentData($val);
		}
		else
		{
			$strResult .= "<li>" . $key . " = " . $val . "</li>";
		}
		$strResult .= "</ul>";
	}
	return $strResult;
}

?>