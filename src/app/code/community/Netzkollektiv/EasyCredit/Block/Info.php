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

    public function getPaymentPlan()
    {
        $summary = \json_decode((string) $this->getInfo()->getAdditionalInformation('summary'));
        if ($summary === false || $summary === null) {
            return null;
        }
        return json_encode($summary);
    }
}
