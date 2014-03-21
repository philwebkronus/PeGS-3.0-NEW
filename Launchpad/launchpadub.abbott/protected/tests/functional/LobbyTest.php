<?php

class LobbyTest extends WebTestCase
{
    public function testIndex()
    {
        $this->open('');
        $this->setBrowserUrl('http://localhost/lp2/index-test.php/?r=launchpad/lobby/index');
        
        
        
        
        $this->assertTextPresent('Nice');
    }
    
    
}