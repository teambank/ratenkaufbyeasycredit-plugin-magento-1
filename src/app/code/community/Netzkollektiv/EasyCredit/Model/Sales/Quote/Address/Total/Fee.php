<?php


class Netzkollektiv_EasyCredit_Model_Sales_Quote_Address_Total_Fee extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    protected $_code = 'easycredit';

    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $this->_setAmount(0);
        $this->_setBaseAmount(0);

        $items = $this->_getAddressItems($address);
        if (!count($items)) {
            return $this;
        }

        $quote = $address->getQuote();

        /**
         * @var Mage_Sales_Model_Quote_Payment $payment
         */
        $payment = Mage::getSingleton('checkout/session')
            ->getQuote()
            ->getPayment();

        $amount = $payment->getAdditionalInformation('interest_amount');

        if ($amount == null || $amount <= 0) {
            return $this;
        }

        $address->setEasycreditAmount($amount);
        $address->setBaseEasycreditAmount($amount);

        $quote->setEasycreditAmount($amount);

        $address->setGrandTotal($address->getGrandTotal() + $address->getEasycreditAmount());
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $address->getBaseEasycreditAmount());

        return $this;
    }

    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $amount = $address->getEasycreditAmount();
        if ($amount <= 0) {
            return $this;
        }
        $address->addTotal(array(
            'code' => $this->getCode(),
            'title' => Mage::helper('easycredit')->__('Interest'),
            'value' => $amount
        ));
        return $this;
    }

}
