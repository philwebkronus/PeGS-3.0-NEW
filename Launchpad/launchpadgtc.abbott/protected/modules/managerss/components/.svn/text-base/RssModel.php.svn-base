<?php

/**
 * Base class for rss models
 * @package application.modules.managerss.components
 * @author Bryan Salazar
 */
class RssModel extends CModel{
    /**
     *
     * @var LPDB 
     */
    protected $_connection;
    
    public function attributeNames()
    {
        $className=get_class($this);
        if(!isset(self::$_names[$className]))
        {
            $class=new ReflectionClass(get_class($this));
            $names=array();
            foreach($class->getProperties() as $property)
            {
                $name=$property->getName();
                if($property->isPublic() && !$property->isStatic())
                    $names[]=$name;
            }
            return self::$_names[$className]=$names;
        }
        else
            return self::$_names[$className];
    }  
    
    public function log($message,$category) 
    {
        Yii::log('[HTTP_REFERER='.$_SERVER['HTTP_REFERER'].'] '.'[TerminalID='.Yii::app()->user->getState('terminalID') . ' TerminalCode='.Yii::app()->user->getState('terminalCode').'] '.$message, 'error', $category);
    }    
}
