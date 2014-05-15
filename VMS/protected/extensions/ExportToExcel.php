<?php

#### Roshan's very simple code to export data to excel
#### Copyright reserved to Roshan Bhattarai - nepaliboy007@yahoo.com
#### if you find any problem contact me at http://roshanbh.com.np
#### fell free to visit my blog http://php-ajax-guru.blogspot.com

class ExportExcel
{
	//variable of the class
	var $titles=array();
	var $all_values=array();
	var $filename;

	//functions of the class
	function ExportExcel($f_name) //constructor
	{
		$this->filename=$f_name;
	}
	function setHeadersAndValues($hdrs,$all_vals) //set headers and query
	{
		$this->titles=$hdrs;
		$this->all_values=$all_vals;
	}
	function GenerateExcelFile() //function to generate excel file
	{
		$header='';
                $data='';
		foreach ($this->titles as $title_val)
 		{
 			$header .= $title_val."\t";
 		}
 		for($i=0;$i<sizeof($this->all_values);$i++)
 		{
 			$line = '';
 			foreach($this->all_values[$i] as $value)
			{
 				if ((!isset($value)) OR ($value == ""))
				{
 					$value = "\t";
 				} //end of if
				else
				{
 					$value = str_replace('"', '""', $value);
 					$value = '"' . $value . '"' . "\t";
                                    //$value = "\t";
 				} //end of else
 				$line .= $value;
 			} //end of foreach
 			$data .= trim($line)."\n";
 		}//end of the while
 		$data = str_replace("\r", "", $data);
                
		if ($data == "")
 		{
 			$data = "\n(0) Records Found!\n";
 		}
		#send headers
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename=".$this->filename);
		header("Content-Transfer-Encoding: binary ");
		print "$header\n$data";
	}
        
        /**
         * This is a customized function for converting and inserting 
         * html codes and values into excel cells.
         * @author Noel Antonio
         * @param type $html
         */
        function toHTML($html)
        {
                header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Disposition: attachment;filename=".$this->filename);
		header("Content-Transfer-Encoding: binary ");
		print "$html";
        }

}
?>