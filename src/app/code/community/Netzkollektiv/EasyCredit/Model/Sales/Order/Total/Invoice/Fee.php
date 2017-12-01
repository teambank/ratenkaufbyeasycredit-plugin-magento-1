<?php
class Netzkollektiv_EasyCredit_Model_Sales_Order_Total_Invoice_Fee extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $feeAmountLeft = $order->getEasycreditAmount() - $order->getEasycreditAmountInvoiced();
        $baseFeeAmountLeft = $order->getBaseEasycreditAmount() - $order->getBaseEasycreditAmountInvoiced();

        if (abs($baseFeeAmountLeft) < $invoice->getBaseGrandTotal()) {
            $invoice->setGrandTotal($invoice->getGrandTotal() + $feeAmountLeft);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseFeeAmountLeft);
        } else {
            $feeAmountLeft = $invoice->getGrandTotal() * -1;
            $baseFeeAmountLeft = $invoice->getBaseGrandTotal() * -1;

            $invoice->setGrandTotal(0);
            $invoice->setBaseGrandTotal(0);
        }

        $invoice->setEasycreditAmount($feeAmountLeft);
        $invoice->setBaseEasycreditAmount($baseFeeAmountLeft);

        return $this;
    }

}
