<?php
class Netzkollektiv_EasyCredit_Model_Observer {

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function invoiceSaveAfter(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order_Invoice $invoice
         */
        $invoice = $observer->getEvent()->getInvoice();

        if ($invoice->getBaseFeeAmount()) {
            $order = $invoice->getOrder();
            $order->setFeeAmountInvoiced($order->getFeeAmountInvoiced() + $invoice->getFeeAmount());
            $order->setBaseFeeAmountInvoiced($order->getBaseFeeAmountInvoiced() + $invoice->getBaseFeeAmount());
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function creditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        /**
         * @var Mage_Sales_Model_Order_Creditmemo $creditmemo
         */
        $creditmemo = $observer->getEvent()->getCreditmemo();

        if ($creditmemo->getFeeAmount()) {
            $order = $creditmemo->getOrder();
            $order->setFeeAmountRefunded($order->getFeeAmountRefunded() + $creditmemo->getFeeAmount());
            $order->setBaseFeeAmountRefunded($order->getBaseFeeAmountRefunded() + $creditmemo->getBaseFeeAmount());
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function expirePayment(Varien_Event_Observer $observer) {
        $quote = $observer->getEvent()
            ->getQuote();

        if (!$quote->getId()
            || Netzkollektiv_EasyCredit_Model_Payment::CODE != $quote->getPayment()->getMethod()
        ) {
            return;
        }

        $checkout = Mage::helper('easycredit')->getCheckout();
        $ecQuote = new \Netzkollektiv\EasyCredit\Api\Quote(); 

        if (!$checkout->isAmountValid($ecQuote)
            || !$checkout->verifyAddressNotChanged($ecQuote)
        ) {
            $checkout->clear();
        }

        return $this;
    }

    public function preventShippingAddressChange(Varien_Event_Observer $observer) {
        $address = $observer->getEvent()->getAddress();
        if ($address->getAddressType() == 'shipping'
            && Netzkollektiv_EasyCredit_Model_Payment::CODE == $address->getOrder()->getPayment()->getMethod() 
        ) {
            throw new Mage_Core_Exception(implode("\n",array(
                'Die Lieferadresse kann bei mit ratenkauf by easyCredit bezahlten Bestellungen nicht im Nachhinein geändert werden.',
                'Bitte stornieren Sie die Bestellung und Zahlung hierfür und legen Sie eine neue Bestellung an.'
            )));
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function setNewOrderState(Varien_Event_Observer $observer) {
        $event = $observer->getEvent();

        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $event->getOrder();
        $payment = $order->getPayment()->getMethodInstance()->getCode();

        $newOrderState = Mage::getStoreConfig('payment/easycredit/order_status');

        if ($payment == Netzkollektiv_EasyCredit_Model_Payment::CODE && !empty($newOrderState)) {
            $order->setState($newOrderState);
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function importCustomerPrefix(Varien_Event_Observer $observer)
    {
        $prefix = Mage::app()->getRequest()->getParam('easycredit-customer-prefix');

        if (!Mage::helper('easycredit')->getCheckout()->isPrefixValid($prefix)) {
            return $this;
        }

        Mage::getSingleton('checkout/session')
            ->setCustomerPrefix($prefix);

        return $this;
    }

    public function saveCustomerPrefix(Varien_Event_Observer $observer) {
        /**
         * @var Mage_Checkout_Model_Session $checkoutSession
         */
        $checkoutSession = Mage::getSingleton('checkout/session');

        $event = $observer->getEvent();

        /**
         * @var Mage_Sales_Model_Order $order
         */
        $order = $event->getOrder();
        $payment = $order->getPayment();
        $paymentCode = $payment->getMethodInstance()->getCode();
        $customerId = $order->getCustomerId();

        $prefix = $checkoutSession->getData('customer_prefix');

        if (
            $order->getCustomerId()
            && $paymentCode == Netzkollektiv_EasyCredit_Model_Payment::CODE
            && Mage::getStoreConfig('payment/easycredit/save_customer_prefix')
            && !empty($prefix)
        ) {
            Mage::getModel('customer/customer')
                ->load($customerId)
                ->setPrefix($prefix)
                ->save();
        }

        return $this;
    }
}
