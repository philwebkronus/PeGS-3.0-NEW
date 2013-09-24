<?php
/**
 * This class file is used to display the application footer menu.
 * 
 * @author Marx - Lenin C. Topico
 * @version 1.0
 * @package application.components.widgets.FooterLinkWidget
 */
class FooterLinkWidget extends CWidget {
    
    /**
     *This class property holds the application footer menu as passed by
     * _createUrl() method.
     * 
     * @var Array 
     */
    private $url;
    
    /**
     * This is a Yii default method to initialize the widget.
     * 
     * The method flow as follows:
     * 
     * 1. It first assigns class property $url with the application footer menus.
     * 2. It then calls the displayFooter() method to display the footer menus.
     */
    public function run () {
        
        $this->url = self::_createUrl();
        
        $this->displayFooter();
        
    }
    
    /**
     *This method just simply holds the static footer menus of the application
     * stored in an array.
     * 
     * @return Array 
     */
    private final static function _createUrl () {
        
        if(Yii::app()->user->isGuest || (!Yii::app()->user->isGuest && Yii::app()->user->getState("aid") == "")) $display = true;
        else $display = false;
        
        return 
        
            array(
                array("HOME", Yii::app()->createUrl('')),
                array("VIP REWARDS", Yii::app()->createUrl('consumer/vipRewards/render/')),
                array("CATALOGUE", Yii::app()->createUrl('consumer/consumer/viewmore')),
                array("FAQs", Yii::app()->createUrl('consumer/faqs/render/')),
                array("CONTACT US", Yii::app()->createUrl('consumer/contactUs/render')),
                array("PHILWEB CORPORATION","http://www.philweb.com.ph/"),
                array("PAGCOR","http://www.pagcor.ph/"),
                array("OPERATOR'S LOGIN", Yii::app()->createUrl("login"), 
                            "visible" => $display ),
                array("TERMS & CONDITIONS", Yii::app()->createUrl("consumer/terms/render/"))
            );
        
    }
    
    /**
     * This method display the footer menus.
     * 
     * The method flow as follows:
     * 
     * 1. It first gets the number of record stored in class property $url via
     *      sizeof() method.
     * 2. It then initializes $ctr to 0. This is used to determine whether a
     *      a divider (|) must be appended right after menu printing.
     * 3. It then loops through class property $url and enters the first section
     *      Visibility that is used to check whether a given menu at a given
     *      authentication level must be displayed. We set this by including the
     *      visibile key in any of the menus existing or to be added in _createUrl().
     *      If visible key doesn't exists then the widget assumes that it is always
     *      visible or otherwise.
     * 4. It then enters the Menu Printing sections which simply prints the menu
     *      only if visible to the current authentication level. Further, it checks
     *      whether the current position of the menu is last via $ctr. If it does,
     *      then it will not append a divider (|) after the menu printing.
     * 5. Each cycle increments $ctr to record the currect menu position.
     * 
     */
    private final function displayFooter () {
        
        $size = sizeof($this->url);
        
        $ctr = 0;

        foreach($this->url as $m) {
            
            /**
             * @section Visibility
             */
            if(!array_key_exists("visible", $m)) {

                $visibility = true;

            }
            else {

                $visibility = $m["visible"];

            }
            
            /**
             * @section Menu Printing
             */
            if($visibility) {

                if($ctr+1 >= $size) {

                    echo CHtml::link($m[0], $m[1]);

                }
                else {

                    echo CHtml::link($m[0], $m[1])."&nbsp;|&nbsp;";

                }

            }

            $ctr++;

        }
        
    }
    
}

?>
