<?php

/**
 * Date Created 10 27, 11 10:43:45 AM <pre />
 * Description of Menu
 * @author Bryan Salazar
 */
class Menu {
    
    
    /************************* SINGLE LEVEL MENU ONLY *************************/
    public static function display($menus=array()) {
        $m = '';
        foreach($menus as $label => $menu) {
            $attr = '';
            if(isset($menu['attr'])) {
                $attr = $menu['attr'];
            }
            if(isset($menu['visible']) && $menu['visible'] === false) {
                
            } else {
                if(isset($menu['mod']) && $menu['mod'] == Mirage::app()->getModuleName() && $menu['act'] == Mirage::app()->getActionName() && $menu['con'] == Mirage::app()->getControllerName()) {
                    $m.='<a ' . $attr . ' class="active" href="' . $menu['link'] . '" />' . $label . '</a>';
                } else if(isset($menu['act']) && $menu['act'] == Mirage::app()->getActionName() && $menu['con'] == Mirage::app()->getControllerName()) {
                    $m.='<a ' . $attr . ' class="active" href="' . $menu['link'] . '" />' . $label . '</a>';
                } else {
                    $m.='<a ' . $attr . ' href="' . $menu['link'] . '" />' . $label . '</a>';
                }
            }
        }
        return $m;
    }
}

