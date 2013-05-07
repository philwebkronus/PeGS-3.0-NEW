<?php

/* * ***************************
 * Author: Roger Sanchez
 * Date Created: 08 15, 10
 * Company: Philweb
 * *************************** */

class BaseEntity extends BaseObject
{

    /**
     * The Identity field of the model. Relates to the Identity of a table.
     * @var String 
     */
    public $Identity;
    /**
     * The Name of the table of the model as related to the database.
     * @var String 
     */
    public $TableName;
    /**
     * @var String The array connection string of the Model.
     */
    public $ConnString;
    public $FoundRows;
    /**
     *
     * @var Integer The number of rows affected on the last query.
     */
    public $AffectedRows;
    /**
     *
     * @var Integer The ID of the Last Inserted Record
     */
    public $LastInsertID;
    public $PDODatabaseType;
    /**
     * The database type of the object.
     * @var DatabaseTypes 
     * @example DatabaseTypes::MySQL, DatabaseTypes::PDO, DatabaseTypes::ODBC 
     */
    public $DatabaseType;
    public $PDODB = null;
    private $isStartTransaction = false;
    public $LastQuery;

    function __construct()
    {
        $this->PDODB = null;
    }

    function GetDBName()
    {
        $pdodb = App::getDBParam($this->ConnString);
        if ($pdodb != false)
        {
            return $pdodb["dbname"];
        }
        else
        {
            return false;
        }
    }

