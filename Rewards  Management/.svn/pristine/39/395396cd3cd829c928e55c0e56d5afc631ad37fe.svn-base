<?php

/**
 * Library of dynamic client script
 * @author Bryan Salazar
 * @version 1.0
 * @package application.modules.managerss.components
 */
class DynamicScript
{
    public static function jqueryUi() {
        Yii::app()->clientScript->registerScript("jqueryui","
            $('input[type=text], textarea').live('focusin',function(){
                $(this).removeClass('ui-state-default');
                $(this).addClass('ui-state-focus');
            });
            $('input[type=text], textarea').live('focusout',function(){
                $(this).removeClass('ui-state-focus');
                $(this).addClass('ui-state-default');
            });
        ");
    }
    
    public static function jqueryUiAjax() {
        $script = "
            $('.row > input[type=text], textarea').focusin(function(){
                $(this).removeClass('ui-state-default');
                $(this).addClass('ui-state-focus');
            });
            $('.row > input[type=text], textarea').focusout(function(){
                $(this).removeClass('ui-state-focus');
                $(this).addClass('ui-state-default');
            });
            $('.row > input[type=text], textarea').bind('mouseover',function(){
                $(this).addClass('ui-state-hover');
            })
            $('.row > input[type=text], textarea').bind('mouseout',function(){
                $(this).removeClass('ui-state-hover');
            })
        ";
        return $script;
    }
    
    /**
     * @param string $scriptID  Please see CClientScript::registerScript()
     * @param string $idOrClass Id or Class
     */
    public static function registerMouseOverAndOutUI($scriptID,$idOrClass)
    {
        Yii::app()->clientScript->registerScript("$scriptID","
            $('$idOrClass').live('mouseover',function(){
                jQuery(this).addClass('ui-state-hover').removeClass('ui-state-default').removeClass('ui-state-active');
            });

            $('$idOrClass').live('mouseout',function(){
                jQuery(this).addClass('ui-state-default').removeClass('ui-state-hover').removeClass('ui-state-active');
            });
        ",CClientScript::POS_READY);
    }
    
    /**
     *
     * @param string $scriptID Please see CClientScript::registerScript()
     */
    public static function scriptForTipTip($scriptID)
    {
        Yii::app()->clientScript->registerScript($scriptID,"
            $('#refresh_grid1').live('mouseover',function(){
                $(this).tipTip({defaultPosition:'right'});
            });

            $('.btnUpdate').live('mouseover',function(){
                $(this).tipTip({defaultPosition:'right'});
            });    

            $('input').live('mouseover',function(){
                $(this).tipTip({defaultPosition:'right'});
            });

            $('textarea').live('mouseover',function(){
                $(this).tipTip({defaultPosition:'right'});
            });
        ");
    }
    
    /**
     * Prevents browser's Back Button when clicked
     * @param int $scriptID script ID
     */
    public static function preventBackButton($scriptID)
    {
        Yii::app()->clientScript->registerScript("$scriptID","
                $(document).ready(function(){
                    preventBackandForward();
                    
                    window.inhibited_load=preventBackandForward;
                    window.onpageshow=function(evt){if(evt.persisted)preventBackandForward();};
                    window.inhibited_unload=function(){void(0);};                
                });
                
                function preventBackandForward()
                {
                    window.history.forward();
                }
            ");
    }
    
    /**
     * Prevents Mouse Right Click Function
     * @param int $scriptID script ID
     */
    public static function preventMouseRightClick($scriptID)
    {
         Yii::app()->clientScript->registerScript("$scriptID","
                $(document).ready(function(){
//                    $(document).bind('contextmenu',function(e){
//                        return false;
//                    });
                });
            ");
    }
    
    /**
     * Prevents copy, cut, paste event
     * @param int $scriptID script ID
     */
    public static function preventCutCopyPaste($scriptID)
    {
        Yii::app()->clientScript->registerScript("$scriptID","
                $(document).ready(function(){
                    $('input').bind('cut copy paste', function (e) {
                        e.preventDefault();
                    });
                }); 
            ");
    }
}
