<?php

class App
{

    /**
     * Loads a class for use. (Not yet instantiated)
     * @author Roger Sanchez
     * @param String $classpath The path (relative to rsframework/include) of the class to be loaded.
     * @filesource relative to rsframework/include
     * @example App::LoadClass("core/BaseObject.class.php"). Loads the class BaseObject.class.php from the directory rsframework/include/core/.
     * @deprecated This method is already obsolete. Please check NewLoadClass, LoadModule, LoadModuleClass
     */
    function LoadClass($classpath)
    {
        require_once(App::getParam("includedir") . $classpath);
    }

    /**
     * Loads a class for use. (Not yet instantiated)
     * @author Roger Sanchez
     * @param String $classname The name of the class to be loaded located on rsframework/include/classes.
     * @filesource relative to rsframework/include/classes
     * @example App::LoadClass("BaseObject.class.php"). Loads the class BaseObject.class.php from the directory rsframework/include/classes/.
     * @deprecated This method is already obsolete. Please check NewLoadClass, LoadModule, LoadModuleClass
     */
    function NewLoadClass($classname)
    {
        require_once (App::getParam("classdir") . $classname);
    }

    /**
     * Loads a class for database use. (Not yet instantiated)
     * @author Roger Sanchez
     * @param String $classname The name of the database wrapper to be loaded located on rsframework/include/datalayer.
     * @filesource relative to rsframework/include/datalayer
     * @example App::LoadDataClass("MySQL.class.php"). Loads the class MySQL.class.php from the directory rsframework/include/datalayer/.
     */
    function LoadDataClass($classname)
    {
        require_once (App::getParam("dataclassdir") . $classname);
    }

    /**
     * Loads configuration settings from the settings directory (default: rsframework/include/settings)
     * @author Roger Sanchez
     * @param String $settingsfile The name of the settings file to be loaded located on rsframework/include/settings.
     * @filesource relative to rsframework/include/settings
     * @example App::LoadDataSettings("settings.inc.php"). Loads the default rsframework settings.
     */
    function LoadSettings($settingsfile)
    {
        require_once (App::getParam("settingsdir") . $settingsfile);
    }

    /**
     * Loads the fundamental classes/helpers of the rsframework from rsframework/include/core
     * @author Roger Sanchez
     * @param String $corefile The name of the core class/helper to be loaded located on rsframework/include/core.
     * @filesource relative to rsframework/include/core
     * @example App::LoadDataCore("CSV.class.php"). Loads CSV the helper .
     */
    function LoadCore($corefile)
    {
        require_once (App::getParam("coredir") . $corefile);
    }

    /**
     * Loads the built in controls of the rsframework from rsframework/include/controls
     * @author Roger Sanchez
     * @param String $controlfile The name of the control to be loaded located on rsframework/include/controls.
     * @filesource relative to rsframework/include/controls
     * @example App::LoadControl("TextBox"). Loads TextBox control.
     */
    function LoadControl($control)
    {
        App::LoadModule("Controls");
        require_once (App::getParam("controlsdir") . $control . ".class.php");
    }

    /**
     * Loads a third party library from rsframework/include/lib
     * @author Roger Sanchez
     * @param String $libraryfile The name of the library to be loaded located on rsframework/include/lib.
     * @filesource relative to rsframework/include/lib
     * @example App::LoadLibrary("nusoap/nusoap.class.php"). Loads nusoap class.
     */
    function LoadLibrary($libraryfile)
    {
        require_once (App::getParam("librarydir") . $libraryfile);
    }

    /**
     * Loads all classes of a Module from rsframework/include/Modules/[$modulename]
     * @author Roger Sanchez
     * @param String $modulename The name of the Module to be loaded located on rsframework/include/Modules/[$modulename].
     * REQUIRED: an include file Module.inc.php should be present on the Module directory
     * @filesource relative to rsframework/include/Modules/[$modulename]
     * @example App::LoadModule("Controls"). Loads all classes on the Controls Module.
     */
    function LoadModule($modulename)
    {
        require_once (App::getParam("moduledir") . $modulename . "/classes/Module.inc.php");
        eval('$moduleclass = new ' . $modulename . 'ModuleClass();');
        $newclasses = $moduleclass->arrClasses;
        foreach ($newclasses as $key => $value)
        {
            require_once (App::getParam("moduledir") . $modulename . "/classes/$key.class.php");
        }
    }

