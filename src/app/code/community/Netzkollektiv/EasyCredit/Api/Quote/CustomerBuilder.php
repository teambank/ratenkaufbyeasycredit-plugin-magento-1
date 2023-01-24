<?php
namespace Netzkollektiv\EasyCredit\Api\Quote;

use Magento\Sales\Model\ResourceModel\Order\Collection as SalesOrderCollection;
use Magento\Customer\Model\Session as CustomerSession;

use Teambank\RatenkaufByEasyCreditApiV3 as ApiV3;

class CustomerBuilder
{
    protected $customer;
    protected $customerSession;
    protected $checkoutSession;

    public function __construct() {
        $this->customerSession = \Mage::getSingleton('customer/session');
        $this->checkoutSession = \Mage::getSingleton('checkout/session');
    }

    public function getPrefix($customer, $quote) {
        $prefix = $this->checkoutSession->getCustomerPrefix();

        if (!$this->customerSession->isLoggedIn()) {
            $prefix = $quote->getBillingAddress()->getPrefix();
        }
        if (!$prefix) {
            $prefix = $customer->getPrefix();

        }
        if (\Mage::helper('easycredit')->getCheckout()->isPrefixValid($prefix)) {
            return $prefix;
        }
    }

    public function build($quote)
    {
        if ($this->customerSession->isLoggedIn()) {
            $customer = $quote->getCustomer();
        } else {
            $customer = $quote->getBillingAddress();
        }

        return new ApiV3\Model\Customer(
            [
            'gender' => $this->getPrefix($customer, $quote),
            'firstName' => $customer->getFirstname(),
            'lastName' => $customer->getLastname(),
            'birthDate' => $customer->getDob(),
            'contact' => new ApiV3\Model\Contact(
                [
                'email' => $quote->getBillingAddress()->getEmail()
                ]
            ),
            'companyName' => $quote->getBillingAddress()->getCompany()
            ]
        );
    }
}
