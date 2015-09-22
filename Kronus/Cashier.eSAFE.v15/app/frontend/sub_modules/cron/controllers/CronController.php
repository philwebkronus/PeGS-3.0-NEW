<?php
class CronController extends MI_Controller{
    
    public function runAction() {
        Mirage::loadLibraries('util');
        Mirage::loadComponents(array('CasinoApi'));
        Mirage::loadModuleLibraries('cron',array('myLibraryTest','testing'));
        $a = helloWorld();
        $b = testing();
        echo $b;
        die($a);
    }
}

