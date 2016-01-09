<?php
class Netzkollektiv_EasyCredit_Block_Form extends Mage_Payment_Block_Form {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('easycredit/form.phtml');
    }

    public function _construct() {
        $methodTitle = Mage::app()->getLayout()->createBlock('core/template')
            ->setMethodBlock($this)
            ->setTemplate('easycredit/method_title.phtml');
        $this->setMethodTitle('')
            ->setMethodLabelAfterHtml($methodTitle->toHtml());
    }

    public function checkAvailability() {
        try {
            Mage::helper('easycredit')->getInstallmentValues();
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return false;
    }

    public function getStoreName() {
        $name = $this->getMethod()->getConfigData('store_name');
        if (!empty(trim($name))) {
            return $name;
        }

        return Mage::getStoreConfig('general/store_information/name');
    }
}
