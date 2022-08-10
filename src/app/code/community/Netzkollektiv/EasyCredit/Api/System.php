<?php
namespace Netzkollektiv\EasyCredit\Api;

class System implements \Netzkollektiv\EasyCreditApi\SystemInterface {

    public function getSystemVendor() {
        if (method_exists('\Mage','getOpenMageVersion')) {
            return 'OpenMage';
        }
        return 'Magento';
    }

    public function getSystemVersion() {
        if (method_exists('\Mage','getOpenMageVersion')) {
            return \Mage::getOpenMageVersion();
        }
        return \Mage::getVersion();
    }

    public function getModuleVersion() {
        return (string) \Mage::getConfig()->getNode()->modules->Netzkollektiv_EasyCredit->version;
    }

    public function getIntegration() {
        return 'PAYMENT_PAGE';
    }
}
