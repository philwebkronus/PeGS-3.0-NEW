<?php

/* * **********************************************
  Author: Roger Sanchez
  Date Created: May 12, 2010
  Company: Philweb
 * ************************************************ */

class MySQLDatabase extends BaseObject
{

    var $host;
    var $username;
    var $password;
    var $dbname;
    var $port = '3306';
    var $conn;
    var $AffectedRows;
    var $LastInsertID;
    var $QueryString;
    public $DieOnError = true;
    public $LastQuery;

    function MySQLDatabase($connString)
    {
        $mydb = App::getDBParam($connString);
        if ($mydb != false)
        {
            $this->host = $mydb["host"];
            $this->username = $mydb["username"];
            $this->password = $mydb["password"];
            $this->dbname = $mydb["dbname"];
            if (isset($mydb["port"]))
            {
                $this->port = $mydb["port"];
            }
        }
        else
        {
            $trace = debug_backtrace();
            die("Connection string $connString does not exist. Please see " . $trace[2]["file"]);
        }
        if (App::getParam("dieondberror") == false)
        {
            $this->DieOnError = false;
        }
    }

    function Open()
    {
        $this->conn = mysql_connect($this->host . ":" . $this->port, $this->username, $this->password, false, 65536);
        if (mysql_error())
        {
            $this->setError(mysql_error());
        }

        mysql_select_db($this->dbname);
        if (mysql_error())
        {
            $this->setError(mysql_error());
            
        }
    }

    function Close()
    {
        mysql_close($this->conn);
    }

