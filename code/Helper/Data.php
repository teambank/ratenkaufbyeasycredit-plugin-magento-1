<?php
/**
 * EasyCredit Payment Extension for Magento Community Edition
 *
 * @category   Payment
 * @package    Netzkollektiv_EasyCredit
 * @author     Dominik Krebs (https://netzkollektiv.com)
 * @license    This work is free software, you can redistribute it and/or modify it
 */

class Netzkollektiv_EasyCredit_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getInstallmentValues() {

        $amount = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();  

        if (!isset($this->_installmentValues[$amount])) {
            $this->_installmentValues[$amount] = Mage::getSingleton('easycredit/api')
                ->callModelCalculation($amount);
        }

        $result = $this->_installmentValues[$amount];

        $values = array();
        foreach ($result->ergebnis as $installment) {
            $values[] = array(
                'label' => $this->_formatInstallmentValue($installment),
                'value' => (int)$installment->zahlungsplan->anzahlRaten
            );
        }
        return $values;
    }

    protected function _formatInstallmentValue($installment) {
        $paymentPlan = $installment->zahlungsplan;
        return sprintf('%d Raten à %0.2f€ (%d x %0.2f€, %d x %0.2f€)',
            (int)   $paymentPlan->anzahlRaten,
            (float) $paymentPlan->betragRate,
            (int)   $paymentPlan->anzahlRaten - 1,
            (float) $paymentPlan->betragRate,
            1,
            (float) $paymentPlan->betragLetzteRate
        );
    }
}
