<?php

class MI_Model {

    /**
     *
     * @var array
     */
    protected static $_cache_query = array();
    protected static $_set_query_cachable = MIRAGE_DEBUG;
    protected $_db = 'db';
    protected $_db2 = 'db2';
    protected $_db3 = 'db3';
    protected $_db4 = 'db4';
    protected $_db5 = 'db5';

    /**
     * @var PDOStatement
     */
    public $sth = null;
    public $sth2 = null;
    public $sth3 = null;
    public $sth4 = null;
    public $sth5 = null;

    /**
     * @var PDO
     */
    public $dbh;
    public $dbh2;
    public $dbh3;
    public $dbh4;
    public $dbh5;

    /**
     * @var int Number of field 
     */
    public $error_count = 0;
    public $error_messages = array();

    public static function getCachedQuery() {
        return self::$_cache_query;
    }

    /*     * *************************************************************************
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
     * ************************************************************************* */

    protected function _validation() {
        return array();
    }

    /*     * *************************************************************************
     * Original Author: Bryan Salazar
     * Creation Date: June 2, 2011
     * Parameter: array $attributes (attributes you want to validate)
     * Return: boolean
     * Description: Return true if there is no error else false.
     *             This method will call executeValidation($attributes)
     * ************************************************************************* */

    public function isValid($attributes) {
        $this->executeValidation($attributes);
        if ($this->error_count) {
            return false;
        }
        return true;
    }

    /*     * *************************************************************************
     * Original Author: Bryan Salazar
     * Creation Date: June 2, 2011
     * Return: string $html
     * Description: Will display all error messages. Override this method if
     *             you want different display
     * ************************************************************************* */

    public function getErrorMessages() {
        if (!$this->error_count)
            return false;
        $html = '<ul style="color: red">';
        foreach ($this->error_messages as $attribute => $message) {
            $html.='<li>' . $message . '</li>';
        }
        $html.='</ul>';
        return $html;
    }

    /*     * *************************************************************************
     * Original Author: Bryan Salazar
     * Creation Date: June 2, 2011
     * Parameter: string $attribute (attribute you want to get error message)
     * Return: string $error
     * Description: Will display error message for this attribute
     * ************************************************************************* */

    public function getAttributeErrorMessage($attribute) {
        if (!isset($this->error_messages[$attribute]))
            return '';
        return $this->error_messages[$attribute];
    }

    public function setAttributeErrorMessage($attribute, $error_message) {
        $this->error_count++;
        $this->error_messages[$attribute] = $error_message;
    }

    /*     * *************************************************************************
     * Original Author: Bryan Salazar
     * Creation Date: June 2, 2011
     * Parameter: array $attributes
     * Description: Excute all validation for attribute define in _validation()
     * ************************************************************************* */

