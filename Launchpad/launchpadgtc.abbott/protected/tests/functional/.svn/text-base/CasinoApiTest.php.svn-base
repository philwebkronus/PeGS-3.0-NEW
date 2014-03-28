<?php
require_once __DIR__.'/../../vendors/nusoap/nusoap.php';
Yii::import('application.modules.launchpad.components.casinoapi.AbstractCasino');
Yii::import('application.modules.launchpad.components.casinoapi.RTGInjector');
Yii::import('application.modules.launchpad.components.casinoapi.MGInjector');
Yii::import('application.modules.launchpad.components.casinoapi.CasinoApi');

/**
 * Test for CasinoApi
 * 
 * @author Bryan Salazar
 */
class CasinoApiTest extends CTestCase{
    
    /**
     *
     * @var CasinoApi
     */
    public $objRTG;
    public $objMG;
    public $configuration;
    public $rtg_account = 'ICSA-EXE07';
    public $mg_account = 'OC0000104837';
    
    public $amount = 500;
    
    protected function setUp() {
        parent::setUp();
//        $this->obj = CasinoApi::app('');
        $this->objRTG = CasinoApi::app('rtg');
        $this->objMG = CasinoApi::app('mg');
    }
    
    public function testApp()
    {
        $this->assertInstanceOf('CasinoApi', CasinoApi::app('rtg'));
    }
    
    public function testSetCasinoApiRTG()
    {
        $this->assertInstanceOf('CasinoApi', $this->objRTG->setCasinoApi(RTGInjector::app()));
    }
    
    public function testSetConfigurationRTG()
    {
        $this->configuration = array(
            'service_api'=>'https://202.44.102.31/ECFDEMOFDGEFNPGEMFOQ/processor/processorapi/cashier.asmx',
            'RTGClientCertsPath'=>__DIR__.'/RTGClientCerts/13/cert.pem',
            'RTGClientKeyPath'=>__DIR__.'/RTGClientCerts/13/key.pem',
            'deposit_method_id' =>503,
            'withdraw_method_id'=>502,
        );
        $this->assertInstanceOf('CasinoApi', $this->objRTG->setConfiguration($this->configuration));
    }
    
    public function testGetBalanceRTG()
    {
        $this->assertNotEquals(false, $this->objRTG->getBalance($this->rtg_account));
    }
    
    public function testDepositRTG()
    {
        $this->assertEquals('TRANSACTIONSTATUS_APPROVED', $this->objRTG->deposit($this->rtg_account, $this->amount, '', '', '', ''));
    }
    
    public function testWithdrawRTG()
    {
        $this->assertEquals('TRANSACTIONSTATUS_APPROVED', $this->objRTG->withdraw($this->rtg_account, $this->amount, '', '', '', ''));
    }
    
    public function testSetCasinoApiMG()
    {
        $this->assertInstanceOf('CasinoApi', $this->objMG->setCasinoApi(MGInjector::app()));
    }
    
    public function testSetConfigurationMG()
    {
        $this->configuration = array(
            'service_api'=>'https://entservices.totalegame.net/EntServices.asmx?WSDL',
            'session_guid'=>'c1496aa2-5160-40c7-b9da-fb75a16d41c9',
            'currency'=>9,
        );
        $this->assertInstanceOf('CasinoApi', $this->objMG->setConfiguration($this->configuration));
    }    
    
    public function testGetBalanceMG()
    {
        $this->assertNotEquals(false, $this->objMG->getBalance($this->mg_account),'Please check if session guid is expired. Please replace session_guid in configuration.');
    }
    
    public function testDepositMG()
    {
        $this->assertEquals('true', $this->objMG->deposit($this->mg_account, $this->amount, '', '', '', ''),'Please check if session guid is expired. Please replace session_guid in configuration.');
    }
    
    public function testWithdrawMG()
    {
        $this->assertEquals('true', $this->objMG->withdraw($this->mg_account, $this->amount, '', '', '', ''),'Please check if session guid is expired. Please replace session_guid in configuration.');
    }
}