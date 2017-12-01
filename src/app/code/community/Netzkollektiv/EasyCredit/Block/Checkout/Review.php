<?php
/**
 * EasyCredit Payment Extension for Magento Community Edition
 *
 * @category   Payment
 * @package    Netzkollektiv_EasyCredit
 * @author     Dominik Krebs (https://netzkollektiv.com)
 * @license    This work is free software, you can redistribute it and/or modify it
 */

class Netzkollektiv_EasyCredit_Block_Checkout_Review extends Mage_Checkout_Block_Onepage_Progress {
    public function getPreContractInformationUrl() {
        return $this->getQuote()->getPayment()
            ->getAdditionalInformation('pre_contract_information_url');
    }

    public function getRedemptionPlan() {
        return $this->getQuote()->getPayment()
            ->getAdditionalInformation('redemption_plan');
    }
}
