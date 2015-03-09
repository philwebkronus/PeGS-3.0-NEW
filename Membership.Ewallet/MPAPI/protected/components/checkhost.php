<?php

class CheckHost
{
    private $_callback = "fsockopen";
    protected $_host = null;
    protected $_port = null;
    protected $_mappings = array();

    public function __construct($host, $port, $method = "fsockopen")
    {
        $this->addMapping("fsockopen", "fsockopen");
        $this->addMapping("sockets", "socket_connect");
        $this->_host = $host;
        $this->_port = $port;
        $this->setMethod($method);
    }

    public function Check()
    {
        return call_user_func(array($this, $this->_callback));
    }

    protected function addMapping($name, $callback)
    {
        $this->_mappings[$name] = $callback;
    }

    protected function getMapping($name)
    {
        return $this->_mappings[$name];
    }

    protected function setMethod($method)
    {
        if (!$callback = $this->getMapping($method))
            throw new Exception ("Method $method does not exists");

        if (!method_exists($this, $callback))
            throw new Exception ("You cannot use $method in your system. Contact your administrator");
        $this->_callback = $callback;
    }

    public function fsockopen()
    {
        $connection = @fsockopen($this->_host, $this->_port);
        if ($connection) {
            fclose($connection);
            return true;
        }
        return false;
    }

    public function socket_connect()
    {
        $sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        $connection = @socket_connect($sock, $this->_host, $this->_port);
        if ($connection) {
            socket_close($sock);
            return true;
        }
        return false;
    }
}

?>