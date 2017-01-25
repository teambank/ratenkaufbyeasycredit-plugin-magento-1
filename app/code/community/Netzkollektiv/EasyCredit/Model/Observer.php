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
        /**
         * @var Mage_Sales_Model_Quote $quote
         */
        $quote = $observer->getEvent()
            ->getQuote();

        $amount = $quote->getGrandTotal();
        $authorizedAmount = $quote->getPayment()
            ->getAdditionalInformation('authorized_amount');
        $interestAmount = $quote->getPayment()
            ->getAdditionalInformation('interest_amount');

        if (
            $authorizedAmount > 0 
            && $interestAmount > 0 
            && $amount != $authorizedAmount + $interestAmount
        ) {
            $quote->getPayment()->unsAdditionalInformation()->save();
        }

        return $this;
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
        $payment = $order->getPayment();
        $paymentCode = $payment->getMethodInstance()->getCode();

        $store = Mage::app()->getStore()->getStoreId();
        $newOrderState = Mage::getStoreConfig('payment/easycredit/order_status', $store);

        if ($paymentCode == Netzkollektiv_EasyCredit_Model_Payment::CODE && !empty($newOrderState)) {
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

        Mage::getSingleton('checkout/session')->setData('customer_prefix', $prefix);

        return $this;
    }

    /**
     * @param int $severity
     * @param string $message
     */
    private function log($severity, $message) {
        if ($severity === Zend_Log::DEBUG || $severity === Zend_Log::INFO) {
            $store = Mage::app()->getStore()->getStoreId();
            $debug = Mage::getStoreConfig('payment/easycredit/debug_logging', $store);

            if ($debug) {
                Mage::log($message, $severity, 'easycredit.log');
            }
        } else {
            Mage::log($message, $severity, 'exception.log');
        }
    }
}
