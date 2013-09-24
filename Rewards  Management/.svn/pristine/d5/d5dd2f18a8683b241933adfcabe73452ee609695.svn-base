<?php
/**
 * This file contains the controller for the side menu widget
 * 
 * @package application.components.widgets
 * @author Sheryl S. Basbas
 * @date-created March 16, 2012
 * @last-modified March 19, 2012
 * 
 */

/*
 * This widget is a database driven menu generator. Instruction for using this 
 * widget is as follows:
 * 
 * 1.) In the controller of the page you want to incorporate the widget initialize 
 * the variables $parent and child where the $parent is the main menu and the 
 * $child is the sub-menu.      
 *          
 * @example
        public $parent = '[{"menuid":"1","name":"ACCOUNTS"}]';
        public $child = '[{"menuid":"1","name":"unlock accounts","link":"lock_unlock_account"},{"menuid":"1","name":"lock accounts","link":"lock_unlock_account/lockUnlock/lockOverview"}]';
 * 
 * 2.) In the view of the page add the following code:
 * 
 * @example 
 *      $parent = CJSON::decode($this->parent);
 *      $child = CJSON::decode($this->child);
 *      $this->widget('application.components.widgets.SideMenuWidget',array('parent'=>$parent,'child'=>$child));        
 * 
 *  
 */

class SideMenuWidget extends CWidget
{
    public $parent;
    public $child; 
    
     /**
      *@section Generate Menu Items to be passed to menu widget
      * 
      * @param array $parent -- main menu
      *     @example: array(2) { 
      *                         [0]=> array(2) 
      *                             { 
      *                                 ["menuid"]=> string(1) "1" 
      *                                 ["name"]=> string(4) "MENU" 
      *                             } 
      *                         [1]=> array(2) 
      *                             { 
      *                                 ["menuid"]=> string(1) "2" 
      *                                 ["name"]=> string(8) "SUB-MENU" 
      *                             } 
      *                         } 
      * 
      * @param array $child  -- sub menu
      *     @example: array(2) { 
      *                        [0]=> array(3) 
      *                             { 
      *                                 ["menuid"]=> string(1) "2" 
      *                                 ["name"]=> string(16) "manage sub-menus" 
      *                                 ["link"]=> string(25) "menus/menuMgt/subOverview" 
      *                             } 
      *                        [1]=> array(3) 
      *                             { 
      *                                 ["menuid"]=> string(1) "1" 
      *                                 ["name"]=> string(12) "manage menus" 
      *                                 ["link"]=> string(5) "menus" 
      *                             } 
      *                         } 
      * 
      * @return array $menus -- consolidated main and sub menu
      *     @example: array(1) { 
      *                         ["items"]=> array(2) 
      *                             { 
      *                                 [0]=> array(4) 
      *                                     { 
      *                                         ["label"]=> string(4) "MENU" 
      *                                         ["url"]=> string(0) "" 
      *                                         ["itemOptions"]=> array(1) 
      *                                                 { 
      *                                                     ["class"]=> string(16) "ui-widget-header" 
      *                                                 } 
      *                                         ["items"]=> array(2) 
      *                                                 { 
      *                                                     [0]=> array(0) 
      *                                                         { } 
      *                                                     [1]=> array(2) 
      *                                                       { 
      *                                                           ["label"]=> string(12) "manage menus" 
      *                                                           ["url"]=> string(38) "/Projects/loyalty_test/index.php/menus" 
      *                                                        } 
      *                                                 } 
      *                                       } 
      *                                 [1]=> array(4) 
      *                                     { 
      *                                         ["label"]=> string(8) "SUB-MENU" 
      *                                         ["url"]=> string(0) "" 
      *                                         ["itemOptions"]=> array(1) 
      *                                                   { 
      *                                                       ["class"]=> string(16) "ui-widget-header" 
      *                                                   } 
      *                                         ["items"]=> array(2) 
      *                                                   { 
      *                                                     [0]=> array(2) 
      *                                                         { 
      *                                                             ["label"]=> string(16) "manage sub-menus" 
      *                                                             ["url"]=> string(58) "/Projects/loyalty_test/index.php/menus/menuMgt/subOverview" 
      *                                                         } 
      *                                                     [1]=> array(0) 
      *                                                         { } 
      *                                                   } 
      *                                      } 
      *                              } 
      *                           } 
      */
    
