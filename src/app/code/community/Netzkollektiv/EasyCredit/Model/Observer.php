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
        if ($invoice->getBaseEasycreditAmount()) {
            $order = $invoice->getOrder();
            $order->setEasycreditAmountInvoiced($order->getEasycreditAmountInvoiced() + $invoice->getEasycreditAmount());
            $order->setBaseEasycreditAmountInvoiced($order->getBaseEasycreditAmountInvoiced() + $invoice->getBaseEasycreditAmount());
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

        if ($creditmemo->getEasycreditAmount()) {
            $order = $creditmemo->getOrder();
            $order->setEasycreditAmountRefunded($order->getEasycreditAmountRefunded() + $creditmemo->getEasycreditAmount());
            $order->setBaseEasycreditAmountRefunded($order->getBaseEasycreditAmountRefunded() + $creditmemo->getBaseEasycreditAmount());
            $order->save();
        }

        return $this;
    }

    /**
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function removeInterest(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        $removeInterest = Mage::getStoreConfig('payment/easycredit/remove_interest');
        if ($order->getEasycreditAmount() > 0
            && $removeInterest
        ) {
            $order->setGrandTotal($order->getGrandTotal() - $order->getEasycreditAmount());
            $order->setBaseGrandTotal($order->getBaseGrandTotal() - $order->getBaseEasycreditAmount());

            $order->setEasycreditAmount(0);
            $order->setBaseEasycreditAmount(0);
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
            return $this;
        }

        $storage = new \Netzkollektiv\EasyCredit\Api\Storage();
        if ($storage->get('interest_amount') === null) {
            return $this;
        }

        $checkout = Mage::helper('easycredit')->getCheckout();
        $quoteBuilder = new \Netzkollektiv\EasyCredit\Api\QuoteBuilder();
        $ecQuote = $quoteBuilder->build();

        if (!$checkout->isValid($ecQuote)) {
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
                'Die Lieferadresse kann bei mit easyCredit-Ratenkauf bezahlten Bestellungen nicht im Nachhinein geändert werden.',
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
}
