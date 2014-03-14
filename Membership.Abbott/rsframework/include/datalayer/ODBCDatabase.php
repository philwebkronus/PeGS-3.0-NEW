<?php
/************************************************
Author: Roger Sanchez
Date Created: May 12, 2010
Company: Philweb
**************************************************/

class ODBCDatabase extends BaseObject
{
	var $dsn;
	var $username;
	var $password;
	var $conn;
	
	function ODBCDatabase($connString)
	{
		$connVar = App::getDBParam($connString);
		if ($connVar != false)
		{
			$this->dsn = $connVar["dsn"];
			$this->username = $connVar["username"];
			$this->password = $connVar["password"];
			//$this->dbname = $connVar["dbname"];
		}
	}
	
	function Open()
	{
		$this->conn = odbc_connect($this->dsn, $this->username, $this->password);
		if (odbc_error()) $this->setError(odbc_error());		
	}
	
	function Close()
	{
		odbc_close($this->conn);
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
                $result = odbc_exec($this->conn, $query);
                if (odbc_error()) $this->setError(odbc_error());
                return $result;
            }
            else
            {
                return false;
            }
	}
	
	function Update($update)
	{
	}
	
	function InsertSingle($strTable, $arrSingle)
	{
            $odbcreturnid = -1;
            if ((count($arrSingle) > 0) && ($this->conn))
            {
                foreach($arrSingle as $key => $val)
                {
                    $strKeys[] = $key;
                    $strVals[] = $val;
                }

                $strInsert = "insert into $strTable (" . implode(", ", $strKeys) . ") values ('" . implode("','", $strVals) . "')";
                $odbcreturnid = odbc_query($strInsert);

            }
            return $odbcreturnid;
	}
	
	function InsertMultiple($strTable, $arrSingle)
	{
            $odbcreturnid = -1;
            if ((count($arrSingle) > 0) && ($this->conn))
            {
                $strInsert = "insert into $strTable (";
                $strKeys = "";
                for ($i = 0; $i < count($arrSingle); $i++)
                {
                    $strVals = "";
                    foreach($arrSingle[$i] as $key => $val)
                    {
                        if ($i == 0)
                        {
                            $strKeys[] = $key;
                        }
                        $strVals[] = $val;
                    }
                    $strMultipleVals[] = "('" . implode("','", $strVals) . "')";
                }
                //print_r($strKeys);
                $strInsert .= implode("," , $strKeys) . ") values " . implode("," , $strMultipleVals );
                odbc_query($strInsert);
            }
            return $odbcreturnid;
	}
}

?>