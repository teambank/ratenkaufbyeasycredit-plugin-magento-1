<?php
namespace Netzkollektiv\EasyCredit\Api;

class System implements \Netzkollektiv\EasyCreditApi\SystemInterface {

    public function getSystemVendor() {
        return 'Magento';
    }

    public function getSystemVersion() {
        return \Mage::getVersion();
    }

    public function getModuleVersion() {
        return (string) \Mage::getConfig()->getNode()->modules->Netzkollektiv_EasyCredit->version;
    }

    public function getIntegration() {
        return 'PAYMENT_PAGE';
    }
}
