<?php
namespace Netzkollektiv\EasyCredit\Api;

class Logger {

    protected $filename = 'easycredit.log';
    protected $debug = false;

    public function __construct() {
        if (\Mage::getStoreConfig('payment/easycredit/debug_logging')) {
            $this->debug = true;
        }
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function emergency($message, array $context = array()) {
        \Mage::log($message, \Zend_Log::EMERG, $this->filename, true);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function alert($message, array $context = array()) {
        \Mage::log($message, \Zend_Log::ALERT, $this->filename, true);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function critical($message, array $context = array()) {
        \Mage::log($message, \Zend_Log::CRIT, $this->filename, true);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function error($message, array $context = array()) {
        \Mage::log($message, \Zend_Log::ERR, $this->filename, true);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function warning($message, array $context = array()) {
        \Mage::log($message, \Zend_Log::WARN, $this->filename, true);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function notice($message, array $context = array()) {
        \Mage::log($message, \Zend_Log::NOTICE, $this->filename, $this->debug);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function info($message, array $context = array()) {
        \Mage::log($message, \Zend_Log::INFO, $this->filename, $this->debug);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    public function debug($message, array $context = array()) {
        \Mage::log($message, \Zend_Log::DEBUG, $this->filename, $this->debug);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = array()) {
        \Mage::log($message, null, $this->filename);
    }
}