    /**
     * @author Roger Sanchez
     * Loads a Class/Model from the framework Modules
     * @param String $modulename = The Module on the framework located on rsframework/include/Modules
     * @param String $classname = Name of class located on rsframework/include/Modules/$modulename/$classname.class.php
     * @example App::LoadModuleClass("Controls", "BaseControl"). Loads the BaseControl class from rsframework/include/Modules/$modulename/.
     */
    function LoadModuleClass($modulename, $classname)
    {
        $filename = App::getParam("moduledir") . $modulename . "/classes/" . $classname . ".class.php";

        if (file_exists($filename))
        {
            require_once ($filename);
        }
        else
        {
            App::SetErrorMessage("Module Class ($modulename, $classname) does not exist.", false, true);
        }
    }

    /**
     * Gets a configuration value
     * @author Roger Sanchez
     * @param String $paramname The name of the configuration parameter.
     * @example App::getParam("basedir"). Returns the base directory of rsframework.
     */
    function getParam($paramname)
    {
        global $_CONFIG;
        if (isset($_CONFIG[$paramname]))
        {
            return $_CONFIG[$paramname];
        }
        else
        {
            return null;
        }
    }

    /**
     * Gets a database configuration value
     * @author Roger Sanchez
     * @param String $paramname The name of the database configuration parameter.
     * @example App::getParam("localhostserver"). Returns the connection information of the localserver database from the dbsettings.inc.php file.
     */
    function getDBParam($paramname)
    {
        global $_DBCONF;
        if (isset($_DBCONF[$paramname]))
        {
            return $_DBCONF[$paramname];
        }
        else
        {
            //App::SetErrorMessage("Database Parameter $paramname does not exist.", true, false);
            return false;
        }
    }

