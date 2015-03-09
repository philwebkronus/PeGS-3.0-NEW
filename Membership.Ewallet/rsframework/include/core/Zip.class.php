<?php
/*****************************
 * Author: Roger Sanchez
 * Date Created: 10 27, 10
 * Company: Philweb
 *****************************/
class Zip extends BaseObject
{
    var $FileName;

    function Zip($filename)
    {
        $this->FileName = $filename;
    }

    function UnzipTo($localpath)
    {
        $zip = zip_open($this->FileName);

        if ($zip)
        {
            while ($zip_entry = zip_read($zip))
            {
                $fp = fopen($localpath . zip_entry_name($zip_entry) , "w");
                if (zip_entry_open($zip, $zip_entry, "r"))
                {
                  $buf = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));
                  fwrite($fp,"$buf");
                  zip_entry_close($zip_entry);
                  fclose($fp);
                }
            }
          zip_close($zip);
        }
    }
}
?>
