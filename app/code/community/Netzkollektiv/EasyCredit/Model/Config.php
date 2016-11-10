<?php
class Netzkollektiv_EasyCredit_Model_Config {
    private function formatState($state) {
        return ucwords(str_replace('_', ' ', $state));
    }
    
    public function getOrderStates() {
        return array(
            Mage_Sales_Model_Order::STATE_NEW => Mage::helper('easycredit')->__($this->formatState(Mage_Sales_Model_Order::STATE_NEW)),
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT => Mage::helper('easycredit')->__($this->formatState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)),
            Mage_Sales_Model_Order::STATE_PROCESSING => Mage::helper('easycredit')->__($this->formatState(Mage_Sales_Model_Order::STATE_PROCESSING)),
            Mage_Sales_Model_Order::STATE_COMPLETE => Mage::helper('easycredit')->__($this->formatState(Mage_Sales_Model_Order::STATE_COMPLETE)),
            Mage_Sales_Model_Order::STATE_CLOSED => Mage::helper('easycredit')->__($this->formatState(Mage_Sales_Model_Order::STATE_CLOSED)),
            Mage_Sales_Model_Order::STATE_CANCELED => Mage::helper('easycredit')->__($this->formatState(Mage_Sales_Model_Order::STATE_CANCELED)),
            Mage_Sales_Model_Order::STATE_HOLDED => Mage::helper('easycredit')->__($this->formatState(Mage_Sales_Model_Order::STATE_HOLDED)),
            Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW => Mage::helper('easycredit')->__($this->formatState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW))
        );
    }
}