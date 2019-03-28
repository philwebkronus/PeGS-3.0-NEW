<?php

/**
 * Abstract class for all casino api
 * @package application.modules.launchpad.components.casinoapi
 * @author Bryan Salazar
 */
abstract class AbstractCasino {
    public abstract function getBalance($account);
    public abstract function withdraw($account,$amount,$tracking1,$tracking2,$tracking3,$tracking4, $terminal_pwd, $ticket_id);
    public abstract function deposit($account,$amount,$tracking1,$tracking2,$tracking3,$tracking4, $terminal_pwd, $ticket_id);
    public abstract function setConfiguration(array $configuration);
    public abstract function getGenericStatus();
    public abstract function getActualResponse();
    protected abstract function _checkConfiguration();
    public abstract function getTransactionID($default=null);
}
