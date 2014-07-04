<?php

class PDOLibrary
{
    /**
     *
     * @var PDO 
     */
    public $_PDO;
    private $_connectionString;
    
    /**
     *
     * @var PDOStatement 
     */
    private $_stmt;

    function __construct( $connectionString )
    {
        $this->_connectionString = explode( ",", $connectionString );        
    }

    function __destruct()
    {
        gc_enable();
        gc_collect_cycles();
        $this->close();
    }

    public function open()
    {
        $bool = false;
        try
        {
            $oconnectionstring1 = $this->_connectionString[0];
            $oconnectionstring2 = $this->_connectionString[1];
            $oconnectionstring3 = $this->_connectionString[2];
            $this->_PDO = new PDO( $oconnectionstring1, $oconnectionstring2, $oconnectionstring3);
            $bool = true;
        }
        catch ( PDOException $e )
        {
            echo $e->getMessage();
        }

        return $bool;
    }

    public function close()
    {
        if ($this->_PDO)
            $this->_PDO = NULL;
    }

   public function executewithparams($sparams)
    {
        return $this->_stmt->execute($sparams);
    }

    public function execute()
    {
        return $this->_stmt->execute();
    }
    
    public function executeQuery( $stmt )
    {
        if ($this->_PDO)
        {
            $this->_stmt = $this->_PDO->query( $stmt );
        }
       else
           throw new Exception("Database connection not yet opened.");
    }
   
    public function deleterec($stmt)
    {
         $this->_stmt = $this->_PDO->exec($stmt);
    }
    
    public function prepare( $stmt )
    {
         $this->_stmt = $this->_PDO->prepare($stmt);  
    }

    public function rowCount()
    {
        return $this->_stmt->rowCount();
    }
    
    public function bindparameter($zid,$zfield)
    {
        $this->_stmt->bindParam($zid,$zfield);        
    }
    
   public function insertedid()
   {
       return $this->_PDO->lastInsertId();  
   }

    public function begintrans()
    {
        $this->_PDO->beginTransaction();
    }   
   
   public function committrans()
   {
       $this->_PDO->commit();
   }
   
   public function rollbacktrans()
   {
       $this->_PDO->rollback();   
   }
   
   
   public function fetchData()
   {
        return $this->_stmt->fetch(PDO::FETCH_ASSOC);
   }

   public function fetchAllData()
   {
        return $this->_stmt->fetchAll(PDO::FETCH_ASSOC);
   }

   public function hasRows()
   {
      return $this->_stmt->fetchColumn();
   }

  //added 07-05-2011 by mtcc for cashier transaction history
  //for fetch per type
    public function fetchPerType( $type )
    {
        return $this->_stmt->fetch($type);
    }
    //for binding parameters
    public function bindParam($parameter,$variable)
    {
        $this->_stmt->bindParam($parameter,$variable);
    }
    
        /**
         * return array
         */
        public function debugPDO()
        {
            return $this->_PDO->errorInfo();
        }
        
        /**
         *
         * @return array 
         */
        public function debugStatement()
        {
            return $this->_stmt->errorInfo();
        }
        
        
        public function getDate()
        {
            $time =microtime(true);
            $micro_time=sprintf("%06d",($time - floor($time)) * 1000000);
            $rawdate = new DateTime( date('Y-m-d H:i:s.'.$micro_time, $time) );
            $date = $rawdate->format("Y-m-d H:i:s.u");
            return $date;
        }
        
        
}

?>