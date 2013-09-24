<?php
/**
 * This class file is used to display the application header menu.
 * 
 * @author Marx - Lenin C. Topico
 * @version 1.0
 * @package application.components.widgets.HeaderMenuWidget
 */
class HeaderMenuWidget extends CWidget {
    
    /**
     *This class property is the direct instance of the controller that calls
     * this widget. This is needed to execute getAction()->getId() methods used
     * for marking the active menu. 
     * 
     * @var Object 
     */
    public $instance;
    
    /**
     * This is a Yii default method to initialize the widget.
     * 
     * The method flow as follows:
     * 
     * 1. At any user level, this method will always call the genericHeaderMenu()
     *      of its class to display the application menus.
     * 2. It will then validate via Yii::app()->user->isGuest() if the two controls:
     *      Control Panel and Logout must be displayed. Method panel() will only be
     *      called when a user has been authenticated.
     */
    public function run () {
        
        $this->genericHeaderMenu();
        
        if(!Yii::app()->user->isGuest && Yii::app()->user->getState("aid") != "") { 
            
            $this->panel();  
            
            
        }
        
    }
    
    /**
     * This method is used to display the main application menu.
     * 
     * NOTE:
     * 
     * 1. $this->instance->getAction()->getId() return the action name currently
     *      executed.
     * 2. getState() retrieves only properties set using setState() in the 
     *      loginController.
     * 
     */
    private final function genericHeaderMenu () {
        
        echo "<div id='mainmenu'>";
        
        $this->instance->widget('zii.widgets.CMenu',array(
            
            'activeCssClass'=>'active',
            
            'items'=>array(                           
                        array(
                            'label'=>'HOME', 
                            'url'=>Yii::app()->createUrl(''),
                            'active'=> $this->instance->getAction()->getId() == 'homepage'
                                            ? true : false),
                        array(
                            'label'=>'VIP REWARDS', 
                            'url'=>Yii::app()->createUrl('consumer/vipRewards/render/'),
                            'active'=>$this->instance->id=='vipRewards'?true:false),
                        array(
                            'label'=>'CATALOGUE', 
                            'url'=>Yii::app()->createUrl('consumer/consumer/viewmore'),
                            'active'=>$this->instance->getAction()->getId()=='viewmore'?true:false),
                        array(
                            'label'=>'FAQs', 
                            'url'=>Yii::app()->createUrl('consumer/faqs/render/'),
                            'active'=>$this->instance->id=='faqs'?true:false),
                        array(
                            'label'=>'CONTACT US', 
                            'url'=>Yii::app()->createUrl('consumer/contactUs/render'),
                            'active'=>$this->instance->id=='contactUs'?true:false),
                        array(
                            'label'=>'DASHBOARD', 
                            'url'=>Yii::app()->createUrl('operator/dashBoard/render'),
                            'active'=>$this->instance->id=='dashBoard'?true:false,
                            'visible'=> Yii::app()->user->getState('acctype') == 2),
                
           
            ),
            
        ));   
        
        echo "</div>";
        
    }
    
    /**
     * This method displays the two controls: Control Panel (CP) and Logout only
     * for authenticated users. The CP will redirect any authenticated user to
     * his default landing page upon successful authentication.
     * 
     * The method flow as follows:
     * 
     * 1. Basically the method starts by explicit initialization of $url
     *      to be used for setting the control panel link.
     * 2. The next line declares HTML markups.
     * 3. The widget line zii.widget.CMenu is used to display the logout control.
     * 4. The next line declares HTML markup.
     * 5. Then [switch block] there's a selection of the correct control panel link based on
     *      the user account type.
     * 6. Then there's the widget display for the Control Panel control. Then
     *      ended with an HTML markup.
     * 
     */
    private final function panel () {
        
       $url = "";
    
       echo  "<div class='right' style='margin-top: -28px;' id='userLogout'>".
                "<div class='ui-icon left ui-icon-person ui-corner-all ui-state-default'></div>";
               
       $this->widget('zii.widgets.CMenu',array(
            'items'=>array(
                    array('label'=>'Logout ('.Yii::app()->user->name.')', 
                            'url'=>Yii::app()->createUrl('sLogin/logout'))
                ),
        ));

        echo  "</div><div class='right' style='margin-top: -28px;' id='userLogout'>".
                    "<div class='ui-icon left ui-icon-wrench ui-corner-all ui-state-default'></div>";
                  

        switch(Yii::app()->user->getState("acctype")) {

            case 8 : 
                    $url = Yii::app()->params["marketingpath"]; 
                    $url = Yii::app()->createUrl($url);
                break;
            case 1: 
                    $url = Yii::app()->params["adminpath"]; 
                    $url = Yii::app()->createUrl($url);
                break;
            case 2 : 
                    $url = Yii::app()->params["operatorpath"]; 
                    $url = Yii::app()->createUrl($url);
                break;
            case 6 : 
                    $url = Yii::app()->params["cspath"]; 
                    $url = Yii::app()->createUrl($url);
                break;
            case 7 : 
                    $url = Yii::app()->params["playerpath"]; 
                    $url = Yii::app()->createUrl($url);
                break;
            case 9: 
                    $url = Yii::app()->params["aspath"]; 
                    $url = Yii::app()->createUrl($url);
                break;
            case 4 : 
                    $url = Yii::app()->params["cashierpath"]; 
                    $url = Yii::app()->createUrl($url);
                break;
        }

        $this->widget('zii.widgets.CMenu',array(
            
                'items'=>array(
                    
                        array('label'=>'Control Panel', 'url'=> $url)
                    
                )
            
            
        )); 
       
        echo "</div>";

    }
    
}

?>
