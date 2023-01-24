<?php
class Netzkollektiv_EasyCredit_Block_Sales_Order_Fee extends Mage_Core_Block_Template
{
    /** @return Mage_Sales_Block_Order_Totals */
    protected function getSalesOrderBlock () {
        /** @var Mage_Sales_Block_Order_Totals */
        $parentBlock = $this->getParentBlock();
        return $parentBlock;
    }

    public function getOrder()
    {
        return $this->getSalesOrderBlock()->getOrder();
    }

    public function getSource()
    {
        return $this->getSalesOrderBlock()->getSource();
    }

    public function initTotals()
    {
        if ((float) $this->getOrder()->getBaseEasycreditAmount()) {
            $source = $this->getSource();
            $value  = $source->getEasycreditAmount();

            $this->getSalesOrderBlock()->addTotal(new Varien_Object(array(
                'code'   => 'easycredit',
                'strong' => false,
                'label'  => Mage::helper('easycredit')->__('Interest'),
                'value'  => $value
            )));
        }

        return $this;
    }
}
