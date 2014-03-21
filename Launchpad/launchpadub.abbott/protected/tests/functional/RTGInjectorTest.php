<?php
Yii::import('application.modules.launchpad.components.casinoapi.AbstractCasino');
Yii::import('application.modules.launchpad.components.casinoapi.RTGInjector');

/**
 * Description of RTGInjectorTest. Please check all parameters use before testing
 *
 * @author Bryan Salazar
 */
class RTGInjectorTest extends CTestCase {
    /**
     *
     * @var RTGInjector 
     */
    public $obj;
    
    /**
     * Configuration of rtg
     * @var array 
     */
    public $configuration = array();
    
    public $account = 'ICSA-EXE01';
//    public $icsa_exe07_pid = '10010645';
    public $amount = 500;
    
    
    protected function setUp()
    {
        parent::setUp();
        $this->obj = RTGInjector::app();
        $this->configuration = array(
//            'account' =>$this->account,
            'service_api'=>'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
            'RTGClientCertsPath'=>__DIR__.'/RTGClientCerts/7/cert.pem',
            'RTGClientKeyPath'=>__DIR__.'/RTGClientCerts/7/key.pem',
            'deposit_method_id' =>503,
            'withdraw_method_id'=>502,
        );
    }
    
    public function testApp()
    {
        $this->assertInstanceOf('RTGInjector', RTGInjector::app());
    }
    
    public function testSetConfiguration()
    {
        $this->assertNotEmpty($this->obj->setConfiguration($this->configuration));
    }
    
    public function test_checkConfiguration()
    {
        $method = new ReflectionMethod(
            'RTGInjector', '_checkConfiguration'
        );  
        $method->setAccessible(TRUE);
        $this->assertTrue($method->invoke(RTGInjector::app()));
    }
    
//    public function test_getPIDFromLogin() {
//        $method = new ReflectionMethod(
//            'RTGInjector', '_getPIDFromLogin'
//        );  
//        $method->setAccessible(TRUE);
//        $this->assertEquals($this->icsa_exe07_pid, $method->invoke(RTGInjector::app(),$this->account));
//    }
    
    public function testGetBalance() 
    {
        $method = new ReflectionMethod(
            'RTGInjector', '_getPIDFromLogin'
        );  
        $method->setAccessible(TRUE);        
        $method->invoke(RTGInjector::app(),$this->account);
//        $this->assertNotEquals(false, $this->obj->getBalance($this->account));
        $this->assertEquals('1000.0000', $this->obj->getBalance($this->account));
    }
    
    public function testGenericStatus()
    {
        $this->assertEquals(true, $this->obj->getGenericStatus());
    }
    
    public function test_getAccountInfoByPID()
    {
        $method = new ReflectionMethod(
            'RTGInjector', '_getAccountInfoByPID'
        );  
        $method->setAccessible(TRUE);
        $this->assertNotEquals(false, $method->invoke(RTGInjector::app(),$this->account));
    }
    
    public function test_login() 
    {
        $method = new ReflectionMethod(
            'RTGInjector', '_login'
        );          
        $method->setAccessible(TRUE);
        $this->assertNotEquals(false, $method->invoke(RTGInjector::app(),$this->account));
    }
    
//    public function testDeposit()
//    {
//        $this->assertEquals('TRANSACTIONSTATUS_APPROVED', $this->obj->deposit($this->account, $this->amount, '', '', '', ''));
//    }
//    
//    public function testWithdraw()
//    {
//        $this->assertEquals('TRANSACTIONSTATUS_APPROVED', $this->obj->withdraw($this->account, $this->amount, '', '', '', ''));
//    }
}