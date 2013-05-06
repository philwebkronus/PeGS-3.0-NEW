<?php

class MI_Model {
    /**
     *
     * @var array
     */
    protected static $_cache_query = array();
    
    protected static $_set_query_cachable = MIRAGE_DEBUG;
    
    protected $_db = 'db';
    
    /**
     * @var PDOStatement
     */
    public $sth = null;
    
    /**
     * @var PDO
     */
    public $dbh;
    
    /**
     * @var int Number of field 
     */
    public $error_count = 0;
    public $error_messages = array();
    
    public static function getCachedQuery() {
        return self::$_cache_query;
    }
    
    /***************************************************************************
    * Original Author: Bryan Salazar
    * Creation Date: June 2, 2011
    * Return: array (validation)
    * Description: You should override this method to declare validation.
    *
    * Example:
    *    protected function _validation()
    *    {
    *       return array(
    *           array('fields'=>array('username','password'),
    *               'validator'=>'StringValidation',
    *           ),
    *       );
    *    }
    ***************************************************************************/
    protected function _validation() {
        return array();
    }    
    
    /***************************************************************************
    * Original Author: Bryan Salazar
    * Creation Date: June 2, 2011
    * Parameter: array $attributes (attributes you want to validate)
    * Return: boolean
    * Description: Return true if there is no error else false.
    *             This method will call executeValidation($attributes)
    ***************************************************************************/
    public function isValid($attributes) {
        $this->executeValidation($attributes);
        if($this->error_count) {
            return false;
        }
        return true;
    }    
   
    /***************************************************************************
    * Original Author: Bryan Salazar
    * Creation Date: June 2, 2011
    * Return: string $html
    * Description: Will display all error messages. Override this method if
    *             you want different display
    ***************************************************************************/
    public function getErrorMessages() {
        if(!$this->error_count)
            return false;
        $html = '<ul style="color: red">';
        foreach($this->error_messages as $attribute => $message) {
            $html.='<li>' . $message . '</li>';
        }
        $html.='</ul>';
        return $html;
    }
   
    /***************************************************************************
    * Original Author: Bryan Salazar
    * Creation Date: June 2, 2011
    * Parameter: string $attribute (attribute you want to get error message)
    * Return: string $error
    * Description: Will display error message for this attribute
    ***************************************************************************/
    public function getAttributeErrorMessage($attribute){
        if(!isset($this->error_messages[$attribute]))
            return '';
        return $this->error_messages[$attribute];
    }
    
    public function setAttributeErrorMessage($attribute,$error_message) {
        $this->error_count++;
        $this->error_messages[$attribute] = $error_message;
    }
    
    /***************************************************************************
    * Original Author: Bryan Salazar
    * Creation Date: June 2, 2011
    * Parameter: array $attributes
    * Description: Excute all validation for attribute define in _validation()
    ***************************************************************************/
    protected function executeValidation($attributes) {
        $validators = array();
        $validations = $this->_validation();
        if(MIRAGE_DEBUG == true) {
            foreach($attributes as $attribute) {
                if(!property_exists($this, $attribute)) {
                    $class_name = get_class($this);
                    throw new Exception('Model '. $class_name .' has no property ' . $attribute);
                }
            }
        }

        // each model attribute define is isValid
        foreach($attributes as $attribute) {
            
            // validations defined in model
            foreach($validations as $validation) {
                
                // check if attribute want to validate has validation defined
                if(in_array($attribute, $validation['fields'])) {
                    
                    // check if validator is already created
                    if(!isset($validators[$validation['validator']])) {
                        $validators_path = Mirage::app()->getAppPath() . DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR;
                        if(!file_exists($validators_path . $validation['validator'] .'.php'))
                            throw new Exception($validation['validator'] . ' not found in ' . $validators_path . 
                        $validation['validator'] .'.php');
                        include $validators_path . $validation['validator'] .'.php';
                        // create instance of validator
                        $validator = new $validation['validator'];
                        $validators[$validation['validator']] = $validator;
                        
                    // validator is already created    
                    } else {
                        $validator = $validators[$validation['validator']];
                    }

                    try {
                        $options = array();
                        $message = '';
                        if(isset($validation['options'])) {
                            $options = $validation['options'];
                        }
                        if(isset($validation['message'])) {
                            $message = $validation['message'];
                        }
                        $validator->rules($this,$attribute,$options,$message);
                    // catch exception throw by custom validation
                    } catch(Exception $e) {
                        $this->error_messages[$attribute] = $e->getMessage();
                        $this->error_count++;
                        break;
                    }
                } // end if $attribute is in $validation['fields']
            }// end foreach $validations
        }// end foreach $attributes
    }   
    
    public function getLastInsertId() {
        return $this->dbh->lastInsertId();
    }
    
    /***************************************************************************
    * Original Author: Bryan Salazar
    * Date Creation: May 31, 2011
    * Parameter: array (attribute name)
    * Description: Create property on the fly and assign value
    ***************************************************************************/
    public function setAttributes(array $attributes) {
        foreach($attributes as $key => $value) {
            if(!property_exists($this, $key)) {
                $file = realpath(dirname(__FILE__)) . '/' . __FILE__;
                throw new Exception(get_class($this) . ' has no property "' . $key . '" in ' . $file);
            }
            $this->$key = $value;
        }
    }
    
    /**
     * Description: execute query
     * @param string $sql
     * @param array $param
     * @return PDOStatement 
     */
    public function exec($sql,$param=null) {
        $config = Mirage::app()->getConfig();
        MI_Database::connect($config[$this->_db]);
        
        $handler = MI_Database::$dbh;
        $this->dbh = MI_Database::$dbh;
        
        $sth = $handler->prepare($sql);
        
        $error = $this->dbh->errorInfo();
        if(!$sth) {
            if(isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if(self::$_set_query_cachable) {
            $time_start = microtime(true);
        }
           
        if(is_array($param)) {
            $result = $sth->execute($param);
            if(self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        } else {
            $result = $sth->execute();
            if(self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        }
        $this->sth = $sth;
        
        if(!$result) {
            if(isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if(self::$_set_query_cachable) {
            self::$_cache_query[] = array('statement'=>$sql,'parameter'=>$param,'time_executed'=>$time_executed . ' sec.');
        }
//            return $result;
        return $sth;
    }
    
    /**
     * Description: debug the PDO
     * @return string 
     */
    public static function debug() {
        return debug(MI_Database::$dbh->errorInfo());
    }
    
    /**
     * Description: fetchAll
     * @return array 
     */
    public function findAll() {
        return $this->sth->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     *
     * @return int
     */
    public function rowCount() {
        return $this->sth->rowCount();
    }
    
    /**
     * Description: fetch
     * @return array 
     */
    public function find() {
        return $this->sth->fetch(PDO::FETCH_ASSOC);
    }
    
    public function beginTransaction(){
        if(!isset($this->dbh)){
            $config = Mirage::app()->getConfig();
            MI_Database::connect($config[$this->_db]);
            $this->dbh = MI_Database::$dbh;
        }
        return $this->dbh->beginTransaction();
    }
    
    /**
     * Description: Close connection
     */
    public function close() {
        MI_Database::close();
    }
}
