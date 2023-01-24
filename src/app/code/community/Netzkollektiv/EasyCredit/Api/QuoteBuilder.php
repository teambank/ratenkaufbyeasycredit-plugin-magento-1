<?php
namespace Netzkollektiv\EasyCredit\Api;

use Mage;
use Teambank\RatenkaufByEasyCreditApiV3 as ApiV3;

class QuoteBuilder {

    protected $_quote;
    protected $customerSession;
    protected $_salesOrderCollection = null;
    protected $storage;
    protected $addressBuilder;
    protected $customerBuilder;
    protected $systemBuilder;

    public function __construct() {
        $this->_quote = Mage::getSingleton('checkout/session')->getQuote();
        $this->_salesOrderCollection = \Mage::getModel('sales/order')->getCollection();
        $this->customerSession = \Mage::getSingleton('customer/session');

        $this->storage = new Storage();

        $this->addressBuilder = new Quote\AddressBuilder();
        $this->customerBuilder = new Quote\CustomerBuilder();
        $this->systemBuilder = new Quote\SystemBuilder();
    }

    public function getId() {
        return $this->_quote->getId();
    }

    public function getShippingMethod() {
        if ($this->_quote->getShippingAddress()) {
            return $this->_quote->getShippingAddress()->getShippingMethod();
        }
    }

    public function getIsClickAndCollect() {
        return $this->_quote->getShippingAddress()->getShippingMethod()
            == \Mage::getStoreConfig('payment/easycredit/clickandcollect_shipping_method');
    }

    public function getGrandTotal() {
        return $this->_quote->getGrandTotal();
    }

    public function getDuration() {
        return $this->storage->get('duration');
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
            $quoteBuilder = new Quote\ItemBuilder();
            $_items[] = $quoteBuilder->build($item);
        }
        return $_items;
    }

    private function getCustomerCreatedAt()
    {
        if (!$this->customerSession->isLoggedIn()) {
            return null;
        }
        return date('Y-m-d',strtotime($this->_quote->getCustomer()->getCreatedAt()));
    }

    public function getCustomerOrderCount() {
        if (!$this->customerSession->isLoggedIn()) {
            return 0;
        }

        return $this->_salesOrderCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->_quote->getCustomer()->getId())
            ->count();
    }

    private function getRedirectLinks()
    {
        if (!$this->storage->get('sec_token')) {
            $this->storage->set('sec_token', bin2hex(random_bytes(20)));
        }

        return new ApiV3\Model\RedirectLinks(
            [
            'urlSuccess' => Mage::getUrl('*/*/return'),
            'urlCancellation' => Mage::getUrl('*/*/cancel'),
            'urlDenial' => Mage::getUrl('*/*/reject'),
            'urlAuthorizationCallback' =>   Mage::getUrl(
                'easycredit/checkout/authorize', [
                'secToken' => $this->storage->get('sec_token')
                ]
            )
            ]
        );
    }

    public function build(): ApiV3\Model\Transaction
    {
        return new ApiV3\Model\Transaction(
            [
            'financingTerm' => $this->getDuration(),
            'orderDetails' => new ApiV3\Model\OrderDetails(
                [
                'orderValue' => $this->getGrandTotal(),
                'orderId' => $this->getId(),
                'numberOfProductsInShoppingCart' => count($this->_quote->getAllVisibleItems()),
                'invoiceAddress' => $this->addressBuilder
                    ->setAddress(new ApiV3\Model\InvoiceAddress())
                    ->build($this->_quote->getBillingAddress()),
                'shippingAddress' => $this->addressBuilder
                    ->setAddress(new ApiV3\Model\InvoiceAddress())
                    ->build($this->_quote->getShippingAddress()),
                'shoppingCartInformation' => $this->getItems()
                ]
            ),
            'shopsystem' => $this->systemBuilder->build(),
            'customer' => $this->customerBuilder->build(
                $this->_quote
            ),
            'customerRelationship' => new ApiV3\Model\CustomerRelationship(
                [
                'customerSince' => $this->getCustomerCreatedAt(),
                'orderDoneWithLogin' => $this->customerSession->isLoggedIn(),
                'numberOfOrders' => $this->getCustomerOrderCount(),
                'logisticsServiceProvider' => $this->getShippingMethod()
                ]
            ),
            'redirectLinks' => $this->getRedirectLinks()
            ]
        );
    }
}
