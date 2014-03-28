<?php

/**
 * Form Model for managing rss
 * @package application.modules.managerss.models
 * @author Bryan Salazar
 */
class RssForm extends CFormModel
{
    public $ID;
    public $title;
    public $content;
    public $oldID;
    
    public function rules() {
        return array(
            array('ID,title,content','required'),
            array('ID','numerical','integerOnly'=>true),
            array('content','length','max'=>RssConfig::app()->params['max_length_content']),
        );
    }
    
    public function attributeLabels() {
        return array(
            'ID'=>'ID'
        );
    }
    
    public function save()
    {
        $componentPath = Yii::getPathOfAlias('application.modules.managerss.components');
        $string = file_get_contents($componentPath . DIRECTORY_SEPARATOR . 'feed.json');
        $data = CJSON::decode($string);
        $data = $data['data'];
        if(isset($data[$this->ID - 1])) {
//            array_splice($inputarray, $offset, $length, $replacement);
            array_splice($data,($this->ID - 1),0,array(array('ID'=>$this->ID,'Title'=>$this->title,'Content'=>$this->content)));
        } else {
            $data[] = array('ID'=>$this->ID,'Title'=>$this->title,'Content'=>$this->content);
        }
        $json = Yii::getPathOfAlias('application.modules.managerss.components') . DIRECTORY_SEPARATOR . 'feed.json';
        
        $newData = array();
        $cntr = 1;
        foreach($data as $k => $v) {
            $newData[] = array('ID'=>$cntr,'Title'=>$v['Title'],'Content'=>$v['Content']);
            $cntr++;
        }
        
        $fh = fopen($json, 'w') or die("can't open file");
        if(($fh=fopen($json, 'w'))) {
            
            fwrite($fh, CJSON::encode(array('datelastupdated'=>date('Y-m-d H:i:s'),'data'=>$newData)));
            fclose($fh);
        }
    }
    
    public function update()
    {
        $componentPath = Yii::getPathOfAlias('application.modules.managerss.components');
        $string = file_get_contents($componentPath . DIRECTORY_SEPARATOR . 'feed.json');
        $data = CJSON::decode($string);
        $data = $data['data'];
        if($this->ID != $this->oldID) {
            $old = $data[($this->oldID - 1)];
            unset($data[($this->oldID - 1)]);
            array_splice($data,($this->ID - 1),0,array(array('ID'=>$this->ID,'Title'=>$this->title,'Content'=>$this->content)));
        } else {
            $data[$this->ID - 1] = array('ID'=>$this->ID,'Title'=>$this->title,'Content'=>$this->content);
        }
        
        $json = Yii::getPathOfAlias('application.modules.managerss.components') . DIRECTORY_SEPARATOR . 'feed.json';
        $newData = array();
        $cntr = 1;
        foreach($data as $k => $v) {
            $newData[] = array('ID'=>$cntr,'Title'=>$v['Title'],'Content'=>$v['Content']);
            $cntr++;
        }
        
        $fh = fopen($json, 'w') or die("can't open file");
        if(($fh=fopen($json, 'w'))) {
            fwrite($fh, CJSON::encode(array('datelastupdated'=>date('Y-m-d H:i:s'),'data'=>$newData)));
            fclose($fh);
        }
    }
    
    public function delete()
    {
        $componentPath = Yii::getPathOfAlias('application.modules.managerss.components');
        $string = file_get_contents($componentPath . DIRECTORY_SEPARATOR . 'feed.json');
        $data = CJSON::decode($string);
        $data = $data['data'];
        unset($data[($this->ID - 1)]);
        $json = Yii::getPathOfAlias('application.modules.managerss.components') . DIRECTORY_SEPARATOR . 'feed.json';
        $newData = array();
        $cntr = 1;
        foreach($data as $k => $v) {
            $newData[] = array('ID'=>$cntr,'Title'=>$v['Title'],'Content'=>$v['Content']);
            $cntr++;
        }
        
        $fh = fopen($json, 'w') or die("can't open file");
        if(($fh=fopen($json, 'w'))) {
            fwrite($fh, CJSON::encode(array('datelastupdated'=>date('Y-m-d H:i:s'),'data'=>$newData)));
            fclose($fh);
        }
    }
}