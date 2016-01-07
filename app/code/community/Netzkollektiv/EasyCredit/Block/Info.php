<?php
class Netzkollektiv_EasyCredit_Block_Info extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('easycredit/info.phtml');
    }

    public function getInfoAdditionalData($field)
    {
        return $this->escapeHtml($this->getMethod()->getInfoInstance()->getAdditionalInformation($field));
    }
}
