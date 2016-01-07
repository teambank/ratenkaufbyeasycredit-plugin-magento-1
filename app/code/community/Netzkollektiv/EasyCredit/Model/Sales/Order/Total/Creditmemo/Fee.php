<?php
class Netzkollektiv_EasyCredit_Model_Sales_Order_Total_Creditmemo_Fee extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        if($order->getEasycreditAmountInvoiced() > 0) {

            $feeAmountLeft = $order->getEasycreditAmountInvoiced() - $order->getEasycreditAmountRefunded();
            $basefeeAmountLeft = $order->getBaseEasycreditAmountInvoiced() - $order->getBaseEasycreditAmountRefunded();

            if ($basefeeAmountLeft > 0) {
                $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmountLeft);
                $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmountLeft);
                $creditmemo->setEasycreditAmount($feeAmountLeft);
                $creditmemo->setBaseEasycreditAmount($basefeeAmountLeft);
            }

        } else {

            $feeAmount = $order->getEasycreditAmountInvoiced();
            $basefeeAmount = $order->getBaseEasycreditAmountInvoiced();

            $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $feeAmount);
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $basefeeAmount);
            $creditmemo->setEasycreditAmount($feeAmount);
            $creditmemo->setBaseEasycreditAmount($basefeeAmount);

        }

        return $this;
    }

}
