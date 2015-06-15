<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 07 21, 10
 * Company: Philweb
 * *************************** */

class CSV extends BaseObject
{

    var $arrFiedTites;
    var $arrFieldNames;
    var $arrFieldValues;
    var $CSVContent;
    var $IgnoreLines;
    var $arrIgnoreLineContainingString;
    var $Delimiter = ",";

    function CSV()
    {
        
    }

    function CreateCSVFromResultSet($resultset, $arrfieldtitles, $arrfieldnames)
    {
        $csvlines = null;
        if (count($resultset) > 0)
        {
            $csvlines = implode(",", $arrfieldtitles) . "\r\n";
            for ($i = 0; $i < count($resultset); $i++)
            {
                $row = $resultset[$i];
                $rowitem = null;
                $csvline = null;
                for ($j = 0; $j < count($arrfieldnames); $j++)
                {
                    if (is_array($row[$arrfieldnames[$j]]) && isset($row[$arrfieldnames[$j]]["Value"]))
                    {
                        $rowitem[] = $row[$arrfieldnames[$j]]["Value"];
                    }
                    else
                    {
                        $rowitem[] = $row[$arrfieldnames[$j]];
                    }
                }
                $csvline = join($this->Delimiter, $rowitem) . "\r\n";
                $csvlines .= $csvline;
            }
            $this->CSVContent = $csvlines;
        }
        return $csvlines;
    }

    function WriteCSVFile($filename, $csvdir = '')
    {
        if ($filename != '' && $csvdir == '')
        {
            App::LoadCore("File.class.php");
            $fp = new File(App::getParam("mgcsvdir") . "$filename.csv");
            $writeresult = $fp->Write($this->CSVContent);
            return $writeresult;
        }

        if ($filename != '' && $csvdir != '')
        {
            App::LoadCore("File.class.php");
            $fp = new File($csvdir . $filename);
            $writeresult = $fp->Write($this->CSVContent);
            if ($fp->HasError)
            {
                $this->setError($fp->getError());
            }
            return $writeresult;
        }
    }

    function ParseCSV($filename)
    {
        $lines = null;
        $newline = null;
        if (file_exists($filename))
        {
            App::LoadCore("File.class.php");
            $fp = new File($filename);
            $strfile = $fp->ReadToEnd();
            $strfile = str_replace("\r", "", $strfile);
            $lines = explode("\n", $strfile);

            for ($i = 0; $i < count($lines); $i++)
            {
                if (($this->IgnoreLines == null) || ($this->IgnoreLines < $i))
                {
                    if (!$this->isIgnoreLine($lines[$i]))
                    {
                        $line = explode($this->Delimiter, $lines[$i]);
                        for ($j = 0; $j < count($line); $j++)
                        {
                            $line[$j] = trim($line[$j]);
                        }

                        $newline[] = $line;
                    }
                    //unset($line);
                }
            }
        }
        //unset($lines);
        //App::Pr($newline);
        return $newline;
    }

    function ParseCSVData($data)
    {
        $lines = null;
        $newline = null;
        if ($data != '')
        {
            $strfile = str_replace("\r", "", $data);
            $lines = explode("\n", $strfile);

            for ($i = 0; $i < count($lines); $i++)
            {
                if (($this->IgnoreLines == null) || ($this->IgnoreLines < $i))
                {
                    if (!$this->isIgnoreLine($lines[$i]))
                    {
                        $line = explode($this->Delimiter, $lines[$i]);
                        for ($j = 0; $j < count($line); $j++)
                        {
                            $line[$j] = trim($line[$j]);
                        }

                        $newline[] = $line;
                    }
                    //unset($line);
                }
            }
        }
        //unset($lines);
        //App::Pr($newline);
        return $newline;
    }

    function isIgnoreLine($line)
    {
        $retval = false;
        if ($this->arrIgnoreLineContainingString != null || $this->arrIgnoreLineContainingString != '')
        {
            foreach ($this->arrIgnoreLineContainingString as $key => $val)
            {
                if (strpos($line, $val) > -1)
                {
                    $retval = true;
                }
            }
        }

        if (($retval == false) && (trim($line) == ""))
        {
            $retval = true;
        }

        if (($retval == false) && (count(trim($line)) == 0))
        {
            $retval = true;
        }

        if (($retval == false) && (strpos($line, ",") === false))
        {
            $retval = true;
        }

        return $retval;
    }

}

?>
