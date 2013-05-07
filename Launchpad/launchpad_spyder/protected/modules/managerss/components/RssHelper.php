<?php

/**
 * Class helper for rss
 * @package application.modules.managerss.components
 * @author Bryan Salazar
 */
class RssHelper
{
    public static function createXMLFromArray($data) {
        $title = RssConfig::app()->params['rssTitle'];
        $url = RssConfig::app()->params['rssUrl'];
        $lang = RssConfig::app()->params['rssLanguage'];
        $category = RssConfig::app()->params['rssCategory'];
        
//        $lastBuildDate = date('D, j F Y h:i:s e');
        $lastBuildDate = date('Y-m-d H:i:s');
        $xml = "<?xml version=\"1.0\"?>";
        $xml.='<rss version="2.0">';
        $xml.='<channel>';
        $xml.="<title>$title</title>";
        $xml.="<link>$url</link>";
        $xml.="<language>$lang</language>";
        $xml.="<lastBuildDate>$lastBuildDate</lastBuildDate>";
        $xml.="<category>$category</category>";
        
        foreach($data as $k => $v) {
            $xml.='<item>';
            $v['Title'] = CHtml::encode($v['Title']);
            $xml.="<title>".$v['Title'] . "</title>";
            $xml.="<link>$url</link>";
            $v['Content'] = CHtml::encode($v['Content']);
            $xml.="<description>".$v['Content']."</description>";
            $xml.='</item>';
        }
        
        $xml.='</channel>';
        $xml.='</rss>';
        return $xml;
    }
    
    
}
