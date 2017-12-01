<?php
namespace Netzkollektiv\EasyCredit\Api;

class Config extends \Netzkollektiv\EasyCreditApi\Config {

    public function __construct() {
       $this->_apiKey = \Mage::getStoreConfig('payment/easycredit/api_key');
       $this->_apiToken = \Mage::getStoreConfig('payment/easycredit/api_token');
    }

    public function getWebshopId() {
        if (!isset($this->_apiKey) || empty($this->_apiKey)) {
            throw new \Exception('api key not configured');
        }
        return $this->_apiKey;
    }

    public function getWebshopToken() {
        if (!isset($this->_apiToken) || empty($this->_apiToken)) {
            throw new \Exception('api token not configured');
        }
        return $this->_apiToken;
    }
}
