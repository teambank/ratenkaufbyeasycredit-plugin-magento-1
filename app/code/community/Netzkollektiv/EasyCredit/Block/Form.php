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
        /**
         * @var Mage_Sales_Model_Quote $quote
         */
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
            /**
             * @see Netzkollektiv_EasyCredit_Helper_Data
             */
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

    /**
     * @return string
     */
    public function getTextConsent() {
        /**
         * @var Netzkollektiv_EasyCredit_Model_Api $easyCreditApi
         */
        $easyCreditApi = Mage::getSingleton('easycredit/api');

        $error = false;

        try {
            $text = $this->__($easyCreditApi->getTextConsent());
        } catch (Exception $e) {
            $text = $this->__($e->getMessage());
            $error = true;
        }

        return array("text" => $text, "error" => $error);
    }

    public function getTextConsentConnectionErrorMessage() {
        return $this->__("Could not connect to easyCredit Server. Please try again later.");
    }

    public function getTextConsentLoadingMessage() {
        return $this->__("Loading transfer agreement from easyCredit servers...");
    }

    /**
     * @return bool
     */
    public function hasCustomerPrefix() {
        /**
         * @var Mage_Checkout_Model_Session $checkoutSession
         */
        $checkoutSession = Mage::getSingleton('checkout/session');

        /**
         * @var Mage_Sales_Model_Quote $quote
         */
        $quote = $checkoutSession->getQuote();

        $prefix = $quote->getCustomerPrefix();

        if (!empty($prefix) && array_key_exists(strtoupper($prefix), \Netzkollektiv_EasyCredit_Model_Api::getAllowedCustomerPrefixes())) {
            return true;
        } else {
            return false;
        }
    }

    public function getAllowedCustomerPrefixes() {
        return \Netzkollektiv_EasyCredit_Model_Api::getAllowedCustomerPrefixes();
    }
}
