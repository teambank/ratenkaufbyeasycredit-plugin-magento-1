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

    public function getWebshopId() {
        return Mage::getStoreConfig('payment/easycredit/api_key');
    }

    public function getGrandTotal() {
        return Mage::getModel('checkout/session')->getQuote()->getGrandTotal();
    }

    public function checkAvailability() {

        $checkout = Mage::helper('easycredit')->getCheckout();

        $ecQuote = new \Netzkollektiv\EasyCredit\Api\QuoteBuilder();
        $quote = $ecQuote->build();

        try {
            $checkout->isAvailable($quote);
        } catch (Exception $e) {
            return $e->getMessage();
        }
        return false;
    }
}