    function isConnected()
    {
        if ($this->conn)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function Query($query)
    {
        if ($this->conn)
        {

            $query = $this->CleanQuery($query);

            if ($this->DieOnError == true)
            {
                $result = mysql_query($query) or die(mysql_error());
            }
            else
            {
                $result = mysql_query($query);
            }
            $this->AffectedRows = mysql_affected_rows();
            $this->LastInsertID = mysql_insert_id();
            $this->LastQuery = $query;
            if (mysql_error())
                $this->setError(mysql_error());
            return $result;
        }
        else
        {
            return false;
        }
    }

    function RunQuery($query)
    {
        if ($this->conn)
        {
            $rows = null;

            $query = $this->CleanQuery($query);

            $result = mysql_query($query) or die(mysql_error());
            if (mysql_error())
                $this->setError(mysql_error());
            while ($row = mysql_fetch_array($result))
            {
                $rows[] = $row;
            }
            mysql_free_result($result);
            return $rows;
        }
        else
        {
            return false;
        }
    }

    function Execute($query)
    {
        if ($this->conn)
        {

            $query = $this->CleanQuery($query);

            mysql_query($query) or die(mysql_error());
            if (mysql_error())
            {
                $this->setError(mysql_error());
                return false;
            }
            else
            {
                $this->LastInsertID = mysql_insert_id();
                $this->AffectedRows = mysql_affected_rows();
                return true;
            }
        }
        else
        {
            return false;
        }
    }

    function RunQueryProc($procname, $arrParams = '')
    {
        if (is_array($arrParams))
        {
            for ($i = 0; $i < count($arrParams); $i++)
            {
                if (strlen($arrParams[$i]) > 0)
                {
                    if ($arrParams[$i][0] == "@")
                    {
                        $arrParams[$i] = "$arrParams[$i]";
                    }
                    else
                    {
                        $arrParams[$i] = "'$arrParams[$i]'";
                    }
                }
                else
                {
                    $arrParams[$i] = "'$arrParams[$i]'";
                }
            }
        }
        if (count($arrParams) > 0 && $arrParams != '')
        {
            $query = "CALL $procname(" . implode(",", $arrParams) . ");";
        }
        else
        {
            $query = "CALL $procname();";
        }
        return $this->RunQuery($query);
    }

    function ExecuteProc($procname, $arrParams = '')
    {
        if (is_array($arrParams))
        {
            if ($arrParams[$i][0] == "@")
            {
                $arrParams[$i] = "$arrParams[$i]";
            }
            else
            {
                $arrParams[$i] = "'$arrParams[$i]'";
            }
        }
        if (count($arrParams) > 0 && $arrParams != '')
        {
            $query = "CALL $procname(" . implode(",", $arrParams) . ");";
        }
        else
        {
            $query = "CALL $procname();";
        }
        return $this->Execute($query);
    }

    function Update($arrSingle, $identity, $strTable)
    {
        $mysqlreturnid = -1;
        if ((count($arrSingle) > 0) && ($this->conn))
        {
            $strupdate = null;
            $strwhere = null;
            foreach ($arrSingle as $key => $val)
            {
                $strupdate[] = "`$key`='$val'";
                if ($key == $identity)
                {
                    $strwhere = " where `$key`='" . addslashes($val) . "' ";
                }
                $strUpdate = implode(",", $strupdate);
            }

            $query = "update $strTable set $strUpdate $strwhere";
            //App::Pr($query);
            $mysqlreturnid = $this->Query($query); //mysql_query($strInsert);
        }
        return $mysqlreturnid;
    }

    function InsertSingle($strTable, $arrSingle)
    {
        $mysqlreturnid = -1;
        if ((count($arrSingle) > 0) && ($this->conn))
        {
            foreach ($arrSingle as $key => $val)
            {
                if ($val != '')
                {
                    $strKeys[] = $key;
                    $strVals[] = addslashes($val);
                }
            }

            $strInsert = "insert into $strTable (" . implode(", ", $strKeys) . ") values ('" . implode("','", $strVals) . "')";
            $mysqlreturnid = $this->Query($strInsert); //mysql_query($strInsert);
            //if (mysql_error()) $this->setError(mysql_error());
        }
        return $mysqlreturnid;
    }

    function InsertMultiple($strTable, $arrSingle)
    {
        $mysqlreturnid = -1;
        if ((count($arrSingle) > 0) && ($this->conn))
        {
            $strInsert = "insert into $strTable (";
            $strKeys = "";
            for ($i = 0; $i < count($arrSingle); $i++)
            {
                $strVals = "";
                if (is_array($arrSingle[$i]) && ($arrSingle[$i] != ''))
                {
                    foreach ($arrSingle[$i] as $key => $val)
                    {
                        if ($i == 0)
                        {
                            $strKeys[] = $key;
                        }
                        $strVals[] = addslashes($val);
                    }
                    $strMultipleVals[] = "('" . implode("','", $strVals) . "')";
                }
            }
            $strInsert .= implode(",", $strKeys) . ") values " . implode(",", $strMultipleVals);
            //print_r($strInsert);
            $this->Query($strInsert);
        }
        return $mysqlreturnid;
    }

    private function CleanQuery($query)
    {
        $query = str_replace("'Now()'", "Now()", $query);
        $query = str_replace("'now()'", "now()", $query);
        $query = str_replace("'now_usec()'", "now_usec()", $query);
        $query = str_replace("'UUID()'", "UUID()", $query);
        $query = str_replace("'uuid()'", "uuid()", $query);
        
        if (App::getParam("useapplicationdatetime") == true)
        {
            $ds = new DateSelector();
            $nowusec = $ds->GetNowUSec();
            $now = $ds->GetNowUSec(false);
            $query = str_replace("now_usec()", "'" . $nowusec . "'", $query);
            $query = str_replace("now()", "'" . $now . "'", $query);
        }
        
        if (App::getParam("applicationmicroseconds") == true)
        {
            $query = str_replace("now_usec()", "'" . $this->GetNowUSec() . "'", $query);
        }
        return $query;
    }

    function GetNowUSec($format = 'Y-m-d H:i:s.u', $utimestamp = null, $timezone = '')
    {
        if (is_null($utimestamp))
        {
            $utimestamp = microtime(true);
        }

        $timestamp = floor($utimestamp);
        $milliseconds = round(($utimestamp - $timestamp) * 1000000);

        return date(preg_replace('`(?<!\\\\)u`', $milliseconds, $format), $timestamp);
    }

}

?>