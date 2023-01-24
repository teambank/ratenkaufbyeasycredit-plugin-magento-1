<?php
namespace Netzkollektiv\EasyCredit\Api\Quote;

class SystemBuilder {

    public function getSystemVendor() {
        if (method_exists('\Mage','getOpenMageVersion')) {
            return 'OpenMage';
        }
        return 'Magento';
    }

    public function getSystemVersion() {
        if (method_exists('\Mage','getOpenMageVersion')) {
            return \Mage::getOpenMageVersion(); // @phpstan-ignore-line
        }
        return \Mage::getVersion();
    }

    public function getModuleVersion() {
        return (string) \Mage::getConfig()->getNode()->modules->Netzkollektiv_EasyCredit->version;
    }

    public function build()
    {
        return new \Teambank\RatenkaufByEasyCreditApiV3\Model\Shopsystem(
            [
            'shopSystemManufacturer' => implode(' ', [$this->getSystemVendor(),$this->getSystemVersion()]),
            'shopSystemModuleVersion' => $this->getModuleVersion()
            ]
        );
    }
}