    public function actionmenuItems($parent,$child)
    {        

        
            $x= 0;
            $menus = array();
            $submenu = array();
            foreach($parent as $items=>$item)
            { 
               
                $i = 0;
                $menuid = $item['menuid'];
             
                foreach($child as $subs => $sub)
                {                 
                    $subid = $sub['menuid'];

                    if($menuid == $subid)
                    {      
                        $submenu[$i]['label'] = $sub['name']; 
                        $submenu[$i]['url'] = Yii::app()->createUrl($sub['link']);
                    } 
                    else
                    {                                 
                        $submenu[$i]['label'] = null; 
                        $submenu[$i]['url'] = null;
                    }                
                 
            

                    $i++;
                }
                
                $submenu = $this->actionremove_null($submenu); 
                $submenu = $this->actionremove_null($submenu);        
                
                $menu[$x]['label'] = $item['name'];                
                
                $menu[$x]['itemOptions']= array('class'=>'ui-widget-header');
                if(isset($item['link']))
                {
                    $menu[$x]['url'] =Yii::app()->createUrl($item['link']); 
                    $menu[$x]['items'] = null;
                }
                else
                {                   
                    if($submenu != null)
                    {
                        $menu[$x]['items'] = $submenu;
                    }
                    else
                    {
                        $menu[$x]['items'] = null;
                    }
                }
                    
               

                $x++;
            }
            $menus = array('items'=>$menu);            
            
            return $menus;
            
            
    }
    
    public function run() 
    {   
        
        /**
         * Added for XML Menu
         * @date-added May 2, 2012
         */

         if(file_exists(Yii::app()->params["menuxml"])) {

             $xml = Yii::app()->params["menuxml"];
             $id = Yii::app()->user->getState('acctype');

             $dom = new DOMDocument();
             $dom->load($xml);
             $xpath = new DOMXpath($dom);
             $pattern = "//right[@id='".$id."']/menu";
             $data = $xpath->query($pattern);
             $controller = array();
             $child[] = '[]';
             $parent = array();
             
             foreach($data as $node) {

                 $currArray = array();

                 $currArrayChild = array();

                 if($node->hasChildNodes()) {

                     $currArray["menuid"] = $node->getAttribute("id");
                     $currArray["name"] = $node->getAttribute("name");

                     $sub = $xpath->query($node->getNodePath()."/submenu");

                     foreach($sub as $childx) {

                        $currArrayChild["menuid"] = $childx->getAttribute("menuid");
                        $currArrayChild["name"] = $childx->getAttribute("name");
                        $currArrayChild["link"] = $childx->getAttribute("link");
                        $currArrayChild["controller"] = $childx->getAttribute("controller");
                        
                        $controller[] = $childx->getAttribute("controller");
                        
                        $child[] = $currArrayChild;

                     }

                 }
                 else {

                     $currArray["menuid"] = $node->getAttribute("id");
                     $currArray["name"] = $node->getAttribute("name");
                     $currArray["link"] = $node->getAttribute("link");
                     $currArray["controller"] = $node->getAttribute("controller");
                     
                     $controller[] = $node->getAttribute("controller");

                 }

                 $parent[] = $currArray;
                 
             } 

         }
         
         Yii::app()->user->setState('controller', $controller);
         
         if(sizeof($parent) > 0 ) {
         
            $menus = $this->actionmenuItems($parent, $child);
            
            $this->render('sidemenuwidget',array('menus'=>$menus));
        
         }
         else {
             
             exit("Cannot view page. Missing set of menus.");
             
         }
        
        
        
    }
    

    
    /**
     *@section Remove null values in array
     * @param array $data -- array to remove nulls in
     *      @example : array(2) 
     *                    { 
     *                      [0]=> array(2) 
     *                          { 
     *                              ["label"]=> NULL 
     *                              ["url"]=> NULL } 
     *                      [1]=> array(2) 
     *                          { 
     *                              ["label"]=> string(12) "manage menus" 
     *                              ["url"]=> string(38) "/Projects/loyalty_test/index.php/menus" 
     *                          } 
     *                     }
     *  
     * @return array $output
     *      @example: array(2) 
     *                  { 
     *                      [0]=> array(0) 
     *                          { } 
     *                      [1]=> array(2) 
     *                          { 
     *                              ["label"]=> string(12) "manage menus" 
     *                              ["url"]=> string(38) "/Projects/loyalty_test/index.php/menus" 
     *                          } 
     *                  } 
     */
    public function actionremove_null($data)
    {
        $output = array();
        foreach ($data as $key=>$value)
        {
            if (is_array($data[$key]))
            {
            $output[$key] = $this->actionremove_null($value);     
            }
            elseif ($data[$key] || $data[$key]===0)
            {
            $output[$key] = $value;
            }
        }       
        return $output;
    }
  
}
?>
