<?php
/**
 * EasyCredit Payment Extension for Magento Community Edition
 *
 * @category   Payment
 * @package    Netzkollektiv_EasyCredit
 * @author     Dominik Krebs (https://netzkollektiv.com)
 * @license    This work is free software, you can redistribute it and/or modify it
 */

class Netzkollektiv_EasyCredit_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getCheckout() {

        $logger = new \Netzkollektiv\EasyCredit\Api\Logger();
        $config = new \Netzkollektiv\EasyCredit\Api\Config();
        $clientFactory = new \Netzkollektiv\EasyCreditApi\Client\HttpClientFactory();

        $client = new \Netzkollektiv\EasyCreditApi\Client(
            $config,
            $clientFactory,
            $logger
        );
        $storage = new \Netzkollektiv\EasyCredit\Api\Storage();

        return new \Netzkollektiv\EasyCreditApi\Checkout(
            $client,
            $storage
        );
    }
}