    protected function executeValidation($attributes) {
        $validators = array();
        $validations = $this->_validation();
        if (MIRAGE_DEBUG == true) {
            foreach ($attributes as $attribute) {
                if (!property_exists($this, $attribute)) {
                    $class_name = get_class($this);
                    throw new Exception('Model ' . $class_name . ' has no property ' . $attribute);
                }
            }
        }

        // each model attribute define is isValid
        foreach ($attributes as $attribute) {

            // validations defined in model
            foreach ($validations as $validation) {

                // check if attribute want to validate has validation defined
                if (in_array($attribute, $validation['fields'])) {

                    // check if validator is already created
                    if (!isset($validators[$validation['validator']])) {
                        $validators_path = Mirage::app()->getAppPath() . DIRECTORY_SEPARATOR . 'validators' . DIRECTORY_SEPARATOR;
                        if (!file_exists($validators_path . $validation['validator'] . '.php'))
                            throw new Exception($validation['validator'] . ' not found in ' . $validators_path .
                            $validation['validator'] . '.php');
                        include $validators_path . $validation['validator'] . '.php';
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
                        if (isset($validation['options'])) {
                            $options = $validation['options'];
                        }
                        if (isset($validation['message'])) {
                            $message = $validation['message'];
                        }
                        $validator->rules($this, $attribute, $options, $message);
                        // catch exception throw by custom validation
                    } catch (Exception $e) {
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

    /*     * *************************************************************************
     * Original Author: Bryan Salazar
     * Date Creation: May 31, 2011
     * Parameter: array (attribute name)
     * Description: Create property on the fly and assign value
     * ************************************************************************* */

    public function setAttributes(array $attributes) {
        foreach ($attributes as $key => $value) {
            if (!property_exists($this, $key)) {
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
    public function exec($sql, $param = null) {
        $config = Mirage::app()->getConfig();
        MI_Database::connect($config[$this->_db]);

        $handler = MI_Database::$dbh;
        $this->dbh = MI_Database::$dbh;

        $sth = $handler->prepare($sql);

        $error = $this->dbh->errorInfo();
        if (!$sth) {
            if (isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if (self::$_set_query_cachable) {
            $time_start = microtime(true);
        }

        if (is_array($param)) {
            $result = $sth->execute($param);
            if (self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        } else {
            $result = $sth->execute();
            if (self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        }
        $this->sth = $sth;

        if (!$result) {
            if (isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if (self::$_set_query_cachable) {
            self::$_cache_query[] = array('statement' => $sql, 'parameter' => $param, 'time_executed' => $time_executed . ' sec.');
        }
//            return $result;
        return $sth;
    }

    /**
     * Description: execute query
     * @param string $sql
     * @param array $param
     * @return PDOStatement 
     */
    public function exec2($sql, $param = null) {
        $config = Mirage::app()->getConfig();
        MI_Database::connect2($config[$this->_db2]);

        $handler = MI_Database::$dbh2;
        $this->dbh2 = MI_Database::$dbh2;

        $sth = $handler->prepare($sql);

        $error = $this->dbh2->errorInfo();
        if (!$sth) {
            if (isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if (self::$_set_query_cachable) {
            $time_start = microtime(true);
        }

        if (is_array($param)) {
            $result = $sth->execute($param);
            if (self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        } else {
            $result = $sth->execute();
            if (self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        }
        $this->sth2 = $sth;

        if (!$result) {
            if (isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if (self::$_set_query_cachable) {
            self::$_cache_query[] = array('statement' => $sql, 'parameter' => $param, 'time_executed' => $time_executed . ' sec.');
        }
//            return $result;
        return $sth;
    }

    /**
     * Description: execute query
     * @param string $sql
     * @param array $param
     * @return PDOStatement 
     */
    public function exec3($sql, $param = null) {
        $config = Mirage::app()->getConfig();
        MI_Database::connect3($config[$this->_db3]);

        $handler = MI_Database::$dbh3;
        $this->dbh3 = MI_Database::$dbh3;

        $sth = $handler->prepare($sql);

        $error = $this->dbh3->errorInfo();
        if (!$sth) {
            if (isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if (self::$_set_query_cachable) {
            $time_start = microtime(true);
        }

        if (is_array($param)) {
            $result = $sth->execute($param);
            if (self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        } else {
            $result = $sth->execute();
            if (self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        }
        $this->sth3 = $sth;

        if (!$result) {
            if (isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if (self::$_set_query_cachable) {
            self::$_cache_query[] = array('statement' => $sql, 'parameter' => $param, 'time_executed' => $time_executed . ' sec.');
        }
//            return $result;
        return $sth;
    }

    /**
     * Description: execute query
     * @param string $sql
     * @param array $param
     * @return PDOStatement 
     */
    public function exec4($sql, $param = null) {
        $config = Mirage::app()->getConfig();
        MI_Database::connect4($config[$this->_db4]);

        $handler = MI_Database::$dbh4;
        $this->dbh4 = MI_Database::$dbh4;

        $sth = $handler->prepare($sql);

        $error = $this->dbh4->errorInfo();
        if (!$sth) {
            if (isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if (self::$_set_query_cachable) {
            $time_start = microtime(true);
        }

        if (is_array($param)) {
            $result = $sth->execute($param);
            if (self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        } else {
            $result = $sth->execute();
            if (self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        }
        $this->sth4 = $sth;

        if (!$result) {
            if (isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if (self::$_set_query_cachable) {
            self::$_cache_query[] = array('statement' => $sql, 'parameter' => $param, 'time_executed' => $time_executed . ' sec.');
        }
//            return $result;
        return $sth;
    }

    /**
     * Description: execute query
     * @param string $sql
     * @param array $param
     * @return PDOStatement 
     */
    public function exec5($sql, $param = null) {
        $config = Mirage::app()->getConfig();
        MI_Database::connect5($config[$this->_db5]);

        $handler = MI_Database::$dbh5;
        $this->dbh5 = MI_Database::$dbh5;

        $sth = $handler->prepare($sql);

        $error = $this->dbh5->errorInfo();
        if (!$sth) {
            if (isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if (self::$_set_query_cachable) {
            $time_start = microtime(true);
        }

        if (is_array($param)) {
            $result = $sth->execute($param);
            if (self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        } else {
            $result = $sth->execute();
            if (self::$_set_query_cachable) {
                $time_end = microtime(true);
                $time_executed = $time_end - $time_start;
            }
        }
        $this->sth5 = $sth;

        if (!$result) {
            if (isset($error['2'])) {
                MI_Logger::sysLog($error['2'], E_ERROR);
            }
            Mirage::app()->error500();
        }
        if (self::$_set_query_cachable) {
            self::$_cache_query[] = array('statement' => $sql, 'parameter' => $param, 'time_executed' => $time_executed . ' sec.');
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

    public static function debug2() {
        return debug(MI_Database::$dbh2->errorInfo());
    }

    public static function debug3() {
        return debug(MI_Database::$dbh3->errorInfo());
    }

    public static function debug4() {
        return debug(MI_Database::$dbh4->errorInfo());
    }

    public static function debug5() {
        return debug(MI_Database::$dbh5->errorInfo());
    }

    /**
     * Description: fetchAll
     * @return array 
     */
    public function findAll() {
        return $this->sth->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAll2() {
        return $this->sth2->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAll3() {
        return $this->sth3->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAll4() {
        return $this->sth4->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findAll5() {
        return $this->sth5->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     *
     * @return int
     */
    public function rowCount() {
        return $this->sth->rowCount();
    }

    public function rowCount2() {
        return $this->sth2->rowCount();
    }

    public function rowCount3() {
        return $this->sth3->rowCount();
    }

    public function rowCount4() {
        return $this->sth4->rowCount();
    }

    public function rowCount5() {
        return $this->sth5->rowCount();
    }

    /**
     * Description: fetch
     * @return array 
     */
    public function find() {
        return $this->sth->fetch(PDO::FETCH_ASSOC);
    }

    public function find2() {
        return $this->sth2->fetch(PDO::FETCH_ASSOC);
    }

    public function find3() {
        return $this->sth3->fetch(PDO::FETCH_ASSOC);
    }

    public function find4() {
        return $this->sth4->fetch(PDO::FETCH_ASSOC);
    }

    public function find5() {
        return $this->sth5->fetch(PDO::FETCH_ASSOC);
    }

    public function beginTransaction() {
        if (!isset($this->dbh)) {
            $config = Mirage::app()->getConfig();
            MI_Database::connect($config[$this->_db]);
            $this->dbh = MI_Database::$dbh;
        }
        return $this->dbh->beginTransaction();
    }

    public function beginTransaction2() {
        if (!isset($this->dbh2)) {
            $config = Mirage::app()->getConfig();
            MI_Database::connect2($config[$this->_db2]);
            $this->dbh2 = MI_Database::$dbh2;
        }
        return $this->dbh2->beginTransaction();
    }

    public function beginTransaction3() {
        if (!isset($this->dbh3)) {
            $config = Mirage::app()->getConfig();
            MI_Database::connect3($config[$this->_db3]);
            $this->dbh3 = MI_Database::$dbh3;
        }
        return $this->dbh3->beginTransaction();
    }

    public function beginTransaction4() {
        if (!isset($this->dbh4)) {
            $config = Mirage::app()->getConfig();
            MI_Database::connect4($config[$this->_db4]);
            $this->dbh4 = MI_Database::$dbh4;
        }
        return $this->dbh4->beginTransaction();
    }

    public function beginTransaction5() {
        if (!isset($this->dbh5)) {
            $config = Mirage::app()->getConfig();
            MI_Database::connect5($config[$this->_db5]);
            $this->dbh5 = MI_Database::$dbh5;
        }
        return $this->dbh5->beginTransaction();
    }

    /**
     * Description: Close connection
     */
    public function close() {
        MI_Database::close();
    }

    /**
     * Description: Close connection
     */
    public function close2() {
        MI_Database::close2();
    }

    /**
     * Description: Close connection
     */
    public function close3() {
        MI_Database::close3();
    }

    public function close4() {
        MI_Database::close4();
    }

    public function close5() {
        MI_Database::close5();
    }

}
