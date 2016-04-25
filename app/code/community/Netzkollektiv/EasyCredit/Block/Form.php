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

    protected function _checkCustomerSameAsBilling() {
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        if (!$quote->getCustomer()->getId()) {
            return true;
        }

        foreach (array('firstname','lastname') as $attribute) {
            $billingValue = strtolower($quote->getBillingAddress()->getData($attribute));
            $customerValue = strtolower($quote->getCustomer()->getData($attribute));
            if (trim($billingValue) != trim($customerValue)) {
                return false;
            }
        }
        return true;
    }

    public function checkAvailability() {
        if (!$this->_checkCustomerSameAsBilling()) {
            return 'Um Ihren Warenkorb mit Ratenkauf by easyCredit bestellen zu können, müssen der Rechnungsempfänger und der Inhaber des Kundenkontos identisch sein. 
                Bitte ändern Sie den Namen des Rechnungsempfängers entsprechend ab.';
        }

        try {
            Mage::helper('easycredit')->getInstallmentValues();
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return false;
    }

    public function getStoreName() {
        $name = $this->getMethod()->getConfigData('store_name');
        $name = trim($name);
        if (!empty($name)) {
            return $name;
        }

        return Mage::getStoreConfig('general/store_information/name');
    }
}
