<?php

/* * *****************
 * Author: Roger Sanchez
 * Date Created: 2011-06-13
 * Company: Philweb
 * ***************** */

class PDOHandler extends BaseObject
{

    public $host;
    public $username;
    public $password;
    public $dbname;
    private $conn = null;
    public $AffectedRows;
    public $LastInsertID;
    public $QueryString;
    public $DatabaseType = 'mysql';
    public $SingleConnect;
    private $Statement;
    public $isStartTransaction = false;
    public $LastQuery;

    function PDOHandler($connString)
    {
        $pdodb = App::getDBParam($connString);
        if ($pdodb != false)
        {
            $this->host = $pdodb["host"];
            $this->username = $pdodb["username"];
            $this->password = $pdodb["password"];
            $this->dbname = $pdodb["dbname"];
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
        $this->conn = null;
    }

    function Open()
    {
        $connresult = false;
        $connectionstring = "$this->DatabaseType:host=" . $this->host . ";dbname=" . $this->dbname . ", $this->username, $this->password";
        //print_r($connectionstring);
        if ($this->conn == null)
        {
            try
            {
                $this->conn = new PDO("$this->DatabaseType:host=" . $this->host . ";dbname=" . $this->dbname, $this->username, $this->password);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $connresult = true;
            }
            catch (PDOException $e)
            {
                $this->setError($e->getMessage());
            }
            //App::Pr("Starting Connection");
        }
        else
        {
            //App::Pr("Reusing Connection");
            $connresult = true;
        }

        if ($this->isStartTransaction == true)
        {
            //App::Pr("Beginning Transaction: ");
            $this->conn->beginTransaction();
            $this->isStartTransaction = false;
        }
        return $connresult;
    }

    function Close()
    {
        if ($this->conn)
        {
            //$this->conn = NULL;
        }
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

    function RunQuery($query, $params = '')
    {
        if ($this->conn)
        {
            $query = $this->CleanQuery($query);
            $statement = new PDOStatement();

            try
            {
                $statement = $this->conn->prepare($query);
                if (is_array($params) && count($params) > 0)
                {
                    $this->BindParameters($statement, $params);
                }
                //print_r($statement);
                $statement->execute();
                $rows = $statement->fetchAll();
                return $rows;
            }
            catch (PDOException $e)
            {
                $this->setError($e->getMessage());
            }
        }
        else
        {
            return false;
        }
    }

    function RunQueryStatement($query, $params = '')
    {
        if ($this->conn)
        {
            $query = $this->CleanQuery($query);
            $statement = new PDOStatement();

            try
            {
                $statement = $this->conn->prepare($query);
                if (is_array($params) && count($params) > 0)
                {
                    $this->BindParameters($statement, $params);
                }
                $statement->execute();
                $this->AffectedRows = $statement->rowCount();
                $rows = $statement->fetchAll();
                return $rows;
            }
            catch (PDOException $e)
            {
                $this->setError($e->getMessage());
            }
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
            $statement = new PDOStatement();
            try
            {
                $statement = $this->conn->prepare($query);
                $statement->execute();
                $this->AffectedRows = $statement->rowCount();
                return true;
            }
            catch (PDOException $e)
            {
                $this->setError($e->getMessage());
            }
        }
        else
        {
            return false;
        }
    }

    function ExecuteStatement($query, $params = '')
    {
        if ($this->conn)
        {
            $query = $this->CleanQuery($query);
            $statement = new PDOStatement();
            try
            {
                $statement = $this->conn->prepare($query);
                if (is_array($params) && count($params) > 0)
                {
                    $this->BindValues($statement, $params);
                }

                $statement->execute();
                $this->AffectedRows = $statement->rowCount();
                $this->LastQuery = $query;
                return true;
            }
            catch (PDOException $e)
            {
                $this->setError($e->getMessage());
                return false;
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

    function InsertSingle($strTable, $arrNameValue)
    {
        $mysqlreturnid = false;
        if ((count($arrNameValue) > 0) && ($this->conn))
        {
            foreach ($arrNameValue as $key => $val)
            {
                if ($val != '')
                {
                    $strKeys[] = $key;
                    $strVals[] = addslashes($val);
                    $strParams[] = $this->CleanValues($val);
                }
            }

            $query = "insert into $strTable (" . implode(", ", $strKeys) . ") values (" . implode(",", $strParams) . ")";
            $result = $this->ExecuteStatement($query, $strVals);

            if ($result == true)
            {
                $this->LastInsertID = $this->conn->lastInsertId();
                $mysqlreturnid = $this->LastInsertID;
            }
            //App::Pr($query);
        }
        return $mysqlreturnid;
    }

    

    function InsertMultiple($strTable, $arrSingle)
    {
        $result = false;
        if ((count($arrSingle) > 0) && ($this->conn))
        {
            $strInsert = "INSERT INTO $strTable (";
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
                        $strParams[] = $this->CleanValues($val);
                    }
                    $strMultipleVals[] = "('" . implode("','", $strVals) . "')";
                }
            }
            $strInsert .= implode(",", $strKeys) . ") VALUES " . implode(",", $strMultipleVals);
            //print_r($strInsert);
            $result = $this->Execute($strInsert);
        }

        //App::Pr("Inserting Single PDO: ");
        return $result;
    }

    function Update($arrSingle, $identity, $strTable)
    {
        $result = false;
        if ((count($arrSingle) > 0) && ($this->conn))
        {
            $strupdate = null;
            $strwhere = null;
            foreach ($arrSingle as $key => $val)
            {
                $strupdate[] = "`$key`='$val'";
                if ($key == $identity)
                {
                    $strwhere = " WHERE `$key`='$val' ";
                }
                $strUpdate = implode(",", $strupdate);
            }

            $query = "UPDATE $strTable SET $strUpdate $strwhere";
            //App::Pr($query);
            $result = $this->Execute($query);
        }
        return $result;
    }

    function beginTransaction()
    {
        $this->isStartTransaction = true;
    }

    function commit()
    {
        //App::Pr("Committing Transaction: ");
        $this->conn->commit();
    }

    function rollBack()
    {
        //App::Pr("Rolling Back Transaction: ");
        $this->conn->rollBack();
    }

//    function CleanQuery($query)
//    {
//        $query = str_replace("'Now()'", "Now()", $query);
//        $query = str_replace("'now()'", "now()", $query);
//        $query = str_replace("'now_usec()'", "now_usec()", $query);
//        $query = str_replace("'UUID()'", "UUID()", $query);
//        $query = str_replace("'uuid()'", "uuid()", $query);
//        return $query;
//    }
    
    function BindValues($statement, $params)
    {
        $paramctr = 0;
        for ($i = 0; $i < count($params); $i++)
        {
            $param = $params[$i];
            if (!$this->HasFunction($param))
            { 
                $statement->bindValue($paramctr + 1, $param);
                $paramctr++;
            }
        }
    }

    function CleanValues($query)
    {
        if ($this->HasFunction($query))
        {
            return $this->CleanQuery($query);
        }
        else
        {
            return "?";
        }
    }

    function HasFunction($query)
    {
        $strquery = strtolower($query);
        $hasfunction = false;
        if (strpos($strquery, "now()") > -1)
        {
            $hasfunction = true;
        }
        if (strpos($strquery, "now_usec()") > -1)
        {
            $hasfunction = true;
        }
        if (strpos($strquery, "uuid()") > -1)
        {
            $hasfunction = true;
        }
        return $hasfunction;
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
