<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 07 8, 10
 * Company: Philweb
 * *************************** */

class FTP extends BaseObject
{

    var $username;
    var $password;
    var $host;
    var $conn;

    function FTP()
    {
        
    }

    function Connect($host, $username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;

        $link = ftp_connect($host);

        if ($link != false)
        {
            $conn = ftp_login($link, $username, $password);
            $this->conn = $link;
            return true;
        }
        else
        {
            $this->setError("Error connecting to FTP server");
            return false;
        }
    }

    function DisplayFiles()
    {
        $rawlist = ftp_nlist($this->conn, ".");
        return $rawlist;
    }

    function ChangeDir($directory)
    {
        ftp_chdir($this->conn, $directory);
    }

    function GetFile($local_file, $remote_file, $mode = FTP_BINARY)
    {
        return ftp_get($this->conn, $local_file, $remote_file, $mode);
    }
    
    function PutFile($local_file, $remote_file, $mode = FTP_BINARY)
    {
        $retval = ftp_put($this->conn, $remote_file, $local_file, $mode);
        if ($retval != true)
        {
            $this->setError("Upload failed.");
        }
        return $retval;
    }

    function Close()
    {
        if ($this->conn)
        {
            ftp_close($this->conn);
        }
    }

    function ftp_mkdir_recusive($path)
    {
        $con_id = $this->conn;
        $parts = explode("/", $path);
        $return = true;
        $fullpath = "";
        foreach ($parts as $part)
        {
            if (empty($part))
            {
                $fullpath .= "/";
                continue;
            }
            $fullpath .= $part . "/";
            if (@ftp_chdir($con_id, $fullpath))
            {
                if (ftp_chdir($con_id, $fullpath))
                {
                    
                }
                else
                {
                    $this->setError("Cannot go to directory $fullpath");
                }
            }
            else
            {
                if (@ftp_mkdir($con_id, $part))
                {
                    ftp_chdir($con_id, $part);
                }
                else
                {
                    $return = false;
                }
            }
        }
        return $return;
    }

}

?>
