<?php
require_once __DIR__.'/../../vendors/nusoap/nusoap.php';
Yii::import('application.modules.launchpad.components.casinoapi.AbstractCasino');
Yii::import('application.modules.launchpad.components.casinoapi.MGInjector');

/**
 * Description of MGInjectorTest
 *
 * @author Bryan Salazar
 */
class MGInjectorTest extends CTestCase{
    
    /**
     *
     * @var MGInjector 
     */
    public $obj;
    public $account = 'OC0000104837';
    public $amount = 500;
    
    /**
     * Configuration of mg
     * @var array 
     */
    public $configuration = array();    
    
    protected function setUp() 
    {
        parent::setUp();
        $this->obj = MGInjector::app();
        $this->configuration = array(
            'service_api'=>'https://entservices.totalegame.net/EntServices.asmx?WSDL',
            'session_guid'=>'66e6076b-ae42-46e7-9214-8850b33de4aa',
            'currency'=>9,
        );
    }
    
    public function testApp()
    {
        $this->assertInstanceOf('MGInjector', MGInjector::app());
    }
    
    public function testSetConfiguration()
    {
        $this->assertNotEmpty($this->obj->setConfiguration($this->configuration));
    }
    
    public function test_checkConfiguration()
    {
        $method = new ReflectionMethod(
            'MGInjector', '_checkConfiguration'
        );  
        
        $method->setAccessible(TRUE);
        $this->assertTrue($method->invoke(MGInjector::app()));
    }
    
    public function testGetBalance()
    {
        $this->assertNotEquals(false, $this->obj->getBalance($this->account),'Please check if session guid is expired. Please replace session_guid in configuration.');
    }
    
    public function testDeposit()
    {
        $this->assertEquals('true', $this->obj->deposit($this->account, $this->amount),'Please check if session guid is expired. Please replace session_guid in configuration.');
    }
    
    public function testWithdraw()
    {
        $this->assertEquals('true', $this->obj->withdraw($this->account, $this->amount),'Please check if session guid is expired. Please replace session_guid in configuration.');
    }
}
