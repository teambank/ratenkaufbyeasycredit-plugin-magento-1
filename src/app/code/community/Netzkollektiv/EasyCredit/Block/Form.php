<?php
/**
 * EasyCredit Payment Extension for Magento Community Edition
 *
 * @category   Payment
 * @package    Netzkollektiv_EasyCredit
 * @author     Dominik Krebs (https://netzkollektiv.com)
 * @license    This work is free software, you can redistribute it and/or modify it
 */

class Netzkollektiv_EasyCredit_Block_Form extends Mage_Payment_Block_Form {

    protected $_customerPrefixes = array('Herr','Frau');

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

        $quote = new \Netzkollektiv\EasyCredit\Api\Quote();
        $checkout = Mage::helper('easycredit')->getCheckout();

        try {
            $checkout->isAvailable($quote);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return false;
    }

    public function getAgreement() {
        $error = false;

        try {
            $text = Mage::helper('easycredit')->getCheckout()
                ->getAgreement();
        } catch (Exception $e) {
            $text = $this->__($e->getMessage());
            $error = true;
        }

        return array("text" => $text, "error" => $error);
    }

    public function hasCustomerPrefix() {
        return Mage::helper('easycredit')->getCheckout()->isPrefixValid(
            Mage::getSingleton('checkout/session')->getQuote()
                ->getCustomerPrefix()
        ); 
    }

    public function getAllowedCustomerPrefixes() {
        return $this->_customerPrefixes;
    }
}