    /**
     * Gets the values from $_POST["act"]
     * @author Roger Sanchez
     * @return POST value
     * @example App::GetAction(). Returns the array of values from $_POST["act"].
     * @deprecated Please use the FormsProcessor class instead
     */
    function GetAction()
    {
        if (isset($_POST["act"]))
        {
            return $_POST["act"];
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns the values from $_POST[$arrFormEntries]
     * @author Roger Sanchez
     * @param type $arrFormEntries
     * @return Array
     * @deprecated Please use the FormsProcessor class instead
     */
    function GetFormValues($arrFormEntries)
    {
        if (isset($_POST[$arrFormEntries]))
        {
            return $_POST[$arrFormEntries];
        }
    }

    /**
     * Prints in <pre> variables from print_r()
     * @author Roger Sanchez
     * @param type $variable 
     */
    function Pr($variable)
    {
        print_r("<pre>");
        print_r($variable);
        print_r("</pre>");
    }

    /**
     * Sets the Application Status Message.
     * @author Roger Sanchez
     * @param String $statusmessage The message that was thrown
     */
    function SetStatusMessage($statusmessage)
    {
        $_SESSION["_ApplicationStatusMessage"] = $statusmessage;
    }

    /**
     * Returns the Application Status Message.
     * @author Roger Sanchez
     * @return string Returns the  
     */
    function GetStatusMessage()
    {
        if (isset($_SESSION["_ApplicationStatusMessage"]))
        {
            return $_SESSION["_ApplicationStatusMessage"];
        }
        else
        {
            return false;
        }
    }

    /**
     * Sets the Status Code of the application.
     * @author Roger Sanchez
     * @param AppStatusCode $statuscode 
     */
    function SetStatusCode($statuscode)
    {
        $_SESSION["_ApplicationStatusCode"] = $statuscode;
    }

    /**
     * Returns the StatusCode of the application.
     * @author Roger Sanchez
     * @return AppStatusCode 
     */
    function GetStatusCode()
    {
        if (isset($_SESSION["_ApplicationStatusCode"]))
        {
            return $_SESSION["_ApplicationStatusCode"];
        }
        else
        {
            return false;
        }
    }

    /**
     * Sets the StatusCode and StatusMessage of the application
     * @author Roger Sanchez
     * @param AppStatusCode $statuscode
     * @param String $statusmessage 
     */
    function SetStatus($statuscode, $statusmessage)
    {
        App::SetStatusCode($statuscode);
        App::SetStatusMessage($statusmessage);
    }

    /**
     * Returns the StatusCode and StatusMessage in an array.
     * @author Roger Sanchez
     * @return array 
     */
    function GetStatus()
    {
        return array("StatusCode" => App::GetStatusCode(), "StatusMessage" => App::GetStatusMessage());
    }

    /**
     * Clears the current application Status Code and Status Message
     * @author Roger Sanchez
     */
    function ClearStatus()
    {
        unset($_SESSION["_ApplicationStatusCode"]);
        unset($_SESSION["_ApplicationStatusMessage"]);
    }

    /**
     * Sets the Application StatusCode to Error and defines the Error Message
     * @author Roger Sanchez
     * @param String $errormessage The string description of the error.
     */
    function SetErrorMessage($errormessage, $throwexception = false, $dieonerror = false)
    {
        App::SetStatus(AppStatusCode::Error, $errormessage);
        if ($throwexception)
        {
            $ex = new Exception($errormessage);
            throw $ex;
        }
        if ($dieonerror)
        {
            die($errormessage);
        }
    }

    /**
     * Returns the Error Message of the application if it has. Returns false if there is no Error Message.
     * @author Roger Sanchez
     * @return String Returns
     */
    function GetErrorMessage()
    {
        if (App::GetStatusCode() == AppStatusCode::Error)
        {
            return App::GetStatusMessage();
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns true if the application catches errors and returns false if not.
     * @author Roger Sanchez
     * @return Boolean 
     */
    function HasError()
    {
        if (App::GetStatusCode() == AppStatusCode::Error)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns true if the application has a Success Message and returns false if not.
     * @author Roger Sanchez
     * @return Boolean 
     */
    function HasSucceeded()
    {
        if (App::GetStatusCode() == AppStatusCode::Success)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Returns true if the application catches warnings and returns false if not.
     * @author Roger Sanchez
     * @return type 
     */
    function HasWarning()
    {
        if (App::GetStatusCode() == AppStatusCode::Warning)
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     * Sets the Success Message of the application
     * @author Roger Sanchez
     * @param type $successmessage 
     */
    function SetSuccessMessage($successmessage)
    {
        App::SetStatus(AppStatusCode::Success, $successmessage);
    }

    /**
     * Returns the Success Message of the application
     * @author Roger Sanchez
     * @return type 
     */
    function GetSuccessMessage()
    {
        if (App::GetStatusCode() == AppStatusCode::Success)
        {
            return App::GetStatusMessage();
        }
        else
        {
            return false;
        }
    }
    
    /**
    * @Description: Generate Alphanumeric combination for security code (For Coupon and Item Redemption)
     * @Author: aqdepliyan
     * @DateCreated: 2013-10-23
    * @param int $length
    * @return string
    */
   public function mt_rand_str ($length) {
       $c = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
       $s = '';
       $cl = strlen($c)-1;
       for ($cl = strlen($c)-1, $i = 0; $i < $length; $s .= $c[mt_rand(0, $cl)], ++$i);
       return $s;
   }

}

/**
 * Contains the defined Status Codes of the application.
 * @example AppStatusCode::Success, AppStatusCode::Error, AppStatusCode::Warning
 */
class AppStatusCode
{
    const Success = 10;
    const Error = 9;
    const Warning = 8;
}

?>