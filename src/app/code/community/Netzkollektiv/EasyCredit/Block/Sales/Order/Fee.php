<?php
class Netzkollektiv_EasyCredit_Block_Sales_Order_Fee extends Mage_Core_Block_Template
{
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    public function getSource()
    {
        return $this->getParentBlock()->getSource();
    }

    public function initTotals()
    {
        if ((float) $this->getOrder()->getBaseEasycreditAmount()) {
            $source = $this->getSource();
            $value  = $source->getEasycreditAmount();

            $this->getParentBlock()->addTotal(new Varien_Object(array(
                'code'   => 'easycredit',
                'strong' => false,
                'label'  => Mage::helper('easycredit')->__('Interest'),
                'value'  => $value
            )));
        }

        return $this;
    }
}
