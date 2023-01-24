<?php
class Netzkollektiv_Easycredit_Block_Sales_Order_Totals_Fee extends Mage_Core_Block_Template {

    /** @return Mage_Sales_Block_Order_Totals */
    protected function getSalesOrderBlock () {
        /** @var Mage_Sales_Block_Order_Totals */
        $parentBlock = $this->getParentBlock();
        return $parentBlock;
    }

    public function initTotals() {

        $field = 'easycredit_amount';
        if((float) $this->getSalesOrderBlock()->getSource()->getData($field) == 0) {
            return $this;
        }

        $total = new Varien_Object(array(
            'code'  => $this->getNameInLayout(),
            'value' => $this->getSalesOrderBlock()->getSource()->getData($field),
            'label' => Mage::helper('easycredit')->__('Interest')
        ));
        $after = 'subtotal';

        $this->getSalesOrderBlock()->addTotal($total, $after);
        return $this;
    }
}