    function Insert($arrEntries)
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $mydb->InsertSingle($this->TableName, $arrEntries);
        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        $this->LastInsertID = $mydb->LastInsertID;
        $this->AffectedRows = $mydb->AffectedRows;
        return $mydb->LastInsertID;
    }

    function InsertMultiple($arrEntries)
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $mydb->InsertMultiple($this->TableName, $arrEntries);
        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        $this->AffectedRows = $mydb->AffectedRows;
        return $mydb->AffectedRows;
    }

    function InsertMultipleBatches($arrEntries, $batchcount = 1000)
    {
        $linecount = count($arrEntries);
        $pg = new Pager($batchcount, $linecount);
        $affectedrows = 0;
        $successful = false;

        while ($pg->NextPage())
        {
            $newlines = null;
            $fromline = $pg->ItemFrom - 1;
            $toline = $pg->ItemTo - 1;

            for ($j = $fromline; $j <= $toline; $j++)
            {
                $newlines[] = $arrEntries[$j];
            }

            if (count($newlines) > 0)
            {
                //App::Pr("Processing lines $pg->ItemFrom to $pg->ItemTo <br>");
                $this->InsertMultiple($newlines);
                $affectedrows += $this->AffectedRows;
                if ($this->HasError)
                {
                    App::Pr($this->getError() . "From Line: $fromline to $toline");
                    $this->HasError = false;
                    $this->errormessage = "";
                }
            }
        }
        $this->AffectedRows = $affectedrows;

        return!$this->HasError;
    }

    protected function Update()
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $mydb->Update($this->ConvertToArray(), $this->Identity, $this->TableName);
        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        $this->AffectedRows = $mydb->AffectedRows;
        return $mydb->AffectedRows;
    }

    function UpdateByArray($arrEntries)
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $mydb->Update($arrEntries, $this->Identity, $this->TableName);
        $this->LastQuery = $mydb->LastQuery;
        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        $this->AffectedRows = $mydb->AffectedRows;
        return $mydb->AffectedRows;
    }

    function SelectByID($id)
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $query = "Select * from `$this->TableName` where `$this->Identity` = '$id'";
//$result = $mydb->Query($query);
        $rows = null;
        if ($this->DatabaseType == DatabaseTypes::MySQL)
        {
            $rows = $mydb->RunQuery($query);
        }
        if ($this->DatabaseType == DatabaseTypes::PDO)
        {
            $rows = $mydb->RunQuery($query);
        }
        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        return $rows;
    }

    function SelectAll()
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $query = "Select * from `$this->TableName`";
//$result = $mydb->Query($query);
        $rows = null;
        if ($this->DatabaseType == DatabaseTypes::MySQL)
        {
            $rows = $mydb->RunQuery($query);
        }
        if ($this->DatabaseType == DatabaseTypes::PDO)
        {
            $rows = $mydb->RunQuery($query);
        }
        $mydb->Close();
        if ($mydb->HasError)
        {
            $this->setError($mydb->getError());
        }
        return $rows;
    }

    function SelectLimit($itemfrom, $rowcount)
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $query = "Select * from `$this->TableName` limit $itemfrom, $rowcount";
//$result = $mydb->Query($query);
        $rows = null;
        if ($this->DatabaseType == DatabaseTypes::MySQL)
        {
            $rows = $mydb->RunQuery($query);
        }

        if ($this->DatabaseType == DatabaseTypes::PDO)
        {
            $rows = $mydb->RunQuery($query);
        }
        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        return $rows;
    }

    function SelectByWhere($where)
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $query = "Select * from `$this->TableName` $where";
        $rows = null;

        if ($this->DatabaseType == DatabaseTypes::MySQL)
        {
            $rows = $mydb->RunQuery($query);
        }
        if ($this->DatabaseType == DatabaseTypes::PDO)
        {
            $rows = $mydb->RunQuery($query);
        }

        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        return $rows;
    }

    function SelectByStatus($status)
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $query = "Select * from `$this->TableName` WHERE `Status` = '$status'";
//$result = $mydb->Query($query);
        $rows = null;

        if ($this->DatabaseType == DatabaseTypes::MySQL)
        {
            $rows = $mydb->RunQuery($query);
        }
        if ($this->DatabaseType == DatabaseTypes::PDO)
        {
            $rows = $mydb->RunQuery($query);
        }

        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        return $rows;
    }

    function RunQuery($query, $queryonlynamedfields = false)
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $result = $mydb->RunQuery($query);
        if ($queryonlynamedfields == true)
        {
            $result = $this->ClearNumericFields($result);
        }
        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        $this->AffectedRows = $mydb->AffectedRows;
        return $result;
    }

    function RunQueryProc($procname, $params = '')
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $result = $mydb->RunQueryProc($procname, $params);
        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        $this->AffectedRows = $mydb->AffectedRows;
        return $result;
    }

    function ExecuteQuery($query)
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $result = $mydb->Execute($query);
        $this->AffectedRows = $mydb->AffectedRows;
        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        return $result;
    }

    function ExecuteProc($procname, $params = '')
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $result = $mydb->ExecuteProc($procname, $params);
        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        $this->AffectedRows = $mydb->AffectedRows;
        return $result;
    }

    function DeleteByID($id)
    {
        $mydb = $this->InitDatabase();
        $mydb->Open();
        $query = "DELETE from `$this->TableName` where `$this->Identity` = '$id'";
        $result = $mydb->Query($query);
        $rows = null;
        while ($row = mysql_fetch_array($result))
        {
            $rows[] = $row;
        }
        $mydb->Close();
        if ($mydb->getError())
        {
            $this->setError($mydb->getError());
        }
        return $rows;
    }

    private function InitDatabase()
    {
        $mydb = false;
        if ($this->DatabaseType == DatabaseTypes::PDO)
        {
            App::LoadDataClass("PDOHandler.php");
            $mydb = new PDOHandler($this->ConnString);
            if (isset($this->PDODatabaseType))
            {
                $mydb->DatabaseType = $this->PDODatabaseType;
            }

            //$this->PDODB = null;
            if ($this->PDODB == null)
            {
                //App::Pr("Creating PDO:");
                $this->PDODB = $mydb;
            }
            else
            {
                //App::Pr("Existing PDO:");
                $mydb = $this->PDODB;
            }

            if ($this->isStartTransaction == true)
            {
                $mydb->isStartTransaction = $this->isStartTransaction;
                $this->isStartTransaction = false;
            }
        }
        elseif ($this->DatabaseType == DatabaseTypes::ODBC)
        {
            App::LoadDataClass("ODBCDatabase.php");
            $mydb = new ODBCDatabase($this->ConnString);
        }
        else
        {
            $this->DatabaseType = DatabaseTypes::MySQL;
            App::LoadDataClass("MySQLDatabase.php");
            $mydb = new MySQLDatabase($this->ConnString);
        }

        return $mydb;
    }

    function ConvertToArray()
    {
        $objList = $this;
        $classname = get_class($objList);
        $objprops = get_class_vars($classname);

        $bvars = get_class_vars("BaseEntity");
        $data = null;
        foreach ($objprops as $key => $value)
        {
            if ($key != null && (!key_exists($key, $bvars)))
            {
                $data[$key] = $objList->{$key};
            }
        }
        return $data;
    }

    function ArrayToObject($arrEntries)
    {
        $classname = get_class($this);
        eval('$objList = new ' . $classname . '();');

        $objprops = get_class_vars($classname);

        foreach ($arrEntries as $key => $val)
        {
            if (key_exists($key, $objprops))
            {
                $objList->{$key} = $arrEntries[$key];
            }
        }

        return $objList;
    }

    function StartTransaction()
    {
        $this->isStartTransaction = true;
    }

    function CommitTransaction()
    {
        if ($this->DatabaseType == DatabaseTypes::PDO)
        {
            $this->PDODB->commit();
        }
    }

    function RollBackTransaction()
    {
        if ($this->DatabaseType == DatabaseTypes::PDO)
        {
            $this->PDODB->rollBack();
        }
    }

    private function ClearNumericFields($result)
    {
        $retval = $result;
        if (is_array($retval))
        {
            for ($i = 0; $i < count($retval); $i++)
            {
                $row = $retval[$i];
                if (is_array($row))
                {
                    foreach ($row as $key => $var)
                    {
                        if (is_numeric($key))
                        {
                            unset($row[$key]);
                        }
                    }
                }
                $retval[$i] = $row;
            }
        }
        return $retval;
    }

}

?>
