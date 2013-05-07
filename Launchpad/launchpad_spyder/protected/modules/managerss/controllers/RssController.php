<?php

/**
 * Description of RssController
 * @package application.modules.managerss.controller
 * @author Bryan Salazar
 */
class RssController extends Controller
{
    
    public $layout = 'main';
    public $defaultAction = 'overview';
    /**
     * Default page
     */
    public function actionOverview()
    {
        if(Yii::app()->request->isAjaxRequest) {
            $componentPath = Yii::getPathOfAlias('application.modules.managerss.components');
            $string = file_get_contents($componentPath . DIRECTORY_SEPARATOR . 'feed.json');
            $data = CJSON::decode($string);
            $data = $data['data'];
            $totalRows = count($data);
            $page = $_GET['page'];
            $limit = $_GET['rows'];
            $count = count($data);
            if($count > 0 ) {
                $total_pages = ceil($count / $limit);
            } else {
                $total_pages = 0;
            }
            if ($page > $total_pages) {
                $page = $total_pages;
                $start = $limit * $page - $limit;
            }

            if ($page == 0) {
                $start = 0;
            } else {
                $start = $limit * $page - $limit;
            }

            $limit = (int) $limit;
            $data = array_slice($data, $start, $limit);
            
            echo jqGrid::generateJSON($totalRows, $data,'ID',true);
            Yii::app()->end();
        } else {
//            $assets=Yii::getPathOfAlias('application.modules.managerss.components.cleditor').'/assets';
//            $baseUrl=Yii::app()->assetManager->publish($assets);
//            Yii::app()->clientScript->registerScriptFile($baseUrl.'/jquery.cleditor.min.js',CClientScript::POS_HEAD);
//            Yii::app()->clientScript->registerCssFile($baseUrl.'/jquery.cleditor.css');
        }
        $this->render('rss_overview');
    }
    
    /**
     * Create new feed
     */
    public function actionCreate()
    {
        if(!Yii::app()->request->isAjaxRequest) 
            throw new CHttpException('Invalid request');
        
        $model = new RssForm();
        
        if(isset($_POST['RssForm'])) {
            $model->attributes = $_POST['RssForm'];
            if($model->validate()) {
                $model->save();
                $html = $this->renderPartial('rss_view', array('model'=>$model), true);
                echo CJSON::encode(array('html'=>$html,'status'=>'ok'));
                Yii::app()->end();
            }
        } else {
            $model->ID = (int)$this->getLastID() + 1;
        }
        $this->renderPartial('rss_create',array('model'=>$model));
    }
    
    public function actionUpdate()
    {
        if(!Yii::app()->request->isAjaxRequest) 
            throw new CHttpException('Invalid request');
        
        $model = $this->getModel();
        if(isset($_POST['RssForm'])) {
            $model->oldID = $model->ID;
            $model->attributes = $_POST['RssForm'];
            if($model->validate()) {
                
                $model->update();
                $html = $this->renderPartial('rss_view', array('model'=>$model), true);
                echo CJSON::encode(array('html'=>$html,'status'=>'ok'));
                Yii::app()->end();
            }
        }
        $this->renderPartial('rss_update',array('model'=>$model));
    }
    
    public function actionDelete()
    {
        if(!Yii::app()->request->isAjaxRequest) 
            throw new CHttpException('Invalid request');
        
        $model = $this->getModel();
        $model->delete();
        echo 'ok';
        Yii::app()->end();
    }
    
    public function actionView()
    {
        if(!Yii::app()->request->isAjaxRequest) 
            throw new CHttpException (404, 'Invalid Request');
        
        $model = $this->getModel();
        $this->renderPartial('rss_view',array('model'=>$model));
    }
    
    public function actionFeed()
    {
        header("Content-Type: application/rss+xml; charset=ISO-8859-1");
        $componentPath = Yii::getPathOfAlias('application.modules.managerss.components');
        $string = file_get_contents($componentPath . DIRECTORY_SEPARATOR . 'feed.json');
        $data = CJSON::decode($string);
        $dateLastUpdated = $data['datelastupdated'];
        $data = $data['data'];
        

        $template = Yii::getPathOfAlias('application.modules.managerss.views.rss.') . DIRECTORY_SEPARATOR . 'rss_feed.php';
        
        if($this->isGenerateNewXML($dateLastUpdated, $template)) {
            $xml = RssHelper::createXMLFromArray($data);
            $fh = fopen($template, 'w') or die("can't open file");
            if(($fh=fopen($template, 'w'))) {
                fwrite($fh, $xml);
                fclose($fh);
            }
        }
        $this->renderPartial('rss_feed');
    }  
    
    protected function isGenerateNewXML($dateLastUpdated,$xmlPath)
    {
        $xml = file_get_contents($xmlPath);

        $xml = simplexml_load_string($xml);
        $json = json_encode($xml);
        $data = json_decode($json, TRUE);
        if(strtotime($data['channel']['lastBuildDate']) < strtotime($dateLastUpdated))
            return true;
        return false;
    }
    
    /**
     *
     * @return RssForm 
     */
    protected function getModel()
    {
        if(!isset($_GET['id']))
            throw new CHttpException (404, 'Invalid Request');
        
        $componentPath = Yii::getPathOfAlias('application.modules.managerss.components');
        $string = file_get_contents($componentPath . DIRECTORY_SEPARATOR . 'feed.json');
        $data = CJSON::decode($string);
        $data = $data['data'];
        if(!isset($data[$_GET['id'] - 1]))
            throw new CHttpException (404, 'Data not found');
        
        $model = new RssForm();
        $model->ID = $_GET['id'];
        $model->title = $data[$_GET['id'] - 1]['Title'];
        $model->content = $data[$_GET['id'] - 1]['Content'];
        
        return $model;
    }
    
    protected function getLastID()
    {
        $componentPath = Yii::getPathOfAlias('application.modules.managerss.components');
        $string = file_get_contents($componentPath . DIRECTORY_SEPARATOR . 'feed.json');
        $data = CJSON::decode($string);
        $data = $data['data'];
        if(!isset($data[count($data) - 1]['ID']))
            return 0;
        return $data[count($data) - 1]['ID'];
    }
}
