<?php

require_once '../models/LPConfig.php';

/**
 * Bootstrap for launchpad modules
 * @package application.modules.launchpad
 */
class LaunchpadController
{
    /**
     * @var string base directory publish assets of module launchpad
     */	
    public $baseAssets;
    public $defaultController = 'Lobby';
        

    /**
     * This method is called before any module controller action is performed
     * @param string $controller
     * @param string $action
     */
    public function getTerminalCode()
    {
                return LPConfig::app()->params['TerminalCode'];
    }
}
