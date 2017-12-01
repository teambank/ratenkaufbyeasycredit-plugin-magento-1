<?php
namespace Netzkollektiv\EasyCredit\Api;

class Logger implements \Netzkollektiv\EasyCreditApi\LoggerInterface {

    protected $_logger;

    protected $debug = false;

    public function __construct() {
        if (\Mage::getStoreConfig('payment/easycredit/debug_logging')) {
            $this->debug = true;
        }
    }

    public function log($msg) {
        if (!$this->debug) {
            return;
        }

        return $this->logInfo($msg);
    }

    public function logDebug($msg) {
        if (!$this->debug) {
            return;
        }

        \Mage::log($msg, \Zend_Log::DEBUG);
        return $this;
    }

    public function logInfo($msg) {
        if (!$this->debug) {
            return;
        }

        \Mage::log($msg, \Zend_Log::INFO);
        return $this;
    }

    public function logError($msg) {
        \Mage::log($msg, \Zend_Log::ERR);
        return $this;
    }
}
