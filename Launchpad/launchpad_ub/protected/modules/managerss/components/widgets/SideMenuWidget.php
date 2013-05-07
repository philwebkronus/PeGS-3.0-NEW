<?php
Yii::import('zii.widgets.CMenu');

/**
 * Widget for side menu
 * @package application.modules.managerss.compoents.widgets
 * @author Bryan Salazar
 */
class SideMenuWidget extends CMenu
{
    public $activateItemsOuter = true;
    public $htmlOptions;
    public $defaultClass = 'sidemenu';
    public $mainTitle = 'Menu';
    
    public function init()
    {
        parent::init();
        
        if(isset($this->htmlOptions['class']))
            $this->htmlOptions['class'] = $this->htmlOptions['class'] . ' ' . $this->defaultClass;
        else
            $this->htmlOptions['class'] = $this->defaultClass;
        $assets = Yii::app()->getAssetManager()->publish(__DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'assets');
        Yii::app()->clientScript->registerCssFile($assets . '/sidemenu.css');
        Yii::app()->clientScript->registerScript('',"
            $('.sidemenu-btn').click(function(){
                if($(this).children('span').attr('class') == 'ui-icon ui-icon-circle-triangle-n') {
                    $(this).children('span').attr('class','ui-icon ui-icon-circle-triangle-s')
                } else {
                    $(this).children('span').attr('class','ui-icon ui-icon-circle-triangle-n')
                }
                return false;
            });
            
            $('.sidemenu-header').mouseover(function(){
                $(this).removeClass('ui-state-default');
                $(this).addClass('ui-state-hover');
            });
            
            $('.sidemenu-header').mouseout(function(){
                $(this).removeClass('ui-state-hover');
                $(this).addClass('ui-state-default');
            });
            
            $('.sidemenu-header').click(function(){
                if($(this).children('span').attr('class') == 'ui-icon ui-icon-circle-triangle-n') {
                    $(this).children('span').removeClass('ui-icon ui-icon-circle-triangle-n');
                    $(this).children('span').addClass('ui-icon ui-icon-circle-triangle-s');
                    $(this).next().slideUp('slow');
                } else {
                    $(this).children('span').removeClass('ui-icon ui-icon-circle-triangle-s');
                    $(this).children('span').addClass('ui-icon ui-icon-circle-triangle-n');
                    $(this).next().slideDown('slow');
                }
            });
            
            $('.sidemenu').children('li').mouseover(function(){
                $(this).addClass('ui-state-hover');
                $(this).addClass('ui-corner-all');
            });
            
            $('.sidemenu').children('li').mouseout(function(){
                $(this).removeClass('ui-state-hover');
            });
        ");
    }
    
    public function run()
    {
        echo '<div class="ui-accordion ui-widget ui-helper-reset ui-accordion-icons" role="tablist">
                <h3 class="ui-accordion-header ui-helper-reset ui-state-default ui-corner-all sidemenu-header" role="tab" aria-expanded="false" aria-selected="false" tabindex="-1">
                    <span class="ui-icon ui-icon-circle-triangle-n"></span><a href="#" tabindex="-1">'.$this->mainTitle.'</a>
                </h3>
                <div style="padding-left:4px;padding-right:4px;" class="sidemenu-content ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom ui-accordion-content-active" style="height: 13.2px; overflow: auto; display: block; padding-top: 9.9px; padding-bottom: 9.9px;" role="tabpanel">';
//                        <a href="#"><span class="ui-icon ui-icon-trash" style="float:left"></span><div style="padding: 3px 0px;">Add Test</div></a>
        $this->renderMenu($this->items);
        echo    '</div>
            </div>';
        
    }
    
    

    protected function renderMenuRecursive($items)
    {
        $count = 0;
        $n = count($items);
        foreach ($items as $item) {
            $count++;
            $options = isset ($item['itemOptions']) ? $item['itemOptions'] : array();
            $class = array();
            if ($item['active'] && $this->activeCssClass != '') {
                if ($this->activateItemsOuter) {
                    $class [] = $this->activeCssClass;
                }
                else {
                    if (isset ($item['linkOptions'])) {
                        $item['linkOptions'] = array('class' => $item['linkOptions']['class'] . ' ' . $this->activeCssClass . ' ui-state-default');

                    }
                    else {
                        $item['linkOptions'] = array('class' => $this->activeCssClass . ' ui-state-default');
                    }
                }
            }
            if ($count === 1 && $this->firstItemCssClass != '')
                $class [] = $this->firstItemCssClass;
            if ($count === $n && $this->lastItemCssClass != '')
                $class [] = $this->lastItemCssClass;
            if ($class !== array()) {
                if (empty ($options['class']))
                    $options['class'] = implode(' ', $class);
                else
                    $options['class'] .= ' ' . implode(' ', $class);
            }
//            if(isset($options['class'])) {
//                $options['class'] = $options['class'] . ' ui-state-default ui-corner-all ';
//            } else {
//                $options['class'] = ' ui-state-default ui-corner-all ' ;
//            }
            
            echo CHtml :: openTag('li', $options);

            if (isset ($item['url'])) {
                $label = $this->linkLabelWrapper === null ? $item['label'] : '<span style="float:left" class="'.(isset($item)?'ui-icon '.$item['icon']:'').'"></span><' . $this->linkLabelWrapper . ' >' . $item['label'] . '</' . $this->linkLabelWrapper . '><div class="clear"></div>';
                $menu = CHtml::link($label, $item['url'], isset ($item['linkOptions']) ? $item['linkOptions'] : array());
            }
            else
                $menu = CHtml::tag('span', isset ($item['linkOptions']) ? $item['linkOptions'] : array(), $item['label']);
                
            if (isset ($this->itemTemplate) || isset ($item['template'])) {
                $template = isset ($item['template']) ? $item['template'] : $this->itemTemplate;
                echo strtr($template, array('{menu}' => $menu));
            }
            else
                echo $menu;
            if (isset ($item['items']) && count($item['items'])) {
                echo "\n" . CHtml :: openTag('ul', $this->submenuHtmlOptions) . "\n";
                $this->renderMenuRecursive($item['items']);
                echo CHtml :: closeTag('ul') . "\n";
            }
            echo CHtml :: closeTag('li') . "\n";
        }
    }
}
