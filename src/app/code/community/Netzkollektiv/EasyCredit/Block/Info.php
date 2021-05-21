<?php
class Netzkollektiv_EasyCredit_Block_Info extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('easycredit/info.phtml');
    }

    public function getInfoAdditionalData($field)
    {
        return $this->getMethod()->getInfoInstance()->getAdditionalInformation($field);
    }

    public function getPaymentPlan() {
        $paymentPlan = json_decode($this->getInfoAdditionalData('payment_plan'));
		if (!is_object($paymentPlan)) {
            return '';
        }

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
