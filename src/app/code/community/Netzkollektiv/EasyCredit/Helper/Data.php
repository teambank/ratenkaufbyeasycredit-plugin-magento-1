<?php
/**
 * EasyCredit Payment Extension for Magento Community Edition
 *
 * @category   Payment
 * @package    Netzkollektiv_EasyCredit
 * @author     Dominik Krebs (https://netzkollektiv.com)
 * @license    This work is free software, you can redistribute it and/or modify it
 */

use Teambank\RatenkaufByEasyCreditApiV3 as ApiV3;
use Netzkollektiv\EasyCredit\Api as Api;

class Netzkollektiv_EasyCredit_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getConfigValue($key) {
        return \Mage::getStoreConfig('payment/easycredit/'.$key);
    }

    protected function getClient () {
        return new ApiV3\Client(
            new Api\Logger()
        );
    }

    public function getConfig() {
        return ApiV3\Configuration::getDefaultConfiguration()
            ->setHost('https://ratenkauf.easycredit.de')
            ->setUsername($this->getConfigValue('api_key'))
            ->setPassword($this->getConfigValue('api_token'))
            ->setAccessToken($this->getConfigValue('api_signature'));
    }

    public function getCheckout() {

        $client = $this->getClient();
        $config = $this->getConfig();

        $webshopApi = new ApiV3\Service\WebshopApi(
            $client,
            $config
        );
        $transactionApi = new ApiV3\Service\TransactionApi(
            $client,
            $config
        );
        $installmentplanApi = new ApiV3\Service\InstallmentplanApi(
            $client,
            $config
        );

        return new ApiV3\Integration\Checkout(
            $webshopApi,
            $transactionApi,
            $installmentplanApi,
            new Api\Storage(),
            new ApiV3\Integration\Util\AddressValidator(),
            new ApiV3\Integration\Util\PrefixConverter(),
            new Api\Logger()
        );
    }

    public function getTransactionApi(): ApiV3\Service\TransactionApi
    {
        $client = $this->getClient();
        $config = clone $this->getConfig();
        $config->setHost('https://partner.easycredit-ratenkauf.de');

        return new ApiV3\Service\TransactionApi(
            $client,
            $config
        );
    }
}
