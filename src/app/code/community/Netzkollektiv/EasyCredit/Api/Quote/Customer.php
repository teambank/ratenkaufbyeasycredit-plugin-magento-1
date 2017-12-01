<?php
namespace Netzkollektiv\EasyCredit\Api\Quote;

use Magento\Sales\Model\ResourceModel\Order\Collection as SalesOrderCollection;
use Magento\Customer\Model\Session as CustomerSession;

class Customer implements \Netzkollektiv\EasyCreditApi\Rest\CustomerInterface
{
    protected $_customer = null;
    protected $_billingAddress = null;
    protected $_customerSession = null;
    protected $_salesOrderCollection = null;

    public function __construct(
        $customerSession,
        $salesOrderCollection,
        $customer,
        $billingAddress
    ) {
        $this->_customerSession = $customerSession;
        $this->_salesOrderCollection = $salesOrderCollection;
        $this->_customer = $customer;
        $this->_billingAddress = $billingAddress;
        $this->_checkoutSession = \Mage::getSingleton('checkout/session');
    }

    public function getPrefix() {
        $prefix = $this->_checkoutSession->getCustomerPrefix();
        if (\Mage::helper('easycredit')->getCheckout()->isPrefixValid($prefix)) {
            return $prefix;
        }

        if (!$this->isLoggedIn()) {
            return $this->_billingAddress->getPrefix();
        }
        return $this->_customer->getPrefix();
    }

    public function getFirstname() {
        if (!$this->isLoggedIn()) {
            return $this->_billingAddress->getFirstname();
        }
        return $this->_customer->getFirstname();
    }

    public function getLastname() {
        if (!$this->isLoggedIn()) {
            return $this->_billingAddress->getLastname();
        }
        return $this->_customer->getLastname();
    }

    public function getEmail() {
        return $this->_billingAddress->getEmail();
    }

    public function getDob() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return $this->_customer->getDob();
    }

    public function getCompany() {
        return $this->_billingAddress->getCompany();
    }

    public function getTelephone() {
        return $this->_billingAddress->getTelephone();
    }

    public function isLoggedIn() {
        return $this->_customerSession->isLoggedIn();
    }

    public function getCreatedAt() {
        return $this->_customer->getCreatedAt();
    }

        /*$billingAddress = $quote->getBillingAddress();

        if ($this->_customerSession->isLoggedIn()) {
            $customer = $quote->getCustomer();
            $customerData = $this->_convertPersonalData($customer);
            $email = $customer->getEmail();
        } else {
            $email = $billingAddress->getEmail();
            $customerData = $this->_convertPersonalDataFromBillingAddress($billingAddress);
        }*/


    public function getOrderCount() {
        if (!$this->isLoggedIn()) {
            return 0;
        }

        return $this->_salesOrderCollection
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $this->_customer->getId())
            ->count();
    }
}
