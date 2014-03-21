<?php

/**
 * Description of JqGridWidget
 * @package application.components.widgets
 * @author Bryan Salazar
 */
class JqGridWidget extends CWidget
{
    public $tableID;
    public $pagerID;
    public $scriptPosition = 4; // CClientScript::POS_READY
    
    public $jqGridParam = array();

    public function run() {
        if($this->tableID == null)
            throw new CException("Please set tableID of JqGridWidget");
                
        if($this->pagerID == null)
            throw new CException("Please set pagerID of JqGridWidget");
        
        if(!isset($this->jqGridParam['rowNum']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('rowNum'=>RssConfig::app()->params['initialLimit']));
        
        if(!isset($this->jqGridParam['rowList']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('rowList'=>RssConfig::app()->params['pageGridLimit']));
        
        if(!isset($this->jqGridParam['pager']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('pager'=>$this->pagerID));
        
        if(!isset($this->jqGridParam['mtype']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('mtype'=>'get'));    
        
        if(!isset($this->jqGridParam['datatype']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('datatype'=>'json'));  
        
        if(!isset($this->jqGridParam['height']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('height'=>'100%'));  
        
        if(!isset($this->jqGridParam['autowidth']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('autowidth'=>true));
        
        if(!isset($this->jqGridParam['viewrecords']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('viewrecords'=>true));
        
        if(!isset($this->jqGridParam['sortorder']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('sortorder'=>'asc'));
        
        if(!isset($this->jqGridParam['viewrecords']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('viewrecords'=>true));
        
        $loadComplete = null;
        if(isset($this->jqGridParam['loadComplete'])) {
            $loadComplete = $this->jqGridParam['loadComplete'];
            unset($this->jqGridParam['loadComplete']);
        }
        
        
        $config = CJSON::encode($this->jqGridParam);
        
        $config = substr($config, 1);
        $config = substr($config, 0,-1);
        if($loadComplete != null)
            $config.=',loadComplete:' . $loadComplete;
        
        
        Yii::app()->clientScript->registerScript($this->tableID,"jQuery('#$this->tableID').jqGrid({".$config."});
            jQuery('#$this->tableID').jqGrid('navGrid','#$this->pagerID',{edit:false,add:false,del:false, search:false});
        ",$this->scriptPosition);
        
//        $this->render('jqgridwidget');
    }
}
