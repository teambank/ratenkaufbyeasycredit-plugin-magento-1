<?php
namespace Netzkollektiv\EasyCredit\Api;

use Mage;

class Quote implements \Netzkollektiv\EasyCreditApi\Rest\QuoteInterface {

    public function __construct() {
        $this->_quote = Mage::getSingleton('checkout/session')->getQuote();
        $this->_customerSession = Mage::getSingleton('customer/session');
        $this->_checkoutSession = Mage::getSingleton('checkout/session');
        $this->_salesOrderCollection = Mage::getModel('sales/order')->getCollection();
        $this->_categoryResource = Mage::getResourceModel('catalog/category');
    }

    public function getId() {
        return $this->_quote->getId();
    }

    public function getShippingMethod() {
        if ($this->_quote->getShippingAddress()) {
            return $this->_quote->getShippingAddress()->getShippingMethod();
        }
    }

    public function getGrandTotal() {
        return $this->_quote->getGrandTotal();
    }

    public function getBillingAddress() {
        return new Quote\Address($this->_quote->getBillingAddress());
    }
    public function getShippingAddress() {
        return new Quote\ShippingAddress($this->_quote->getShippingAddress());
    }

    public function getCustomer() {
        return new Quote\Customer(
            $this->_customerSession,
            $this->_salesOrderCollection,
            $this->_quote->getCustomer(),
            $this->_quote->getBillingAddress()
        );
    }

    public function getItems() {
        return $this->_getItems(
            $this->_quote->getAllVisibleItems()
        );
    }

    protected function _getItems($items) {
        $_items = array();
        foreach ($items as $item) {
            if ($item->getPrice() == 0) {
                continue;
            }
            $_items[] = new Quote\Item(
                $item,
                $this->_categoryResource
            );
        }
        return $_items;
    }

    public function getSystem() {
        return new System();
    }
}
