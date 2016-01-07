<?php
class Netzkollektiv_EasyCredit_Model_Observer {

    public function invoiceSaveAfter(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();

        if ($invoice->getBaseFeeAmount()) {
            $order = $invoice->getOrder();
            $order->setFeeAmountInvoiced($order->getFeeAmountInvoiced() + $invoice->getFeeAmount());
            $order->setBaseFeeAmountInvoiced($order->getBaseFeeAmountInvoiced() + $invoice->getBaseFeeAmount());
        }

        return $this;
    }

    public function creditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        $creditmemo = $observer->getEvent()->getCreditmemo();

        if ($creditmemo->getFeeAmount()) {
            $order = $creditmemo->getOrder();
            $order->setFeeAmountRefunded($order->getFeeAmountRefunded() + $creditmemo->getFeeAmount());
            $order->setBaseFeeAmountRefunded($order->getBaseFeeAmountRefunded() + $creditmemo->getBaseFeeAmount());
        }

        return $this;
    }

    public function expirePayment($observer) {
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
    }
}