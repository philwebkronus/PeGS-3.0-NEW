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
        
        //if(!isset($this->jqGridParam['rowNum']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('rowNum'=>Yii::app()->params['initialLimit']));
        
        //if(!isset($this->jqGridParam['rowList']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('rowList'=>Yii::app()->params['pageGridLimit']));
        
        if(!isset($this->jqGridParam['pager']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('pager'=>$this->pagerID));
        
        if(!isset($this->jqGridParam['mtype']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('mtype'=>'get'));    
        
        if(!isset($this->jqGridParam['datatype']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('datatype'=>'json'));  
        
        //if(!isset($this->jqGridParam['height']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('height'=>'300px'));  
        
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
        
        if(isset($this->jqGridParam['url']))
            $this->jqGridParam = array_merge($this->jqGridParam,array('url'=>$this->jqGridParam['url']));
        
        $config = CJSON::encode($this->jqGridParam);
        
        $config = substr($config, 1);
        $config = substr($config, 0,-1);
        if($loadComplete != null)
            $config.=',loadComplete:' . $loadComplete;
        
        /**
         *Added for optional search feature of jQGrid
         *  
         */
        if(!Yii::app()->user->isGuest && Yii::app()->user->getState("acctype") != 7)
            $search = 'true';
        else 
            $search = 'false';
        
        Yii::app()->clientScript->registerScript($this->tableID,"
            
                
            jQuery('#$this->tableID').jqGrid({".$config."});
            jQuery('#$this->tableID').jqGrid('navGrid','#$this->pagerID',{edit:false,add:false,del:false, search: {$search}});
                
            jQuery('#$this->tableID tbody tr:nth-child(1)').css('visibility','collapse');
                
        ",$this->scriptPosition);
        
//        $this->render('jqgridwidget');
    }
}
