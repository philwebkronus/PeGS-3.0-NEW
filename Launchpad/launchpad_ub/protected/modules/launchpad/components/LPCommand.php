<?php

/**
 * Extend the CDbCommand for logging purpose
 * @package application.modules.launchpad.components
 * @author Bryan Salazar
 */
class LPCommand extends CDbCommand
{
    protected $_bound = array();
        
    /**
     * This only applicable if you do not use bindParam,bindValue and bindValues
     * @return string 
     */
    public function getBound()
    {
        $params = $this->_bound;
        if($params) {
            $p=array();
            foreach($params as $name=>$value)
                    $p[$name]=$name.'='.var_export($value,true);
            $par='. Bound with '.implode(', ',$p);
        } else
            $par='';
        return $par;
    }
    
    /**
     *
     * @param bool $fetchAssociative
     * @param array $params data bind
     * @return bool|int false if no row affected
     */
    public function queryRow($fetchAssociative = true, $params = array()) 
    {
        $this->_bound = array_merge($this->params,$params);
        return parent::queryRow($fetchAssociative, $params);
    }

    /**
     *
     * @param bool $fetchAssociative
     * @param array $params data bind
     * @return bool|int false if no row affected
     */
    public function queryAll($fetchAssociative = true, $params = array())
    {
        $this->_bound = array_merge($this->params,$params);
        return parent::queryAll($fetchAssociative, $params);
    }
    
    /**
     * 
     * @param array $params data bind
     * @return bool|int false if no row affected
     */
    public function execute($params = array())
    {
        $this->_bound = array_merge($this->params,$params);
        return parent::execute($params);
    }
}
