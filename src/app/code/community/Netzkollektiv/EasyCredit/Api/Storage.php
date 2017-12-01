<?php
namespace Netzkollektiv\EasyCredit\Api;

class Storage implements \Netzkollektiv\EasyCreditApi\StorageInterface {

    protected $_payment;

    public function __construct() {
        $this->_payment = \Mage::getSingleton('checkout/session')
            ->getQuote()
            ->getPayment();
    }

    public function set($key, $value) {
        $this->_payment->setAdditionalInformation($key, $value);
        return $this;
    }

    public function get($key) {
        return $this->_payment->getAdditionalInformation($key);
    }

    public function clear() {
        $this->_payment->unsAdditionalInformation()->save();
    }
}
